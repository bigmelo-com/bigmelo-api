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

            $total_embeddings = 0;

            foreach ($embeddings_text as $text) {
                $new_embedding = $chat_gpt_client->getEmbedding($text);

                $project_embedding = ProjectEmbedding::create([
                    'project_id'         => $project_content->project_id,
                    'project_content_id' => $project_content->id,
                    'text'               => $text,
                    'embedding'          => $new_embedding->getEmbedding()
                ]);

                $project_embedding->storeOpenAIEmbeddingData($new_embedding);

                $total_embeddings++;
            }

            if ($project_content->total_embeddings === $total_embeddings) {
                $project_content->markAsCompleted();
            } else {
                $project_content->markAsError();
            }

        } catch (\Throwable $e) {
            $project_content->markAsError();

            Log::error(
                'StoreEmbeddingsFromContent: Internal error, ' .
                "project_id: " . $project_content->project_id . ", " .
                "project_content_id: " . $project_content->id . ', ' .
                'error: ' . $e->getMessage()
            );
        }
    }
}
