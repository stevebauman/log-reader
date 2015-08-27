<?php

namespace Stevebauman\LogReader\Facades;

use Illuminate\Support\Facades\Facade;

class LogReader extends Facade
{
    /**
     * Returns the Laravel IoC accessor string.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'log-reader';
    }
}
