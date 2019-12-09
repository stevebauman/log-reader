<?php

namespace Stevebauman\LogReader;

use Illuminate\Support\ServiceProvider;

class LoggerServiceProvider extends ServiceProvider
{
    /**
     * Register the publishable migrations.
     *
     * @return void
     */
    public function boot()
    {
        if (! class_exists('CreateLogsTable')) {
            $this->publishes([
                __DIR__.'/Migrations/create_logs_table.php.stub' => database_path('migrations/'.date('Y_m_d_His', time()).'_create_logs_table.php'),
            ], 'migrations');
        }
    }
}
