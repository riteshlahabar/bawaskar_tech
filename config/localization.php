<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fallback locales
    |--------------------------------------------------------------------------
    |
    | The admin Languages module is the real source of truth. This list is only
    | used when that table cannot be read (migrations not run, database down),
    | so a request never ends up with an empty locale list.
    |
    */

    'fallback_locales' => ['en', 'hi', 'mr', 'gu', 'pa', 'te'],

    /*
    |--------------------------------------------------------------------------
    | App translation sync
    |--------------------------------------------------------------------------
    |
    | The mobile apps post their English UI strings once per locale so the
    | server can translate and store them alongside the website's own strings.
    | The cap keeps a single request bounded.
    |
    */

    'app_sync_max_items' => 400,

    'app_string_group' => 'app',

];
