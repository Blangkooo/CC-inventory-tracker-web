<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $notifications = $request->user()
            ->alertNotifications()
            ->with('discrepancyAlert')
            ->latest()
            ->paginate(20);

        return response()->json($notifications);
    }

    public function markRead(Request $request, Notification $notification): JsonResponse
    {
        abort_if($notification->user_id !== $request->user()->id, 403);

        $notification->markAsRead();

        return response()->json([
            'message' => 'Notification marked as read.',
            'notification' => $notification,
        ]);
    }
}
