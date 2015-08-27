<?php

namespace Stevebauman\LogReader;

use Stevebauman\LogReader\Handlers\EloquentHandler;
use Monolog\Logger;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;

class EloquentLoggerServiceProvider extends ServiceProvider
{
    /**
     * Push the eloquent handler to monolog on boot.
     */
    public function boot()
    {
        $logger = app('log');

        if($logger instanceof Writer) {
            $monolog = $logger->getMonolog();

            if($monolog instanceof Logger) {
                $handler = app(EloquentHandler::class);

                $monolog->pushHandler($handler);
            }
        }
    }

    public function register()
    {
        //
    }
}
