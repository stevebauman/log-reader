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
        $handlers = $this->getHandlers();

        $logger = app('log');

        // Make sure the logger is a Writer instance
        if($logger instanceof Writer) {
            $this->pushHandlers($logger->getMonolog(), $handlers);
        }
    }

    /**
     * Register bindings in the container.
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/Migrations' => database_path('migrations'),
        ], 'migrations');
    }

    /**
     * Returns the LogReader handlers class array.
     *
     * @return array
     */
    public function getHandlers()
    {
        return app('config')->get('log-reader.handlers', []);
    }

    /**
     * Pushes the specific array of handlers into the
     * Monolog Logger instance.
     *
     * @param Logger $logger
     * @param array $handlers
     */
    public function pushHandlers(Logger $logger, array $handlers = [])
    {
        if(count($handlers) > 0) {
            foreach($handlers as $handler) {
                $handler = app($handler);

                $logger->pushHandler($handler);
            }
        }
    }
}
