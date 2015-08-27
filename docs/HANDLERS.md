# Handlers

Log Handlers allow you to save log entries in whatever way you please. Adding your own log handlers has never been so easy.

Create your handler (maybe in `app/Handlers` directory?). As an example, lets create a log handler that overrides
the current build in database handler.

> **Note**: Adding your own handlers does not override the standard Laravel logger. All log entries will still be
> created inside your `.log` files on your server.

    namespace App\Handlers;
    
    use App\Models\Log;
    use Monolog\Handler\AbstractHandler;
    
    class DatabaseLoggerHandler extends AbstractHandler
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
            $model = new Log();
    
            $model->message     = array_get($record, 'message');
            $model->context     = array_get($record, 'context');
            $model->level       = array_get($record, 'level');
            $model->level_name  = array_get($record, 'level_name');
            $model->channel     = array_get($record, 'channel');
            $model->generated   = array_get($record, 'datetime');
            $model->extra       = array_get($record, 'extra');
    
            if($model->save() && $model->exists) {
                return true;
            }
    
            return false;
        }
    }

Then add the handler to the `config/log-reader.php` file:

    /*
    |--------------------------------------------------------------------------
    | Log Handlers
    |--------------------------------------------------------------------------
    |
    | Here you may configure the monolog handlers for handling all log entries.
    |
    | By default, the EloquentHandler is included.
    |
    */

    'handlers' => [
        
        App\Handlers\DatabaseLoggerHandler::class,

    ],
   
Now you're all set! Anytime a log record is generated, it will be sent to your class.
