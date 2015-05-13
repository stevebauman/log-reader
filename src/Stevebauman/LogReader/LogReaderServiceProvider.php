<?php

namespace Stevebauman\LogReader;

use Illuminate\Support\ServiceProvider;

class LogReaderServiceProvider extends ServiceProvider
{
    /**
     * The laravel version.
     *
     * @var int
     */
    public static $laravelVersion = 4;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the service provider.
     */
    public function boot()
    {
        /*
         * The package method was removed on laravel 5, so we know
         * if it does not exist, we're using L5.
         */
        if (!method_exists($this, 'package')) {
            $this::$laravelVersion = 5;
        }
    }

    /**
     * Register the service provider.
     */
    public function register()
    {
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
        return array('log-reader');
    }
}
