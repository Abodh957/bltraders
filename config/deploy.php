<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Deploy token
    |--------------------------------------------------------------------------
    | Optional shared secret for the HTTP deploy endpoints (/migration, etc).
    |
    |   empty -> no token needed, plain /migration works
    |   set   -> ?token=... (or an X-Deploy-Token header) becomes mandatory
    |
    | If you set it, use at least 20 characters. Generate one with:
    |   php -r "echo bin2hex(random_bytes(24));"
    */
    'token' => env('DEPLOY_TOKEN', ''),

];
