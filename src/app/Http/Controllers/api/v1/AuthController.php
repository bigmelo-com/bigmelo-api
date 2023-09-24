<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

    public function signUp(Request $request): JsonResponse 
    {
        try {
            $request->validate([
                'name' => 'required|string',
                'last_name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password' => 'required|string|min:8|confirmed',
                'country_code' => 'required|string',
                'phone_number' => 'required|string',
                'full_phone_number' => 'required|string',
            ]);

            $user = new User();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->country_code = $request->country_code;
            $user->phone_number = $request->phone_number;
            $user->full_phone_number = $request->full_phone_number;
            $user->role = 'user';
            $user->save();

            $token = $user->createToken('token-name', $user->getRoleAbilities());

            return response()->json(
                [
                    'access_token' => $token->plainTextToken,
                    'user' => $user,
                ],
                200
            );

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
