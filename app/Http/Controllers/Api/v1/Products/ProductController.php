<?php


namespace App\Http\Controllers\Api\v1\Stores;

use App\Http\Controllers\Controller;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponser;


class ProductController extends Controller
{



    use ApiResponser;


    public function createProduct(CreateProductRequest $request)
    {

        try {
            $validated = $request->validated();

            if (!auth()->user()->storeBelongsToUser($validated['store_id'])) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }



            $validated['quantity_remaining'] = 0;

            $product = Product::create($validated);



            return $this->successResponse(
                'Product created successfully',
                [
                    'product' =>  new ProductResource($product),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error("ProductController@addUserToStore", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function updateProduct(int $product_id, UpdateProductRequest $request)
    {

        try {
            $validated = $request->validated();

            $product = Product::find($product_id);

            if (!auth()->user()->storeBelongsToUser($product->store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }

            $product->update($validated);
            /**
             * If default store is empty then this is a new user
             */
            return $this->successResponse(
                'Product updated successfully',
                [
                    'store' =>  new ProductResource($product),
                ],
                200
            );
        } catch (\Exception $e) {
            Log::error("ProductController@updateProduct", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function deleteProduct(int $product_id)
    {

        try {

            $product = Product::find($product_id);

            if (auth()->user()->getStoreRole($product->store_id) !== 'owner') {
                return $this->errorResponse('You dont have owner access to this store', 403);
            }


            $product->delete();

            return $this->successResponse(
                'Product deleted successfully',
                [],
                200
            );
        } catch (\Exception $e) {
            Log::error("ProductController@deleteProduct", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }
}
