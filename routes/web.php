<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Filament\Pages\RegistrationSuccess;
use App\Http\Controllers\Auth\NewAdminRegistrationController;
use App\Filament\Pages\AdminRegistration;




Route::get('/', function () {
    return view('welcome');
});

// Test route
Route::get('/test-sms', function() {
    $smsService = new App\Services\SmsService();
    $success = $smsService->sendSms('255653434522', 'Your login credentials.');
    return response()->json(['success' => $success]);
});




Route::group(['middleware' => ['web']], function () { // Use 'web' group for sessions etc.

    // This route makes YOUR AdminRegistration page accessible at /admin/register
    // It will use the simple layout because AdminRegistration::getLayout() defines it.
    Route::get('/admin/register', AdminRegistration::class) // Route to your Filament Page class
        ->middleware('guest:' . config('filament.panels.admin.auth.guard', 'web')) // IMPORTANT: GUEST for panel's guard
        ->name('custom.public.admin.register'); // A unique route name FOR THIS CUSTOM ROUTE

    // Success page (if RegistrationSuccess::getUrl() from panel listing isn't preferred)
    Route::get('/admin/' . RegistrationSuccess::getSlug(), RegistrationSuccess::class)
        ->middleware('guest:' . config('filament.panels.admin.auth.guard', 'web'))
        ->name('custom.admin.registration.success'); // Name used in redirect
});

// Route for a simple "Registration Success" message (if you don't want a Filament Page for it)
Route::get('/admin/shukrani-usanidi', \App\Filament\Pages\RegistrationSuccess::class) // Matches slug if it's 'shukrani-usanidi'
    ->middleware('guest:' . config('filament.panels.admin.auth.guard', 'web'))
    ->name('admin.setup.success');
