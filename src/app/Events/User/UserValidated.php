<?php

namespace App\Events\User;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UserValidated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * New user stored.
     *
     * @var User
     */
    public User $user_validated;

    /**
     * Create a new event instance.
     *
     * @param User $user_validated
     *
     * @return void
     */
    public function __construct(User $user_validated)
    {
        $this->user_validated = $user_validated;

        Log::info(
            "Event: User validated, " .
            "user_id: " . $user_validated->id
        );
    }
}
