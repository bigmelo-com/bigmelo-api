<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Roles and scopes
    |--------------------------------------------------------------------------
    |
    | Here you may configure the abilities by role.
    | This data is set in this config file, but It would be possible
    | manage it from a different source as a data base.
    |
    */

    'admin' => [
        'abilities' => [
            'test:test',
            'message:store'
        ]
    ],

];
