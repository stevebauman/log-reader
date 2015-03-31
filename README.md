![Log Reader Banner]
(https://raw.githubusercontent.com/stevebauman/log-reader/master/log-reader-banner.jpg)

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/stevebauman/log-reader/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/stevebauman/log-reader/?branch=master)
[![Build Status](https://travis-ci.org/stevebauman/log-reader.svg?branch=master)](https://travis-ci.org/stevebauman/log-reader)
[![Latest Stable Version](https://poser.pugx.org/stevebauman/log-reader/v/stable.svg)](https://packagist.org/packages/stevebauman/log-reader)
[![Total Downloads](https://poser.pugx.org/stevebauman/log-reader/downloads.svg)](https://packagist.org/packages/stevebauman/log-reader)
[![License](https://poser.pugx.org/stevebauman/log-reader/license.svg)](https://packagist.org/packages/stevebauman/log-reader)

## Description

Log Reader is an easy, tested, Laravel log reader and management tool. You're able to view, manage, and modify log entries
with ease. Using Log Reader is almost exactly like using any Eloquent model.

## Installation

Add LogReader to your `composer.json` file:

	"stevebauman/log-reader": "1.0.*"

Then run `composer update` on your project source.

#### Laravel 4

Add the service provider in `app/config/app.php` file:

	'Stevebauman\LogReader\LogReaderServiceProvider',
	
Add the alias in `app/config/app.php` file:

	'LogReader' => 'Stevebauman\LogReader\Facades\LogReader',

#### Laravel 5

Add the service provider in `config/app.php` file:

	'Stevebauman\LogReader\LogReaderServiceProvider',
	
Add the alias in `config/app.php` file:

	'LogReader' => 'Stevebauman\LogReader\Facades\LogReader',

## Usage

##### Getting all the log entries, use:

    LogReader::get();

A laravel collection is returned with all of the entries. This means your able to use all of Laravels handy collection
functions such as: 

    LogReader::get()->first();
    LogReader::get()->filter($closure);
    LogReader::get()->lists('id', 'header');
    LogReader::get()->search();
    //etc

Now you can loop over your results and display all the log entries:

    $entries = LogReader::get();
    
    foreach($entries as $entry)
    {
        echo $entry->id;        // Returns unique md5 string such as: fae8205b40bc9d6663db76011931716f
        echo $entry->level;     // Returns the level of the entry such as: emergency, alert, critical, error etc.
        echo $entry->header;    // Returns the entry header such as: [2015-03-19 14:56:08] production.ERROR ...
        echo $entry->date;      // Returns the entry date such as: 2015-03-19 14:56:08
        echo $entry->stack;     // Returns the stack trace of the error
        echo $entry->filePath;  // Returns the complete file path of log file which contains the error
    }

##### Finding a log entry:

    LogReader::find($id);

##### Marking an entry as read:

    LogReader::find($id)->markRead();
    
This will cache the entry, and exclude it from any future results.

##### Marking all entries as read:

    $marked = LogReader::markRead();
    
    echo $marked; // Returns integer of how many entries were marked
    
This will cache all the entries and exclude them from future results.

##### Including read entries in your results:

    LogReader::includeRead()->get();
    
    LogReader::includeRead()->find($id);
    
    // etc.

##### Deleting a log entry:

    LogReader::find($id)->delete();
    
    // Or if you've marked the entry as read
    LogReader::includeRead()->find($id)->delete();
    
This will remove the entire entry from the log file, but keep all other entries in-tack.


##### Deleting all log entries:

    $deleted = LogReader::delete();
    
    echo $deleted; // Returns integer of how many entries were deleted
    
This will remove all entries in all log files. It will not delete the files however.

##### Getting log entries by level

    LogReader::level('error')->get();

##### Ordering

You can easily order your results as well using `orderBy($field, $direction = 'desc')`:

    LogReader::orderBy('level')->get();
    LogReader::orderBy('date', 'asc')->get();
    
    // Chaining example
    LogReader::level('error')->orderBy('date', 'asc')->get();
    
##### Filter by date

If you're running Laravel 4.2, you must enable daily files to filter your log results by date. You can do so here:

http://laravel.com/docs/4.2/errors#configuration

If you have it enabled or you're running Laravel 5 (Laravel 5 has this enabled by default):

    $date = strtotime('2015-03-19');
    
    $entries = LogReader::date($date)->get();

##### Paginate your results

    LogReader::paginate(25);
    
This returns a regular Laravel pagination object. You can use it how you'd typically use it on any eloquent model:

    //In your controller
    
    $entries = LogReader::paginate(25);
    
    return view('log', array('entries' => $entries));
    
    //In your view
    
    @foreach($entries as $entry)
        {{ $entry->id }}
    @endforeach
    
    {{ $entries->links() }}

You can also combine functions with the pagination like so:

    $date = strtotime('2015-03-19');

    $entries = LogReader::level('error')->date($date)->paginate(25);
    
##### Setting your own log path

By default LogReader uses the laravel helper `storage_path('logs')` as the log directory. If you need this changed just
set a different path using:

    LogReader::setLogPath('logs');

## Exceptions

##### InvalidTimestampException

If you've inserted a non-valid timestamp into the `date($date)` function, then you will receive an `InvalidTimestampException`
(full namespace is `Stevebauman\LogReader\Exceptions\InvalidTimestampException`).

For example:

    $entries = LogReader::date('10a'); // Throws InvalidTimestampException

##### UnableToRetrieveLogFilesException

If you've set your log path manually and log files do not exist in the given directory, you will receive
an `UnableToRetrieveLogFilesException` (full namespace is `Stevebauman\LogReader\Exceptions\UnableToRetrieveLogFilesException`).

For example:

    LogReader::setLogPath('testing')->get(); // Throws UnableToRetrieveLogFilesException