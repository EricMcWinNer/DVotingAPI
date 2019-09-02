<?php

namespace App\Listeners;

use App\Event\OfficialDeleted;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOfficialDeletedNotification
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  OfficialDeleted  $event
     * @return void
     */
    public function handle(OfficialDeleted $event)
    {
        //
    }
}
