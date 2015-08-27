<?php

namespace Stevebauman\LogReader;

use Stevebauman\LogReader\Exceptions\InvalidTimestampException;
use Stevebauman\LogReader\Exceptions\UnableToRetrieveLogFilesException;
use Stevebauman\LogReader\Objects\Entry;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class LogReader
{
    /**
     * The log file path.
     *
     * @var string
     */
    protected $path = '';

    /**
     * The current log file path.
     *
     * @var string
     */
    protected $currentLogPath = '';

    /**
     * Stores the field to order the log entries in.
     *
     * @var string
     */
    protected $orderByField = '';

    /**
     * Stores the direction to order the log entries in.
     *
     * @var string
     */
    protected $orderByDirection = 'asc';

    /**
     * Stores the current level to sort the log entries.
     *
     * @var string
     */
    protected $level = 'all';

    /**
     * Stores the date to search log files for.
     *
     * @var string
     */
    protected $date;

    /**
     * Stores the bool whether or
     * not to return read entries.
     *
     * @var bool
     */
    protected $includeRead = false;

    /**
     * The log levels.
     *
     * @var array
     */
    protected $levels = [
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    ];

    /**
     * Construct a new instance and set the path of the log entries.
     */
    public function __construct()
    {
        $path = Config::get('log-reader.path');

        $this->setLogPath($path);
    }

    /**
     * Returns a Laravel collection of log entries.
     *
     * @return Collection
     *
     * @throws UnableToRetrieveLogFilesException
     */
    public function get()
    {
        $entries = [];

        $files = $this->getLogFiles();

        if (is_array($files)) {
            // Retrieve the log files
            foreach ($files as $log) {
                // Set the current log path for easy manipulation of the file if needed
                $this->setCurrentLogPath($log['path']);

                // Parse the log into an array of entries, passing
                // in the level so it can be filtered
                $parsedLog = $this->parseLog($log['contents'], $this->getLevel());

                // Create a new Entry object for each parsed log entry
                foreach ($parsedLog as $entry) {
                    $newEntry = new Entry($entry);

                    // Check if the entry has already been read, and if read entries should be included.
                    // If includeRead is false, and the entry is read, then continue processing.
                    if (!$this->includeRead && $newEntry->isRead()) {
                        continue;
                    }

                    $entries[] = $newEntry;
                }
            }

            // Return a new Collection of entries
            return $this->postCollectionModifiers(new Collection($entries));
        }

        $message = 'Unable to retrieve files from path: '.$this->getLogPath();

        throw new UnableToRetrieveLogFilesException($message);
    }

    /**
     * Finds a logged error by it's ID.
     *
     * @param string $id
     *
     * @return mixed|null
     */
    public function find($id = '')
    {
        $entries = $this->get()->filter(function ($entry) use ($id) {
            if ($entry->id === $id) {
                return true;
            }
        });

        return $entries->first();
    }

    /**
     * Marks all retrieved log entries as read and
     * returns the number of entries that have been marked.
     *
     * @return int
     */
    public function markRead()
    {
        $entries = $this->get();

        $count = 0;

        foreach ($entries as $entry) {
            if ($entry->markRead()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Deletes all retrieved log entries and returns
     * the number of entries that have been deleted.
     *
     * @return int
     */
    public function delete()
    {
        $entries = $this->get();

        $count = 0;

        foreach ($entries as $entry) {
            if ($entry->delete()) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * Paginates the returned log entries.
     *
     * @param int $perPage
     *
     * @return mixed
     */
    public function paginate($perPage = 25)
    {
        $currentPage = $this->getPageFromInput();

        $offset = (($currentPage - 1) * $perPage);

        $entries = $this->get();

        $total = $entries->count();

        $entries = $entries->slice($offset, $perPage, true)->all();

        return new LengthAwarePaginator($entries, $total, $perPage);
    }

    /**
     * Sets the level to sort the log entries by.
     *
     * @param $level
     *
     * @return $this
     */
    public function level($level)
    {
        $this->setLevel($level);

        return $this;
    }

    /**
     * Sets the date to sort the log entries by.
     *
     * @param $date
     *
     * @return $this
     *
     * @throws InvalidTimestampException
     */
    public function date($date)
    {
        $this->setDate($date);

        return $this;
    }

    /**
     * Includes read entries in the log results.
     *
     * @return $this
     */
    public function includeRead()
    {
        $this->setIncludeRead(true);

        return $this;
    }

    /**
     * Sets the direction to return the log entries in.
     *
     * @param string $field
     * @param string $direction
     *
     * @return $this
     */
    public function orderBy($field, $direction = 'desc')
    {
        $this->setOrderByField($field);
        $this->setOrderByDirection($direction);

        return $this;
    }

    /**
     * Retrieves the orderByField property.
     *
     * @return string
     */
    public function getOrderByField()
    {
        return $this->orderByField;
    }

    /**
     * Retrieves the orderByDirection property.
     *
     * @return string
     */
    public function getOrderByDirection()
    {
        return $this->orderByDirection;
    }

    /**
     * Retrieves the level property.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Retrieves the date property.
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Retrieves the currentLogPath property.
     *
     * @return string
     */
    public function getCurrentLogPath()
    {
        return $this->currentLogPath;
    }

    /**
     * Retrieves the path property.
     *
     * @return string
     */
    public function getLogPath()
    {
        return $this->path;
    }

    /**
     * Sets the directory path to retrieve the
     * log files from.
     *
     * @param $path
     */
    public function setLogPath($path)
    {
        $this->path = $path;
    }

    /**
     * Modifies and returns the collection result if modifiers are set
     * such as an orderBy.
     *
     * @param Collection $collection
     *
     * @return Collection
     */
    private function postCollectionModifiers(Collection $collection)
    {
        if ($this->getOrderByField() && $this->getOrderByDirection()) {
            $collection = $this->processCollectionOrderBy($collection);
        }

        return $collection;
    }

    /**
     * Modifies the collection to be sorted by the orderByField and
     * orderByDirection properties.
     *
     * @param Collection $collection
     *
     * @return $this|Collection
     */
    private function processCollectionOrderBy(Collection $collection)
    {
        $field = $this->getOrderByField();

        $direction = $this->getOrderByDirection();

        $desc = false;

        if ($direction === 'desc') {
            $desc = true;
        }

        return $collection->sortBy(function ($entry) use ($field) {
            if (property_exists($entry, $field)) {
                return $entry->{$field};
            }
        }, SORT_REGULAR, $desc);
    }

    /**
     * Returns the current page from the current input.
     * Used for pagination.
     *
     * @return int
     */
    private function getPageFromInput()
    {
        $page = Input::get('page');

        if (is_numeric($page)) {
            return intval($page);
        }

        return 1;
    }

    /**
     * Sets the currentLogPath property to
     * the specified path.
     *
     * @param $path
     */
    private function setCurrentLogPath($path)
    {
        $this->currentLogPath = $path;
    }

    /**
     * Sets the orderByField property to the specified field.
     *
     * @param string $field
     */
    private function setOrderByField($field)
    {
        $field = strtolower($field);

        $fields = [
            'date',
            'level',
        ];

        if (in_array($field, $fields)) {
            $this->orderByField = $field;
        }
    }

    /**
     * Sets the orderByDirection property to the specified direction.
     *
     * @param string $direction
     */
    private function setOrderByDirection($direction)
    {
        $direction = strtolower($direction);

        if ($direction == 'desc' || $direction == 'asc') {
            $this->orderByDirection = $direction;
        }
    }

    /**
     * Sets the level property to the specified level.
     *
     * @param $level
     */
    private function setLevel($level)
    {
        $level = strtolower($level);

        $this->level = $level;
    }

    /**
     * Sets the date property to filter log results.
     *
     * @param int $date
     *
     * @throws InvalidTimestampException
     */
    private function setDate($date)
    {
        if (!$this->isValidTimeStamp($date)) {
            $message = "Inserted date: $date is not a valid timestamp.";

            throw new InvalidTimestampException($message);
        }

        $this->date = date('Y-m-d', $date);
    }

    /**
     * Sets the includeRead property.
     *
     * @param bool $bool
     */
    private function setIncludeRead($bool = false)
    {
        $this->includeRead = $bool;
    }

    /**
     * Returns true/false if the inserted timestamp is valid.
     *
     * @param $timestamp
     *
     * @return bool
     */
    private function isValidTimestamp($timestamp)
    {
        return is_numeric($timestamp);
    }

    /**
     * Parses the content of the file separating
     * the errors into a single array.
     *
     * @param $content
     * @param string $allowedLevel
     *
     * @return array
     */
    private function parseLog($content, $allowedLevel = 'all')
    {
        $entries = [];

        // The regex pattern to match the logging format '[YYYY-MM-DD HH:MM:SS]'
        $pattern = "/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/";

        preg_match_all($pattern, $content, $headings);

        $data = preg_split($pattern, $content);

        if ($data[0] < 1) {
            $trash = array_shift($data);

            unset($trash);
        }

        foreach ($headings as $heading) {
            for ($i = 0, $j = count($heading); $i < $j; $i++) {
                foreach ($this->levels as $level) {
                    if ($level == $allowedLevel || $allowedLevel == 'all') {
                        if (strpos(strtolower($heading[$i]), strtolower('.'.$level))) {
                            $entries[] = [
                                'level' => $level,
                                'header' => $heading[$i],
                                'stack' => $data[$i],
                                'filePath' => $this->getCurrentLogPath(),
                            ];
                        }
                    }
                }
            }
        }

        return $entries;
    }

    /**
     * Retrieves all the data inside each log file
     * from the log file list.
     *
     * @return array|bool
     */
    private function getLogFiles()
    {
        $data = [];

        $files = $this->getLogFileList();

        if (is_array($files)) {
            $count = 0;

            foreach ($files as $file) {
                $data[$count]['contents'] = file_get_contents($file);
                $data[$count]['path'] = $file;
                $count++;
            }

            return $data;
        }

        return false;
    }

    /**
     * Returns an array of log file paths.
     *
     * @return bool|array
     */
    private function getLogFileList()
    {
        $path = $this->getLogPath();

        if (is_dir($path)) {
            // Matches files in the log directory with the name of 'laravel.log'
            $logPath = sprintf('%s%slaravel.log', $path, DIRECTORY_SEPARATOR);

            if ($date = $this->getDate()) {
                // Matches files in the log directory with the file name of
                // 'laravel-YYYY-MM-DD.log' if a date is supplied
                $logPath = sprintf('%s%slaravel-%s.log', $path, DIRECTORY_SEPARATOR, $date);
            }

            return glob($logPath);
        }

        return false;
    }
}
