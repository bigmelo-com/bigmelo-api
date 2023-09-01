<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Chat\ChatCollection;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatController extends Controller
{
    /**
     * List the chats
     * Chats are users with messages
     *
     * @return JsonResponse
     *
     * @OA\Get(
     *     path="/v1/chat",
     *     operationId="listChats",
     *     description="List all user chats.",
     *     tags={"Chats"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response="200",
     *         description="List all chats.",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent()
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $users =  User::join('messages', 'users.id', '=', 'messages.user_id')
                ->select('users.*', DB::raw('MAX(messages.created_at) as latest_message_date'))
                ->orderBy('latest_message_date', 'DESC')
                ->groupBy('users.id')
                ->paginate(50);

            return (new ChatCollection($users))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
