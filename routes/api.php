<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CompteController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\AuthController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Authentication endpoints (use password grant client credentials in .env)
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/verify-otp', [AuthController::class, 'verifyOTP']);
Route::post('/auth/refresh', [AuthController::class, 'refresh']);
Route::post('/auth/logout', [AuthController::class, 'logout'])->middleware(['auth.cookie', 'auth:api']);

// Protected routes
Route::middleware(['auth:sanctum'])->group(function () {
    // Comptes CRUD
    Route::get('/comptes', [CompteController::class, 'index']);
    Route::post('/comptes', [CompteController::class, 'store']);
    Route::get('/comptes/{id}', [CompteController::class, 'show']);
    Route::put('/comptes/{id}', [CompteController::class, 'update']);
    Route::delete('/comptes/{id}', [CompteController::class, 'destroy']);

    // Transactions
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('/transactions/transfer', [TransactionController::class, 'transfer']);
    Route::post('/transactions/payment', [TransactionController::class, 'payment']);
});
