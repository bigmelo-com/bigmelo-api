<?php

namespace App\Http\Controllers\webhooks;

use App\Classes\Twilio\TwilioWhatsAppRequestValidator;
use App\Events\Message\UserMessageStored;
use App\Http\Controllers\Controller;
use App\Models\Message;
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
            $raw_content = $request->getContent();
            parse_str($raw_content, $content);

            $whatsapp_validator = new TwilioWhatsAppRequestValidator($request);

            if ($whatsapp_validator->isValidRequest()) {
                $message_text = $content['Body'];
                $from_number = str_replace('whatsapp:', '', $content['From']);
                $user = User::where('full_phone_number', $from_number)->first();

                $whatsapp_data = [
                    'message_id'            => 0,
                    'media_content_type'    => $content['MediaContentType0'] ?? null,
                    'sms_message_sid'       => $content['SmsMessageSid'] ?? null,
                    'num_media'             => $content['NumMedia'] ?? null,
                    'profile_name'          => $content['ProfileName'] ?? null,
                    'sms_sid'               => $content['SmsSid'] ?? null,
                    'wa_id'                 => $content['WaId'] ?? null,
                    'sms_status'            => $content['SmsStatus'] ?? null,
                    'to'                    => $content['To'] ?? null,
                    'num_segments'          => $content['NumSegments'] ?? null,
                    'referral_num_media'    => $content['ReferralNumMedia'] ?? null,
                    'message_sid'           => $content['MessageSid'] ?? null,
                    'account_sid'           => $content['AccountSid'] ?? null,
                    'from'                  => $content['From'] ?? null,
                    'media_url'             => $content['MediaUrl0'] ?? null,
                    'api_version'           => $content['ApiVersion'] ?? null
                ];

                if (!$user) {
                    $message = Message::create([
                        'user_id' => 0,
                        'content' => $message_text,
                        'source'  => 'WhatsApp'
                    ]);

                    $whatsapp_data['message_id'] = $message->id;
                    WhatsappMessage::create($whatsapp_data);

                    Log::info(
                        'Message from a unknown whatsapp number stored, ' .
                        'message_id: ' . $message->id . ', ' .
                        'phone_number: ' . $from_number
                    );

                    return response()->json(['message' => 'User not found.'], 404);
                }

                $message = Message::create([
                    'user_id' => $user->id,
                    'content' => $message_text,
                    'source'  => 'WhatsApp'
                ]);

                $whatsapp_data['message_id'] = $message->id;
                WhatsappMessage::create($whatsapp_data);

                event(new UserMessageStored($message));

                Log::info(
                    'Message from whatsapp stored, ' .
                    'message_id: ' . $message->id
                );

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
