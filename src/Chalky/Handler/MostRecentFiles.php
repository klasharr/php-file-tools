<?php

namespace Chalky\Handler;

use Monolog\Logger as Logger;
use Chalky\Processor\AbstractProcessor;
use Chalky\Processor\ProcessorInterface;

/**
 * @package Chalky\File
 */
class MostRecentFiles extends AbstractProcessor implements ProcessorInterface
{

    /**
     * @var int
     */
    protected $number_files_processed = 0;

    /**
     * Holds the final data array which gets returned
     * @var array
     */
    protected $files = array();

    /**
     * The keys for each entry in $files. Used to ensure key uniqueness in case
     * file timestamps are the same
     * @var array
     */
    static $keys = array();

    /**
     * Replacements used to generate the friendly filename e.g. summary.htm -> summary
     * @var array
     */
    private $friendly_filename_replacements = array(
        array('.htm','.html'), array('','')
        );

    /**
     * @param $sSourceDir
     * @param Logger $logger
     * @param array $opt
     * @throws \Exception
     */
    public function __construct($sSourceDir, Logger $logger, $opt = array())
    {
        parent::__construct($sSourceDir, $logger, $opt);
    }

    /**
     * @param \SplFileInfo $file
     */
    public function process(\SplFileInfo $file)
    {

        $localFilePath = $this->getLocalFilePath($file);

        $this->files[$this->getKey($file)] = array(
            'friendly_path' => $this->getFriendlyFileName($localFilePath),
            'date' => date("F j, g:i a", $file->getMTime()),
            'timestamp' => $file->getMTime(),
            'link' => $this->opt['base_url'] . str_replace(DIRECTORY_SEPARATOR, '/', $localFilePath),
            'path_segments' => $this->getSegments($localFilePath),
            'friendly_file_name' => $this->getSegments($localFilePath, true),
        );

        $this->logger->addDebug($file->getFilename() . ' ' . date("F j, g:i a", $file->getMTime()));
        $this->num_files_processed++;
    }

    /**
     * /path/to/your/app/Cup Races/1974_cup.htm
     * becomes
     * /Cup Races/1974_cup.htm
     *
     * @param \SplFileInfo $file
     * @return string
     */
    private function getLocalFilePath(\SplFileInfo $file){
        return ltrim(str_replace($this->source_dir, '', $file->getPathname()), DIRECTORY_SEPARATOR);
    }

    public function getNumberFilesProcessed()
    {
        return $this->number_files_processed;
    }

    /**
     * Returns either a friendly filename or the path (before the filename) in an array
     * e.g. for a path like this /2015/Sunday Series/Summer Sunday Series/summary.htm
     * with the returned array would be
     * array( "2015", "Sunday Series", "Summer Sunday Series" );
     *
     * @param $filename
     * @param bool $fileonly
     * @return array|mixed
     */
    private function getSegments($filename, $fileonly = false){

        $filename = str_replace(
          $this->friendly_filename_replacements[0],
          $this->friendly_filename_replacements[1], $filename
        );

        $out = explode(DIRECTORY_SEPARATOR, $filename);
        foreach ($out as &$segment) {
            $segment = $this->getFriendlyFileName($segment);
        }
        $last = array_pop($out);
        return $fileonly ? $last : $out;
    }

    /**
     * @param $file string
     * @return string
     */
    public function getFriendlyFileName($filename)
    {
        if (empty($this->opt['name_replacements'])) {
            $tmp = str_replace(
                array(DIRECTORY_SEPARATOR, '.htm', '_'),
                array(' > ', '', ' '),
                $filename
            );
        } else {

            $tmp = str_replace(
                $this->opt['name_replacements']['search'],
                $this->opt['name_replacements']['replace'],
                $filename
            );
        }

        $tmp1 = str_replace(
            array('And', 'To', 'For', 'From', 'Of'),
            array('and', 'to', 'for', 'from', 'of'),
            ucwords($tmp)
        );

        return preg_replace('/([a-z]+)(\d+)$/i', '$1 $2', $tmp1);
    }

    /**
     * It can happen that files have the same timestamp, this will ensure each file has a unique key
     *
     * @param \SplFileInfo $file
     * @return int
     */
    private function getKey(\SplFileInfo $file)
    {
        $time = $file->getMTime() * 100;

        while (true) {
            if (!in_array($time, self::$keys)) {
                break;
            }
            $time++;
        }

        self::$keys[] = $time;
        return $time;
    }

    /**
     * @return array of most recently altered files based on file modified time.
     */
    public function getLatestFiles()
    {
        krsort($this->files);
        return array_slice($this->files, 0, $this->opt['number']);
    }

}