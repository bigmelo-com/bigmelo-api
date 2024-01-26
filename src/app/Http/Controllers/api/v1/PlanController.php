<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Project\StorePlanRequest;
use App\Models\Plan;
use App\Models\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PlanController extends Controller
{
    /**
     * Store a new plan.
     * 
     * @OA\Post(
     *     path="/projects/{project_id}/plans",
     *     operationId="storePlan",
     *     tags={"Plans"},
     *     summary="Store a new plan for a project",
     *     @OA\Parameter(
     *         name="project_id",
     *         in="path",
     *         description="ID of the project",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string"),
     *             @OA\Property(property="description", type="string"),
     *             @OA\Property(property="price", type="number"),
     *             @OA\Property(property="message_limit", type="integer"),
     *             @OA\Property(property="period", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Plan created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="plan_id", type="integer")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not authorized"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Plan already exists"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function store(StorePlanRequest $request, int $project_id): JsonResponse
    {
        try {

            $lead = $request->user()->lead;
            $project = Project::find($project_id);

            if(!$lead->projects->contains($project) || $request->user()->role === 'user'){
                return response()->json(['message' => 'Not Authorized'], 403);
            }

            if($project->plans->contains('name', $request->name)){
                return response()->json(['message' => 'Plan already exists.'], 422);
            }

            $plan = Plan::create([
                'project_id'    => $project_id,
                'name'          => $request->name,
                'description'   => $request->description,
                'price'         => $request->price,
                'message_limit' => $request->message_limit,
                'period'        => $request->period,
            ]);

            return response()->json(
                [
                    'message' => 'Plan has been stored successfully.',
                    'plan_id' => $plan->id,
                ], 
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
