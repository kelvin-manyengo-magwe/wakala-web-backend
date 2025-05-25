<?php

namespace App\Services;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Support\Facades\Log;


class SmsService
{
    protected $sms;

    public function __construct()
    {

      $apiKey = config('services.africastalking.key');

          if (empty($apiKey)) {
              throw new \RuntimeException("Africa's Talking API key is missing!");
          }


          Log::debug("Initializing Africa's Talking SDK", [
          'username' => config('services.africastalking.username'),
          'key' => config('services.africastalking.key') ? '***' : 'MISSING'
            ]);

        $this->sms = (new AfricasTalking(
            config('services.africastalking.username'),
            config('services.africastalking.key')
        ))->sms();
    }

            public function sendPasswordSms($phoneNumber, $password)
              {


            try {
                $formattedNumber = $this->formatPhoneNumber($phoneNumber);
                Log::debug("Formatting phone number", [
                    'original' => $phoneNumber,
                    'formatted' => $formattedNumber
                ]);

                $message = "Your Wakala login details:\nPhone: {$formattedNumber}\nPassword: {$password}";
                Log::debug("Preparing SMS message", ['message' => $message]);

                $response = $this->sms->send([
                    'to' => $formattedNumber,
                    'message' => $message,
                    //'from' => config('services.africastalking.sender_id'),
                    'enqueue' => false // Send immediately
                ]);

                Log::debug("Africa's Talking API response", ['response' => $response]);

                return $response['status'] === 'success';

            } catch (\Exception $e) {
                Log::error('SMS sending failed', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
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
