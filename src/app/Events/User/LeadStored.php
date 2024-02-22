<?php

namespace App\Events\User;

use App\Models\Lead;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class LeadStored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * New lead stored.
     *
     * @var Lead
     */
    public Lead $lead;

    /**
     * Create a new event instance.
     */
    public function __construct(Lead $lead)
    {
        $this->lead = $lead;

        Log::info(
            "Event: Lead stored, " .
            "lead_id: " . $lead->id
        );
    }
}
