<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendaftaranPesertaController;
use App\Http\Controllers\PendaftaranPanitiaController;
use App\Http\Controllers\PenerimaanPeserta;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/phpinfo', function () {
    phpinfo();
});

// Route untuk pendaftaran peserta
Route::post('/pendaftaran-peserta', [PendaftaranPesertaController::class, 'daftar']);
Route::get('/pendaftaran-peserta', [PendaftaranPesertaController::class, 'get']);

// Route untuk pendaftaran panitia
Route::post('/pendaftaran-panitia', [PendaftaranPanitiaController::class, 'daftar']);
Route::get('/pendaftaran-panitia', [PendaftaranPanitiaController::class, 'get']);

// Route untuk penerimaan peserta
Route::get('/peserta-pending', [PenerimaanPeserta::class, 'pending']);
Route::get('/peserta-diterima', [PenerimaanPeserta::class, 'diterima']);
Route::get('/peserta/{id}', [PenerimaanPeserta::class, 'show']);
Route::put('/peserta/{id}/terima', [PenerimaanPeserta::class, 'terima']);
Route::put('/peserta/{id}/tolak', [PenerimaanPeserta::class, 'tolak']);

