<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    use ApiResponse;

    public function show(Request $request): JsonResponse
    {
        return $this->success($request->user()->load('profile', 'addresses'));
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => "sometimes|string|max:255|unique:users,username,{$user->id}",
            'email' => "sometimes|email|unique:users,email,{$user->id}",
            'phone' => "sometimes|string|unique:users,phone,{$user->id}",
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $user->update(collect($data)->only(['name', 'username', 'email', 'phone'])->toArray());

        if ($request->hasAny(['address', 'city', 'country'])) {
            $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                collect($data)->only(['address', 'city', 'country'])->toArray()
            );
        }

        return $this->success($user->fresh(['profile', 'addresses']), 'Profile updated');
    }

    public function uploadImage(Request $request): JsonResponse
    {
        $request->validate(['image' => 'required|image|max:2048']);

        $user = $request->user();
        $path = $request->file('image')->store('profiles', 'public');
        $user->update(['profile_image' => $path]);

        return $this->success([
            'profile_image' => Storage::url($path),
        ], 'Profile image uploaded');
    }

    public function changePassword(Request $request): JsonResponse
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $user = $request->user();

        if (! Hash::check($request->current_password, $user->password)) {
            return $this->error('Current password is incorrect', 422);
        }

        $user->update(['password' => $request->password]);

        return $this->success(null, 'Password changed successfully');
    }
}
