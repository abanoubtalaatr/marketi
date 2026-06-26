<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    use ApiResponse;

    public function index(): JsonResponse
    {
        return $this->success(Notification::latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'type' => 'nullable|string',
            'user_id' => 'nullable|exists:users,id',
            'scheduled_at' => 'nullable|date',
        ]);

        if ($data['user_id'] ?? null) {
            $notification = Notification::create($data);

            return $this->success($notification, 'Notification sent', 201);
        }

        $users = User::where('is_active', true)->pluck('id');
        $count = 0;

        foreach ($users as $userId) {
            Notification::create([...$data, 'user_id' => $userId]);
            $count++;
        }

        return $this->success(['count' => $count], 'Broadcast notification sent', 201);
    }
}
