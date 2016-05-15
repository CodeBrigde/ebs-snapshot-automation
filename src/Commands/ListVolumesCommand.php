<?php

namespace CodeBridge\EbsSnapshotAutomation\Commands;

use CodeBridge\EbsSnapshotAutomation\Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use CodeBridge\EbsSnapshotAutomation\Services\Ec2Service;
use Cron\CronExpression;

class ListVolumesCommand extends Command
{
    protected $cache;

    public function __construct($name = null, Cache $cache)
    {
        $this->cache = $cache;

        parent::__construct($name);
    }

    protected function configure()
    {
        $this
            ->setName('volumes:list')
            ->setDescription('List all EBS volumes for the credential set')
            ->addOption(
                'cache',
                'c',
                InputOption::VALUE_NONE,
                'If set, the task will cache schedulable volumes'
            )
            ->addOption(
                'show-cached',
                's',
                InputOption::VALUE_NONE,
                'If set, the task show will show all cached volumes (which will be used to run the scheduler against)'
            );
    }


    protected function getScheduleTag($tags)
    {
        foreach ($tags as $tag) {
            if ($tag['Key'] == 'schedule') {
                return $tag['Value'];
            }
        }

        return false;
    }


    protected function getNameTag($tags)
    {
        foreach ($tags as $tag) {
            if ($tag['Key'] == 'Name') {
                return $tag['Value'];
            }
        }

        return false;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $schedulableVolumes = [];
        if ($input->getOption('show-cached')) {
            $schedulableVolumes = $this->cache->retrieve('volumes');

            foreach ($schedulableVolumes as $volume) {
                $output->writeln(str_pad($volume['name'], 16, ' ') . ' <info>' . str_pad($volume['schedule'], 16, ' ') . '</info>' . $volume['id']);
            }

        } else {
            $ec2 = new Ec2Service();

            $volumes = $ec2->client->describeVolumes();
            foreach ($volumes['Volumes'] as $volume) {
                $schedule = $this->getScheduleTag($volume['Tags']);
                $name = $this->getNameTag($volume['Tags']);

                if (CronExpression::isValidExpression($schedule)) {

                    $schedulableVolumes[] = [
                        'name' => $name,
                        'schedule' => $schedule,
                        'id' => $volume['VolumeId']
                    ];

                    $output->writeln(str_pad($name, 16, ' ') . ' <info>' . str_pad($schedule, 16, ' ') . '</info>' . $volume['VolumeId']);
                } else {
                    $output->writeln(str_pad($name, 16, ' ') . ' <error>' . str_pad($schedule, 16, ' ') . '</error>' . $volume['VolumeId']);
                }
            }
        }

        if ($input->getOption('cache')) {
            $this->cache->store('volumes', $schedulableVolumes);
        }
    }
}