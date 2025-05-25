<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $apiKey;
    protected $secretKey;
    protected $baseUrl = 'https://apisms.beem.africa/v1/send';

    public function __construct()
    {
        $this->apiKey = config('services.beem.api_key');
        $this->secretKey = config('services.beem.secret_key');

        if (empty($this->apiKey)) {
            throw new \RuntimeException("Beem API key is missing!");
        }

    }


    public function sendSms($phoneNumber, $message)
    {
        $formattedNumber = $this->formatPhoneNumber($phoneNumber);

        $payload = [
          'source_addr' => config('services.beem.sender_id', 'INFO'), // Fallback to 'INFO' if not set
          'encoding' => 0, // 0 for GSM-7 (normal text), 1 for Unicode
          'schedule_time' => '',
            'message' => $message,

            'recipients' => [
                                  [
                                      'recipient_id' => '1',
                                      'dest_addr'=> $formattedNumber
                                  ],
                            ]
        ];

        try {
            /*$response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode("{$this->apiKey}:{$this->secretKey}"),
                'Content-Type' => 'application/json',
            ])->post($this->baseUrl, $payload); */

            Log::info('Payload:', $payload);  //logs to see what application sends


            $response = Http::withBasicAuth($this->apiKey, $this->secretKey)
                          ->asJson()
                          ->post($this->baseUrl, $payload);

            if ($response->successful()) {
                Log::info('Beem SMS sent', ['response' => $response->json()]);
                return true;
            }

            Log::error('Beem SMS failed', [
                'status' => $response->status(),
                'error' => $response->body()
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Beem API error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return false;
        }
    }

    protected function formatPhoneNumber($phoneNumber)
    {
        // Beem requires format: 255XXXXXXXXX (no + sign)
        $phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($phoneNumber, '0')) {
            return '255' . substr($phoneNumber, 1); // Convert 0712... to 255712...
        }

        if (str_starts_with($phoneNumber, '+255')) {
            return substr($phoneNumber, 1); // Remove + prefix
        }

        return $phoneNumber;
    }
}
