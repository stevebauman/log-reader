# Using LogReader to save your log entries

LogReader can save your log entries to your database so you can manage them yourself with eloquent.

Follow the installation and usage below to get started.

## Database Installation

If you want to save all your log events to the database so you can easily manage
them with Eloquent instead of parsing your log file, you can do so.

Start by inserting the `LoggerServiceProvider` int your `app/config.php` file:

    Stevebauman\LogReader\LoggerServiceProvider::class,

Then publish the migration:

    php artisan vendor:publish --provider="Stevebauman\LogReader\LoggerServiceProvider" --tag="migrations"

Run the migration:

    php artisan migrate
    
You're all set! Anything logged in your application (such as exceptions) will be logged in your database as well.
