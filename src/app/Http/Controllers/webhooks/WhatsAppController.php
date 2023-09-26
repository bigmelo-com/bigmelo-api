<?php

namespace App\Http\Controllers\webhooks;

use App\Classes\Twilio\TwilioWhatsAppRequestValidator;
use App\Events\Message\UserMessageStored;
use App\Events\Webhook\WhatsappMessageReceived;
use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Models\Message;
use App\Models\Project;
use App\Models\User;
use App\Models\WhatsappMessage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Get and stored message from whatsapp
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws \App\Exceptions\Twilio\TwilioRequestValidatorCouldNotGetMessageException
     * @throws \Throwable
     */
    public function storeMessage(Request $request): JsonResponse
    {
        try {
            $whatsapp_validator = new TwilioWhatsAppRequestValidator($request);

            if ($whatsapp_validator->isValidRequest()) {
                $raw_content = $request->getContent();

                event(new WhatsappMessageReceived($raw_content));

                return response()->json(['message' => 'Message has been stored successfully.'], 200);
            }

            Log::warning('Webhooks/WhatsAppController::getMessage. No signed request.');

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
