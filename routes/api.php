<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\v1\Auth\AuthController;
use App\Http\Controllers\Api\v1\Purchases\PurchasesController;
use App\Http\Controllers\Api\v1\Stores\StoreController;

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
    Route::group(['prefix' => 'customers'], function () {});
    Route::group(['prefix' => 'invoices'], function () {});


    Route::group(['prefix' => 'stores'], function () {
        Route::post('{store_id}/add-user', [StoreController::class, 'addUserToStore']);
        Route::post('{store_id}/remove-user', [StoreController::class, 'removeUserStore']);
    });


    Route::group(['prefix' => 'purchases'], function () {
        Route::post('{store_id}', [PurchasesController::class, 'createPurchase']);
        Route::get('{store_id}', [StoreController::class, 'getPurchases']);
        Route::get('{purchase_id}/single', [StoreController::class, 'getPurchase']);
        Route::delete('{purchase_id}', [StoreController::class, 'deletePurchase']);
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
