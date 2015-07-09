<?php

namespace Chalky\Processor;

use Monolog\Logger as Logger;

abstract class AbstractProcessor
{

    protected
        $source_top_level_directory,
        $source_dir,
        $logger,
        $num_files_processed = 0,
        $num_files_failed = 0,
        $opt = array();

    /**
     * @param $sSourceDir string
     * @param Logger $logger
     * @param $opt array of optional settings to get passed through to the processor(s)
     * @throws \Exception
     */
    public function __construct($source_dir, Logger $logger, $opt = array())
    {
        if (($this->source_dir = realpath($source_dir)) === false) {
            throw new \Exception('Source directory "' . $source_dir . '" does not exist');
        }

        $this->logger = $logger;
        $this->opt = $opt;
        $this->source_top_level_directory = end(explode(DIRECTORY_SEPARATOR, $this->source_dir));

    }

    public function getNumFilesProcessed()
    {
        return $this->num_files_processed;
    }

    public function getNumFilesFailed()
    {
        return $this->num_files_failed;
    }

    abstract function process(\SplFileInfo $file);
} 