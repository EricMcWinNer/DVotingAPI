<?php

namespace App\Listeners;

use App\Events\GenerateRegistrationPins;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\RegistrationPin;
use Illuminate\Support\Facades\Log;

class GeneratePins implements ShouldQueue
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
        $successes = 0;
        while ($successes < $event->count)
        {   
            try
            {
                $content = random_int(111111111111, 999999999999);
                $pin = new RegistrationPin;
                $pin->content = $content;
                $pin->user_type = $event->pinType;
                $pin->created_by = $event->user->id;
                $pin->save();
                $successes += 1;
            } catch (\Illuminate\Database\QueryException $e)
            {
                $errorCode = $e->errorInfo[1];
                Log::debug($e->getMessage());  
            }
        }
        
    }
}
