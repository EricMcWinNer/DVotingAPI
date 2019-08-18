<?php

namespace App\Listeners;

use App\Events\ElectionCreated;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class ElectionListener
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
     * @param  ElectionCreated  $event
     * @return void
     */
    public function handle(ElectionCreated $event)
    {
        //
    }
}
