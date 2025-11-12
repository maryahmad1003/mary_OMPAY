<?php

namespace App\Http\Services;

use Twilio\Rest\Client;
use Illuminate\Support\Facades\Log;

class TwilioSmsService implements SmsServiceInterface
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $sid = config('services.twilio.sid');
        $token = config('services.twilio.token');
        $this->from = config('services.twilio.from');

        if ($sid && $token) {
            $this->client = new Client($sid, $token);
        }
    }

    public function sendSms(string $to, string $message): bool
    {
        try {
            if (!$this->client) {
                throw new \Exception('Twilio client not configured');
            }

            $this->client->messages->create($to, [
                'from' => $this->from,
                'body' => $message
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            return false;
        }
    }
}