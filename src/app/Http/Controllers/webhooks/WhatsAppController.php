<?php

namespace App\Http\Controllers\webhooks;

use App\Classes\Twilio\TwilioWhatsAppRequestValidator;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    public function storeMessage(Request $request): void
    {
        try {
            $raw_content = $request->getContent();
            parse_str($raw_content, $inputs);

            $whatsapp_validator = new TwilioWhatsAppRequestValidator($request);

            if ($whatsapp_validator->isValidRequest()) {
                Log::info(
                    'inputs: ' . json_encode($inputs)
                );
            } else {
                Log::error('Request No Valid');
            }

        } catch (\Throwable $e) {
            Log::error(
                'Error webhooks/WhatsAppController::getMessage, ' .
                'error: ' . $e->getMessage()
            );

            throw $e;
        }
    }
}
