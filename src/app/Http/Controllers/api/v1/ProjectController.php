<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StoreProjectRequest;
use App\Http\Requests\Project\UpdateProjectRequest;
use App\Http\Resources\Project\ProjectCollection;
use App\Models\Organization;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /**
     * List projects of an organization
     *
     * @param Request $request
     * @param int $organization_id
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/organization/{organization_id}/projects",
     *     operationId="ListOrganizationProjects",
     *     description="List organization projects.",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="ID of organization",
     *         in="path",
     *         name="organization_id",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Parameter(
     *          name="page",
     *          in="query",
     *          required=false,
     *          description="Page number for pagination",
     *          @OA\Schema(
     *              type="int"
     *          )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="List all projects.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function index(Request $request, int $organization_id): JsonResponse
    {
        try {
            $organization = Organization::find($organization_id);
            $user = $organization->users()->find($request->user()->id);

            if ($request->user()->role != 'admin' && !$user){
                return response()->json(['message' => 'User is not related to the organization.'], 409);
            }

            $projects = $organization->projects()->paginate(10);

            return (new ProjectCollection($projects))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

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
     *                 required={
     *                      "organization_id", "name", "phone_number", "assistant_description", "assistant_goal",
     *                      "assistant_knowledge_about", "target_public", "language", "default_answer"
     *                  },
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
     *                     property="phone_number",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="assistant_description",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="assistant_goal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="assistant_knowledge_about",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="target_public",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="language",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="default_answer",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "organization_id": 1,
     *                     "name": "Bigmelo",
     *                     "description": "New big organization.",
     *                     "phone_number": "+573121234567",
     *                     "assistant_description": "an official of the Superintendency of Industry and Commerce",
     *                     "assistant_goal": "to advise citizens on their consumer concerns",
     *                     "assistant_knowledge_about": "consumer rights and consumer duties",
     *                     "target_public": "a colombian consumer",
     *                     "language": "Spanish",
     *                     "target_public": "Estoy aqui solo para resolver tus dudas como consumidor"
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
            $project = Project::where('phone_number', $request->phone_number)->first();

            if ($project) {
                return response()->json(['message' => 'phone_number already exist in another project.'], 422);
            }

            $project = Project::create([
                'organization_id'           => $request->organization_id,
                'name'                      => $request->name,
                'description'               => $request->description ?? '',
                'phone_number'              => $request->phone_number,
                'assistant_description'     => $request->assistant_description ?? '',
                'assistant_goal'            => $request->assistant_goal ?? '',
                'assistant_knowledge_about' => $request->assistant_knowledge_about ?? '',
                'target_public'             => $request->target_public ?? '',
                'language'                  => $request->language ?? '',
                'default_answer'            => $request->default_answer ?? '',
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

    /**
     * Update a specific project
     *
     * @param UpdateProjectRequest $request
     * @param int $project_id
     *
     * @return JsonResponse
     *
     *
     * @OA\Patch(
     *     path="/v1/project/{project_id}",
     *     operationId="updateProject",
     *     description="Update a project.",
     *     tags={"Projects"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="ID of project to be updated",
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
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="assistant_description",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="assistant_goal",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="assistant_knowledge_about",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="target_public",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="language",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="default_answer",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "name": "Bigmelo",
     *                     "description": "New big organization.",
     *                     "assistant_description": "an official of the Superintendency of Industry and Commerce",
     *                     "assistant_goal": "to advise citizens on their consumer concerns",
     *                     "assistant_knowledge_about": "consumer rights and consumer duties",
     *                     "target_public": "a colombian consumer",
     *                     "language": "Spanish",
     *                     "target_public": "Estoy aqui solo para resolver tus dudas como consumidor"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Project updd.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Project has been updated successfully.")
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *         @OA\JsonContent()
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
    public function update(UpdateProjectRequest $request, int $project_id): JsonResponse
    {
        try {
            $project = Project::find($project_id);

            if (!$project) {
                return response()->json(['message' => 'Project not found.'], 404);
            }

            $project->name = $request->name ?? $project->name;
            $project->description = $request->description ?? $project->description;
            $project->assistant_description = $request->assistant_description ?? $project->assistant_description;
            $project->assistant_goal = $request->assistant_goal ?? $project->assistant_goal;
            $project->assistant_knowledge_about = $request->assistant_knowledge_about ?? $project->assistant_knowledge_about;
            $project->target_public = $request->target_public ?? $project->target_public;
            $project->language = $request->language ?? $project->language;
            $project->default_answer = $request->default_answer ?? $project->default_answer;

            $project->save();

            return response()->json(['message' => 'Project has been updated successfully.'], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
