#!/usr/bin/env php
<?php
// application.php

date_default_timezone_set('UTC');

require __DIR__ . '/vendor/autoload.php';

use Symfony\Component\Console\Application;

use CodeBridge\EbsSnapshotAutomation\Commands\ListVolumesCommand;
use CodeBridge\EbsSnapshotAutomation\Commands\ScheduledSnapshotsCommand;
use CodeBridge\EbsSnapshotAutomation\Cache;

$env = new Dotenv\Dotenv(__DIR__);
$env->load();

$cache = new Cache(
    [
        'name'      => 'ebs-volumes',
        'path'      => dirname(__FILE__).'/cache/',
        'extension' => '.cache'
    ]
);

$application = new Application();
$application->add(new ListVolumesCommand(null ,$cache));
$application->add(new ScheduledSnapshotsCommand(null ,$cache));
$application->run();