<?php

namespace App\Http\Controllers\Api\v1\Organization;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Customer\CreateCustomerRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use App\Http\Resources\CustomerResource;


class CustomerController extends Controller
{

    use ApiResponser;




    public function createCustomer(CreateCustomerRequest $request)
    {

        try {
            $validated_data = $request->validated();

            DB::beginTransaction();

            $supplier =  Customer::create([
                ...$validated_data,
                'user_id' => auth()->user()->id,
            ]);

            return $this->successResponse('Customer created ', 201, [
                'customer' => new CustomerResource($supplier)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("CustomerController@createCustomer", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }

    public function listCustomers()
    {

        try {



            $user = auth()->user();


            $perPage =  request('per_page') ?? 20;

            $customers = Customer::where('user_id', $user->id)
                ->get()->orderBy('created_at', 'desc')->paginate($perPage);


            $pagination = [
                'current_page' => $customers->currentPage(),
                'per_page' => $customers->perPage(),
                'total' => $customers->total(),
                'last_page' => $customers->lastPage(),
                'from' => $customers->firstItem(),
                'to' => $customers->lastItem(),
            ];






            return $this->successResponse('Customer retrieved', 200, [
                'customers' =>  $customers->items(),
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            Log::error("CustomerController@listCustomers", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getCustomer(int $customer_id)
    {

        try {

            $user = auth()->user();



            $customer = Customer::where('id', $customer_id)->where('user_id', $user->id)->firstOrFail();

            return $this->successResponse('Customer retrieved', 200, [
                'customer' =>  new CustomerResource($customer)

            ]);
        } catch (\Exception $e) {
            Log::error("SupplierController@getSupplier", [
                "error" => $e->getMessage(),
                'customer_id' => $customer_id
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function updateCustomer(int $supplier_id,  CreateCustomerRequest $request)
    {
        try {

            $user = auth()->user();

            $validated_data = $request->validated();

            $supplier = Customer::where('id', $supplier_id)
                ->where('user_id' . $user->id)->update($validated_data);

            $supplier->refresh();

            return $this->successResponse('Customer updated', 200, [
                'customer' =>  new CustomerResource($supplier)
            ]);
        } catch (\Exception $e) {

            Log::error("CustomerController@updateCustomer", ["error" => $e->getMessage(), 'supplier_id' => $supplier_id, 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
