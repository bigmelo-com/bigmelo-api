<?php

namespace App\Http\Controllers\api\v1;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Events\Project\ProjectContentStored;
use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectContentRequest;
use App\Models\Project;
use App\Models\ProjectContent;
use App\Models\ProjectEmbedding;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectEmbeddingController extends Controller
{
    /**
     * Store the project content from a txt file
     *
     * @param StoreProjectContentRequest $request
     * @param string $project_id
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/project/{project_id}/content",
     *     operationId="storeProjectContent",
     *     description="Store a project content from a txt file.",
     *     tags={"Project Content"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="ID of project related to the new content",
     *         in="path",
     *         name="project_id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"file"},
     *                 @OA\Property(
     *                     property="file",
     *                     type="string",
     *                     format="binary"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Content stored.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Project content upload successfully.")
     *          )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Wrong Request",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function store(StoreProjectContentRequest $request, string $project_id): JsonResponse
    {
        try {
            $project = Project::find($project_id);

            if (!$project) {
                return response()->json(['message' => "Project not found."], 404);
            }

            $is_owner = $project->organization->owner->id === $request->user()->id;

            if (!$is_owner && $request->user()->role != 'admin') {
                return response()->json(['message' => "The current user is not the the organization owner."], 422);
            }

            $file = $request->file('file');
            $content = $file->getContent();
            $embeddings = explode("\n-----\n", $content);
            $total_embeddings = count($embeddings);

            if ($total_embeddings === 0) {
                return response()->json(['message' => "No content to use in the file."], 422);
            }

            $project_content = ProjectContent::create([
                'project_id'       => $project_id,
                'content'          => $content,
                'total_embeddings' => $total_embeddings
            ]);

            event(new ProjectContentStored($project_content));

            return response()->json(
                [
                    'message'            => "Project content upload successfully.",
                    'project_id'         => $project_id,
                    'project_content_id' => $project_content->id
                ],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * get texts and store project embeddings.
     *
     * @param Request $request
     * @param string $project_id
     *
     * @return JsonResponse
     */
    public function store_embeddings(Request $request, string $project_id): JsonResponse
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

            return response()->json(['message' => 'Storing embeddings for project ' . $project->id], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
