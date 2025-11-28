<?php

namespace App\Http\Controllers\Api\v1\Organization;

use App\Http\Controllers\Controller;
use App\Traits\ApiResponser;
use App\Http\Requests\Supplier\CreateSupplierRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\Supplier;
use App\Http\Resources\SupplierResource;


class SupplierController extends Controller
{

    use ApiResponser;




    public function createSupplier(CreateSupplierRequest $request)
    {

        try {
            $validated_data = $request->validated();

            DB::beginTransaction();

            $supplier =  Supplier::create([
                'name' => $validated_data['name'],
                'address' => $validated_data['address'] ?? null,
                'phone_number' => $validated_data['phone_number'] ?? null,
                'user_id' => auth()->user()->id,
            ]);

            return $this->successResponse('Supplier created ', 201, [
                'supplier' => new SupplierResource($supplier)
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error("SupplierController@supplier", ["error" => $e->getMessage(), 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }

    public function listSuppliers()
    {

        try {



            $user = auth()->user();


            $perPage =  request('per_page') ?? 20;

            $suppliers = Supplier::where('user_id', $user->id)
                ->get()->orderBy('created_at', 'desc')->paginate($perPage);


            $pagination = [
                'current_page' => $suppliers->currentPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'last_page' => $suppliers->lastPage(),
                'from' => $suppliers->firstItem(),
                'to' => $suppliers->lastItem(),
            ];






            return $this->successResponse('Supplier retrieved', 200, [
                'suppliers' =>  $suppliers->items(),
                'pagination' => $pagination
            ]);
        } catch (\Exception $e) {
            Log::error("SupplierController@listSuppliers", ["error" => $e->getMessage()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getSupplier(int $supplier_id)
    {

        try {

            $user = auth()->user();



            $supplier = Supplier::where('id', $supplier_id)->where('user_id', $user->id)->firstOrFail();

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


    public function updateSupplier(int $supplier_id,  CreateSupplierRequest $request)
    {
        try {

            $user = auth()->user();

            $validated_data = $request->validated();

            $supplier = Supplier::where('id', $supplier_id)
                ->where('user_id' . $user->id)->update($validated_data);

            $supplier->refresh();

            return $this->successResponse('Supplier updated', 200, [
                'supplier' =>  new SupplierResource($supplier)
            ]);
        } catch (\Exception $e) {

            Log::error("SupplierController@updateSupplier", ["error" => $e->getMessage(), 'supplier_id' => $supplier_id, 'payload' => request()->all()]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
