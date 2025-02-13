<?php

use App\Http\Controllers\AccountStatmentController;
use App\Http\Controllers\AdController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ContainerController;
use App\Http\Controllers\PlaceController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('login', [AuthController::class, 'login']);
Route::apiResource('places', PlaceController::class)->only(['index', 'show']);
Route::apiResource('ads', AdController::class)->only(['index', 'show']);
Route::get('storage/{path}', function ($path) {
    $file = Storage::disk('public')->path($path);

    abort_unless(file_exists($file), 404);

    return response()->file($file);
})->where('path', '.*');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::apiResource('users', UserController::class);
    Route::patch('users/{user_id}/reset-password', [UserController::class, 'resetPassword']);
    Route::post('users/{user_id}/containers', [ContainerController::class, 'store']);
    Route::post('users/{user_id}/account-statments', [AccountStatmentController::class, 'store']);
    Route::apiResource('places', PlaceController::class)->only(['store', 'destroy', 'update']);
    Route::apiResource('ads', AdController::class)->only(['store', 'destroy', 'update']);
    Route::get('users/{user_id}/containers', [UserController::class, 'getUserContainers']);
    Route::get('user/containers', [UserController::class, 'getContainersForLoggedUser']);
    Route::get('user/account-statments', [UserController::class, 'getAccountStatmentsForLoggedUser']);
    Route::get('users/{user_id}/account-statments', [UserController::class, 'getUserAccountStatments']);
    Route::patch('user/password', [AuthController::class, 'changePassword']);
    Route::patch('user/mobile', [UserController::class, 'changeMobile']);
    Route::post('user/notifications/send', [UserController::class, 'sendNotification']);
    Route::post('user/token', [UserController::class, 'StoreMobileToken']);


});