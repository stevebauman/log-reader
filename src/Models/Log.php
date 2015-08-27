<?php

namespace Stevebauman\LogReader\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    /**
     * The logs database table.
     *
     * @var string
     */
    protected $table = 'logs';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'context' => 'array',
        'extra' => 'array',
    ];
}
