<?php

return [
    'chat_gpt' => [
        'api_key' => env('CHATGPT_API_KEY'),
    ],
    'twilio' => [
        'sid'   => env('TWILIO_SID'),
        'token' => env('TWILIO_TOKEN'),
    ]
];
