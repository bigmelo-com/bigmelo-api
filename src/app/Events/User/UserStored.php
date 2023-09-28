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

class UserStored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * New user stored.
     *
     * @var User
     */
    public User $new_user;

    /**
     * Create a new event instance.
     *
     * @param User $new_user
     *
     * @return void
     */
    public function __construct(User $new_user)
    {
        $this->new_user = $new_user;

        Log::info(
            "Event: New user stored, " .
            "user_id: " . $new_user->id
        );
    }
}
