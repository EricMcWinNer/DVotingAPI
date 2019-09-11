<?php

namespace App\Listeners;


use App\Events\ElectionCreated;
use App\Notifications\ElectionCreatedNotification;
use App\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

/**
 * Class SendElectionCreatedNotification
 * @package App\Listeners
 */
class SendElectionCreatedNotification implements ShouldQueue
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
     * @param ElectionCreated $event
     * @return void
     */
    public function handle(ElectionCreated $event)
    {
        $users = User::all();
        Notification::send($users, new ElectionCreatedNotification($event->election));
    }
}
