<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ForgotPasswordController;


Route::get('/login', function () {
    return response()->json([
        'success' => false,
        'message' => 'Please login to continue',
    ], 401);
})->name('login');

Route::post('/login', [AuthController::class, 'login']);

Route::post('/send-otp', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/password-reset', [ForgotPasswordController::class, 'resetPassword']);
Route::post('/register', [AuthController::class, 'register'])->name('api.register');


Route::post('/refresh', [AuthController::class, 'refresh']);

Route::middleware('auth:api')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
});

Route::middleware(['auth:api', 'role:admin'])->group(function () {

    Route::middleware('auth:api')->post('/update-password', [UserController::class, 'updatePass']);
    Route::middleware('auth:api')->put('/profile-update', [UserController::class, 'profileUpdate']);

});

require __DIR__.'/niaz.php';
require __DIR__.'/sahadat.php';
require __DIR__.'/shanto.php';
