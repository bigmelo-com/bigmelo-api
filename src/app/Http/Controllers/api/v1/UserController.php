<?php

namespace App\Http\Controllers\api\v1;

use App\Events\User\UserStored;
use App\Events\User\UserValidated;
use App\Http\Controllers\Controller;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Http\Requests\User\ValidateUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    /**
     * Create a new user
     *
     * @param StoreUserRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/user",
     *     operationId="storeUser",
     *     description="Store a new user.",
     *     tags={"Users"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"message", "source"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="last_name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="country_code",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="phone_number",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="full_phone_number",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="email",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "name": "Peter",
     *                     "last_name": "Parker",
     *                     "country_code": "+57",
     *                     "phone_number": "3121234567",
     *                     "full_phone_number": "+573121234567",
     *                     "email": "peterparker@avengers.com"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User stored.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="User has been stored successfully."),
     *              @OA\Property(property="user_id", type="number", example="88"),
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
    public function store(StoreUserRequest $request): JsonResponse
    {
        try {
            $user = User::create([
                'role'              => 'user',
                'name'              => $request->name,
                'last_name'         => $request->last_name ?? '',
                'email'             => $request->email,
                'country_code'      => $request->country_code,
                'phone_number'      => $request->phone_number,
                'full_phone_number' => $request->full_phone_number,
                'password'          => Hash::make('qwerty123')
            ]);

            event(new UserStored($user));

            return response()->json(
                ['message' => 'User has been stored successfully.', 'user_id' => $user->id],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }
    
    /**
     * Validate a new user
     *
     * @param ValidateUserRequest $request
     *
     * @return JsonResponse
     * 
     * @OA\Post(
     *     path="/v1/user/validate",
     *     summary="Validate user",
     *     description="Validate user with the provided validation code.",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Validation request data",
     *         @OA\JsonContent(
     *             @OA\Property(property="validation_code", type="string", example="123456")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User validated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="User has been validated successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Invalid code or expired validation code",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid code or expired validation code.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     )
     * )
     * 
     */
    public function validateUser(ValidateUserRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->refresh();

            if($user->role !== 'inactive' || User::where('email', $user->email)->whereOr('full_phone_number', $user->full_phone_number)->where('active', true)->exists()){
                return response()->json(
                    [
                        'message' => 'Not Authorized.',
                    ],
                    403
                );
            }

            if(Carbon::now()->diffInMinutes($user->validation_code_sent_at) > 60){
                return response()->json(['message' => 'There are not valid codes under your account, ask for new one.'], 403);
            }

            if($user->validation_code !== $request->validation_code){
                return response()->json(['message' => 'Invalid code.'], 403);
            }

            $user->active = true;
            $user->role = 'user';
            $user->validation_code = null;
            $user->save();

            event(new UserValidated($user));

            $token = $user->createToken('token-name', $user->getRoleAbilities());

            return response()->json(
                [
                    'message'       => 'User has been validated successfully.',
                    'access_token'  => $token->plainTextToken
                ],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * 
     * @OA\Patch(
     *     path="/api/create-validation-code",
     *     summary="Create Validation Code",
     *     description="Create a new validation code for an inactive user.",
     *     tags={"Users"},
     *     @OA\Response(
     *         response=200,
     *         description="Validation code created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="A new validation code was created successfully.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Not Authorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Authorized.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     )
     * )
     *
     */
    public function createValidationCode(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $user->refresh();

            if($user->role !== 'inactive' || User::where('email', $user->email)->whereOr('full_phone_number', $user->full_phone_number)->where('active', true)->exists()){
                return response()->json(
                    [
                        'message' => 'Not Authorized.',
                    ],
                    403
                );
            }

            $user->validation_code = str_pad(rand(1, 999999), 6, "0", STR_PAD_LEFT);
            $user->save();

            event(new UserStored($user));

            return response()->json(
                ['message' => 'A new validation code was created successfully.'],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);

        }
    }

    /**
     * @param UpdateUserRequest $request
     * @return JsonResponse
     *
     * @OA\Patch(
     *     path="/v1/user/update-user",
     *     summary="Update User",
     *     description="Update user information.",
     *     tags={"Users"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="last_name", type="string", example="Doe"),
     *             @OA\Property(property="country_code", type="string", example="+57"),
     *             @OA\Property(property="phone_number", type="string", example="3123456789"),
     *             @OA\Property(property="full_phone_number", type="string", example="+573123456789"),
     *             @OA\Property(property="email", type="string", format="email", example="johndoe@example.com"),
     *             @OA\Property(property="new_password", type="string", format="password", example="password123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Success")   
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Internal Server Error.")
     *         )
     *     )
     * )
     */
    public function updateUser(UpdateUserRequest $request): JsonResponse
    {
        try {
            $user = $request->user();

            $user->name = $request->name ?? $user->name;
            $user->last_name = $request->last_name ?? $user->last_name;
            $user->country_code = $request->country_code ?? $user->country_code;
            $user->phone_number = $request->phone_number ?? $user->phone_number;
            $user->full_phone_number = $request->full_phone_number ?? $user->full_phone_number;
            $user->email = $request->email ?? $user->email;
            $user->password = $request->new_password ? Hash::make($request->new_password) : $user->password;
            $user->save();

            return response()->json(
                ['message' => 'User updated successfully'],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
