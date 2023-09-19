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
            'project:store'
        ]
    ],
    'user' => [
        'abilities' => [
            'message:store'
        ]
    ],

];
