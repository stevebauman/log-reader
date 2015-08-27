<?php

namespace Stevebauman\LogReader;

use Monolog\Logger;
use Illuminate\Log\Writer;
use Illuminate\Support\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Push the eloquent handler to monolog on boot.
     */
    public function boot()
    {
        $handlers = app('config')->get('log-reader.handlers', []);
        $logger = app('log');

        // Make sure the logger is a Writer instance
        if($logger instanceof Writer) {
            $monolog = $logger->getMonolog();

            // Make sure the Monolog Logger is returned
            if($monolog instanceof Logger) {
                // We'll go through each handler, make them through
                // the IoC, and then push them to monolog
                foreach($handlers as $handler) {
                    $handler = app($handler);

                    $monolog->pushHandler($handler);
                }
            }
        }
    }

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        //
    }
}
