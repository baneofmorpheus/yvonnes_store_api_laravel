<?php


namespace App\Http\Controllers\Api\v1\Stores;

use App\Http\Controllers\Controller;

use App\Http\Requests\Store\AddUserToStoreRequest;
use App\Http\Requests\Store\RemoveUserFromStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use App\Models\User;
use App\Models\Store;
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
            return $this->successResponse(
                'User added to store successfully',
                201,

                [
                    'user' =>  new UserResource($user),
                ],
            );
        } catch (\Exception $e) {
            Log::error("StoreController@addUserToStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function updateStore(int $store_id, UpdateStoreRequest $request)
    {

        try {
            $validated = $request->validated();

            if (auth()->user()->getStoreRole($store_id) !== 'owner') {
                return $this->errorResponse('You dont have owner access to this store', 403);
            }



            $store =   Store::where('id', $store_id)->where('user_id', auth()->user()->id)
                ->firstorFail();

            $store->update([
                'name' => $validated['name']
            ]);

            $store->refresh();


            /**
             * If default store is empty then this is a new user
             */
            return $this->successResponse(
                'Store updated successfully',
                200,

                [
                    'store' =>  new StoreResource($store),
                ],
            );
        } catch (\Exception $e) {
            dd($e);
            Log::error("StoreController@addUserToStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
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
            return $this->successResponse(
                'User removed from store',
                200,

                [],
            );
        } catch (\Exception $e) {
            Log::error("StoreController@removeUserStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
