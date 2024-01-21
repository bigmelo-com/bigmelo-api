<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Resources\User\ProfileResource;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use OpenApi\Annotations as OA;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile information.
     *
     * @OA\Get(
     *     path="/api/v1/profile",
     *     tags={"Profile"},
     *     summary="Get authenticated user's profile",
     *     description="Retrieves the profile information of the currently authenticated user.",
     *     operationId="getProfileInfo",
     *     @OA\Response(
     *         response=200,
     *         description="Profile information retrieved successfully",
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error"
     *     )
     * )
     */
    public function getProfileInfo(Request $request): JsonResponse
    {
        try {

            $user = $request->user();

            return (new ProfileResource($user))->response()->setStatusCode(200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
