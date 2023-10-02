<?php

namespace App\Listeners\Project;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Events\Project\ProjectContentStored;
use App\Models\ProjectEmbedding;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class StoreEmbeddingsFromContent implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * The number of times the queued listener may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * The number of seconds before the job should be retried.
     *
     * @var int
     */
    public int $retryAfter = 10;

    /**
     * Handle the event.
     */
    public function handle(ProjectContentStored $event): void
    {
        $project_content = $event->project_content;

        try {
            $embeddings_text = explode("\n-----\n", $project_content->content);

            $chat_gpt_client = new ChatGPTClient();

            foreach ($embeddings_text as $text) {
                ProjectEmbedding::create([
                    'project_id'         => $project_content->project_id,
                    'project_content_id' => $project_content->id,
                    'text'               => $text,
                    'embedding'          => $chat_gpt_client->getEmbedding($text)
                ]);
            }

            // Update project content status

        } catch (\Throwable $e) {
            Log::error(
                'StoreEmbeddingsFromContent: Internal error, ' .
                "project_id: " . $project_content->project_id . ", " .
                "project_content_id: " . $project_content->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
