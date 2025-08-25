<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImageCompressorController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Token-authenticated compression endpoint
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/v1/compress', [ImageCompressorController::class, 'compressApi']);
});
