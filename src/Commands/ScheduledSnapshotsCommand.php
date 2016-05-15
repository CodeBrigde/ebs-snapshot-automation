<?php

namespace CodeBridge\EbsSnapshotAutomation\Commands;

use CodeBridge\EbsSnapshotAutomation\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use CodeBridge\EbsSnapshotAutomation\Services\Ec2Service;
use CodeBridge\EbsSnapshotAutomation\Scheduler;

use Cron\CronExpression;
use Carbon\Carbon;

class ScheduledSnapshotsCommand extends Command
{
    protected $cache;
    protected $scheduler;

    public function __construct($name = null, Cache $cache)
    {
        $this->cache = $cache;
        $this->scheduler = new Scheduler();

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('volumes:snapshot')
            ->setDescription('Check all schedules and create snapshot accordingly');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$this->cache->isCached('volumes')) {
            throw new \Exception('Run php ebs-php volumes:list --cache before starting the scheduler');
        }

        $volumes = $this->cache->retrieve('volumes');

        $ec2 = new Ec2Service();

        foreach ($volumes as $volume) {

            $this->scheduler->call(
                function ($params) {
                    $ec2 = $params['ec2'];
                    $volume = $params['volume'];
                    $output = $params['output'];

                    $response = $ec2->client->createSnapshot(
                        [
                            'VolumeId' => $volume['id'],
                            'Description' => date('Y-m-d-H:i:s') . '-' . $volume['name']
                        ]
                    );

                    $ec2->client->createTags(
                        [
                            'Resources' => [$response['SnapshotId']],
                            'Tags' => [
                                [
                                    'Key' => 'Name',
                                    'Value' => $volume['name']
                                ],
                            ]

                        ]
                    );

                    $output->writeln(str_pad($volume['name'], 16, ' ') . ' <info>' . str_pad($response['SnapshotId'], 16, ' ') . '</info>' . $volume['id']);
                },
                [
                    'ec2' => $ec2,
                    'volume' => $volume,
                    'output' => $output

                ]
            )->cron($volume['schedule']);
        }

        $this->scheduler->runAll($output);
    }
}