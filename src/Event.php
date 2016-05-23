<?php

namespace CodeBridge\EbsSnapshotAutomation;

use Illuminate\Console\Scheduling\CallbackEvent;

use Symfony\Component\Console\Output\OutputInterface;

class Event extends CallbackEvent
{

    public function __construct($callback, array $parameters = [])
    {
        $this->callback = $callback;
        $this->parameters = $parameters;

    }

    public function call(OutputInterface $output = null)
    {
        $callback = $this->callback;

        if ($output) {
            $output->writeln('<info>Running command of type: ' . get_class($callback) . '</info>');
        }

        return $callback($this->parameters);
    }

}