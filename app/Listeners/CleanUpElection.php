<?php

namespace App\Listeners;

use App\Events\ElectionDeleted;
use App\Utils\UserHelper;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class CleanUpElection implements ShouldQueue
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
     * The time (seconds) during which the job would be processed
     *
     * @var int
     */
    public $timeout = 300;

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
     * @param  ElectionDeleted  $event
     * @return void
     */
    public function handle(ElectionDeleted $event)
    {
        $election = $event->election;
        $users = $election->candidates();
        foreach($users as $user)
        {
            $user = UserHelper::makeVoter($user);
            $user->save();
        }
        $users->delete();

    }
}
