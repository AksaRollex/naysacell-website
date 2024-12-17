<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DigiflazController;
use App\Http\Controllers\Api\TripayController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CodeOperatorController;
use App\Http\Controllers\ProductPascaController;
use App\Http\Controllers\ProductPrepaidController;
use App\Http\Controllers\ProductProviderController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::prefix('digiflazz')->group(function () {
//     Route::post('get-product-prepaid', [ProductPrepaidController::class, 'get_product_prepaid']);
//     Route::post('get-product-pasca', [ProductPascaController::class, 'get_product_pasca']);
//     Route::post('/topup', [DigiflazController::class, 'digiflazTopup']);
//     Route::post('/cek-tagihan', [DigiflazController::class, 'digiflazCekTagihan']);
//     Route::post('/bayar-tagihan', [DigiflazController::class, 'digiflazBayarTagihan']);
// });

// Route::prefix('auth')->group(function() {
//     Route::post('/email', [PasswordResetController::class, 'sendResetLink']);
//     Route::post('/reset', [PasswordResetController::class, 'resetPassword']);
// });

Route::prefix('tripay')->group(function () {
    Route::get('get-kategori-prabayar', [TripayController::class, 'get_kategori_prabayar']);    
    Route::get('get-operator-prabayar', [TripayController::class, 'get_operator_prabayar']);
    Route::get('get-produk-prabayar', [TripayController::class, 'get_produk_prabayar']);
    Route::get('get-detail-produk-prabayar', [TripayController::class, 'get_detail_produk_prabayar']);
});

// Authentication Route
Route::middleware(['auth', 'json'])->prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login'])->withoutMiddleware('auth');
    Route::delete('logout', [AuthController::class, 'logout']);
    Route::get('me', [AuthController::class, 'me'])->withoutMiddleware('auth');

    Route::prefix('user')->group(function () {
        Route::post('store', [UserController::class, 'storeUser'])->withoutMiddleware('auth');
        Route::post('update', [UserController::class, 'updateMobile'])->withoutMiddleware('auth');
    });

    Route::prefix('histori')->group(function () {
        Route::post('', [DigiflazController::class, 'histori'])->withoutMiddleware('auth');
    });
});


Route::middleware(['auth', 'verified', 'json'])->group(function () {

    Route::prefix('setting')->middleware('can:setting')->group(function () {
        Route::post('', [SettingController::class, 'update']);
    });

    Route::prefix('master')->group(function () {

        // USER
        Route::middleware('can:master-user')->group(function () {
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

        // INSERT DATA DIGIFLAZZ
        // Route::prefix('digiflazz')->group(function () {
        // Route::middleware('can:master-digiflazz')->group(function () {
        // Route::post('get-product-prepaid', [ProductPrepaidController::class, 'get_product_prepaid']);
        // Route::post('get-product-pasca', [ProductPascaController::class, 'get_product_pasca']);
        // Route::post('/topup', [DigiflazController::class, 'digiflazTopup']);
        // Route::post('/cek-tagihan', [DigiflazController::class, 'digiflazCekTagihan']);
        // Route::post('/bayar-tagihan', [DigiflazController::class, 'digiflazBayarTagihan']);
        // });
        // });

        // MASTER
        Route::prefix('master')->group(function () {
            Route::middleware('can:master-brand-operator')->group(function () {
                Route::prefix('brand')->group(function () {
                    Route::post('', [ProductProviderController::class, 'index']);
                    Route::get('get/{id}', [ProductProviderController::class, 'get']);
                    Route::put('update/{id}', [ProductProviderController::class, 'update']);
                    Route::post('store', [ProductProviderController::class, 'store']);
                    Route::delete('delete/{id}', [ProductProviderController::class, 'destroy']);
                });
                Route::prefix('operator')->group(function () {
                    Route::post('', [CodeOperatorController::class, 'index']);
                });
            });
        });

        // PRODUCT
        Route::prefix('product')->group(function () {
            Route::middleware('can:master-product')->group(function () {
                Route::prefix('prepaid')->group(function () {
                    Route::post('', [ProductPrepaidController::class, 'indexPrepaid'])->withoutMiddleware('can:master-product');
                    Route::get('get-pbb/{id}', [ProductPrepaidController::class, 'getPBBPrepaid'])->withoutMiddleware('can:master-product');
                    Route::put('update-pbb/{id}', [ProductPrepaidController::class, 'updatePBBPrepaid']);
                });
                Route::prefix('pasca')->group(function () {
                    Route::post('', [ProductPascaController::class, 'indexPasca'])->withoutMiddleware('can:master-product');
                });
            });
        });

        // PPOB
        Route::prefix('ppob')->group(function () {
            Route::middleware('can:master-ppob')->group(function () {});
        });

        // LAPORAN
        Route::middleware('can:master-laporan')->group(function () {
            Route::post('laporan', [DigiflazController::class, 'laporan']);
        });

        // ISI SALDO
        Route::middleware('can:master-isi-saldo')->group(function () {
            Route::post('isi-saldo', [DigiflazController::class, 'isiSaldo']);
        });
    });
});

Route::prefix('setting')->group(function () {
    Route::get('', [SettingController::class, 'index']);
});
