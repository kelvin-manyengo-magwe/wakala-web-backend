<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Filament\Pages\AdminRegistration;
use App\Filament\Pages\RegistrationSuccess;



Route::get('/', function () {
    return view('welcome');
});

// Test route
Route::get('/test-sms', function() {
    $smsService = new App\Services\SmsService();
    $success = $smsService->sendSms('255653434522', 'Your login credentials.');
    return response()->json(['success' => $success]);
});

Route::group(['middleware' => ['web'], 'prefix' => config('filament.panels.admin.path','admin')], function () {

    Route::get('/register', AdminRegistration::class)
        ->middleware('guest:' . config('filament.panels.admin.auth.guard', 'web')) // Ensure only guests on the panel's guard can access
        ->name(config('filament.panels.admin.id', 'admin') . '.auth.register'); // Matches potential internal Filament naming

    Route::get('/usajili-umefanikiwa', RegistrationSuccess::class) // Matches slug
         ->middleware('guest:' . config('filament.panels.admin.auth.guard', 'web')) // Should be accessible as guest
         ->name(config('filament.panels.admin.id', 'admin') . '.pages.registration-success'); // Consistent naming
});


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/test-widget-class', function () {
    $classExists = class_exists(\App\Filament\Widgets\SummaryStatsOverviewWidget::class);
    if ($classExists) {
        return "SUCCESS: Class App\Filament\Widgets\SummaryStatsOverviewWidget FOUND by PHP.";
    } else {
        return "ERROR: Class App\Filament\Widgets\SummaryStatsOverviewWidget NOT FOUND by PHP. Check path, name, namespace, and run composer dump-autoload.";
    }
});

require __DIR__.'/auth.php';
