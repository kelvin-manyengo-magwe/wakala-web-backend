<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

use Filament\Notifications\Notification;


class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    protected function afterCreate(): void
    {
        $user = $this->record;

        Log::debug("afterCreate hook triggered");

        $password = $this->data['password'];
        $phoneNumber = $user->phone_no;
        $name = $user->name;

        $tillNumbers = collect($user->till_no ?? [])->map(function ($entry) {
                return "{$entry['mno_key']}: {$entry['till_no']}";
              })->implode(', ');


        Log::debug("Preparing to send SMS", [
            'phone' => $phoneNumber,
            'name' => $name,
        ]);

        try {
            $smsService = new SmsService();
            $message = "Habari {$name}, umefanikiwa kusajiliwa kama wakala kwenye mfumo wa WakalaTel. Namba zako za Till ni : {$tillNumbers} ."
                     . "Tumia neno la siri lifuatalo kuingia: {$password}. "
                     . "Tafadhali hifadhi salama. Karibu!";

            $result = $smsService->sendSms($phoneNumber, $message);

            Log::debug("SMS service response", ['result' => $result]);

            if (!$result) {
                throw new \Exception('SMS service returned failure status');
            }

            Log::info("SMS sent successfully to {$phoneNumber}");

        } catch (\Exception $e) {
            Log::error("SMS sending failed", [
                'phone' => $phoneNumber,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
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
