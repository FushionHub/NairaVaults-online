<?php

namespace App\Http\Controllers\Support;

use App\Http\Controllers\Controller;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($tickets);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'subject' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string'],
            'priority' => ['nullable', 'string', 'in:low,medium,high,urgent'],
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::create([
            'user_id' => $request->user()->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'] ?? 'medium',
        ]);

        SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json(['ticket' => $ticket], 201);
    }

    public function show(int $id, Request $request): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)
            ->with('messages')
            ->findOrFail($id);

        return response()->json(['ticket' => $ticket]);
    }

    public function addMessage(int $id, Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string'],
        ]);

        $ticket = SupportTicket::where('user_id', $request->user()->id)->findOrFail($id);

        $msg = SupportMessage::create([
            'ticket_id' => $ticket->id,
            'sender_id' => $request->user()->id,
            'message' => $validated['message'],
        ]);

        return response()->json(['message' => $msg], 201);
    }

    public function close(int $id, Request $request): JsonResponse
    {
        $ticket = SupportTicket::where('user_id', $request->user()->id)->findOrFail($id);
        $ticket->update(['status' => 'closed', 'closed_at' => now()]);

        return response()->json(['message' => 'Ticket closed']);
    }
}
