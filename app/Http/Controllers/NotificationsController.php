<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Session-authed web counterpart to Api\NotificationController — same
 * Notification model, no HTTP hop, since the JWT-guarded API can't be
 * called directly from a session-authenticated page.
 */
class NotificationsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->alertNotifications()
            ->with('discrepancyAlert')
            ->latest()
            ->take(20)
            ->get();

        return response()->json([
            'notifications' => $notifications,
            'unread_count' => $request->user()->alertNotifications()->whereNull('read_at')->count(),
        ]);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json(['success' => true, 'notification' => $notification]);
    }
}
