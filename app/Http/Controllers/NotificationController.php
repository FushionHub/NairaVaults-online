<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\NotificationPreference;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = Notification::where('user_id', $request->user()->id)
            ->orderByDesc('created_at')
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markAsRead(int $id, Request $request): JsonResponse
    {
        $notification = Notification::where('user_id', $request->user()->id)->findOrFail($id);
        $notification->update(['is_read' => true, 'read_at' => now()]);

        return response()->json(['message' => 'Marked as read']);
    }

    public function updatePreferences(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_deposits' => ['nullable', 'boolean'],
            'email_withdrawals' => ['nullable', 'boolean'],
            'email_trades' => ['nullable', 'boolean'],
            'email_bills' => ['nullable', 'boolean'],
            'email_loans' => ['nullable', 'boolean'],
            'email_kyc' => ['nullable', 'boolean'],
            'email_system' => ['nullable', 'boolean'],
        ]);

        $prefs = NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json(['preferences' => $prefs]);
    }

    public function stream(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        return response()->stream(function () use ($request) {
            $userId = $request->user()->id;
            $lastId = 0;

            while (true) {
                $notifications = Notification::where('user_id', $userId)
                    ->where('id', '>', $lastId)
                    ->where('is_read', false)
                    ->get();

                if ($notifications->isNotEmpty()) {
                    $lastId = $notifications->max('id');
                    echo 'data: ' . json_encode(['notifications' => $notifications]) . "\n\n";
                    ob_flush();
                    flush();
                }

                sleep(5);

                if (connection_aborted()) {
                    break;
                }
            }
        }, 200, [
            'Content-Type' => 'text/event-stream',
            'Cache-Control' => 'no-cache',
            'Connection' => 'keep-alive',
        ]);
    }
}
