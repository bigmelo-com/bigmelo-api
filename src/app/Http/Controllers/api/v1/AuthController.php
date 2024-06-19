<?php

namespace App\Http\Controllers\api\v1;

use App\Events\User\UserStored;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\RecoveryPasswordRequest;
use App\Http\Requests\User\ResetPasswordRequest;
use App\Http\Requests\User\SignUpRequest;
use App\Mail\RecoveryPasswordMail;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

            if($user->role === "forgotten") {
                $user->role = $user->active ? 'user' : 'inactive';
                $user->save();
            }

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

    /**
     * Register new user.
     *
     * @param SignUpRequest $request
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/v1/auth/signup",
     *     tags={"Auth"},
     *     summary="Sign up a new user",
     *     description="Creates a new user account with specified credentials and returns an access token if successful.",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User signup details",
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe", description="The user's full name"),
     *             @OA\Property(property="last_name", type="string", example="Smith", description="The user's last name"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com", description="The user's email address"),
     *             @OA\Property(property="password", type="string", format="password", example="password123", description="The user's password"),
     *             @OA\Property(property="country_code", type="string", example="US", description="The user's country code"),
     *             @OA\Property(property="phone_number", type="string", example="1234567890", description="The user's phone number"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful signup",
     *         @OA\JsonContent(
     *             @OA\Property(property="access_token", type="string"),
     *             @OA\Property(property="token_type", type="string", example="bearer"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Email or phone number already in use",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email or phone number is already in use.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function signUp(SignUpRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->whereOr('full_phone_number', $request->full_phone_number)->where('active', true)->exists();

            if($user){
                return response()->json(
                    [
                        'message'   => 'Email or phone number is already in use.'
                    ],
                    422
                );
            }

            User::where('email', $request->email)->whereOr('full_phone_number', $request->full_phone_number)->update(['validation_code' => null]);

            $user = new User();
            $user->name = $request->name;
            $user->last_name = $request->last_name;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->country_code = $request->country_code;
            $user->phone_number = $request->phone_number;
            $user->full_phone_number = $request->full_phone_number;
            $user->role = 'inactive';
            $user->validation_code = str_pad(rand(1, 999999), 6, "0", STR_PAD_LEFT);
            $user->active = false;
            $user->save();
            
            event(new UserStored($user));

            $token = $user->createToken('token-name', $user->getRoleAbilities());

            return response()->json(
                [
                    'access_token' => $token->plainTextToken,
                ],
                200
            );

        } catch (ValidationException $e) {
            return response()->json(['message' => $e->errors()], 400);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Initiate password recovery for a user.
     *
     * @param RecoveryPasswordRequest $request
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/v1/auth/password-recovery",
     *     tags={"Auth"},
     *     summary="Initiate password recovery",
     *     description="Sends a password recovery link to the user's email if it exists",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User email",
     *         @OA\JsonContent(
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com", description="The user's email address"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Recovery link sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Recovery link has been seent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Email not linked to any user",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Email not linked to any user")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function passwordRecovery(RecoveryPasswordRequest $request): JsonResponse
    {
        try {
            $user = User::where('email', $request->email)->first();

            if (!$user) {
                return response()->json(
                    ['message' => 'Email not linked to any user'],
                    404
                );
            }

            $user->role = 'forgotten';
            $user->save();
            $token = $user->createToken('recovery-token', $user->getRoleAbilities());
            $data = [
                'link' => config("bigmelo.client.url") . '/reset-password/' . $token->plainTextToken
            ];

            Mail::to($user->email)->send(new RecoveryPasswordMail($data));

            return response()->json(
                ['message' => 'Recovery link has been seent'],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    /**
     * Reset user password.
     *
     * @param ResetPasswordRequest $request
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/v1/auth/reset-password",
     *     tags={"Auth"},
     *     summary="Reset password",
     *     description="Resets the password for the authenticated user",
     *     security={
     *         {"bearerAuth": {}},
     *     },
     *     @OA\RequestBody(
     *         required=true,
     *         description="New user password",
     *         @OA\JsonContent(
     *             @OA\Property(property="password", type="string", format="password", example="new_password123", description="The user's new password"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Password reset successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string")
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->password = Hash::make($request->password);
            $user->role = 'user';
            $user->tokens()->delete();
            $user->save();

            return response()->json(
                ['message' => "Password updated succesfully"],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
}
