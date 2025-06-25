<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    /**
     * Fetch all notifications for the authenticated user.
     */
    public function index()
    {
        $user = Auth::user();

        // Paginate for better performance on mobile
        $notifications = $user->notifications()->paginate(20);

        // Transform the data to match the React Native NotificationData interface
        $formattedNotifications = $notifications->map(function ($notification) {
            return [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? 'Taarifa',
                'message' => $notification->data['body'] ?? 'Ujumbe mpya.',
                'timestamp' => $notification->created_at->toIso8601String(),
                'senderInitial' => substr($notification->data['title'] ?? 'S', 0, 1),
                'avatarColor' => '#81C784', // You can add logic for dynamic colors
                'read' => !is_null($notification->read_at),
            ];
        });

        return response()->json([
            'data' => $formattedNotifications,
            'links' => [
                'next' => $notifications->nextPageUrl(),
            ]
        ]);
    }

    /**
     * Mark all unread notifications as read.
     */
    public function markAsRead(Request $request)
    {
        $user = Auth::user();
        $user->unreadNotifications->markAsRead();

        return response()->json(['status' => 'success', 'message' => 'All notifications marked as read.']);
    }


              public function unreadCount()
              {
                  return response()->json(['count' => Auth::user()->unreadNotifications()->count()]);
              }
}
