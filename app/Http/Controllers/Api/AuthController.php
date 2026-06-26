<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    use ApiResponse;

    public function __construct(private AuthService $authService) {}

    public function register(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'country_phone_code' => 'nullable|string|max:10',
            'email' => 'required|email|unique:users',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $result = $this->authService->register($data);

        return $this->success($result, 'Registration successful', 201);
    }

    public function login(Request $request): JsonResponse
    {
        $request->validate([
            'identifier' => 'required|string',
            'password' => 'required|string',
        ]);

        $result = $this->authService->login(
            $request->identifier,
            $request->password
        );

        if (! $result) {
            return $this->error('Invalid credentials or account inactive', 401);
        }

        return $this->success($result, 'Login successful');
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['phone' => 'required|string']);

        $otp = $this->authService->sendOtp($request->phone);

        Log::info("OTP for {$request->phone}: {$otp->otp}");

        return $this->success(
            ['expires_at' => $otp->expires_at],
            'OTP sent successfully'
        );
    }

    public function verifyOtp(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'otp' => 'required|string|size:6',
        ]);

        if (! $this->authService->verifyOtp($request->phone, $request->otp)) {
            return $this->error('Invalid or expired OTP', 422);
        }

        return $this->success(null, 'OTP verified successfully');
    }

    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'phone' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        if (! $this->authService->resetPassword($request->phone, $request->password)) {
            return $this->error('OTP verification required', 422);
        }

        return $this->success(null, 'Password reset successfully');
    }

    public function logout(Request $request): JsonResponse
    {
        $this->authService->logout($request->user());

        return $this->success(null, 'Logged out successfully');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success($request->user()->load('profile', 'addresses'));
    }
}
