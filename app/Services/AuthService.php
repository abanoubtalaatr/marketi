<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\OtpVerification;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function register(array $data): array
    {
        $user = User::create([
            'name' => $data['name'],
            'username' => $data['username'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'country_phone_code' => $data['country_phone_code'] ?? null,
            'password' => $data['password'],
            'role' => UserRole::Customer,
        ]);

        UserProfile::create(['user_id' => $user->id]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function login(string $identifier, string $password): ?array
    {
        $user = User::where('email', $identifier)
            ->orWhere('phone', $identifier)
            ->first();

        if (! $user || ! Hash::check($password, $user->password)) {
            return null;
        }

        if (! $user->is_active) {
            return null;
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return ['user' => $user, 'token' => $token];
    }

    public function sendOtp(string $phone): OtpVerification
    {
        OtpVerification::where('phone', $phone)->delete();

        $otp = config('services.otp.fixed_code', '123456');

        return OtpVerification::create([
            'phone' => $phone,
            'otp' => $otp,
            'expires_at' => now()->addMinutes(10),
        ]);
    }

    public function verifyOtp(string $phone, string $otp): bool
    {
        $record = OtpVerification::where('phone', $phone)
            ->where('otp', $otp)
            ->where('is_verified', false)
            ->latest()
            ->first();

        if (! $record || $record->isExpired()) {
            return false;
        }

        $record->update(['is_verified' => true]);

        return true;
    }

    public function resetPassword(string $phone, string $password): bool
    {
        $verified = OtpVerification::where('phone', $phone)
            ->where('is_verified', true)
            ->latest()
            ->first();

        if (! $verified) {
            return false;
        }

        $user = User::where('phone', $phone)->first();

        if (! $user) {
            return false;
        }

        $user->update(['password' => $password]);
        OtpVerification::where('phone', $phone)->delete();

        return true;
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }
}
