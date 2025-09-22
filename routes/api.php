<?php

use App\Http\Controllers\EventController;
use App\Http\Controllers\KeuanganController;
use App\Http\Controllers\UangMasukController;
use App\Http\Controllers\UangKeluarController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/event', [EventController::class, 'Index']);
Route::get('/event/{id}', [EventController::class, 'Show']);
Route::post('/event', [EventController::class, 'Store']);
Route::put('/event/{id}', [EventController::class, 'Update']);
Route::delete('/event/{id}', [EventController::class, 'Destroy']);

Route::get('/keuangan', [KeuanganController::class, 'Index']);
Route::get('/keuangan/total-saldo', [KeuanganController::class, 'TotalSaldo']);
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



