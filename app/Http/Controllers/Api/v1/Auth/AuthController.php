<?php

namespace App\Http\Controllers\Api\v1\Auth;

use App\Services\UtilityService;
use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Auth\SocialAuthRequest;
use App\Models\User;
use App\Models\LoginHistory;
use Illuminate\Support\Facades\DB;
use Laravel\Socialite\Facades\Socialite;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;
use  App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use App\Services\StoreService;

use function PHPUnit\Framework\isNull;

class AuthController extends Controller
{

    use ApiResponser;



    public function loginSocial(SocialAuthRequest $request)
    {
        try {
            $validated_data = $request->validated();
            $oauth_user = Socialite::driver($validated_data['provider'])->userFromToken($validated_data['access_token']);

            $user = User::withTrashed()->where('email', $oauth_user->getEmail())->first();


            $full_name = explode(' ', $oauth_user->getName());

            DB::beginTransaction();



            if (!isset($user)) {
                $user = User::create([
                    'email' => $oauth_user->getEmail(),
                    'name' => $full_name[0] . " " . $full_name[1] ?? '',
                    'provider' => $validated_data['provider'],
                    'provider_id' => $oauth_user->getId(),
                ]);

                StoreService::createStore($user->id);
            }

            $this->trackLogin($user->id);

            $user->update([
                ...(isNull($user->first_name) || isNull($user->last_name) ? [

                    'first_name' => $full_name[0],
                    'last_name' => $full_name[1] ?? '',


                ] : []),
                'token' => UtilityService::generateUniqueId(User::class, 'token'),
                'token_expires_at' => Carbon::now()->addMonth()
            ]);

            $default_store_user = StoreService::getDefaultStoreUser($user->id);


            $user->refresh();

            DB::commit();


            return $this->successResponse('Login successful', 200, [

                'default_store' =>  new StoreResource($default_store_user->store),
                'role' =>  $default_store_user->role,
                'token' =>   $user->token,

                'user' => new UserResource($user)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("AuthController@loginSocial", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }



    public function trackLogin(int $user_id)
    {

        $ip = request()->ip();

        if (!LoginHistory::where('ip_address', $ip)->first()) {
        }
        LoginHistory::create([
            'user_id' => $user_id,
            'ip_address' =>  $ip,
            'country' => 'not available',
            'browser' => request()->userAgent(),
            'date' => now(),
        ]);
    }
}
