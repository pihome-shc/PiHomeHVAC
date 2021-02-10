#!/bin/bash

# check if python schedule needs to be installed
if ! pip3 list | grep schedule >/dev/null 2>&1 ; then
  echo "Installing Python schedule"
  sudo pip3 install schedule
else
  echo "Python schedule already installed"
fi

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
ExecStart=/usr/bin/python3 /var/www/cron/jobs_schedule.py >/dev/null 2>&1
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
