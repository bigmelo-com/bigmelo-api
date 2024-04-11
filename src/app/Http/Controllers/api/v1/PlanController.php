<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StorePlanRequest;
use App\Http\Requests\Project\UpdatePlanRequest;
use App\Http\Resources\Plan\PlanCollection;
use App\Models\Plan;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Get a list of plans for a project.
     *
     * @OA\Get(
     *     path="/v1/project/{project_id}/plan",
     *     operationId="getPlans",
     *     tags={"Plan"},
     *     summary="Get a list of plans for a project",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="id", type="integer"),
     *                 @OA\Property(property="project_id", type="integer"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="description", type="string"),
     *                 @OA\Property(property="price", type="number"),
     *                 @OA\Property(property="message_limit", type="integer"),
     *                 @OA\Property(property="period", type="string"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Project not found",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function index(Request $request, int $project_id): JsonResponse
    {
        try {
            $project = Project::find($project_id);

            $plans = $project->plans()->paginate(10);

            return (new PlanCollection($plans))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }


    /**
    * Get a list of plans for the lead's project.
    *
    * @OA\Get(
    *     path="/v1/plan/purchase",
    *     operationId="getLeadPlans",
    *     tags={"Plan"},
    *     summary="Get a list of plans for the lead's project",
    *     @OA\Response(
    *         response=200,
    *         description="Successful operation",
    *         @OA\JsonContent(
    *             type="array",
    *             @OA\Items(
    *                 type="object",
    *                 @OA\Property(property="id", type="integer"),
    *                 @OA\Property(property="project_id", type="integer"),
    *                 @OA&lt;3>Property(property="name", type="string"),
    *                 @OA\Property(property="description", type="string"),
    *                 @OA\Property(property="price", type="number"),
    *                 @OA\Property(property="message_limit", type="integer"),
    *                 @OA\Property(property="period", type="string"),
    *                 @OA\Property(property="created_at", type="string", format="date-time"),
    *                 @OA\Property(property="updated_at", type="string", format="date-time"),
    *             )
    *         )
    *     ),
    *     @OA\Response(
    *         response=500,
    *         description="Internal server error",
    *          @OA\JsonContent(
    *             @OA\Property(property="message", type="string")
    *         )
    *     )
    * )
    */
    public function getLeadPlans(Request $request): JsonResponse
    {
        try {
            $project = $request->user()->lead->projects->first();
            $plans = $project->plans()->paginate(10);

            return (new PlanCollection($plans))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Store a new plan.
     * 
     * @OA\Post(
     *     path="/v1/plan",
     *     operationId="storePlan",
     *     tags={"Plan"},
     *     summary="Store a new plan for a project",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="project_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="message_limit", type="integer"),
     *             @OA\Property(property="period", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Plan created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Plan already exists",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function store(StorePlanRequest $request): JsonResponse
    {
        try {
            if($request->user()->role === 'user'){
                return response()->json(['message' => 'Not Authorized'], 403);
            }

            $project = Project::find($request->project_id);

            if($project->plans->contains('name', $request->name)){
                return response()->json(['message' => 'A plan with the name ' . $request->name . ' already exists.'], 422);
            }

            Plan::create([
                'project_id'    => $request->project_id,
                'name'          => $request->name,
                'description'   => $request->description,
                'price'         => $request->price,
                'message_limit' => $request->message_limit,
                'period'        => $request->period,
            ]);

            return response()->json(
                [
                    'message' => 'Plan has been stored successfully.'
                ], 
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    /**
     * Update a plan.
     *
     * @OA\Patch(
     *     path="/v1/plan/{plan_id}",
     *     tags={"Plan"},
     *     summary="Update a plan",
     *     description="Updates only the specified fields of the plan with the provided details. Requires authentication.",
     *     operationId="updatePlan",
     *     @OA\Parameter(
     *         name="plan_id",
     *         in="path",
     *         description="ID of the plan to update",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="project_id", type="integer"),
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="message_limit", type="integer"),
     *             @OA\Property(property="period", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Plan updated successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Plan not found",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *          @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function update(UpdatePlanRequest $request, int $plan_id): JsonResponse
    {
        try {
            if($request->user()->role === 'user'){
                return response()->json(['message' => 'Not Authorized'], 403);
            }

            $plan = Plan::find($plan_id);

            if(!$plan){
                return response()->json(['message' => 'Plan not found.'], 404);
            }

            $plan->name = $request->name ?? $plan->name;
            $plan->description = $request->description ?? $plan->description;
            $plan->price = $request->price ?? $plan->price;
            $plan->message_limit = $request->message_limit ?? $plan->message_limit;
            $plan->period = $request->period ?? $plan->period;

            $plan->save();

            return response()->json(['message' => 'Plan has been updated successfully.'], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
