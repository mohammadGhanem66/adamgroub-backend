<?php

use App\Http\Controllers\AccountStatmentController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::patch('users/{user_id}/reset-password', [UserController::class, 'resetPassword']);
    Route::post('users/{user_id}/containers', [ContainerController::class, 'store']);
    Route::post('users/{user_id}/account-statments', [AccountStatmentController::class, 'store']);
});