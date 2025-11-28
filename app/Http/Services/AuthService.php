<?php

namespace App\Services;

use App\Models\User;

use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendLoginCodeEmail;


class AuthService
{





    static function sendLoginCodeEmail(User $user)
    {


        $user->update([
            'login_code' => rand(1000, 9999),
            'login_code_expires_at' => Carbon::now()->addMinutes(10)
        ]);

        $user->refresh();
        Mail::to($user->email)->queue(new SendLoginCodeEmail([
            'code' => $user->login_code,
            'email'=>$user->email
        ]));
    }
}
