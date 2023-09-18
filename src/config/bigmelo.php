<?php

return [
    'chat_gpt' => [
        'api_key' => env('CHATGPT_API_KEY'),
    ],
    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
    ],
    'message' => [
        'no_available_messages' => 'Ha excedido el límite de mensajes o su límite es cero.',
        'wrong_media_type'      => 'El archivo adjunto no se puede procesar'
    ],
];
