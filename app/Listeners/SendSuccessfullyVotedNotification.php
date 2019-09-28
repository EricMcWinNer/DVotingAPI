<?php

namespace App\Listeners;

use App\Events\VotedSuccessfully;
use App\Notifications\VotedSuccessfullyNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendSuccessfullyVotedNotification implements ShouldQueue
{
    /**
     * The name of the connection the job should be sent to
     *
     * @var string
     */
    public $connection = 'database';

    /**
     * The name of the queue the job should be sent to.
     *
     * @var string|null
     */
    public $queue = 'listeners';

    /**
     *  The time (seconds) before the job should be processed.
     *
     * @var int
     */
    public $delay = 5;


    /**
     * Create the event listener.
     *
     * @return void
     */

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
     * @param  VotedSuccessfully $event
     * @return void
     */
    public function handle(VotedSuccessfully $event)
    {
        User::find($event->vote->user_id)
            ->notify(new VotedSuccessfullyNotification($event->vote));
    }
}
