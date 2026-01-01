<?php


namespace App\Http\Controllers\Api\v1\Products;

use App\Http\Controllers\Controller;

use App\Http\Requests\Product\CreateProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use App\Traits\ApiResponser;
use App\Http\Resources\ProductCollection;

use Illuminate\Support\Str;


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

            $prefix = strtoupper(Str::substr(str_replace(' ', '', $validated['name']), 0, 3));


            $sku_exists = false;



            do {
                $middle  = strtoupper(Str::random(4));
                $suffix = rand(1000, 9999);
                $sku =  $prefix . '-' . $middle . '-' . $suffix;

                $sku_exists = Product::where('sku', $sku)->exists();
            } while ($sku_exists);

            $validated['sku'] = $sku;

            $product = Product::create($validated);



            return $this->successResponse(
                'Product created successfully',
                201,

                [
                    'product' =>  new ProductResource($product),
                ],
            );
        } catch (\Exception $e) {
            Log::error("ProductController@createProduct", [
                "message" => $e->getMessage(),
                "file" => $e->getFile(),
                "line" => $e->getLine(),
            ]);

            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function getProduct(int $product_id)
    {

        try {


            $product = Product::where('id', $product_id)->firstOrFail();

            if (!auth()->user()->storeBelongsToUser($product->store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }


            return $this->successResponse('Product retrieved', 200, [
                'product' =>  new ProductResource($product)

            ]);
        } catch (\Exception $e) {
            Log::error("ProductController@getProduct", [
                "error" => $e->getMessage(),
                'product_id' => $product_id
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }


    public function listProducts(int $store_id)
    {

        try {


            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }
            $perPage = (int) request('per_page') ?? 20;

            $products = Product::where('store_id', $store_id)
                ->orderBy('name', 'asc')->paginate($perPage);



            return $this->successResponse('Product retrieved', 200, [
                'products' =>  new ProductCollection($products),


            ]);
        } catch (\Exception $e) {
            Log::error("ProductController@listProducts", [
                "error" => $e->getMessage(),
                'store_id' => $store_id
            ]);
            return $this->errorResponse('An error occured', 500, [], $e->getMessage());
        }
    }



    public function searchProducts(int $store_id)
    {

        try {





            if (!auth()->user()->storeBelongsToUser($store_id)) {
                return $this->errorResponse('You dont have  access to this store', 403);
            }




            $products = Product::search(
                request('query') ?? '',
                function ($meilsearch, string $query, array $options) {
                    $options['attributesToHighlight'] =  ['name', 'unit'];
                    return $meilsearch->search($query, $options);
                }
            )->where('store_id', $store_id)

                ->orderBy('created_at', 'desc')->get();




            return $this->successResponse('Searched Products retrieved', 200, [
                'products' =>  ProductResource::collection($products),
            ]);
        } catch (\Exception $e) {
            Log::error("ProductController@searchProducts", ["error" => $e->getMessage(), 'query' =>    request('query')]);
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
            $product->refresh();
            return $this->successResponse(
                'Product updated successfully',
                200,

                [
                    'product' =>  new ProductResource($product),
                ],
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
                200,

                [],
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
