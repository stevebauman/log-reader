<?php

namespace Stevebauman\LogReader\Handlers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
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
    public function handle(array $record = [])
    {
        $model = $this->getModel();

        $model->message     = array_get($record, 'message');
        $model->context     = array_get($record, 'context');
        $model->level       = array_get($record, 'level');
        $model->level_name  = array_get($record, 'level_name');
        $model->channel     = array_get($record, 'channel');
        $model->generated   = array_get($record, 'datetime');
        $model->extra       = array_get($record, 'extra');

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
    public function getModel()
    {
        $model = Config::get('log-reader.model');

        if(class_exists($model)) return new $model;

        $message = 'Log Model could not be found in the configuration.';

        throw new ModelDoesNotExistException($message);
    }
}
