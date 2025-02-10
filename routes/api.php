<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductPrepaidController;
use App\Http\Controllers\DepositTransactionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserBalanceController;

Route::post('midtrans-callback', [DepositTransactionController::class, 'handleCallback']);

Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('form-password', [UserController::class, 'updatePasswordWebsite'])->withoutMiddleware('auth');

    Route::post('login', [AuthController::class, 'loginWeb'])->withoutMiddleware('auth');
    Route::post('loginMobile', [AuthController::class, 'loginMobile'])->withoutMiddleware('auth');
    Route::post('logout', [AuthController::class, 'logout'])->withoutMiddleware('auth');
    Route::get('me', [AuthController::class, 'me'])->withoutMiddleware('auth');

    Route::prefix('user')->group(function () {
        Route::post('store', [UserController::class, 'storeUser'])->withoutMiddleware('auth');
        Route::post('update', [UserController::class, 'updateMobile'])->withoutMiddleware('auth');
    });

    Route::prefix('histori')->group(function () {
        Route::post('', [TransactionController::class, 'histori'])->withoutMiddleware('auth');
        Route::post('home', [TransactionController::class, 'historiHome'])->withoutMiddleware('auth');
    });

    Route::post('/submit-product', [OrdersController::class, 'submitProduct']);
    Route::post('/place-order', [OrdersController::class, 'placeOrder']);

    Route::middleware(['throttle:6,1'],)->group(function () {
        Route::post('/topup', [DepositTransactionController::class, 'topup']);
        Route::get('/check-saldo', [DepositTransactionController::class, 'checkBalance']);

        Route::post('/check-saldo-wb', [DepositTransactionController::class, 'checkBalanceWb']);
        Route::post('/get-deposit/{id}', [DepositTransactionController::class, 'getDataById']);
        Route::post('histori-deposit', [DepositTransactionController::class, 'index']);
        Route::post('histori-deposit-web', [DepositTransactionController::class, 'indexWeb']);
    });

    Route::post('/saldo-user', [UserBalanceController::class, 'index']);
    Route::get('/edit-saldo/{id}', [UserBalanceController::class, 'get']);
    Route::put('/update-saldo/{id}', [UserBalanceController::class, 'update']);

    Route::post('send-user-otp', [AuthController::class, 'sendUserOTP'])->withoutMiddleware('auth'); // send otp forgot password
    Route::post('verify-user-otp', [AuthController::class, 'verifyUserOTP'])->withoutMiddleware('auth'); // verifikasi otp forgot password
    Route::post('reset-user-password', [AuthController::class, 'resetUserPassword'])->withoutMiddleware('auth'); // reset password forgot password

    Route::post('send-user-otp-regist', [AuthController::class, 'sendUserOtpRegist'])->withoutMiddleware('auth'); // send otp regist
    Route::post('resend-user-otp-regist', [AuthController::class, 'resendUserOtpRegist'])->withoutMiddleware('auth'); // resend otp regist
    Route::post('verify-user-otp-regist', [AuthController::class, 'verifyUserOtpRegist'])->withoutMiddleware('auth'); // verifikasi otp regist untuk validasi akun

    Route::get('deposit/finish', [DepositTransactionController::class, 'finish'])->name('deposit.finish');
    Route::get('deposit/unfinish', [DepositTransactionController::class, 'unfinish'])->name('deposit.unfinish');
    Route::get('deposit/error', [DepositTransactionController::class, 'error'])->name('deposit.error');
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {
    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {
        Route::middleware('can:master-user')->group(function () {
            Route::get('users/get/{id}', [UserController::class, 'getById']);
            Route::put('users/update/{id}', [UserController::class, 'updateById']);
            Route::delete('users/delete/{id}', [UserController::class, 'destroy']);
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index'])->withoutMiddleware('can:master-user');
            Route::post('users/admin', [UserController::class, 'indexAdmin']);
            Route::post('users/mitra', [UserController::class, 'indexMitra']);
            Route::post('users/user', [UserController::class, 'indexUser']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::post('users/update', [UserController::class, 'update']);
            Route::post('usersPass', [UserController::class, 'updatePasswordWebsite']);
            Route::put('users/update-password', [UserController::class, 'updatePassword']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
            Route::apiResource('usersPass', UserController::class)
                ->except(['updatePasswordWebsite', 'store'])->scoped(['user' => 'uuid']);
        });

        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });

        Route::prefix('product')->group(function () {
            Route::middleware('can:master-product')->group(function () {
                Route::prefix('prepaid')->group(function () {
                    Route::post('', [ProductPrepaidController::class, 'indexPrepaid'])->withoutMiddleware('can:master-product');
                    Route::get('get-pbb/{id}', [ProductPrepaidController::class, 'getPBBPrepaid'])->withoutMiddleware('can:master-product');
                    Route::put('update-pbb/{id}', [ProductPrepaidController::class, 'updatePBBPrepaid']);
                    Route::post('store-pbb', [ProductPrepaidController::class, 'storePBBPrepaid']);
                    Route::delete('delete-pbb/{id}', [ProductPrepaidController::class, 'destroyPBBPrepaid']);
                });
            });
        });

        Route::middleware('can:master-laporan')->group(function () {
            Route::delete('delete-laporan-deposit/{id}', [DepositTransactionController::class, 'destroy']);
            Route::post('laporan', [TransactionController::class, 'laporan']);
            Route::delete('delete-laporan/{id}', [TransactionController::class, 'destroy']);
            Route::get('/transaction/chart-data', [TransactionController::class, 'getChartData']);

            Route::get('user/download-excel', [UserController::class, 'downloadExcel']);
            Route::get('deposit/download-excel', [DepositTransactionController::class, 'downloadExcel']);
            Route::get('transaction/download-excel', [TransactionController::class, 'downloadExcel']);
            Route::get('productPrepaid/download-excel', [ProductPrepaidController::class, 'downloadExcel']);
        });

        Route::prefix('order')->group(function () {
            Route::middleware('can:master-order')->group(function () {
                Route::post('', [OrdersController::class, 'index']);
                Route::get('get/{id}', [OrdersController::class, 'get']);
                Route::put('update/{id}', [OrdersController::class, 'update']);
                Route::delete('delete/{id}', [OrdersController::class, 'destroy']);
                Route::put('update-status/{id}', [OrdersController::class, 'updateStatus']);
            });
        });
    });
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
    Route::post('update', [SettingController::class, 'update']);
});
