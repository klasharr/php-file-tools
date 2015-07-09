<?php

namespace Chalky\Processor;

use Monolog\Logger as Logger;

class Iterator extends \FilterIterator
{

    const
        PROCESS_STATE_NEW = 0,
        PROCESS_STATE_FILES_PROCESSED = 1,
        PROCESS_STATE_SKIP_FILES = 2,
        PROCESS_STATE_SKIP_ALL = 3,
        PROCESS_STATE_UPDATE = 4;

    private
        $mProcessFiles = true,
        $accepted_extensions = array(),
        $ignored_directories = array(),
        $logger;

    public function __construct(
        $path,
        Logger $logger = null,
        array $accepted_extensions = array(),
        array $ignored_directories = array()
    ) {

        parent::__construct(new \DirectoryIterator($path));
        $this->logger = $logger;
        //$this->setProcessFilesFlag($path);
        $this->accepted_extensions = $accepted_extensions;
        $this->ignored_directories = $ignored_directories;
    }

    /**
     * @return bool
     */
    public function accept()
    {

        //if ($this->mProcessFiles === self::PROCESS_STATE_SKIP_ALL) {
        //    return false;
        //}

        // Directory
        if ($this->current()->isDir()) {

            if (!$this->current()->isDot()
                && !in_array($this->current()->getFileName(), $this->ignored_directories)
            ) {
                return true;
            }
            $this->logger->addDebug("Skipping " . $this->current()->getFileName() . " not allowed directory");

            return false;
        }

        /* File
        if (in_array(
            $this->mProcessFiles,
            array(self::PROCESS_STATE_SKIP_FILES, self::PROCESS_STATE_FILES_PROCESSED)
        )) {
            return false;
        } */

        if (in_array(
            strtolower($this->current()->getExtension()),
            $this->accepted_extensions
        )) {
            return true;
        }
        $this->logger->addDebug("Skipping " . $this->current()->getFileName() . " not allowed extension");
    }

    /*
    private function setProcessFilesFlag($path)
    {
        if (!file_exists($path . '/info')) {
            $this->logger->addDebug("No info file in $path");
            $this->mProcessFiles = self::PROCESS_STATE_NEW;

            return;
        }
        $a = parse_ini_file($path . '/info');

        if (!array_key_exists('state', $a)) {
            $this->logger->addDebug("No state value in $path the info file");
            $this->mProcessFiles = self::PROCESS_STATE_NEW;

            return;
        }

        $state = (int)$a['state'];

        if (in_array(
            $state,
            array(
                self::PROCESS_STATE_NEW,
                self::PROCESS_STATE_SKIP_ALL,
                self::PROCESS_STATE_FILES_PROCESSED,
                self::PROCESS_STATE_SKIP_FILES,
                self::PROCESS_STATE_UPDATE,
            )
        )) {
            $this->logger->addInfo("$path has info state: " . $state);
            $this->mProcessFiles = $state;
        } else {
            throw new \Exception(
                'info file has invalid state in directory ' . $path
            );
        }
    } */
}