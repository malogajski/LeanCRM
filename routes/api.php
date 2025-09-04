<?php

use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

// Authentication routes - stricter rate limiting for auth
Route::prefix('auth')
    ->middleware('crm.access')
    ->middleware('throttle:auth')
    ->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::get('user', [AuthController::class, 'user']);
            Route::post('logout', [AuthController::class, 'logout']);
        });
    });

// Protected routes - standard API rate limiting
// Note: DemoSessionBootstrap runs first via bootstrap/app.php configuration
Route::middleware('auth:sanctum')
    ->middleware('crm.access')
    ->middleware('throttle:api')
    ->group(function () {
        Route::apiResource('companies', CompanyController::class);
        Route::apiResource('contacts', ContactController::class);
        Route::apiResource('deals', DealController::class);
        Route::apiResource('activities', ActivityController::class);
        Route::apiResource('notes', NoteController::class);
    });
