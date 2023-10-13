<?php

namespace App\Providers;

use App\Repositories\MessageRepository;
use Illuminate\Support\ServiceProvider;

class MessageRepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(MessageRepository::class, function() {
            return new MessageRepository();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
