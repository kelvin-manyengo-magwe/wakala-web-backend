<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/test-sms', function() {
    $smsService = new \App\Services\SmsService();
    $result = $smsService->sendPasswordSms('0712345678', 'test123');

    return response()->json(['success' => $result]);
});

Route::get('/test-credentials', function() {
    return response()->json([
        'username' => config('services.africastalking.username'),
        'api_key_exists' => !empty(config('services.africastalking.api_key')),
        'sender_id' => config('services.africastalking.sender_id')
    ]);
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
