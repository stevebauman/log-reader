<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Log Path
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log path for your application to be read
    | by LogReader.
    |
    */

    'path' => storage_path('logs'),


    /*
    |--------------------------------------------------------------------------
    | Log Model
    |--------------------------------------------------------------------------
    |
    | Here you may configure the log model for storing logs into your database.
    |
    */

    'model' => Stevebauman\LogReader\Models\Log::class,

    /*
    |--------------------------------------------------------------------------
    | Log Handlers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the monolog handlers for handling all log entries.
    |
    | By default, the EloquentHandler is included.
    |
    */

    'handlers' => [

        Stevebauman\LogReader\Handlers\EloquentHandler::class,

    ],
];
