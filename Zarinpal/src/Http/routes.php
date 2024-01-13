<?php
use Illuminate\Support\Facades\Route;
use Webkul\Zarinpal\Http\Controllers\ZarinpalController;

Route::group(['middleware' => ['web']], function () {
    Route::prefix('/zarinpal')->group(function () {

        Route::get('/redirect', [ZarinpalController::class, 'redirect'])
             ->name('zarinpal.payment.redirect');

        Route::get('/callback', [ZarinpalController::class, 'callback'])
             ->name('zarinpal.payment.callback');
    });
});
