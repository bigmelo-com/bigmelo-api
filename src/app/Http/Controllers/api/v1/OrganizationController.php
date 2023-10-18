<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class OrganizationController extends Controller
{
    /**
     * Store a new organization
     *
     * If the logged user is the admin he can create an organization with any owner
     * if the logged user is not an admin he will be the owner of the new organization
     *
     * @param StoreOrganizationRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/organization",
     *     operationId="storeOrganization",
     *     description="Store a new organization.",
     *     tags={"Organizations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "description"},
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="integer"
     *                 ),
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "user_id": 1,
     *                     "name": "Bigmelo",
     *                     "description": "New big organization."
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organization stored.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Organization has been stored successfully."),
     *              @OA\Property(property="organization_id", type="number", example="88"),
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="User already is an organization owner",
     *         @OA\JsonContent()
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
    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        try {
            $user = $request->user()->role === 'admin' ? User::find($request->user_id) : $request->user();

            if (!$user) {
                return response()->json(['message' => 'User not found.'], 404);
            }

            if ($user->own_organizations()->count() > 0) {
                return response()->json(['message' => 'User already is an organization owner.'], 409);
            }

            $organization = new Organization([
                'name'          => $request->name,
                'description'   => $request->description
            ]);

            $user->own_organizations()->save($organization);

            return response()->json(
                [
                    'message'         => 'Organization has been stored successfully.',
                    'organization_id' => $organization->id
                ],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Update a specific organization
     *
     * @param UpdateOrganizationRequest $request
     * @param int $organization_id
     *
     * @return JsonResponse
     *
     * @OA\Patch(
     *     path="/v1/organization/{organization_id}",
     *     operationId="updateOrganization",
     *     description="Update an organization.",
     *     tags={"Organizations"},
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         description="ID of organization to be updated",
     *         in="path",
     *         name="organization_id",
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
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="description",
     *                     type="string"
     *                 ),
     *                 example={
     *                     "name": "Bigmelo",
     *                     "description": "New big organization."
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Organization stored.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Organization has been updated successfully.")
     *          )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Organization not found",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=409,
     *         description="User is not the organization owner",
     *         @OA\JsonContent()
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
    public function update(UpdateOrganizationRequest $request, int $organization_id): JsonResponse
    {
        try {
            $organization = Organization::find($organization_id);

            if (!$organization) {
                return response()->json(['message' => 'Organization not found.'], 404);
            }

            if ($request->user()->role != 'admin' && $request->user()->id != $organization->owner_id){
                return response()->json(['message' => 'User is not the organization owner.'], 409);
            }

            $organization->name = $request->name ?? $organization->name;
            $organization->description = $request->description ?? $organization->description;
            $organization->save();

            return response()->json(['message' => "Organization has been updated successfully."], 200);

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
