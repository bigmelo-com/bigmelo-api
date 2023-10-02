<?php

namespace App\Events\Project;

use App\Models\ProjectContent;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProjectContentStored
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var ProjectContent
     */
    public ProjectContent $project_content;

    /**
     * Create a new event instance.
     */
    public function __construct(ProjectContent $project_content)
    {
        $this->project_content = $project_content;

        Log::info(
            "Event: Project content stored, " .
            "project_id: " . $project_content->project_id . ", " .
            "project_content_id: " . $project_content->id
        );
    }
}
