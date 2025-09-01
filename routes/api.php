<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\ContactController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\ActivityController;
use App\Http\Controllers\Api\NoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Authentication routes
Route::prefix('auth')->middleware('crm.access')->group(function () {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);
    
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('user', [AuthController::class, 'user']);
        Route::post('logout', [AuthController::class, 'logout']);
    });
});

// Protected routes
Route::middleware(['auth:sanctum', 'crm.access'])->group(function () {
    // Companies
    Route::apiResource('companies', CompanyController::class);
    
    // Contacts
    Route::apiResource('contacts', ContactController::class);
    
    // Deals
    Route::apiResource('deals', DealController::class);
    
    // Activities
    Route::apiResource('activities', ActivityController::class);
    
    // Notes
    Route::apiResource('notes', NoteController::class);
});