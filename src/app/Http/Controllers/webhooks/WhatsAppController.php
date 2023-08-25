<?php

namespace App\Http\Controllers\webhooks;

use App\Classes\Twilio\TwilioWhatsAppRequestValidator;
use App\Http\Controllers\Controller;
use App\Models\Message;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function storeMessage(Request $request): JsonResponse
    {
        try {
            $raw_content = $request->getContent();
            parse_str($raw_content, $content);

            $whatsapp_validator = new TwilioWhatsAppRequestValidator($request);

            if ($whatsapp_validator->isValidRequest()) {
                $message_text = $content['Body'];

                $message = Message::create([
                    'user_id' => 1,
                    'content' => $message_text,
                    'source'  => 'WhatsApp'
                ]);

                Log::info(
                    'Message from whatsapp stored, ' .
                    'message_id: ' . $message->id
                );

                return response()->json(['message' => 'Message has been stored successfully.'], 200);
            }

            return response()->json(['message' => 'You are not Twilio :('], 403);

        } catch (\Throwable $e) {
            Log::error(
                'Error webhooks/WhatsAppController::getMessage, ' .
                'error: ' . $e->getMessage()
            );

            throw $e;
        }
    }
}
