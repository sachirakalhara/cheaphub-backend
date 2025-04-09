<?php

namespace App\Http\Controllers\API\Notification;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'unread' => $user->unreadNotifications,
            'all' => $user->notifications,
        ]);
    }

    public function markAllAsRead(Request $request)
    {
        $ids = $request->input('ids', []);

        $request->user()->unreadNotifications()
            ->when(!empty($ids), fn($query) => $query->whereIn('id', $ids))
            ->update(['read_at' => now()]);

        return response()->json([
            'message' => empty($ids) ? 'All notifications marked as read.' : 'Selected notifications marked as read.',
        ]);
    }


    // public function markAllAsRead(Request $request)
    // {
    //     $user = $request->user();
    //     $user->unreadNotifications->markAsRead();

    //     return response()->json(['message' => 'All notifications marked as read']);
    // }
}
