<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Purchases\PurchasesController;
use App\Http\Controllers\Api\v1\Stores\StoreController;
use App\Http\Controllers\Api\v1\Suppliers\SupplierController;
use App\Http\Controllers\Api\v1\Products\ProductController;
use App\Http\Controllers\Api\v1\Customers\CustomerController;
use App\Http\Controllers\Api\v1\Invoices\InvoicePaymentController;
use App\Http\Controllers\Api\v1\Invoices\InvoiceController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });





Route::group(['prefix' => 'v1', 'middleware' => ['auth:api', 'throttle:api']], function () {
    Route::group(['prefix' => 'customers'], function () {

        Route::post('', [CustomerController::class, 'createCustomer']);
        Route::get('{store_id}', [CustomerController::class, 'listCustomers']);
        Route::get('{store_id}/search', [CustomerController::class, 'searchCustomers']);

        Route::get('{customer_id}/single', [CustomerController::class, 'getCustomer']);
        Route::post('{customer_id}', [CustomerController::class, 'updateCustomer']);
        Route::delete('{customer_id}', [CustomerController::class, 'deleteCustomer']);
    });
    Route::group(['prefix' => 'invoices'], function () {

        Route::group(['prefix' => 'payments'], function () {
            Route::post('{invoice_id}', [InvoicePaymentController::class, 'createPayment']);
            Route::get('{invoice_id}', [InvoicePaymentController::class, 'getPayments']);
            Route::delete('{payment_id}', [InvoicePaymentController::class, 'deletePayment']);
        });

        Route::post('{store_id}', [InvoiceController::class, 'createInvoice']);
        Route::get('{store_id}', [InvoiceController::class, 'getInvoices']);
        Route::get('{store_id}/search', [InvoiceController::class, 'searchInvoice']);

        Route::get('{invoice_id}/single', [InvoiceController::class, 'getInvoice']);
        Route::delete('{invoice_id}', [InvoiceController::class, 'deleteInvoice']);
    });


    Route::group(['prefix' => 'stores'], function () {
        Route::post('{store_id}/add-user', [StoreController::class, 'addUserToStore']);
        Route::get('{store_id}/users', [StoreController::class, 'listUsers']);

        Route::post('{store_id}', [StoreController::class, 'updateStore']);
        Route::post('{store_id}/add-user', [StoreController::class, 'addUserToStore']);
        Route::post('{store_id}/remove-user', [StoreController::class, 'removeUserStore']);
    });


    Route::group(['prefix' => 'purchases'], function () {
        Route::post('{store_id}', [PurchasesController::class, 'createPurchase']);
        Route::get('{store_id}', [PurchasesController::class, 'getPurchases']);
        Route::get('{store_id}/search', [PurchasesController::class, 'searchPurchases']);

        Route::get('{purchase_id}/single', [PurchasesController::class, 'getSinglePurchase']);
        Route::delete('{purchase_id}', [PurchasesController::class, 'deletePurchase']);
    });
    Route::group(['prefix' => 'products'], function () {
        Route::get('{store_id}', [ProductController::class, 'listProducts']);
        Route::get('{store_id}/search', [ProductController::class, 'searchProducts']);
        Route::get('{product_id}/single', [ProductController::class, 'getProduct']);
        Route::post('', [ProductController::class, 'createProduct']);
        Route::post('{product_id}', [ProductController::class, 'updateProduct']);
        Route::delete('{product_id}', [ProductController::class, 'deleteProduct']);
    });

    Route::group(['prefix' => 'suppliers'], function () {
        Route::get('{store_id}/search', [SupplierController::class, 'searchSuppliers']);

        Route::post('', [SupplierController::class, 'createSupplier']);
        Route::get('{store_id}', [SupplierController::class, 'listSuppliers']);
        Route::get('{supplier_id}/single', [SupplierController::class, 'getSupplier']);
        Route::post('{supplier_id}', [SupplierController::class, 'updateSupplier']);
        Route::delete('{supplier_id}', [SupplierController::class, 'deleteSupplier']);
    });
});


Route::group(['prefix' => 'v1', 'middleware' => ['throttle:api']], function () {


    /**
     * Auth
     */
    Route::group(['prefix' => 'auth'], function () {


        Route::post('login/social', [AuthController::class, 'loginSocial']);
    });
});


Route::get('health', function () {
    return response('OK', 200);
});
