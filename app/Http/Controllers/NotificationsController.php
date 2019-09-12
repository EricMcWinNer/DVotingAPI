<?php

namespace App\Http\Controllers;

use App\Election;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $unreadNotificationsCount = count($user->unreadNotifications);
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')
                            ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
        $notifications = new \stdClass();
        $notifications->data = $user->notifications;
        $notifications->unreadNotificationsCount = $unreadNotificationsCount;
        $notifications->election = $election;
        return ([
            "notifications" => $notifications,
        ]);
    }

    public function readNotifications(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        return response(["completed" => true]);
    }
}
