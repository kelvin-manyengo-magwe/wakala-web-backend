<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;

class SmsService
{
    protected $sms;

    public function __construct()
    {
        $this->sms = (new AfricasTalking(
            config('services.africastalking.username'),
            config('services.africastalking.api_key')
        ))->sms();
    }

    public function sendPasswordSms($phoneNumber, $password)
    {
        try {
            $formattedNumber = $this->formatPhoneNumber($phoneNumber);
            $message = "Your Wakala login details:\nPhone: {$formattedNumber}\nPassword: {$password}";

            $response = $this->sms->send([
                'to' => $formattedNumber,
                'message' => $message,
                'from' => config('services.africastalking.sender_id')
            ]);

            return $response['status'] === 'success';
        } catch (\Exception $e) {
            \Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }

    protected function formatPhoneNumber($phoneNumber)
    {
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '0')) {
            return '+255' . substr($phoneNumber, 1);
        }

        if (!str_starts_with($phoneNumber, '+255')) {
            return '+255' . $phoneNumber;
        }

        return $phoneNumber;
    }
}
