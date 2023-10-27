#!/bin/bash

# check if Unit File already exists
echo "Checking For Existing Unit File"
FILE=/lib/systemd/system/pihome_jobs_schedule.service
if [  -f "$FILE" ]; then
    echo "Deleting Existing Unit File: $FILE"
    rm $FILE
fi
echo "Creating Unit File: $FILE"
sudo cat <<EOT >> /lib/systemd/system/pihome_jobs_schedule.service
[Unit]
Description=Schedule
After=network.target
StartLimitIntervalSec=0

[Service]
Type=simple
ExecStart=/usr/bin/python3 /var/www/cron/jobs_schedule.py
Restart=always
RestartSec=1

[Install]
WantedBy=multi-user.target
EOT

echo "Enabling the service"
sudo chmod 644 /lib/systemd/system/pihome_jobs_schedule.service
sudo systemctl daemon-reload
sudo systemctl enable pihome_jobs_schedule.service
echo "Starting the service"
sudo systemctl start pihome_jobs_schedule.service
