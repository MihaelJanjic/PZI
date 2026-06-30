<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\BugsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\CommentsController;
use App\Http\Controllers\UsersController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

// Superadmin-only routes
Route::prefix("superadmin")->middleware(['auth:sanctum', SuperAdminMiddleware::class])->group(function () {
    Route::delete('/users/remove', [UsersController::class, 'remove']);
    Route::apiResource('users', UsersController::class);
    Route::post('/categories', [CategoriesController::class, 'store']);
    Route::put('/categories/{category}', [CategoriesController::class, 'update']);
    Route::delete('/categories/{category}', [CategoriesController::class, 'destroy']);
    Route::post('/users/change-role', [UsersController::class, 'changeRole']);
    Route::post('/bugs/delete-closed', [BugsController::class, 'deleteClosed']);
});

// Admin-only routes
Route::prefix("admin")->middleware(['auth:sanctum', AdminMiddleware::class])->group(function () {
    Route::post('/bugs/close-resolved', [BugsController::class, 'closeResolved']);
});

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/users/{id}', [UsersController::class, 'destroy']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'getUser']);
    Route::apiResource('bugs', BugsController::class)->only([
        'store',
        'update',
        'destroy'
    ]);

    Route::get('/bugs/{bug}/comments', [CommentsController::class, 'index']);
    Route::post('/bugs/{bug}/comments', [CommentsController::class, 'store']);
    Route::apiResource('images', ImageController::class)->middleware('auth:sanctum');
});

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/categories', [CategoriesController::class, 'index']);
Route::apiResource('bugs', BugsController::class)->only([
    'index',
    'show'
]);