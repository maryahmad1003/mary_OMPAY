<?php

namespace App\Http\Services;

use App\Http\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\Models\CodeSecret;
use Illuminate\Support\Facades\Hash;

class AuthService implements AuthServiceInterface
{
    protected UserRepositoryInterface $users;
    protected SmsServiceInterface $smsService;

    public function __construct(UserRepositoryInterface $users, SmsServiceInterface $smsService)
    {
        $this->users = $users;
        $this->smsService = $smsService;
    }

    public function attemptLogin(string $telephone, string $password): ?User
    {
        $user = $this->users->findByTelephone($telephone);
        if (!$user) {
            return null;
        }
        if (!Hash::check($password, $user->password)) {
            return null;
        }
        return $user;
    }

    public function findUserByTelephone(string $telephone): ?User
    {
        return $this->users->findByTelephone($telephone);
    }

    public function generateOTP(User $user): string
    {
        // Generate 6-digit OTP
        $code = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);

        // Deactivate previous codes
        CodeSecret::where('user_id', $user->id)->update(['is_active' => false]);

        // Create new code
        CodeSecret::create([
            'user_id' => $user->id,
            'code' => $code,
            'is_active' => true,
        ]);

        // Send OTP via SMS
        $message = "Votre code OTP est : $code";
        $phoneNumber = $user->telephone;
        if (!str_starts_with($phoneNumber, '+')) {
            $phoneNumber = '+' . $phoneNumber;
        }
        $this->smsService->sendSms($phoneNumber, $message);

        return $code;
    }

    public function verifyOTP(User $user, string $code): bool
    {
        $codeSecret = CodeSecret::where('user_id', $user->id)
            ->where('code', $code)
            ->where('is_active', true)
            ->first();

        if ($codeSecret) {
            $codeSecret->update(['is_active' => false]);
            return true;
        }

        return false;
    }
}
