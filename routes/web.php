<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageCompressorController;

Route::get('/', [ImageCompressorController::class, 'showForm']);

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

// Public image compressor
Route::get('/compressor', [ImageCompressorController::class, 'showForm'])->name('compressor.form');
Route::post('/compressor', [ImageCompressorController::class, 'compressWeb'])->name('compressor.compress');
