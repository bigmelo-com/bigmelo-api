<?php

namespace App\Http\Controllers\api\v1;

use App\Events\Message\MessageStored;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Http\Resources\Message\MessageCollection;
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
     *     path="/api/v1/message",
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

            $user_id = $filters['user_id'] ?? null;

            if (!$user_id) {
                return response()->json(['message' => 'Missing parameter: user_id'], 400);
            }

            $user = User::find($user_id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $messages = $user->messages()->orderBy('id', 'desc')->paginate(10);

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
     *     path="/api/v1/message",
     *     operationId="storeMessage",
     *     description="Store a new message.",
     *     tags={"Messages"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"message"},
     *                 @OA\Property(
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "message": "Por que la velocidad de la luz es constante?"
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
            $message = Message::create([
                'user_id' => $request->user()->id,
                'content' => $request->message,
                'source'  => 'API'
            ]);

            event(new MessageStored($message));

            $last_message = Message::latest()->firstOrFail();

            return response()->json(['message' => $last_message->content], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
