<?php

namespace App\Http\Controllers\api\v1;

use App\Events\Message\BigmeloMessageStored;
use App\Events\Message\UserMessageStored;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\Message\MessageCollection;
use App\Models\Lead;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
    /**
     * List the message related to a user
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/message",
     *     operationId="listMessages",
     *     description="List all message of a user.",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *          name="user_id",
     *          in="query",
     *          required=false,
     *          description="User id related to the messages",
     *          @OA\Schema(
     *              type="int"
     *          )
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
     *         description="List all messages.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $filters = $request->input();

            $lead_id = $filters['lead_id'] ?? null;

            if (!$lead_id) {
                return response()->json(['message' => 'Missing parameter: lead_id'], 400);
            }

            $lead = Lead::find($lead_id);

            if (!$lead) {
                return response()->json(['message' => 'Lead not found'], 404);
            }

            $messages = $lead->messages()->orderBy('id', 'desc')->paginate(10);

            return (new MessageCollection($messages))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    /**
     * Store a new message
     *
     * @param StoreMessageRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/message",
     *     operationId="storeMessage",
     *     description="Store a new message.",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"message", "source"},
     *                 @OA\Property(
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="source",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer"
     *                 ),
     *                 example={
     *                     "message": "Por que la velocidad de la luz es constante?",
     *                     "source": "API",
     *                     "user_id": 1
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Message stores.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="La velocidad de la luz es constante porque si."),
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
    public function store(StoreMessageRequest $request): JsonResponse
    {
        try {
            $lead_id = $request->lead_id ?? $request->user()->lead->id;
            $source  = $request->source;
            $project_id = $request->project_id;

            $lead = Lead::find($lead_id);

            $project = $lead->projects->where('id', $project_id)->first();

            if (!$project) {
                return response()->json(['message' => 'The lead is not in that project.'], 422);
            }

            $message = Message::create([
                'lead_id'       => $lead_id,
                'project_id'    => $project_id,
                'content'       => $request->message,
                'source'        => $source
            ]);

            if ($message->source == 'Admin') {
                event(new BigmeloMessageStored($message));
                return response()->json(['message' => 'Message has been stored successfully.'], 200);
            }

            event(new UserMessageStored($message));

            return response()->json(['message' => 'Message stored successfully.'], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
