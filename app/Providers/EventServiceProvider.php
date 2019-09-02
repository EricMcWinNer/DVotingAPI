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
        Registered::class             => [
            SendEmailVerificationNotification::class,
        ],
        'App\Events\CandidateCreated' => [
            'App\Listeners\SendCandidateCreatedNotifications'
        ],
        'App\Events\OfficialCreated'  => [
            'App\Listeners\SendOfficialCreatedNotification'
        ],
        'App\Event\OfficialDeleted'   => [
            'App\Listeners\SendOfficialDeletedNotification'
        ],
        'App\Event\OfficerCreated'    => [
            'App\Listeners\SendOfficerCreatedNotification'
        ],
        'App\Event\OfficerDeleted'    => [
            'App\Listeners\SendOfficerDeletedNotification'
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
