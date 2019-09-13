<?php

namespace App\Http\Controllers;

use App\Election;
use App\User;
use Illuminate\Http\Request;

class NotificationsController extends Controller
{
    private function returnNotifications(User $user)
    : \stdClass
    {
        $unreadNotificationsCount = count($user->unreadNotifications);
        $election = Election::where('status', 'pending')->orWhere('status', 'ongoing')
                            ->orWhere('status', 'completed')->orderBy('id', 'desc')->first();
        $notifications = new \stdClass();
        $notifications->data = $user->notifications;
        $notifications->unreadNotificationsCount = $unreadNotificationsCount;
        $notifications->election = $election;
        return $notifications;
    }

    public function getNotifications(Request $request)
    {
        $notifications = $this->returnNotifications($request->user());
        return ([
            "notifications" => $notifications,
        ]);
    }

    public function readNotifications(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();
        $notifications = $this->returnNotifications($user);
        return response([
            "completed"     => true,
            "notifications" => $notifications
        ]);
    }
}
