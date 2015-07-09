<?php

namespace Chalky\Processor;

use Monolog\Logger as Logger;

class DirectoryRecursor
{

    private
        $source_dir = null,
        $file_processors = array(),
        $accepted_extensions = array(),
        $ignored_directories = array(),
        $logger;

    public function __construct(
        $source_dir,
        Logger $logger,
        array $accepted_extensions = array(),
        array $ignored_directories = array()
    ) {

        if (empty($source_dir)) {
            throw new \Exception('$source_dir can not be empty');
        }

        $this->logger = $logger;
        $this->source_dir = realpath($source_dir);

        if (!file_exists($this->source_dir)) {
            throw new \Exception('Source directory "' . $source_dir . '" must be a valid directory');
        }
        $this->accepted_extensions = $accepted_extensions;
        $this->ignored_directories = $ignored_directories;
    }

    public function addFileProcessor(ProcessorInterface $oFileProcessor)
    {
        array_push($this->file_processors, $oFileProcessor);
    }

    public function process()
    {
        $this->recurseDirectory($this->source_dir);
    }

    private function recurseDirectory($sDirectory)
    {
        $aDirs = $this->processDirectory($sDirectory);

        if (empty($aDirs)) {
            return;
        }

        foreach ($aDirs as $sDir) {
            $this->recurseDirectory($sDir);
        }
    }

    private function processDirectory($sDirectory)
    {
        $aDirs = array();
        $oIterator = $this->getIterator($sDirectory);

        foreach ($oIterator as $oFile) {

            if ($oFile->isDir()) {
                $aDirs[] = $oFile->getPathName();
                continue;
            }

            foreach ($this->file_processors as $oFileProcessor) {

                $oFileProcessor->process($oFile);
            }
        }

        return $aDirs;
    }

    public function getIterator($sDirectory)
    {
        $oIterator = new Iterator(
            $sDirectory,
            $this->logger,
            $this->accepted_extensions,
            $this->ignored_directories
        );

        return $oIterator;
    }

    /*
    public function getActualDirectoryHash(Iterator $oIterator)
    {
        $oIterator = $this->getIterator($oIterator);
        $sHash = '';
        foreach ($oIterator as $oFile) {
            if ($oFile->isDir()) {
                continue;
            }
            $sHash .= $this->getSplFileInfoHash($oFile);
        }
        $oIterator->rewind();

        return $sHash;
    }

    private function getSplFileInfoHash(\SplFileInfo $oFile)
    {
        return md5($oFile->getFilename() . $oFile->getCTime() . $oFile->getMTime() . $oFile->getSize());
    }

    private function writeDirectoryFilesHash($aHash, $sDirectory)
    {
        file_put_contents($sDirectory . "/.hash", serialize($aHash));
    }

    private function getStoredDirectoryHash($sDirectory)
    {
        if (!file_exists($sDirectory . '/.hash')) {
            $this->logger->addInfo('No data file in this directory :' . $sDirectory);

            return false;
        }
        $s = file($sDirectory . '/.hash');
        $a = unserialize($s[0]);

        return $a['directory_hash'];
    }
    */
}