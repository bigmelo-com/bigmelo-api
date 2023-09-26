<?php

namespace App\Providers;

use App\Events\Message\UserMessageStored;
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
        'App\Events\Message\UserMessageStored' => [
            'App\Listeners\Message\GetChatGPTMessage',
        ],

        /*
         * Bigmelo message events
         */
        'App\Events\Message\BigmeloMessageStored' => [
            'App\Listeners\Message\SendMessageToWhatsapp',
        ],

        /*
         * Webhooks events
         */
        'App\Events\Webhook\WhatsappMessageReceived' => [
            'App\Listeners\Message\StoreMessageFromWhatsapp',
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
