<?php

namespace App\Services\Auth;


use App\Models\User;
use Exception;

class AuthService
{
    public static function getOrCreateUserWithGoogleToken(array $verification_data): User|null
    {

        $auth = app('firebase.auth');
        $verified_id_token = auth->verifyIdToken($verification_data['id_token']);

        $uid = $verified_id_token->claims()->get('sub');
        $user = User::where('google_id', $uid)->first();

        if (!isset($user)) {
            $google_user = $auth->getUser($uid);

            $user = User::create([
                'name' => $google_user->displayName,
                'email' => $google_user->email,
                'google_id' => $uid,

            ]);
        }
        return $user;
    }
}
