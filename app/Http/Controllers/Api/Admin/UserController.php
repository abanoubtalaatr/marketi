<?php

namespace App\Http\Controllers\Api\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\User;
use App\Models\UserProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $users = User::when($request->role, fn ($q) => $q->where('role', $request->role))
            ->when($request->search, fn ($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->latest()
            ->paginate(20);

        return $this->success($users);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|unique:users',
            'email' => 'required|email|unique:users',
            'phone' => 'nullable|string|unique:users',
            'password' => ['required', Password::min(8)],
            'role' => 'required|in:customer,admin,support_agent',
        ]);

        $user = User::create([
            ...$data,
            'role' => UserRole::from($data['role']),
        ]);

        UserProfile::create(['user_id' => $user->id]);

        return $this->success($user, 'User created', 201);
    }

    public function show(User $user): JsonResponse
    {
        return $this->success($user->load('profile', 'orders'));
    }

    public function update(Request $request, User $user): JsonResponse
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'username' => "sometimes|string|unique:users,username,{$user->id}",
            'email' => "sometimes|email|unique:users,email,{$user->id}",
            'phone' => "nullable|string|unique:users,phone,{$user->id}",
            'role' => 'sometimes|in:customer,admin,support_agent',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($data['role'])) {
            $data['role'] = UserRole::from($data['role']);
        }

        $user->update($data);

        return $this->success($user, 'User updated');
    }

    public function destroy(User $user): JsonResponse
    {
        $user->delete();

        return $this->success(null, 'User deleted');
    }
}
