<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use OpenApi\Annotations as OA;

class AuthController extends Controller
{
    /**
     * Returns an access token.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/auth/get-token",
     *     operationId="getToken",
     *     description="Returns an access token according to the email and password.",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="password",
     *                     type="password"
     *                 ),
     *                 example={
     *                     "email": "your-email@domain.com",
     *                     "password": "YourPassword"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Access Token",
     *         @OA\JsonContent(
     *              @OA\Property(property="access_token", type="string", example="7|RZs7AaR737I5cwh1O09ZoaJTXZeA6yZ2dfhDHola"),
     *          )
     *     ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Your email or password are incorrect.")
     *          )
     *      )
     * )
     */
    public function getToken(Request $request): JsonResponse
    {
        try {
            $login = $request->validate([
                'email' => 'required|string',
                'password' => 'required|string',
            ]);

            if (!Auth::attempt($login)) {
                return response()->json(['message' => 'Your email or password are incorrect.'], 403);
            }

            $user = Auth::user();

            $token = $user->createToken('token-name', $user->getRoleAbilities());

            return response()->json(
                [
                    'access_token' => $token->plainTextToken,
                ],
                200
            );

        } catch (ValidationException $e) {
            return response()->json(['message' => 'Your email or password are incorrect.'], 403);
        }
    }
}
