<?php

namespace App\Http\Controllers\api\v1;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectEmbedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectEmbeddingController extends Controller
{
    /**
     * get texts and store project embeddings.
     *
     * @param Request $request
     * @param Project $project
     *
     * @return JsonResponse
     */
    public function store(Request $request, string $project_id): JsonResponse
    {
        try {
            $project = Project::find($project_id);

            if (!$project) {
                return response()->json(['message' => 'Project not found.'], 404);
            }

            $chat_gpt_client = new ChatGPTClient();
            $embeddings = [];

            foreach ($request->data as $text) {
                $embeddings[] = [
                    'project_id'    => $project->id,
                    'text'          => $text,
                    'embedding'     => $chat_gpt_client->getEmbedding($text)
                ];
            }

            foreach ($embeddings as $embedding) {
                ProjectEmbedding::create($embedding);
            }

            return response()->json(['message' => 'Storing embeddings for project ' . $project->id], 500);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
