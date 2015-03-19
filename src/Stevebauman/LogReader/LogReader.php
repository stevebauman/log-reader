<?php

namespace Stevebauman\LogReader;

use Stevebauman\LogReader\Objects\Entry;
use Illuminate\Support\Facades\File as Filesystem;

class LogReader
{
    /**
     * The laravel filesystem instance
     *
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * The log file path
     *
     * @var string
     */
    protected $path;

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
     * @param Filesystem $filesystem
     */
    public function __construct(Filesystem $filesystem)
    {
        $this->filesystem = $filesystem;

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

        foreach($this->getLogFiles() as $log)
        {
            $parsedLog = $this->parseLog($log, $this->getLevel());

            foreach($parsedLog as $entry)
            {
                $entries[] = new Collection($entry);
            }
        }

        return new Collection($entries);
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
     * if it exists inside the levels array
     *
     * @param $level
     */
    private function setLevel($level)
    {
        $level = strtolower($level);

        if(in_array($level, $this->levels))
        {
            $this->level = $level;
        }
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
                            $log[] = ['level' => $level, 'header' => $heading[$i], 'stack' => $data[$i]];
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

        foreach($files as $file)
        {
            $data[] = file_get_contents($file);
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
        if($this->filesystem->isDirectory($this->path))
        {
            $logPath = sprintf('%s%s*.log', $this->path, DIRECTORY_SEPARATOR);

            if($this->getDate() != 'none')
            {
                $logPath = sprintf('%s%slaravel-%s.log', $this->path, DIRECTORY_SEPARATOR, $this->getDate());
            }

            return $this->filesystem->glob($logPath);
        }
    }
}