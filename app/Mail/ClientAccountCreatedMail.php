<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ClientAccountCreatedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $compte;
    public $codePlain;

    /**
     * Create a new message instance.
     */
    public function __construct($compte, $codePlain)
    {
        $this->compte = $compte;
        $this->codePlain = $codePlain;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Votre compte a été créé')
            ->view('emails.client_account_created')
            ->with([
                'compte' => $this->compte,
                'code' => $this->codePlain,
            ]);
    }
}
