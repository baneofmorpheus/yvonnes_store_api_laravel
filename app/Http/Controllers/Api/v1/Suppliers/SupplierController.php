<?php

namespace App\Http\Controllers\Api\v1\Suppliers;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Supplier\CreateSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Http\Resources\SupplierResource;
use App\Http\Resources\SupplierCollection;


class SupplierController extends Controller
{

    use ApiResponser;




    public function createSupplier(CreateSupplierRequest $request)
    {

        try {
            $validated_data = $request->validated();

            DB::beginTransaction();

            $supplier =  Supplier::create($validated_data);

            return $this->successResponse('Supplier created ', 201, [
                'supplier' => new SupplierResource($supplier)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("SupplierController@supplier", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }

    public function listSuppliers(int $store_id)
    {

        try {




            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }

            $perPage = (int) request('per_page') ?? 20;


            $suppliers = Supplier::where('store_id', $store_id)
                ->orderBy('created_at', 'desc')->paginate($perPage);






            return $this->successResponse('Supplier retrieved', 200, [
                'suppliers' =>  new SupplierCollection($suppliers),
            ]);
        } catch (\Exception $e) {
            Log::error("SupplierController@listSuppliers", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getSupplier(int $supplier_id)
    {

        try {


            $supplier = Supplier::where('id', $supplier_id)->firstOrFail();

            if (!auth()->user()->storeBelongsToUser($supplier->store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }


            return $this->successResponse('Supplier retrieved', 200, [
                'supplier' =>  new SupplierResource($supplier)

            ]);
        } catch (\Exception $e) {
            Log::error("SupplierController@getSupplier", [
                "error" => $e->getMessage(),
                'supplier_id' => $supplier_id
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function updateSupplier(int $supplier_id,  UpdateSupplierRequest $request)
    {
        try {


            $validated_data = $request->validated();

            $supplier = Supplier::where('id', $supplier_id)
                ->firstOrFail();

            if (!auth()->user()->storeBelongsToUser($supplier->store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }

            $supplier->update($validated_data);

            $supplier->refresh();

            return $this->successResponse('Supplier updated', 200, [
                'supplier' =>  new SupplierResource($supplier)
            ]);
        } catch (\Exception $e) {

            Log::error("SupplierController@updateSupplier", ["error" => $e->getMessage(), 'supplier_id' => $supplier_id, 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
    public function deleteSupplier(int $supplier_id)
    {
        try {


            $supplier = Supplier::where('id', $supplier_id)
                ->firstOrFail();


            if (auth()->user()->getStoreRole($supplier->store_id) !== 'owner') {
                return $this->errorResponse('You dont have owner access to this store', 403);
            }
            $supplier->delete();



            return $this->successResponse('Supplier deleted', 200, []);
        } catch (\Exception $e) {

            Log::error("SupplierController@deleteSupplier", ["error" => $e->getMessage(), 'supplier_id' => $supplier_id, 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
