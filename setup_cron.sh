#!/bin/bash
# Setup cron job to run import_job.php daily at midnight

JOB_PATH="/home/tomasvlasakdev/source/projects/API/jobs/import_job.php"
CRON_CMD="0 0 * * * php $JOB_PATH"

(crontab -l 2>/dev/null; echo "$CRON_CMD") | crontab -
echo "Cron job added to run $JOB_PATH daily at midnight."
