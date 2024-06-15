<?php

namespace App\Http\Controllers;

use  App\Helpers\ApiResponse;

use App\Http\Requests\AuthenticationRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use App\Services\Auth\AuthService;
use App\Services\Auth\StoreService;
use Illuminate\Support\Facades\Log;


class PostController extends Controller
{


    public function login(AuthenticationRequest $request)
    {

        try {
            $validated = $request->validated();
            $default_store = null;

            $user =   AuthService::getOrCreateUserWithGoogleToken($validated['token']);

            if (isset($user)) {

                $default_store = StoreService::getUserDefaultStore($user->id);
            }



            /**
             * If default store is empty then this is a new user
             */
            return ApiResponse::validResponse(
                'Login Success',
                [
                    'login_type' => $validated['login_type'],
                    'user' =>  new UserResource($user),
                    'default_store' => !!$default_store ? new StoreResource($default_store) : null
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error("AuthService@isValidGoogleToken", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);
        }
    }
}
