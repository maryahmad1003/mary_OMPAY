<?php

namespace App\Http\Services;

interface SmsServiceInterface
{
    public function sendSms(string $to, string $message): bool;
}