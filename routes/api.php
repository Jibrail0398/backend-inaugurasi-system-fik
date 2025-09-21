<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PendaftaranPesertaController;

Route::post('/pendaftaran', [PendaftaranPesertaController::class, 'daftar']);
