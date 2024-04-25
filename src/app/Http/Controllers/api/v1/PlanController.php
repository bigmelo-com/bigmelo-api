<?php

namespace App\Http\Controllers\api\v1;

use App\Classes\MercadoPago\MercadoPagoClient;
use App\Events\Plan\PlanActivated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Plan\PlanPaymentRequest;
use App\Http\Requests\Project\StorePlanRequest;
use App\Http\Requests\Project\UpdatePlanRequest;
use App\Http\Resources\Plan\PlanCollection;
use App\Models\Payment;
use App\Models\Plan;
use App\Models\Project;
use App\Models\Transaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use MercadoPago\Resources\Invoice\Payment as InvoicePayment;
use MercadoPago\Resources\MerchantOrder\Payment as MerchantOrderPayment;
use MercadoPago\Resources\Payment as ResourcesPayment;

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
     * Get a preference ID for purchasing a specific plan.
     *
     * @OA\Post(
     *     path="/v1/plan/purchase/{plan_id}",
     *     operationId="getPreferenceId",
     *     tags={"Plan"},
     *     summary="Get a preference ID for purchasing a plan",
     *     @OA\Parameter(
     *         name="plan_id",
     *         in="path",
     *         required=true,
     *         description="The ID of the plan to purchase",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="payment_link", type="string", description="URL to initiate the payment")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Plan not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", description="Error message")
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
    public function getPreferenceId(Request $request , int $plan_id): JsonResponse
    {
        try {
            $user = $request->user();
            $project = $user->lead->projects->first();
            $available_plan = $project->plans->find($plan_id);
            
            if(!$available_plan) {
                return response()->json([
                    'message' => 'This plan does not exist'
                ], 404);
            }

            $transaction = Transaction::create([
                'lead_id'       => $user->lead->id,
                'plan_id'       => $available_plan->id,
                'amount'        => $available_plan->price
            ]);

            $mercado_pago_client = new MercadoPagoClient();
            $preference = $mercado_pago_client->createPreference([
                "items" => [
                    [
                    "title" => $available_plan->name,
                    "quantity" => 1,
                    "unit_price" => floatval($available_plan->price),
                    "currency_id" => "USD"
                    ]
                ],
                "back_urls" => [
                    "success" => config('bigmelo.client.url') . '/payment-success',
                    "failure" => config('bigmelo.client.url') . '/payment-failed',
                ],
                "auto_return" => "approved",
                "external_reference" => $transaction->id,
                ]);

            $transaction->preference_id = $preference->id;
            $transaction->save();

            return response()->json([
                'payment_link' => $preference->init_point
            ], 200);

        } catch (\Throwable $e) {
            Log::error(
                'PlanController - getPreferenceId, ' .
                'Error: ' . $e->getMessage() . ',' .
                'Plan_id: ' . $plan_id
            );

            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
    * Register the payment of a plan.
    *
    * @OA\Post(
    *     path="/v1/plan/payment",
    *     operationId="planPayment",
    *     tags={"Plan"},
    *     summary="Register the payment of a plan",
    *     @OA\RequestBody(
    *         required=true,
    *         description="Payment information",
    *         @OA\JsonContent(
    *             required={"preference_id", "payment_id", "status"},
    *             @OA\Property(property="preference_id", type="string", description="The preference ID received when calling getPreferenceId"),
    *             @OA\Property(property="payment_id", type="string", description="The payment ID returned by Mercado Pago"),
    *             @OA\Property(property="status", type="string", description="The payment status. Can be 'approved', 'pending', 'rejected', 'cancelled', 'refunded', or 'in_process'")
    *         )
    *     ),
    *     @OA\Response(
    *         response=200,
    *         description="Transaction registered successfully",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", description="Success message")
    *         )
    *     ),
    *     @OA\Response(
    *         response=404,
    *         description="Transaction not found",
    *         @OA\JsonContent(
    *             @OA\Property(property="message", type="string", description="Error message")
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
    public function planPayment(PlanPaymentRequest $request): JsonResponse
    {
        try {
            $transaction = Transaction::where('preference_id', $request->preference_id)->first();

            if(!$transaction){
                return response()->json([
                    'message' => 'This transaction does not exist'
                ], 404);
            }

            $payment = Payment::where('payment_id', $request->payment_id)->first();
            $transaction->payment_id = $request->payment_id;
            $transaction->status = $payment ? 'completed' : $request->status;
            $transaction->save();

            if($payment){
                event(new PlanActivated($transaction->id));
            }
            
            return response()->json([
                'message' => 'Transacition registered successfully',
            ], 200);

        } catch (\Throwable $e) {
            Log::error(
                'PlanController - planPayment, ' .
                'Error: ' . $e->getMessage() . ',' .
                'Request: ' . json_encode($request->input())

            );
            
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
