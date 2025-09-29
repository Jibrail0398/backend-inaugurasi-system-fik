<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PendaftaranPesertaController;
use App\Http\Controllers\Api\PendaftaranPanitiaController;
use App\Http\Controllers\Api\PenerimaanPeserta;
use App\Http\Controllers\Api\PenerimaanPanitiaController;

// Semua route di sini akan memiliki prefix /api dan middleware api secara default
// Route untuk pendaftaran peserta
Route::post('/pendaftaran-peserta', [PendaftaranPesertaController::class, 'daftar']);
Route::get('/pendaftaran-peserta', [PendaftaranPesertaController::class, 'get']);

// Route untuk pendaftaran panitia
Route::post('/pendaftaran-panitia', [PendaftaranPanitiaController::class, 'daftar']);
Route::get('/pendaftaran-panitia', [PendaftaranPanitiaController::class, 'get']);

// Route untuk penerimaan peserta
Route::get('/peserta-pending', [PenerimaanPeserta::class, 'pending']);
Route::get('/peserta-diterima', [PenerimaanPeserta::class, 'diterima']);
Route::get('/peserta-tampilkan', [PenerimaanPeserta::class, 'semuaPeserta']);
Route::get('/peserta/{id}', [PenerimaanPeserta::class, 'show']);
Route::put('/peserta/{id}/terima', [PenerimaanPeserta::class, 'terima']);
Route::put('/peserta/{id}/tolak', [PenerimaanPeserta::class, 'tolak']);

// Route untuk penerimaan panitia
Route::get('/panitia-pending', [PenerimaanPanitiaController::class, 'pending']);
Route::get('/panitia-diterima', [PenerimaanPanitiaController::class, 'diterima']);
Route::get('/panitia-tampilkan', [PenerimaanPanitiaController::class, 'semuaPanitia']);
Route::get('/panitia/{id}', [PenerimaanPanitiaController::class, 'show']);
Route::put('/panitia/{id}/terima', [PenerimaanPanitiaController::class, 'terima']);
Route::put('/panitia/{id}/tolak', [PenerimaanPanitiaController::class, 'tolak']);
