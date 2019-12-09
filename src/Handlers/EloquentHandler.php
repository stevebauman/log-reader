<?php

namespace Stevebauman\LogReader\Handlers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Model;
use Monolog\Handler\AbstractHandler;
use Stevebauman\LogReader\Exceptions\ModelDoesNotExistException;

class EloquentHandler extends AbstractHandler
{
    /**
     * Adds a log record to the database through eloquent.
     *
     * @param array $record
     *
     * @return bool
     */
    public function handle(array $record = []) : bool
    {
        $model = $this->createNewModel();

        $model->message     = Arr::get($record, 'message');
        $model->context     = Arr::get($record, 'context');
        $model->level       = Arr::get($record, 'level');
        $model->level_name  = Arr::get($record, 'level_name');
        $model->channel     = Arr::get($record, 'channel');
        $model->generated   = Arr::get($record, 'datetime');
        $model->extra       = Arr::get($record, 'extra');

        return $this->createLog($model);
    }

    /**
     * Creates a new log record using Eloquent.
     *
     * @param Model $model
     *
     * @return bool
     */
    public function createLog(Model $model)
    {
        if($model->save() && $model->exists) {
            return true;
        }

        return false;
    }

    /**
     * Returns a new instance of the configured LogReader model.
     *
     * @return \Illuminate\Database\Eloquent\Model
     *
     * @throws ModelDoesNotExistException
     */
    public function createNewModel()
    {
        $model = Config::get('log-reader.model');

        if(class_exists($model)) return new $model;

        $message = 'Log Model could not be found in the configuration.';

        throw new ModelDoesNotExistException($message);
    }
}
