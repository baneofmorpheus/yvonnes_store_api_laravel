<?php


namespace App\Http\Controllers\Api\v1\Stores;

use  App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;

use App\Http\Requests\Store\AddUserToStoreRequest;
use App\Http\Requests\Store\RemoveUserFromStoreRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\StoreService;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponser;


class StoreController extends Controller
{



    use ApiResponser;


    public function addUserToStore(int $store_id, AddUserToStoreRequest $request)
    {

        try {
            $validated = $request->validated();

            if (auth()->user()->getStoreRole($store_id) !== 'owner') {
                return $this->errorResponse('You dont have owner access to this store', 403);
            }


            $user =  User::where('email', $validated['email'])->first();

            if (!isset($user)) {

                $user = User::create([
                    'email' => $validated['email'],
                    'name' => $validated['name'],
                ]);
            }

            $user->refresh();

            StoreService::addUserToStore(
                $user->id,
                $store_id,
                'staff'
            );

            /**
             * If default store is empty then this is a new user
             */
            return ApiResponse::validResponse(
                'User added to store successfully',
                [
                    'user' =>  new UserResource($user),
                ],
                201
            );
        } catch (\Exception $e) {
            Log::error("StoreController@addUserToStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);
        }
    }
    public function removeUserStore(int $store_id, RemoveUserFromStoreRequest $request)
    {

        try {
            $validated = $request->validated();


            $user =  User::where('email', $validated['email'])->firstOrFail();



            if (auth()->user()->getStoreRole($store_id) !== 'owner') {
                return $this->errorResponse('You dont have owner access to this store', 403);
            }


            StoreService::removeUserFromStore(
                $user->id,
                $store_id,
            );

            /**
             * If default store is empty then this is a new user
             */
            return ApiResponse::validResponse(
                'User removed from store',
                [],
                200
            );
        } catch (\Exception $e) {
            Log::error("StoreController@removeUserStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);
        }
    }
}
