<?php

use App\Http\Controllers\AdminAnalyticsController;
use App\Http\Controllers\ApplicationController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\JobController;
use App\Http\Controllers\PaymentController;

Route::prefix('v1')->group(function () {

    //Authentication
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/register', [UserController::class, 'store']);
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:api');

     Route::prefix('users')->middleware(['auth:api', 'role:admin,recruiter'])->group(function () {

         //job analytics admin + recruiter
        Route::get('/analytics', [AdminAnalyticsController::class, 'summary']);
    });

    //User Management (Admin only)
    Route::prefix('users')->middleware(['auth:api', 'role:admin'])->group(function () {

        Route::get('/', [UserController::class, 'index']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    //Company Management (Admin only)
    Route::prefix('companies')->middleware(['auth:api', 'role:admin'])->group(function () {
        Route::get('/', [CompanyController::class, 'index']);
        Route::post('/', [CompanyController::class, 'store']);
        Route::get('/{id}', [CompanyController::class, 'show']);
        Route::put('/{id}', [CompanyController::class, 'update']);
        Route::delete('/{id}', [CompanyController::class, 'destroy']);
    });

    //Job Management (Admin + Recruiter)
    Route::prefix('jobs')->middleware(['auth:api', 'role:admin,recruiter'])->group(function () {
        
        Route::get('/', [JobController::class, 'index']);
        Route::put('/{id}', [JobController::class, 'update']);
        Route::delete('/{id}', [JobController::class, 'destroy']);
        Route::post('/', [JobController::class, 'store']);
        Route::get('/{id}', [JobController::class, 'show']);
    });

    //Job View + Payment (All Authenticated Users)
    Route::prefix('jobs')->middleware(['auth:api'])->group(function () {
        
        Route::get('/', [JobController::class, 'index']);  
        Route::post('/payment', [PaymentController::class, 'createCheckout']);

    });

    // Payment Webhook + Success
    Route::post('/stripe/webhook', [PaymentController::class, 'handle']);
    Route::get('/payments/success', [PaymentController::class, 'success'])->name('api.payment.success');


    //Application management
     Route::prefix('applications')->middleware(['auth:api'])->group(function () {
        
        //view application admin->all, recruiter-> own application, user-> applied application
        Route::get('/', [ApplicationController::class, 'index']);
    });

     Route::prefix('applications')->middleware(['auth:api', 'role:admin,recruiter'])->group(function () {
        
        Route::put('/{jobId}/{applicationId}', [ApplicationController::class, 'update']);
        Route::get('/{id}', [ApplicationController::class, 'show']);
        Route::delete('/{id}', [ApplicationController::class, 'destroy']);
    });

});
