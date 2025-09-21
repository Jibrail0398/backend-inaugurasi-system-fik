<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\UangMasukController;
use App\Http\Controllers\UangKeluarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Middleware\AuthJWT;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PendaftarPesertaController;
use App\Http\Controllers\Api\PenerimaanPesertaController;
use App\Http\Controllers\Api\DaftarHadirController;
use App\Http\Controllers\Api\PresensiController;
use App\Http\Controllers\Api\DaftarHadirPanitiaController;
use App\Http\Controllers\Api\PendaftarPanitiaController;
use App\Http\Controllers\Api\PenerimaanPanitiaController;
use App\Http\Controllers\Api\UangMasukController;
use App\Http\Controllers\Api\UangKeluarController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/event', [EventController::class, 'Index']);
Route::get('/event/{id}', [EventController::class, 'Show']);
Route::post('/event', [EventController::class, 'Store']);
Route::put('/event/{id}', [EventController::class, 'Update']);
Route::delete('/event/{id}', [EventController::class, 'Destroy']);

Route::get('/keuangan', [KeuanganController::class, 'Index']);
Route::get('/keuangan/report', [KeuanganController::class, 'Report']);
Route::get('/keuangan/{id}', [KeuanganController::class, 'Show']);
Route::post('/keuangan', [KeuanganController::class, 'Store']);
Route::put('/keuangan/{id}', [KeuanganController::class, 'Update']);
Route::delete('/keuangan/{id}', [KeuanganController::class, 'Destroy']);

Route::get('/uang-masuk', [UangMasukController::class, 'Index']);
Route::get('/uang-masuk/{id}', [UangMasukController::class, 'Show']);
Route::post('/uang-masuk', [UangMasukController::class, 'Store']);
Route::put('/uang-masuk/{id}', [UangMasukController::class, 'Update']);
Route::delete('/uang-masuk/{id}', [UangMasukController::class, 'Destroy']);

Route::get('/uang-keluar', [UangKeluarController::class, 'Index']);
Route::get('/uang-keluar/{id}', [UangKeluarController::class, 'Show']);
Route::post('/uang-keluar', [UangKeluarController::class, 'Store']);
Route::put('/uang-keluar/{id}', [UangKeluarController::class, 'Update']);
Route::delete('/uang-keluar/{id}', [UangKeluarController::class, 'Destroy']);



    Route::prefix('peserta')->group(function () {
        Route::post('/pendaftaran-peserta/{kode_event}', [PendaftarPesertaController::class,'store']);
    });

    Route::prefix('panitia')->group(function () {
       Route::post('/', [PendaftarPanitiaController::class,'store']);
    });

    Route::middleware('auth.jwt')->group(function () {

        // User Profile and Logout
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Event Routes
        Route::prefix('event')->group(function () {
            Route::get('/', [EventController::class,'index']);
            Route::post('/', [EventController::class,'store']);
            Route::get('{id}', [EventController::class,'show']);
            Route::put('{id}', [EventController::class,'update']);
            Route::delete('{id}', [EventController::class,'destroy']);
        });

        Route::prefix('peserta')->group(function () {
            Route::get('/', [PendaftarPesertaController::class,'index']);
            Route::post('/', [PendaftarPesertaController::class,'store']);
            Route::get('{id}', [PendaftarPesertaController::class,'show']);
            Route::put('{id}', [PendaftarPesertaController::class,'update']);
            Route::delete('{id}', [PendaftarPesertaController::class,'destroy']);
        });

        Route::prefix('panitia')->group(function () {
            Route::get('/', [PendaftarPanitiaController::class,'index']);
            Route::post('/', [PendaftarPanitiaController::class,'store']);
            Route::get('{id}', [PendaftarPanitiaController::class,'show']);
            Route::put('{id}', [PendaftarPanitiaController::class,'update']);
            Route::delete('{id}', [PendaftarPanitiaController::class,'destroy']);
        });

        // Penerimaan Peserta Routes
        Route::prefix('penerimaan')->group(function () {
            Route::get('/', [PenerimaanPesertaController::class,'index']);
            Route::get('{id}', [PenerimaanPesertaController::class,'show']);
            Route::put('{id}', [PenerimaanPesertaController::class,'update']);
        });

       // Penerimaan Panitia
        Route::put('/penerimaan-panitia/{id}', [PenerimaanPanitiaController::class, 'update']);
        Route::get('/penerimaan-panitia', [PenerimaanPanitiaController::class, 'index']);
        Route::get('/penerimaan-panitia/{id}', [PenerimaanPanitiaController::class, 'show']);


        Route::prefix('uang-masuk')->group(function () {
            Route::get('/', [UangMasukController::class, 'index']);       // GET semua pemasukan
            Route::get('/{id}', [UangMasukController::class, 'show']);    // GET detail pemasukan
            Route::post('/', [UangMasukController::class, 'store']);      // POST tambah pemasukan
            Route::put('/{id}', [UangMasukController::class, 'update']);  // PUT update pemasukan
            Route::delete('/{id}', [UangMasukController::class, 'destroy']); // DELETE hapus pemasukan
        });


        // Daftar Hadir Peserta dan Panitia Routes
        Route::get('/presensi/scan', [PresensiController::class, 'scan'])->name('presensi.scan');
        


    });
});
