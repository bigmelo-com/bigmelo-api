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
