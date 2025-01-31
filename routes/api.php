<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DigiflazController;
use App\Http\Controllers\OrdersController;
use App\Http\Controllers\ProductPascaController;
use App\Http\Controllers\ProductPrepaidController;
use App\Http\Controllers\DepositTransactionController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserBalanceController;
use App\Http\Controllers\Api\TripayController;
use App\Http\Controllers\CodeOperatorController;

Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'loginWeb'])->withoutMiddleware('auth');
    Route::post('loginMobile', [AuthController::class, 'loginMobile'])->withoutMiddleware('auth');
    Route::post('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me'])->withoutMiddleware('auth');

    //MOBILE
    Route::prefix('user')->group(function () {
        Route::post('store', [UserController::class, 'storeUser'])->withoutMiddleware('auth');
        Route::post('update', [UserController::class, 'updateMobile'])->withoutMiddleware('auth');
    });

    Route::prefix('histori')->group(function () {
        Route::post('', [TransactionController::class, 'histori'])->withoutMiddleware('auth');
        Route::post('home', [TransactionController::class, 'historiHome'])->withoutMiddleware('auth');
    });

    // PESANAN
    Route::post('/submit-product', [OrdersController::class, 'submitProduct']);
    Route::post('/place-order', [OrdersController::class, 'placeOrder']);

    // SALDO
    Route::middleware(['throttle:6,1'],)->group(function () {
        Route::post('/topup', [DepositTransactionController::class, 'topup']);
        Route::get('/check-saldo', [DepositTransactionController::class, 'checkBalance']);

        Route::post('/check-saldo-wb', [DepositTransactionController::class, 'checkBalanceWb']);
        Route::post('/get-deposit/{id}', [DepositTransactionController::class, 'getDataById']);
        Route::post('histori-deposit', [DepositTransactionController::class, 'index']);
        Route::post('histori-deposit-web', [DepositTransactionController::class, 'indexWeb']);
    });

    Route::post('/saldo-user', [UserBalanceController::class, 'index']);

    // FORGOT PASSWORD
    Route::post('send-user-otp', [AuthController::class, 'sendUserOTP'])->withoutMiddleware('auth');
    Route::post('verify-user-otp', [AuthController::class, 'verifyUserOTP'])->withoutMiddleware('auth');
    Route::post('reset-user-password', [AuthController::class, 'resetUserPassword'])->withoutMiddleware('auth');

    // REGISTER
    Route::post('send-user-otp-regist', [AuthController::class, 'sendUserOtpRegist'])->withoutMiddleware('auth');
    Route::post('verify-user-otp-regist', [AuthController::class, 'verifyUserOtpRegist'])->withoutMiddleware('auth');

    // MIDTRANS DEPOSIT 
    Route::post('deposit/callback', [DepositTransactionController::class, 'handleCallback']);
    Route::get('deposit/finish', [DepositTransactionController::class, 'finish'])->name('deposit.finish');
    Route::get('deposit/unfinish', [DepositTransactionController::class, 'unfinish'])->name('deposit.unfinish');
    Route::get('deposit/error', [DepositTransactionController::class, 'error'])->name('deposit.error');
});

Route::middleware(['auth', 'verified', 'json'])->group(function () {

    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {

        // USER
        Route::middleware('can:master-user')->group(function () {
            // MOBILE
            Route::get('users/get/{id}', [UserController::class, 'getById']);
            Route::put('users/update/{id}', [UserController::class, 'updateById']);
            Route::delete('users/delete/{id}', [UserController::class, 'destroy']);
            // WEBSITE
            Route::get('users', [UserController::class, 'get']);
            Route::post('users', [UserController::class, 'index'])->withoutMiddleware('can:master-user');
            Route::post('users/admin', [UserController::class, 'indexAdmin']);
            Route::post('users/mitra', [UserController::class, 'indexMitra']);
            Route::post('users/user', [UserController::class, 'indexUser']);
            Route::post('users/store', [UserController::class, 'store']);
            Route::post('users/update', [UserController::class, 'update']);
            Route::apiResource('users', UserController::class)
                ->except(['index', 'store'])->scoped(['user' => 'uuid']);
        });

        // ROLE
        Route::middleware('can:master-role')->group(function () {
            Route::get('roles', [RoleController::class, 'get'])->withoutMiddleware('can:master-role');
            Route::post('roles', [RoleController::class, 'index']);
            Route::post('roles/store', [RoleController::class, 'store']);
            Route::apiResource('roles', RoleController::class)
                ->except(['index', 'store']);
        });

        // PRODUCT
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

        // PPOB
        Route::prefix('ppob')->group(function () {
            Route::middleware('can:master-ppob')->group(function () {});
        });

        // LAPORAN
        Route::middleware('can:master-laporan')->group(function () {
            // DEPOSIT
            Route::get('deposit/download-excel', [DepositTransactionController::class, 'downloadExcel']);
            // TRANSACTION
            Route::post('laporan', [TransactionController::class, 'laporan']);
            Route::get('transaction/download-excel', [TransactionController::class, 'downloadExcel']);
            Route::delete('delete-laporan/{id}', [TransactionController::class, 'destroy']);
            Route::get('/transaction/chart-data', [TransactionController::class, 'getChartData']);
            // USER
            Route::get('user/download-excel', [UserController::class, 'downloadExcel']);
            // PRODUCT
            Route::get('productPrepaid/download-excel', [ProductPrepaidController::class, 'downloadExcel']);
        });

        // ISI SALDO
        Route::middleware('can:master-isi-saldo')->group(function () {
            Route::post('isi-saldo', [DigiflazController::class, 'isiSaldo']);
        });

        // PESANAN
        Route::prefix('order')->group(function () {
            Route::middleware('can:master-order')->group(function () {
                Route::post('', [OrdersController::class, 'index']);
                Route::get('get/{id}', [OrdersController::class, 'get']);
                Route::put('update/{id}', [OrdersController::class, 'update']);
                Route::delete('delete/{id}', [OrdersController::class, 'destroy']);
            });
        });
    });
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
    Route::post('update', [SettingController::class, 'update']);
});

Route::prefix('digiflazz')->group(function () {
    Route::post('get-product-prepaid', [ProductPrepaidController::class, 'get_product_prepaid']);
    Route::post('/topup', [DigiflazController::class, 'digiflazTopup']);
    Route::post('/cek-tagihan', [DigiflazController::class, 'digiflazCekTagihan']);
    Route::post('/bayar-tagihan', [DigiflazController::class, 'digiflazBayarTagihan']);
});

// Route::prefix('tripay')->group(function () {
//     //CEK
//     Route::get('cek-server', [TripayController::class, 'cek_server']);
//     Route::get('cek-saldo', [TripayController::class, 'cek_saldo']);
//     //PRABAYAR
//     Route::get('get-kategori-prabayar', [TripayController::class, 'get_kategori_prabayar']);
//     Route::get('get-operator-prabayar', [TripayController::class, 'get_operator_prabayar']);
//     Route::get('get-produk-prabayar', [TripayController::class, 'get_produk_prabayar']);
//     Route::post('get-detail-produk-prabayar', [TripayController::class, 'get_detail_produk_prabayar']);
//     //PASCABAYAR
//     Route::get('get-kategori-pascabayar', [TripayController::class, 'get_kategori_pascabayar']);
//     Route::get('get-operator-pascabayar', [TripayController::class, 'get_operator_pascabayar']);
//     Route::get('get-produk-pascabayar', [TripayController::class, 'get_produk_pascabayar']);
//     Route::post('get-detail-produk-pascabayar', [TripayController::class, 'get_detail_produk_pascabayar']);
//     //TRANSAKSI
//     Route::post('request-transaksi-prabayar', [TripayController::class, 'request_transaksi_prabayar']);
//     Route::post('cek-tagihan-pascabayar', [TripayController::class, 'cek_tagihan_pascabayar']);
//     Route::post('bayar-tagihan-pascabayar', [TripayController::class, 'bayar_tagihan_pascabayar']);
//     Route::get('riwayat-transaksi', [TripayController::class, 'riwayat_transaksi']);
//     Route::post('detail-riwayat-transaksi', [TripayController::class, 'detail_riwayat_transaksi']);
//     Route::post('riwayat-transaksi-bydate', [TripayController::class, 'riwayat_transaksi_bydate']);
// });
