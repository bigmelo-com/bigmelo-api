<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Models\Project;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Store a new project.
     *
     * @param StoreProjectRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/project",
     *     operationId="storeProject",
     *     description="Store a new project.",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"organization_id", "name", "phone_number"},
     *                 @OA\Property(
     *                     property="organization_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="system_prompt",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone_number",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "organization_id": 1,
     *                     "name": "Bigmelo",
     *                     "description": "New big organization.",
     *                     "system_prompt": "This is an assistant for the Bigmelo clients.",
     *                     "phone_number": "+573121234567"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project stored.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Project has been stored successfully."),
     *              @OA\Property(property="project_id", type="number", example="88"),
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
    public function store(StoreProjectRequest $request): JsonResponse
    {
        try {
            $project = Project::create([
                'organization_id'   => $request->organization_id,
                'name'              => $request->name,
                'description'       => $request->description ?? '',
                'system_prompt'     => $request->system_prompt ?? '',
                'phone_number'      => $request->phone_number,
            ]);

            return response()->json(
                [
                    'message' => 'Project has been stored successfully.',
                    'project_id' => $project->id
                ],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
