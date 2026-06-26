<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponse;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SupportController extends Controller
{
    use ApiResponse;

    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->latest()
            ->paginate(15);

        return $this->success($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
        ]);

        $ticket = SupportTicket::create([
            'ticket_number' => 'TKT-'.strtoupper(Str::random(8)),
            'user_id' => $request->user()->id,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        return $this->success($ticket, 'Ticket created', 201);
    }

    public function show(Request $request, SupportTicket $ticket): JsonResponse
    {
        if ($ticket->user_id !== $request->user()->id) {
            return $this->error('Ticket not found', 404);
        }

        return $this->success($ticket);
    }

    public function faqs(): JsonResponse
    {
        $faqs = [
            ['question' => 'How do I track my order?', 'answer' => 'Go to Orders section and tap on your order to see its status.'],
            ['question' => 'What payment methods are accepted?', 'answer' => 'We accept Cash on Delivery and Online Payment.'],
            ['question' => 'How do I return a product?', 'answer' => 'Contact support through the app to initiate a return.'],
            ['question' => 'How long does delivery take?', 'answer' => 'Delivery typically takes 2-5 business days.'],
        ];

        return $this->success($faqs);
    }
}
