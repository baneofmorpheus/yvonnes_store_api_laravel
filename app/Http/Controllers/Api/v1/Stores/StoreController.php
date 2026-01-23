<?php


namespace App\Http\Controllers\Api\v1\Stores;

use App\Http\Controllers\Controller;

use App\Http\Requests\Store\AddUserToStoreRequest;
use App\Http\Requests\Store\RemoveUserFromStoreRequest;
use App\Http\Requests\Store\UpdateStoreRequest;
use App\Http\Resources\UserResource;
use App\Http\Resources\StoreResource;
use App\Http\Resources\UserCollection;
use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
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
            Log::error("StoreController@addUserToStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }




    public function listUsers(int $store_id)
    {

        try {


            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }
            $perPage = (int) request('per_page') ?? 20;

            $store = Store::find($store_id);
            $users = $store->users()->where('users.id', '!=', auth()->id())      // use the relation
                ->orderBy('users.name', 'asc') // order by user name
                ->paginate($perPage);
            return $this->successResponse('Users retrieved', 200, [
                'users' =>  new UserCollection($users),


            ]);
        } catch (\Exception $e) {
            Log::error("UserController@listUsers", [
                "error" => $e->getMessage(),
                'store_id' => $store_id
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getAnalytics(int $store_id)
    {

        try {


            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }

            $customer_count = Customer::where('store_id', $store_id)->count();
            $invoice_count = invoice::where('store_id', $store_id)->count();
            $monthly_total = Invoice::where('store_id', $store_id)
                ->whereYear('created_at', now()->year)
                ->selectRaw('MONTH(created_at) as month, SUM(total) as total')
                ->groupBy('month')
                ->pluck('total', 'month');


            $monthly_total = collect(range(1, 12))->map(fn($m) => [
                'month' => $m,
                'total' => $monthly_total[$m] ?? 0,
            ]);
            return $this->successResponse('Data retrieved', 200, [
                'customer_count' =>  $customer_count,
                'invoice_count' =>  $invoice_count,
                'monthly_total' =>  $monthly_total,


            ]);
        } catch (\Exception $e) {
            Log::error("StoreController@getAnalytics", [
                "error" => $e->getMessage(),
                'store_id' => $store_id
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
