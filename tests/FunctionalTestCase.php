<?php

namespace Stevebauman\LogReader\Tests;

use Stevebauman\LogReader\Handlers\EloquentHandler;
use Stevebauman\LogReader\LoggerServiceProvider;
use Stevebauman\LogReader\Models\Log;

class FunctionalTestCase extends TestCase
{
    /**
     * Set up the testing environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testbench',
            '--realpath' => realpath(__DIR__.'/../src/Migrations'),
        ]);
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $application
     */
    protected function getEnvironmentSetUp($application)
    {
        // Setup default database to use sqlite :memory:
        $application['config']->set('database.default', 'testbench');
        $application['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $application['config']->set('log-reader.model', Log::class);

        $application['config']->set('log-reader.handlers', [EloquentHandler::class]);
    }

    /**
     * Returns the packages service providers.
     *
     * @param \Illuminate\Foundation\Application $application
     *
     * @return array
     */
    protected function getPackageProviders($application)
    {
        return [LoggerServiceProvider::class];
    }
}
