<?php

namespace App\Listeners;

use App\Events\OfficialCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendOfficialCreatedNotification
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
     * @param  OfficialCreated  $event
     * @return void
     */
    public function handle(OfficialCreated $event)
    {
        //
    }
}
