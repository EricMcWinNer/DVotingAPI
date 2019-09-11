<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    public function getNotifications(Request $request)
    {
        $user = $request->user();
        $unreadNotificationsCount = count($user->unreadNotifications);
        $notifications = new \stdClass();
        $notifications->data = $user->notifications;
        $notifications->unreadNotificationsCount = $unreadNotificationsCount;
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
