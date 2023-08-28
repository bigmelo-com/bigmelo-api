<?php

namespace App\Providers;

use App\Events\Message\ApiMessageStored;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        /*
         * User message events
         */
        'App\Events\Message\ApiMessageStored' => [
            'App\Listeners\Message\GetChatGPTMessage',
        ],

        /*
         * Admin message events
         */
        'App\Events\Message\AdminMessageStored' => [
            'App\Listeners\Message\SendMessageToWhatsapp',
        ],

        /*
         * WhatsApp message events
         */
        'App\Events\Message\WhatsAppMessageStored' => [
            'App\Listeners\Message\GetChatGPTMessage',
        ],

        /*
         * ChatGPT message events
         */
        'App\Events\Message\ChatGPTMessageStored' => [
            'App\Listeners\Message\SendMessageToWhatsapp',
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
