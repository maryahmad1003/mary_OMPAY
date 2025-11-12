<?php

namespace App\Listeners;

use App\Events\ClientAccountCreated;
use App\Mail\ClientAccountCreatedMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendClientNotification
{
    /**
     * Handle the event.
     */
    public function handle(ClientAccountCreated $event): void
    {
        $user = $event->user;

        // Send SMS - here we log for demo purposes or integrate with a gateway
        $smsText = sprintf("Votre compte %s a été créé. Code: %s", $event->compte->numero_compte, $event->codePlain);
        Log::info('[SMS] Send to ' . ($user->telephone ?? 'unknown') . ': ' . $smsText);

        // Send email when available
        if (! empty($user->email)) {
            try {
                Mail::to($user->email)->send(new ClientAccountCreatedMail($event->compte, $event->codePlain));
                Log::info('Email envoyé à ' . $user->email);
            } catch (\Throwable $e) {
                Log::error('Erreur en envoyant l\'email: ' . $e->getMessage());
            }
        }
    }
}
