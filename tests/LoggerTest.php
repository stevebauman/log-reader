<?php

namespace Stevebauman\LogReader\Tests;

use Illuminate\Support\Facades\Log;
use Stevebauman\LogReader\Models\Log as LogModel;

class LoggerTest extends FunctionalTestCase
{
    public function testLogging()
    {
        Log::log('info', 'testing', [
            'context' => 'context'
        ]);

        $entry = LogModel::first();

        $expected = [
            'id' => '1',
            'read' => '0',
            'message' => 'testing',
            'context' => '{"context":"context"}',
            'level' => '200',
            'level_name' => 'INFO',
            'channel' => 'testing',
            'extra' => '[]',
        ];

        $this->assertEquals($expected, array_only($entry->getAttributes(), array_keys($expected)));
    }
}
