<?php

namespace CodeBridge\EbsSnapshotAutomation\Services;
use Symfony\Component\Console\Output\OutputInterface;

use Aws\Ec2\Ec2Client;
use Cron\CronExpression;


class Ec2Service
{
    public $client;

    public function __construct()
    {
        $this->client = Ec2Client::factory(
            [
                'version' => 'latest',
                'credentials' => [
                    'key' => getenv('EC2_KEY'),
                    'secret' => getenv('EC2_SECRET')
                ],
                'region' => getenv('AWS_REGION')
            ]
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


    public function getAllSchedulableVolumes(OutputInterface $output = null){

        $volumes = $this->client->describeVolumes();
        $schedulableVolumes = [];

        foreach ($volumes['Volumes'] as $volume) {
            $schedule = $this->getScheduleTag($volume['Tags']);
            $name = $this->getNameTag($volume['Tags']);

            if (CronExpression::isValidExpression($schedule)) {

                $schedulableVolumes[] = [
                    'name' => $name,
                    'schedule' => $schedule,
                    'id' => $volume['VolumeId']
                ];

                if($output){
                    $output->writeln(str_pad($name, 16, ' ') . ' <info>' . str_pad($schedule, 16, ' ') . '</info>' . $volume['VolumeId']);
                }

            } else {
                if($output) {
                    $output->writeln(str_pad($name, 16, ' ') . ' <error>' . str_pad($schedule, 16, ' ') . '</error>' . $volume['VolumeId']);
                }
            }
        }

        return $schedulableVolumes;

    }


}