<?php


namespace CodeBridge\EbsSnapshotAutomation\Services;

use Aws\Ec2\Ec2Client;


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
}