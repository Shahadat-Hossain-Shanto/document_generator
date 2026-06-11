<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SettingsController;

Route::middleware(['auth:api', 'role:admin'])->group(function () {
    Route::get('/setting', [SettingsController::class, 'getSettings']);
    Route::post('/update/admin/profile', [SettingsController::class, 'updateSettings']);
    Route::patch('/update/notifications', [SettingsController::class, 'updateNotificationSettings']);
});
