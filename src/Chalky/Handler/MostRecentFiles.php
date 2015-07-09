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

    protected $number_files_processed = 0;

    protected $files = array();

    static $keys = array();

    public function __construct($sSourceDir, Logger $logger, $opt = array())
    {
        parent::__construct($sSourceDir, $logger, $opt);
    }

    public function process(\SplFileInfo $file)
    {
        $filename = ltrim(str_replace($this->source_dir, '', $file->getPathname()), DIRECTORY_SEPARATOR);

        $this->files[$this->getKey($file)] = array(
            'file' => $this->getFriendlyFileName($filename),
            'date' => date("F j, g:i a", $file->getMTime()),
            'timestamp' => $file->getMTime(),
            'link' => $this->opt['base_url'] . str_replace(DIRECTORY_SEPARATOR, '/', $filename),
        );

        $this->logger->addDebug($file->getFilename() . ' ' . date("F j, g:i a", $file->getMTime()));
        $this->num_files_processed++;
    }

    public function getNumberFilesProcessed()
    {
        return $this->number_files_processed;
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

        return str_replace(
            array('And', 'To', 'For', 'From', 'Of'),
            array('and', 'to', 'for', 'from', 'of'),
            ucwords($tmp)
        );
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
     * @return array of most recently altered files
     */
    public function getLatestFiles()
    {
        krsort($this->files);

        return array_slice($this->files, 0, $this->opt['number']);

    }

}