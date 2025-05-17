<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TransactionController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

//uses auth:sanctum middleware to protect routes with token based authenitication
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/transactions/sync', [TransactionController::class, 'sync']);
    Route::get('/transactions', [TransactionController::class, 'index']);
});
