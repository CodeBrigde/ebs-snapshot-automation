# Simple transfer.sh client

## Installation

    composer require codebridge/ebs-snapshot-automation

## Setup

    copy .env.example and edit with your own AWS credentials
    
    Login to the AWS console and tag the volumes you want to snapshot with the proper tags.
    You can tag a volume for scheduling by tagging it with "Key" "schedule" and a "Value" which is a valid CRON expression, like 0 23 * * *.
    
## Usage
    
    php ebs-php volumes:list (see all your volumes)
    php ebs-php volumes:list --cache (save your volumes locally for snapshotting)
    php ebs-php volumes:list --show-cached (show all volumes in the local cache)
    php ebs-php volumes:snapshot (check if there are any volumes that need to be snapshotted right now and snapshot them)
    
## CRON jobs
    Check for volumes to snapshot every minute
    * * * * * php /path/to/ebs-php volumes:snapshot >/dev/null 2>&1
    
    Check for new volumes every hour
    0 * * * * php /path/to/ebs-php volumes:snapshot >/dev/null 2>&1