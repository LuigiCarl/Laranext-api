<?php

use App\Http\Controllers\Api\bookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::apiResource('book', bookController::class);

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');
