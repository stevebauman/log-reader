![Log Reader Banner]
(https://raw.githubusercontent.com/stevebauman/log-reader/master/log-reader-banner.jpg)

## Description

Log Reader is an easy, Laravel log reader and management tool. You're able to view, manage, and modify log entries
with ease. Using Log Reader is almost exactly like using any Eloquent model.

## Installation



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
        echo $entry->id; // Returns unique md5 string such as: fae8205b40bc9d6663db76011931716f
        echo $entry->level; // Returns the level of the entry such as: emergency, alert, critical, error etc.
        echo $entry->header; // Returns the entry header such as: [2015-03-19 14:56:08] production.ERROR ...
        echo $entry->stack; // Returns the stack trace of the error
    }

##### Finding a log entry:

    LogReader::find($id);

##### Marking an entry as read:

    LogReader::find($id)->markRead();
    
This will cache the entry, and exclude it from any future results.

##### Marking all entries as read:

    LogReader::markRead();
    
This will cache all the entries and exclude them from future results.

##### Deleting a log entry:

    LogReader::find($id)->delete();
    
This will remove the entire entry from the log file, but keep all other entries in-tack.

##### Deleting all log entries:

    LogReader::delete();
    
This will remove all entries in all log files. It will not delete the files however.

##### Getting log entries by level

    LogReader::level('error')->get();
    
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

##### Filter by date

If you're running Laravel 4.2, you must enable daily files to filter your log results by date. You can do so here:

http://laravel.com/docs/4.2/errors#configuration

If you have it enabled or you're running Laravel 5:

    $date = strtotime('2015-03-19');
    
    $entries = LogReader::date($date)->get();
