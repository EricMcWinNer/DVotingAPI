<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class              => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\CandidateCreated'  => [
            'App\Listeners\SendCandidateCreatedNotification'
        ],
        'App\Events\CandidateDeleted'  => [
            'App\Listeners\SendCandidateDeletedNotification'
        ],
        'App\Events\CandidateUpdated'  => [
            'App\Listeners\SendCandidateUpdatedNotification'
        ],
        'App\Events\OfficialCreated'   => [
            'App\Listeners\SendOfficialCreatedNotification'
        ],
        'App\Events\OfficialDeleted'   => [
            'App\Listeners\SendOfficialDeletedNotification'
        ],
        'App\Events\OfficerCreated'    => [
            'App\Listeners\SendOfficerCreatedNotification'
        ],
        'App\Events\OfficerDeleted'    => [
            'App\Listeners\SendOfficerDeletedNotification'
        ],
        'App\Events\ElectionCreated'   => [
            'App\Listeners\SendElectionCreatedNotification'
        ],
        'App\Events\ElectionStarted'   => [
            'App\Listeners\SendElectionStartedNotification'
        ],
        'App\Events\ElectionCompleted' => [
            'App\Listeners\SendElectionCompletedNotification'
        ],
        'App\Events\ElectionUpdated'   => [
            'App\Listeners\SendElectionUpdatedNotification'
        ],
        'App\Events\ElectionFinalized' => [
            'App\Listeners\CleanUpElection',
            'App\Listeners\SendElectionFinalizedNotification',
        ],
        'App\Events\ElectionDeleted'   => [
            'App\Listeners\CleanUpElection',
            'App\Listeners\SendElectionDeletedNotification',
        ],
        'App\Events\VotedSuccessfully' => [
            'App\Listeners\SendSuccessfullyVotedNotification'
        ],
        'App\Events\GenerateRegistrationPins' => [
            'App\Listeners\GeneratePins',
            'App\Listeners\SendPinGeneratedSucessfullyNotification'
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
