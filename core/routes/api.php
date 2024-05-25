<?php

use Illuminate\Support\Facades\Route;

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

Route::namespace('Api')->name('api.')->group(function () {
    Route::get('general-setting', function () {
        $general = gs();
        $notify[] = 'General setting data';
        return response()->json([
            'remark' => 'general_setting',
            'status' => 'success',
            'message' => ['success' => $notify],
            'data' => [
                'general_setting' => $general,
            ],
        ]);
    });

    Route::controller('FrontendController')->group(function () {
        Route::get('privacy-policy', 'policyPages');
    });

    Route::controller('InstallationController')->prefix('verify-purchase-code')->group(function () {
        Route::post('/', 'verifyPurchasedCode')->name('purchase.code.verify');
    });
});
