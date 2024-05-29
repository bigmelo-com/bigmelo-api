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
