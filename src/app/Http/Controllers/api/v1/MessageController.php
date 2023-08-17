<?php

namespace App\Http\Controllers\api\v1;

use App\Classes\ChatGPT\ChatGPTClient;
use App\Events\Message\MessageStored;
use App\Http\Controllers\Controller;
use App\Http\Requests\Message\StoreMessageRequest;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MessageController extends Controller
{
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
