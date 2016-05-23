<?php

namespace CodeBridge\EbsSnapshotAutomation;

use Illuminate\Console\Scheduling\Schedule;
use Symfony\Component\Console\Output\OutputInterface;

use Carbon\Carbon;
use Cron\CronExpression;


class Scheduler extends Schedule
{

    public function call($callback, array $parameters = [])
    {
        $this->events[] = $event = new Event($callback, $parameters);
        return $event;
    }


    public function runAll(OutputInterface $output = null)
    {
        $date = Carbon::now();
        $events = $this->events();

        $e = 0;
        $i = 0;
        foreach ($events as $event) {
            ++$e;
            if (CronExpression::factory($event->expression)->isDue($date->toDateTimeString())) {
                $event->call($output);
                ++$i;
            };

        }

        if ($output) {
            $output->writeLn('Ran <info>' . $i . '</info> of <info>' . $e . '</info> schedulable commands');
        }
    }

}