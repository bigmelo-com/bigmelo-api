<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserMessagesLimitRequest;
use App\Models\User;
use App\Models\UserMessageLimit;
use Illuminate\Http\JsonResponse;

class UserMessagesLimitController extends Controller
{
    /**
     * Store message limits by user
     *
     * @param StoreUserMessagesLimitRequest $request
     * @param $user_id
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/user/{user_id}/messages-limit",
     *     operationId="storeUserMessagesLimit",
     *     description="Store the messages limit by user.",
     *     tags={"Messages Limit"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="ID of user to set the limit",
     *         in="path",
     *         name="user_id",
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
     *                 required={"limit"},
     *                 @OA\Property(
     *                     property="limit",
     *                     type="integer"
     *                 ),
     *                 example={
     *                     "limit": 100
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User stored.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User messages limit has been stored successfully.")
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
    public function store(StoreUserMessagesLimitRequest $request, $user_id): JsonResponse
    {
        try {
            $user = User::find($user_id);

            if (!$user || $user->deleted) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            $limit_data = [
                'user_id'   => $user->id,
                'assigned'  => $request->limit,
                'available' => $request->limit,
                'status'    => 'active'
            ];

            if ($user->hasAvailableMessages()) {
                $limit_data['status'] = 'pending';
            }

            $messages_limit = UserMessageLimit::create($limit_data);

            return response()->json(['message' => 'User messages limit has been stored successfully.'], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
