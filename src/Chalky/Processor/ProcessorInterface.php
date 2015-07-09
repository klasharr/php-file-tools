<?php
namespace Chalky\Processor;

interface ProcessorInterface
{
    public function process(\SplFileInfo $file);
}