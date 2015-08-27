# Using LogReader to save your log entries

LogReader can save your log entries to your database so you can manage them yourself with eloquent.

Follow the installation and usage below to get started.

## Installation

If you want to save all your log events to the database so you can easily manage
them with Eloquent instead of parsing your log file, you can do so.

Start by inserting the `LoggerServiceProvider` int your `app/config.php` file:

    Stevebauman\LogReader\LoggerServiceProvider::class,

Then publish the migration:

    php artisan vendor:publish --provider="Stevebauman\LogReader\LoggerServiceProvider" --tag="migrations"

Run the migration:

    php artisan migrate
    
You're all set! Anything logged in your application (such as exceptions) will be logged in your database as well.

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

To use your own model, create one with the following casts:
    
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
