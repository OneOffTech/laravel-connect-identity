<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | The key is used by the encrypter service and should be set to a 
    | random, 32 character string, otherwise encrypted strings will
    | not be safe. By default the current APP_KEY is used.
    |
    | The old_key is used in case is required to rotate the key.
    | https://divinglaravel.com/app_key-is-a-secret-heres-what-its-used-for-how-you-can-rotate-it
    |
    */

    'key' => env('IDENTITY_KEY', env('APP_KEY')),

    'old_key' => env('OLD_IDENTITY_KEY'),
];
