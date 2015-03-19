<?php

namespace Stevebauman\LogReader\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * The LogReader facade
 *
 * Class LogReader
 * @package Stevebauman\LogReader\Facades
 */
class LogReader extends Facade
{
    protected static function getFacadeAccessor() { return 'log-reader'; }
}