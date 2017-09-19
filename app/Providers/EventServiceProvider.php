<?php

namespace App\Providers;

use App\Events\V1\OrderPlacedNotification;
use App\Http\Listeners\V1\OrderPlacedNotifyViaSms;
use App\Http\Listeners\V1\OrderPlacedNotifyViaEmail;
use Illuminate\Contracts\Events\Dispatcher as DispatcherContract;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        OrderPlacedNotification::class  => [
            OrderPlacedNotifyViaSms::class,
            OrderPlacedNotifyViaEmail::class
        ]
    ];

    /**
     * Register any other events for your application.
     *
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
