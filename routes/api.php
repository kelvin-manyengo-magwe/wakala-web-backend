<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\MobileAuthController;


Route::post('/mobile/login', [MobileAuthController::class, 'login']);


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//making login public
Route::post('/login', [AuthController::class, 'login']);

//uses auth:sanctum middleware to protect routes with token based authenitication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions/sync', [TransactionController::class, 'sync']);
    Route::get('/transactions', [TransactionController::class, 'index']);


      //notifications routes on the mobile
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/mark-as-read', [NotificationController::class, 'markAsRead']);

        Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
});
