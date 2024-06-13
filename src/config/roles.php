<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles and scopes
    |--------------------------------------------------------------------------
    |
    | Here you may configure the abilities by role.
    | This data is set in this config file, but It would be possible
    | manage it from a different source as a database.
    |
    */

    'admin' => [
        'abilities' => [
            'test:test',
            'message:get',
            'message:store',
            'chats:get',
            'user:store',
            'project:store-embeddings',
            'project:store',
            'project:list',
            'organization:list',
            'organization:store',
            'profile:get',
            'plan:store',
            'plan:get',
            'plan:update',
            'plan:purchase',
            'plan:payment',
            'mail:send',
        ]
    ],
    'user' => [
        'abilities' => [
            'test:test',
            'message:store',
            'project:store',
            'project:list',
            'organization:list',
            'profile:get',
            'plan:get',
            'plan:purchase',
            'plan:payment',
            'mail:send',
        ]
    ],
    'inactive' => [
        'abilities' => [
            'test:test',
            'code:validate',
            'code:get-validation-code'
        ]
    ],
    'forgotten' => [
        'abilities' => [
            'password:recovery'
        ]
    ]
];
