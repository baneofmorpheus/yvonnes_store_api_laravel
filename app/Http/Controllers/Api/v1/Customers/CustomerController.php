<?php

namespace App\Http\Controllers\Api\v1\Customers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Customer\CreateCustomerRequest;
use App\Http\Requests\Customer\UpdateCustomerRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;
use App\Http\Resources\CustomerCollection;


class CustomerController extends Controller
{

    use ApiResponser;




    public function createCustomer(CreateCustomerRequest $request)
    {

        try {
            $validated_data = $request->validated();

            DB::beginTransaction();

            $supplier =  Customer::create(
                $validated_data,
            );

            return $this->successResponse('Customer created ', 201, [
                'customer' => new CustomerResource($supplier)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("CustomerController@createCustomer", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }



    public function searchCustomers(int $store_id)
    {

        try {





            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }




            $customers = Customer::search(
                request('query') ?? '',
                function ($meilsearch, string $query, array $options) {
                    $options['attributesToHighlight'] =  ['name', 'phone_number'];
                    return $meilsearch->search($query, $options);
                }
            )->where('store_id', $store_id)

                ->orderBy('created_at', 'desc')->get();




            return $this->successResponse('Searched Customers retrieved', 200, [
                'customers' =>  CustomerResource::collection($customers),
            ]);
        } catch (\Exception $e) {
            Log::error("CustomerController@searchCustomers", ["error" => $e->getMessage(), 'query' =>    request('query')]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }



    public function listCustomers(int $store_id)
    {

        try {

            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }


            $perPage = (int) request('per_page') ?? 20;


            $customers = Customer::where('store_id', $store_id)
                ->orderBy('created_at', 'asc')->paginate($perPage);






            return $this->successResponse('Customer retrieved', 200, [
                'customers' =>  new CustomerCollection($customers)
            ]);
        } catch (\Exception $e) {
            Log::error("CustomerController@listCustomers", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getCustomer(int $customer_id)
    {

        try {

            $customer = Customer::where('id', $customer_id)->firstOrFail();

            if (!auth()->user()->storeBelongsToUser($customer->store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }



            return $this->successResponse('Customer retrieved', 200, [
                'customer' =>  new CustomerResource($customer)

            ]);
        } catch (\Exception $e) {
            Log::error("CustomerController@getCustomer", [
                "error" => $e->getMessage(),
                'customer_id' => $customer_id
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function updateCustomer(int $customer_id,  UpdateCustomerRequest $request)
    {
        try {


            $validated_data = $request->validated();

            $customer = Customer::where('id', $customer_id)
                ->firstOrFail();

            if (!auth()->user()->storeBelongsToUser($customer->store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }

            $customer->update($validated_data);

            $customer->refresh();
            return $this->successResponse('Customer updated', 200, [
                'customer' =>  new CustomerResource($customer)
            ]);
        } catch (\Exception $e) {

            Log::error("CustomerController@updateCustomer", [
                "error" => $e->getMessage(),
                'payload' => request()->all()
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }



    public function deleteCustomer(int $customer_id)
    {
        try {


            $customer = Customer::where('id', $customer_id)
                ->firstOrFail();


            if (auth()->user()->getStoreRole($customer->store_id) !== 'owner') {
                return $this->errorResponse('You dont have owner access to this store', 403);
            }
            $customer->delete();



            return $this->successResponse('Customer deleted', 200, []);
        } catch (\Exception $e) {

            Log::error("CustomerController@deleteCustomer", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
