<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\BookController;
use App\Http\Controllers\API\BorrowController;
use App\Http\Controllers\API\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('auth/register', [AuthController::class, 'register']);
Route::post('auth/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth routes
    Route::get('auth/user', [AuthController::class, 'user']);
    Route::post('auth/logout', [AuthController::class, 'logout']);

    // Books routes
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{id}', [BookController::class, 'show']);

    // Admin and librarian only routes
    Route::middleware('role:admin,librarian')->group(function () {
        // Books management
        Route::post('books', [BookController::class, 'store']);
        Route::put('books/{id}', [BookController::class, 'update']);
        Route::delete('books/{id}', [BookController::class, 'destroy']);
        Route::post('books/{id}/cover', [BookController::class, 'uploadCover']);

        // Users management
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::get('/users', [UserController::class, 'index']);
            Route::get('/users/{id}', [UserController::class, 'show']);
        });
        // Borrows management
        Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
            Route::get('/borrows', [BorrowController::class, 'index']);
            Route::get('/borrows/{id}', [BorrowController::class, 'show']);
        });
        
        // Route for a specific user's borrows
        Route::middleware(['auth:sanctum', 'role:admin'])->get('/users/{userId}/borrows', [BorrowController::class, 'getUserBorrows']);
    });
    // User routes
    Route::middleware('role:user')->group(function () {
        // Get own borrows
        Route::get('my/borrows', function () {
            return app(BorrowController::class)->getUserBorrows(auth()->id(), request());
        });
    });
});