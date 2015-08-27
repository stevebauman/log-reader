# Using LogReader to save your log entries

LogReader can save your log entries to your database so you can manage them yourself with eloquent.

Follow the installation and usage below to get started.

## Installation

If you want to save all your log events to the database so you can easily manage
them with Eloquent instead of parsing your log files, you can do so.

Add LogReader to your `composer.json` file:

	"stevebauman/log-reader": "1.1.*"

Then run `composer update` on your project source.

Insert the `LoggerServiceProvider` int your `app/config.php` file:

    Stevebauman\LogReader\LoggerServiceProvider::class,

Publish the migration:

    php artisan vendor:publish --provider="Stevebauman\LogReader\LoggerServiceProvider" --tag="migrations"

Run the migration:

    php artisan migrate
    
You're all set! Anything that is logged in your application (such as exceptions) will be stored in the `logs` database
table.

## Usage

#### Using the built in model

LogReader comes with a built in model for retrieving your log entries from the database. You use it like
any ordinary eloquent model:

    use Stevebauman\LogReader\Models\Log;
    
    class LogController extends Controller
    {
        /**
         * Displays all log entries. 
         *
         * @return \Illuminate\View\View
         */
        public function index()
        {
            $logs = Log::paginate(25);
            
            return view('admin.logs.index', compact('logs'));
        }
    }

#### Using your own model

To use your own model, create one with the following casts like so:
    
    namespace App\Models;
    
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
            'read' => 'bool',
            'context' => 'array',
            'extra' => 'array',
        ];
    }

You're all set!

## Model Fields

The model will contain the following attributes:

    $record = Log::first();
    
    $record->id; // (int)
    $record->created_at; // (datetime)
    $record->updated_at; // (datetime)
    $record->read; // Returns true / false (bool)
    $record->message; // The exception that occurred (string)
    $record->context; // The stack trace of the exception (array)
    $record->level; // The integer level of the error (int)
    $record->level_name; // The name of the level of error, such as `INFO`, `ERROR`, `WARNING` (string)
    $record->channel; // The channel the log was sent through, which will most likely be `local` (string)
    $record->generated; // The datetime of the exception (datetime)
    $record->extra; // Extra data that was passed (array)
