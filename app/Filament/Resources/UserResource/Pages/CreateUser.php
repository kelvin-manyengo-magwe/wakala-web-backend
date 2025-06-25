<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use App\Models\User;
use App\Notifications\NewWakalaCreatedNotification;
use App\Services\SmsService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Log;

// ##### THIS IS THE FIX #####
// 1. Import Filament's Notification class directly for the toast.
use Filament\Notifications\Notification;
// 2. Import Laravel's Notification Facade with a nickname `NotificationFacade`.
use Illuminate\Support\Facades\Notification as NotificationFacade;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $createdWakala = $this->record;

        // Your SMS logic is perfect
        try {
            $password = $this->data['password'];
            $tillNumbers = collect($createdWakala->till_no ?? [])->map(fn($entry) => "{$entry['mno_key']}: {$entry['till_no']}")->implode(', ');
            $message = "Habari {$createdWakala->name}, umesajiliwa kama wakala kwenye mfumo wa WakalaTel. Namba zako za Till ni: {$tillNumbers}. Tumia neno la siri lifuatalo kuingia: {$password}. Tafadhali Hifadhi salama. Karibu!";
            app(SmsService::class)->sendSms($createdWakala->phone_no, $message);
        } catch (\Exception $e) {
            Log::error("SMS sending failed for new wakala {$createdWakala->phone_no}", ['error' => $e->getMessage()]);
        }

        // Now, we use the `NotificationFacade` nickname to send the database notification.
        $admins = User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->get();
        if ($admins->isNotEmpty()) {
            NotificationFacade::send($admins, new NewWakalaCreatedNotification($createdWakala));
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Wakala Ametengenezwa Kikamilifu!';
    }

    // This method now correctly uses Filament\Notifications\Notification
    // because of the updated 'use' statements. The code inside does not need to change.
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Wakala Ametengenezwa!')
            ->body('Mtumiaji mpya wa wakala ameongezwa kwenye mfumo.')
            ->icon('heroicon-o-check-circle');
    }

    // This redirect method is perfect.
    protected function getRedirectUrl(): string
    {
        session()->flash('user_created_confetti', true);
        session()->flash('new_user_name', $this->record?->name ?? 'Wakala');
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}
