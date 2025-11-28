<?php

namespace App\Services;

use App\Models\Media;
use App\Models\Feedback;
use App\Models\SlackToken;
use App\Models\Widget;
use Exception;

use App\Enums\Feedback\FeedbackType;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;




class SlackService
{



    static function sendMessage(Feedback $feedback)
    {

        $payload = self::generateMessage($feedback);

        $slack_token = SlackToken::where('widget_id', $feedback->widget_id)->first();

        if (!$slack_token || !$slack_token->channel_id) {

            Log::error("SlackService@sendMessage", [
                "payload" => $payload,
                "slack_token" => $slack_token,
                "error" => 'no channel_id set'
            ]);

            return;
        }

        $payload['channel'] = $slack_token->channel_id;
        $payload['token'] = $slack_token->bot_token;
        $payload['blocks'] = json_encode($payload['blocks']);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
        ])->asForm()->post(config('services.slack.api_url') . "/chat.postMessage", $payload);



        if ($response->failed() || !$response->json('ok')) {
            Log::error("SlackService@sendMessage", [
                "payload" => $payload,
                "error" => $response->body()
            ]);
        }
    }

    static function exchangeCodeForToken(string $widet_uuid, string $code)
    {

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ])->asForm()->post(config('services.slack.api_url') . "/oauth.v2.access", [
            "client_id" => config('services.slack.client_id'),
            'redirect_uri' => config('app.url') . "/api/v1/slack/oauth",

            "code" => $code,
            "client_secret" => config('services.slack.client_secret')

        ]);

        if ($response->failed() ||  !$response->json('ok')) {
            Log::error("SlackService@exchangeCodeForToken", [
                "widget_uuid" => $widet_uuid,
                'redirect_uri' => config('app.url') . "/api/v1/slack/oauth",

                "code" => $code,
                "error" => $response->body()
            ]);

            throw new Exception('SlackService@exchangeCodeForToken');
        }

        $widget = Widget::where('uuid', $widet_uuid)->firstOrFail();

        $slack_token = SlackToken::where('widget_id', $widget->id)->first();

        if ($slack_token) {

            $slack_token->update([
                'bot_token' =>  $response['access_token'],
                'auth_user_id' =>  $response['authed_user']['id'],
                'user_token' =>  $response['authed_user']['access_token'],
                'bot_user_id' =>  $response['bot_user_id'],
                'channel_id' => '',
                'scopes' => $response['scope'],
            ]);
            $slack_token->refresh();
        } else {
            $slack_token = SlackToken::create([
                'bot_token' =>  $response['access_token'],
                'auth_user_id' =>  $response['authed_user']['id'],
                'user_token' =>  $response['authed_user']['access_token'],
                'team_name' => $response['team']['name'],
                'user_scopes' => $response['authed_user']['scope'],
                'bot_user_id' =>  $response['bot_user_id'],
                'channel_id' => '',
                'scopes' => $response['scope'],
                'widget_id' => $widget->id
            ]);
        }


        return $slack_token;
    }

    static function registerChannel(string $widget_uuid, string $channel_id)
    {


        $widget = Widget::where('uuid', $widget_uuid)->firstOrFail();

        $slack_token = SlackToken::where('widget_id', $widget->id)->firstOrFail();

        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ])->asForm()->post(config('services.slack.api_url') . "/conversations.invite", [
            'channel' => $channel_id,

            'token' => $slack_token->user_token,
            'users' => $slack_token->bot_user_id

        ]);

        if ($response->failed() || !$response->json('ok') && $response->json('error') !== 'already_in_channel') {
            Log::error("SlackService@registerChannel", [
                "widget_uuid" => $widget_uuid,
                "channel_id" => $channel_id,
                "error" => $response->body()
            ]);

            throw new Exception('SlackService@registerChannel');
        }

        $slack_token->update(['channel_id' => $channel_id]);
        $slack_token->refresh();

        $payload = [
            "text" => "Qatchup notifications have been enabled for this channel, you will receive notifications here.",
            'token' => $slack_token->bot_token,
            'channel' => $slack_token->channel_id,

        ];


        $response = Http::withHeaders([
            'Content-Type' => 'application/json; charset=utf-8',
        ])->asForm()->post(config('services.slack.api_url') . "/chat.postMessage", $payload);



        if ($response->failed() || !$response->json('ok')) {
            Log::error("SlackService@registerChannel-sendMessage", [
                "payload" => $payload,
                "error" => $response->body()
            ]);
        }



        return $slack_token;
    }
    static function disconnectSlack()
    {

        $user = auth()->user();
        $slack_token = $user->slackToken;

        if (!$slack_token) {

            return;
        }




        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ])->asForm()->post(config('services.slack.api_url') . "/auth.revoke", [

            'token' => $slack_token->user_token,

        ]);

        if ($response->failed() || !$response->json('ok')) {
            Log::error("SlackService@disconnectSlack@userToken", [
                "error" => $response->body()
            ]);
        }


        $response = Http::withHeaders([
            'Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8',
        ])->asForm()->post(config('services.slack.api_url') . "/auth.revoke", [

            'token' => $slack_token->bot_token,

        ]);

        if ($response->failed() || !$response->json('ok')) {
            Log::error("SlackService@disconnectSlack@botToken", [
                "error" => $response->body()
            ]);
        }

        $slack_token->delete();
        return;
    }

    static function getChannels(string $widget_uuid)
    {



        $slack_token = SlackToken::whereHas('widget', function ($query) use ($widget_uuid) {
            $query->where('uuid', $widget_uuid);
        })->firstOrFail();

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $slack_token->user_token,
        ])->get(config('services.slack.api_url') . "/users.conversations?types=public_channel,private_channel");




        if ($response->failed()) {
            Log::error("SlackService@getChannels", [
                "widget_uuid" => $widget_uuid,
                "error" => $response->body()
            ]);

            throw new Exception('SlackService@getChannels');
        }


        $response = $response->json();




        $channels = collect($response['channels']);

        return $channels->filter(function ($single_channel) {

            return isset($single_channel['is_channel']);
        })->values()->toArray();
    }

    static function generateMessage(Feedback $feedback)
    {




        $feedback_types = array_map(fn($single_type)
        => strtolower($single_type->name), FeedbackType::cases());


        if (!in_array($feedback->type, $feedback_types)) {
            throw new Exception('Slackservice@generateMessage invalid feedback type ' . $feedback->type);
        }

        $title = Str::title(str_replace('_', ' ', $feedback->type));

        $images =  $feedback->images->map(function (Media $media) {
            return [
                "type" => "image",
                "alt_text" => $media->name,
                'image_url' => $media->type === 'custom' ? $media->path  : Storage::temporaryUrl($media->path,   now()->addDays(6))

            ];
        })->toArray();






        $payload  = [
            [
                "type" => "header",
                "text" => [
                    "type" => "plain_text",
                    "text" => $title,
                    "emoji" => true,
                ],
            ],

            [
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" => ":bust_in_silhouette:" . ($feedback->name  ? " *{$feedback->name}" : "")
                            . ($feedback->email ? " ({$feedback->email})* " : "")
                            . " \n {$feedback->text}",
                    ],
                ],
            ],


            ...($images ? [[
                "type" => "context",
                "elements" => $images
            ]] : []),
            ...($feedback->rating && $feedback->type === 'rating' ? [[
                "type" => "section",
                "fields" => [
                    [
                        "type" => "mrkdwn",
                        "text" =>  str_repeat(':star: ', $feedback->rating) . "/{$feedback->rating} " . (intval($feedback->rating) > 1 ? "stars" : "star"),

                    ],
                ],
            ]] : []),
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => Carbon::parse($feedback->created_at)->format('d M Y \a\t h:i A T'),
                ],
            ],
            [
                "type" => "actions",
                "elements" => [
                    [
                        "type" => "button",
                        "style" => "primary",
                        "text" => [
                            "type" => "plain_text",
                            "text" => "View in Qatchup",
                            "emoji" => true,
                        ],
                        "url" => config('app.frontend_url') . "/feedback/{$feedback->id}",
                    ],
                    [
                        "type" => "button",
                        "text" => [
                            "type" => "plain_text",
                            "text" => "Reply via mail",
                            "emoji" => true,
                        ],
                        "url" => "mailto:{$feedback->email}?subject=" . rawurlencode("Feedback Response"),
                    ],
                ],
            ],
        ];

        $payload = array_values(($payload));
        return ['blocks' => $payload];
    }
}
