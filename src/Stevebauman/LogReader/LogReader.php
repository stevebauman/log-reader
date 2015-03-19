<?php

namespace Stevebauman\LogReader;

use Stevebauman\LogReader\Objects\Entry;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Paginator;
use Illuminate\Support\Collection;

/**
 * Class LogReader
 * @package Stevebauman\LogReader
 */
class LogReader
{
    /**
     * The log file path
     *
     * @var string
     */
    protected $path = '';

    /**
     * The current log file path
     *
     * @var string
     */
    protected $currentLogPath = '';

    /**
     * Stores the direction to order the log entries in
     *
     * @var string
     */
    protected $orderBy = 'asc';

    /**
     * Stores the current level to sort the log entries
     *
     * @var string
     */
    protected $level = 'all';

    /**
     * Stores the date to search log files for
     *
     * @var string
     */
    protected $date = 'none';

    /**
     * The log levels
     *
     * @var array
     */
    protected $levels = array(
        'emergency',
        'alert',
        'critical',
        'error',
        'warning',
        'notice',
        'info',
        'debug',
    );

    /**
     * Construct a new instance and set the path of the log entries
     */
    public function __construct()
    {
        $this->path = storage_path('logs');
    }

    /**
     * Returns a Laravel collection of log entries
     *
     * @return Collection
     */
    public function get()
    {
        $entries = array();

        /*
         * Retrieve the log files
         */
        foreach($this->getLogFiles() as $log)
        {
            /*
             * Set the current log path for easy manipulation
             * of the file if needed
             */
            $this->setCurrentLogPath($log['path']);

            /*
             * Parse the log into an array of entries, passing in the level
             * so it can be filtered
             */
            $parsedLog = $this->parseLog($log['contents'], $this->getLevel());

            /*
             * Create a new Entry object for each parsed log entry
             */
            foreach($parsedLog as $entry)
            {
                $newEntry = new Entry($entry);

                if($newEntry->isRead()) continue;

                $entries[] = $newEntry;
            }
        }

        /*
         * Return a new Collection of entries
         */
        return new Collection($entries);
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

        foreach($entries as $entry) if($entry->markRead()) $count++;

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

        foreach($entries as $entry) if($entry->delete()) $count++;

        return $count;
    }

    /**
     * Paginates the returned log entries
     *
     * @param int $perPage
     * @return mixed
     */
    public function paginate($perPage = 25)
    {
        $currentPage = $this->getPageFromInput();

        $offset = (($currentPage - 1) * $perPage);

        $entries = $this->get();

        $total = $entries->count();

        $entries = $entries->slice($offset, $perPage, true)->all();

        return Paginator::make($entries, $total, $perPage);
    }

    /**
     * Sets the level to sort the log entries by
     *
     * @param $level
     * @return $this
     */
    public function level($level)
    {
        $this->setLevel($level);

        return $this;
    }

    /**
     * Sets the date to sort the log entries by
     *
     * @param $date
     * @return $this
     */
    public function date($date)
    {
        $this->setDate($date);

        return $this;
    }

    /**
     * Sets the direction to return the log entries in
     *
     * @param $direction
     * @return $this
     */
    public function orderBy($direction)
    {
        $this->setOrderBy($direction);

        return $this;
    }

    /**
     * Retrieves the orderBy property
     *
     * @return string
     */
    public function getOrderBy()
    {
        return $this->orderBy;
    }

    /**
     * Retrieves the level property
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Retrieves the date property
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Retrieves the currentLogPath property
     *
     * @return string
     */
    public function getCurrentLogPath()
    {
        return $this->currentLogPath;
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

        if(is_numeric($page)) return intval($page);

        return 1;
    }

    /**
     * Sets the currentLogPath property to
     * the specified path
     *
     * @param $path
     */
    private function setCurrentLogPath($path)
    {
        $this->currentLogPath = $path;
    }

    /**
     * Sets the orderBy property to the specified direction
     *
     * @param $direction
     */
    private function setOrderBy($direction)
    {
        $direction = strtolower($direction);

        if($direction == 'desc' || $direction == 'asc') $this->orderBy = $direction;
    }

    /**
     * Sets the level property to the specified level
     *
     * @param $level
     */
    private function setLevel($level)
    {
        $level = strtolower($level);

        $this->level = $level;
    }

    /**
     * Sets the date property to search the log files for
     *
     * @param $date
     */
    private function setDate($date)
    {
        $this->date = date('Y-m-d', $date);
    }

    /**
     * Parses the content of the file separating the errors into
     * a single array
     *
     * @param $content
     * @param string $allowedLevel
     * @return array
     */
    private function parseLog($content, $allowedLevel = 'all')
    {
        $log = array();

        // The regex pattern to match the logging format '[YYYY-MM-DD HH:MM:SS]'
        $pattern = "/\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\].*/";

        preg_match_all($pattern, $content, $headings);

        $data = preg_split($pattern, $content);

        if ($data[0] < 1)
        {
            $trash = array_shift($data);

            unset($trash);
        }

        foreach ($headings as $heading)
        {
            for ($i = 0, $j = count($heading); $i < $j; $i++)
            {
                foreach ($this->levels as $level)
                {
                    if ($level == $allowedLevel || $allowedLevel == 'all')
                    {
                        if (strpos(strtolower($heading[$i]), strtolower('.'.$level)))
                        {
                            $log[] = array(
                                'level' => $level,
                                'header' => $heading[$i],
                                'stack' => $data[$i],
                                'filePath' => $this->getCurrentLogPath(),
                            );
                        }
                    }
                }
            }
        }

        unset($headings);

        unset($log_data);

        return $log;
    }

    /**
     * Retrieves all the data inside each log file
     * from the log file list
     *
     * @return array|bool
     */
    private function getLogFiles()
    {
        $data = array();

        $files = $this->getLogFileList();

        $count = 0;

        foreach($files as $file)
        {
            $data[$count]['contents'] = file_get_contents($file);
            $data[$count]['path'] = $file;
            $count++;
        }

        return $data;
    }

    /**
     * Returns an array of log file paths
     *
     * @return array
     */
    private function getLogFileList()
    {
        if(is_dir($this->path))
        {
            $logPath = sprintf('%s%s*.log', $this->path, DIRECTORY_SEPARATOR);

            if($this->getDate() != 'none')
            {
                $logPath = sprintf('%s%slaravel-%s.log', $this->path, DIRECTORY_SEPARATOR, $this->getDate());
            }

            return glob($logPath);
        }
    }
}