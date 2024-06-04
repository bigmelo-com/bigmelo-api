<?php

namespace App\Http\Controllers\api\v1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Mail\SupportMailRequest;
use App\Mail\SupportMail;
use App\Mail\SupportNotificationToUserMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    /**
     * Send a support email
     *
     * @param SupportMailRequest $request
     *
     * @return JsonResponse
     *
     * @OA\Post(
     *     path="/v1/contact",
     *     operationId="sendSupportEmail",
     *     description="Send an email to support.",
     *     tags={"Support"},
     *     security={{"bearerAuth":{}}},
     * @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "message", "form_email"},
     *                 @OA\Property(
     *                     property="name",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="message",
     *                     type="string"
     *                 ),
     *                 @OA\Property(
     *                     property="form_email",
     *                     type="string",
     *                     description="Optional email address to include in the notification."
     *                 ),
     *                 example={
     *                     "name": "John Doe",
     *                     "message": "This is a support message.",
     *                     "form_email": "johndoe@example.com"
     *                  }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Email sent successfully.",
     *         @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Email sent successfully."),
     *          )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Wrong Request",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error",
     *         @OA\JsonContent()
     *     )
     * )
    */
    public function sendSupportEmail(SupportMailRequest $request): JsonResponse
    {
        try {
            $data = [
                'name'              => $request->name,
                'lead_id'           => $request->user()->lead->id,
                'user_id'           => $request->user()->id,
                'lead_email'        => $request->user()->lead->email,
                'forms_email'       => $request->form_email,
                'support_message'   => $request->message,
            ];

            $notification_data = [
                'support_message' => $request->message
            ];

            Mail::to(json_decode(config('bigmelo.mail.notification_addresses')))->send(new SupportMail($data));
            Mail::to([$request->user()->lead->email, $request->form_email])->send(new SupportNotificationToUserMail($notification_data));

            return response()->json(
                [
                    'message' => 'Email sent successfully',
                ],
                200
            );

        } catch (\Throwable $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}
