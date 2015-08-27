<?php

namespace Stevebauman\LogReader;

use Illuminate\Support\ServiceProvider;
use Stevebauman\LogReader\Handlers\EloquentHandler;

class LogReaderServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Register the service provider.
     */
    public function register()
    {
        $config = __DIR__.'/Config/config.php';

        $this->mergeConfigFrom($config, 'log-reader');

        $this->publishes([
            $config => config_path('log-reader.php'),
        ], 'config');

        $this->app->bind('log-reader', function () {
            return new LogReader();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['log-reader'];
    }
}
