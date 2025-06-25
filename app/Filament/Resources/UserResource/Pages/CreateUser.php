<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

use Filament\Notifications\Notification;


class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;


     protected function afterCreate(): void  {
        $createdWakala = $this->record;

        // Part 1: Your existing SMS logic (which is great)
        $password = $this->data['password'];
        $tillNumbers = collect($createdWakala->till_no ?? [])->map(function ($entry) {
                return "{$entry['mno_key']}: {$entry['till_no']}";
        })->implode(', ');

        try {
            $smsService = new SmsService();
            $message = "Habari {$createdWakala->name}, umesajiliwa kama wakala kwenye mfumo wa WakalaTel. Namba zako za Till ni: {$tillNumbers}. "
                     . "Tumia neno la siri lifuatalo kuingia: {$password} ."
                      . "Tafadhali Hifadhi salama. Karibu!";
            $smsService->sendSms($createdWakala->phone_no, $message);
        } catch (\Exception $e) {
            Log::error("SMS sending failed for new wakala {$createdWakala->phone_no}", ['error' => $e->getMessage()]);
        }

        // ##### THIS IS THE NEW PART FOR DATABASE NOTIFICATIONS #####
        // 1. Find all admin users who should receive the notification.
        $admins = User::whereHas('roles', fn ($query) => $query->where('name', 'admin'))->get();

        // 2. Create the notification content.
        $notification = Notification::make()
            ->title('Wakala Mpya Amesajiliwa!')
            ->body("Wakala '{$createdWakala->name}' ameongezwa kwenye mfumo.")
            ->icon('heroicon-o-user-group')
            ->success();

        // 3. Send it to all admins' databases.
        foreach ($admins as $admin) {
            $notification->sendToDatabase($admin);
        }
    }



          protected function getCreatedNotificationTitle(): ?string // Custom success notification title
            {
                return 'Wakala Ametengenezwa Kikamilifu!';
            }

        protected function getCreatedNotification(): ?Notification // Full custom notification
            {
                return Notification::make()
                    ->success()
                    ->title('Wakala Ametengenezwa!')
                    ->body('Mtumiaji mpya wa wakala ameongezwa kwenye mfumo.')
                    ->icon('heroicon-o-check-circle'); // Or a party popper if available as icon
            }

        // Redirect URL after creation
        protected function getRedirectUrl(): string
            {
                // Optionally redirect to a specific page or just the resource index
                // For now, let's stay on a page that can show confetti
                // We will add a session flash and check for it on ListUsers or ViewUser
                // If CreateRecord directly supports events we can dispatch from here
                // return $this->getResource()::getUrl('index');

                // Store a session flash for the confetti
                session()->flash('user_created_confetti', true);
                session()->flash('new_user_name', $this->record?->name ?? 'Wakala'); // $this->record is the created User

                return $this->getResource()::getUrl('view', ['record' => $this->record]); // Redirect to view the new user
            }
}
