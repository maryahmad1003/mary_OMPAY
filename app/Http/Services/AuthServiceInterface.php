<?php

namespace App\Http\Services;

use App\Models\User;

interface AuthServiceInterface
{
    public function attemptLogin(string $telephone, string $password): ?User;
    public function findUserByTelephone(string $telephone): ?User;
    public function generateOTP(User $user): string;
    public function verifyOTP(User $user, string $code): bool;
}
