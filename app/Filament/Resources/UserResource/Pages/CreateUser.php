<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Services\SmsService;
use Illuminate\Support\Facades\Log;
use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

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

        Log::debug("Preparing to send SMS", [
            'phone' => $phoneNumber,
            'name' => $name,
        ]);

        try {
            $smsService = new SmsService();
            $message = "Habari {$name}, umefanikiwa kusajiliwa kama wakala kwenye mfumo wa Wakala App. "
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
}
