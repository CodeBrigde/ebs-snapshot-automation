<?php

namespace CodeBridge\EbsSnapshotAutomation;

use Illuminate\Console\Scheduling\CallbackEvent;
use Symfony\Component\Console\Exception\InvalidArgumentException;

class Event extends CallbackEvent{

    public function __construct($callback, array $parameters = [])
    {
        $this->callback = $callback;
        $this->parameters = $parameters;

    }

    public function call(){
        $callback = $this->callback;
        return $callback($this->parameters);
    }

}