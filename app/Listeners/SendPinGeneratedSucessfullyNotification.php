<?php

namespace App\Listeners;

use App\Events\GenerateRegistrationPins;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Notifications\PinGeneratedSuccessfullyNotification;

class SendPinGeneratedSucessfullyNotification implements ShouldQueue
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
    public $queue = 'election_listeners';

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
    public $timeout = 1000;

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
     * @param  GenerateRegistrationPins  $event
     * @return void
     */
    public function handle(GenerateRegistrationPins $event)
    {
        $user = $event->user;
        $user->notify(new PinGeneratedSuccessfullyNotification($event->election));
    }
}
