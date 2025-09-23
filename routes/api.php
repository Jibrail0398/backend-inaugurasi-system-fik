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


Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/pendaftaran-peserta/{kode_event}', [PendaftarPesertaController::class,'store']);
    Route::post('/pendaftaran-panitia/{kode_event}', [PendaftarPanitiaController::class,'store']);

    Route::middleware('auth.jwt:admin,mentor')->group(function () {

        // User Profile and Logout
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);

        // Event Routes
        Route::prefix('event')->group(function () {
            Route::get('/index', [EventController::class,'index']);
            Route::post('/add', [EventController::class,'store']);
            Route::get('/show/{id}', [EventController::class,'show']);
            Route::put('/update/{id}', [EventController::class,'update']);
            Route::delete('/delete/{id}', [EventController::class,'destroy']);
        });

        // Pendaftar Peserta Routes
        Route::prefix('peserta')->group(function () {
            Route::get('/pendaftaran/index', [PendaftarPesertaController::class,'index']);
            Route::get('/pendaftaran/{id}', [PendaftarPesertaController::class,'show']);
            Route::put('/pendaftaran/update/{id}', [PendaftarPesertaController::class,'update']);
            Route::delete('/pendaftaran/delete/{id}', [PendaftarPesertaController::class,'destroy']);
        });

        // Pendaftar Panitia Routes
        Route::prefix('panitia')->group(function () {
            Route::get('/pendaftaran/index', [PendaftarPanitiaController::class,'index']);
            Route::get('/pendaftaran/{id}', [PendaftarPanitiaController::class,'show']);
            Route::put('/pendaftaran/update/{id}', [PendaftarPanitiaController::class,'update']);
            Route::delete('/pendaftaran/delete/{id}', [PendaftarPanitiaController::class,'destroy']);
        });

        // Penerimaan Peserta Routes
        Route::prefix('penerimaan-peserta')->group(function () {
            Route::get('/', [PenerimaanPesertaController::class,'index']);
            Route::get('/show/{id}', [PenerimaanPesertaController::class,'show']);
            Route::put('/update/{id}', [PenerimaanPesertaController::class,'update']);
        });

        // Penerimaan Panitia Routes
        Route::prefix('penerimaan-panitia')->group(function () {
            Route::get('/', [PenerimaanPanitiaController::class,'index']);
            Route::get('/show/{id}', [PenerimaanPanitiaController::class,'show']);
            Route::put('/update/{id}', [PenerimaanPanitiaController::class,'update']);
        });

        // Keuangan Routes
        Route::prefix('uang-masuk')->group(function () {
            Route::get('/', [UangMasukController::class, 'index']);      
            Route::get('/show/{id}', [UangMasukController::class, 'show']);    
            Route::post('/add', [UangMasukController::class, 'store']);      
            Route::put('/update/{id}', [UangMasukController::class, 'update']);  
            Route::delete('/delete/{id}', [UangMasukController::class, 'destroy']); 
        });
        
        // Uang Keluar Routes
        Route::prefix('uang-keluar')->group(function () {
            Route::get('/', [UangKeluarController::class, 'index']);       
            Route::get('/show/{id}', [UangKeluarController::class, 'show']);    
            Route::post('/add', [UangKeluarController::class, 'store']);      
            Route::put('/update/{id}', [UangKeluarController::class, 'update']); 
            Route::delete('/delete/{id}', [UangKeluarController::class, 'destroy']); 
        });

    });

    Route::middleware('auth.jwt:admin,mentor,peserta,panitia')->group(function () {
        // Daftar Hadir Peserta dan Panitia Routes
        Route::post('/presensi/scan', [PresensiController::class, 'scan'])->name('presensi.scan');

    });
});
