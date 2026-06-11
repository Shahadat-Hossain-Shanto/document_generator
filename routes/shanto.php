<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\ServiceSubmitController;


Route::get('/services', [ServiceController::class, 'services']);

//Sservice submission
Route::post('/service-store/{service}', [ServiceSubmitController::class, 'submit']);
Route::get('/service-data/{id}', [ServiceSubmitController::class, 'getSubmission']);
Route::get('/document/{id}', [ServiceSubmitController::class, 'getDocument']);
Route::get('/service-form-view/{service}', [ServiceController::class, 'view']);

//update submission
Route::post('/service/{serviceId}/submission/{submissionId}', [ServiceSubmitController::class, 'update']);

// Additional routes for submissions
Route::get('/get-service-submissions/{service}', [ServiceSubmitController::class, 'getServiceSubmissions']);

//download-document url
Route::get('/download-document/{submission}', [ServiceSubmitController::class, 'downloadDocument'])->name('download.document');


Route::middleware(['auth:api', 'role:admin'])->group(function () {

    Route::get('/service-forms', [ServiceController::class, 'index']);
    Route::post('/service-form', [ServiceController::class, 'store']);
    Route::get('/service-form-edit/{service}', [ServiceController::class, 'edit']);
    Route::post('/service-form-update/{service}', [ServiceController::class, 'update']);
    Route::delete('/service-form-delete/{service}', [ServiceController::class, 'destroy']);
    Route::patch('/service-form-active/{service}', [ServiceController::class, 'active']);
    Route::get('/document-keys/{service}', [ServiceController::class, 'documentKeys']);
    Route::get('/value-using-document-key/{service}', [ServiceController::class, 'value']);

    Route::post('/document-upload/{service}', [ServiceController::class, 'documentUpload']);

});

