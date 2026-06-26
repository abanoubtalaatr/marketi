<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::with('user')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->latest()
            ->paginate(20);

        return $this->success($tickets);
    }

    public function show(SupportTicket $ticket): JsonResponse
    {
        return $this->success($ticket->load('user'));
    }

    public function reply(Request $request, SupportTicket $ticket): JsonResponse
    {
        $request->validate(['admin_reply' => 'required|string']);

        $ticket->update([
            'admin_reply' => $request->admin_reply,
            'status' => 'closed',
            'replied_at' => now(),
        ]);

        return $this->success($ticket, 'Reply sent');
    }

    public function close(SupportTicket $ticket): JsonResponse
    {
        $ticket->update(['status' => 'closed']);

        return $this->success($ticket, 'Ticket closed');
    }
}
