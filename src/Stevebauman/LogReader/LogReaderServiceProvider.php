<?php namespace Stevebauman\LogReader;

use Illuminate\Support\ServiceProvider;

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
	 *
	 * @return void
	 */
	public function register()
	{
        if(method_exists($this, 'package'))
        {
            $this->package('stevebauman/log-reader');
        } else
        {

        }
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
