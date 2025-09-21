<?php

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


Route::prefix('v1')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

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
        Route::post('/presensi/scan', [PresensiController::class, 'scan'])->name('presensi.scan');
        


    });
});
