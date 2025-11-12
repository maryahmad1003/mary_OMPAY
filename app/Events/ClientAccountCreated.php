<?php

namespace App\Events;

use App\Models\User;
use App\Models\Compte;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ClientAccountCreated
{
    use Dispatchable, SerializesModels;

    public User $user;
    public Compte $compte;
    public string $codePlain;

    public function __construct(User $user, Compte $compte, string $codePlain)
    {
        $this->user = $user;
        $this->compte = $compte;
        $this->codePlain = $codePlain;
    }
}
