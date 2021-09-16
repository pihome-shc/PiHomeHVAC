#!/bin/bash

#app_name:Install Install HomeAssistant Service
#app_description:Install HomeAssistant Service to Link to MaxAir

echo "Installing Phyton modules"
REQUIREMENTS=requirements.txt
sudo pip3 install -r $REQUIREMENTS

echo "Editing controller.php to disable override when the current schedule for the zone end"
sudo sed -i.bak '/HA-Integration/s/^#//g' /var/www/cron/controller.php
echo "Backup of original version of controller.php created (/var/www/cron/controller.php)"

echo "Creating service for auto start"
sudo cp HA_integration.service /etc/systemd/system/HA_integration.service
sudo systemctl enable HA_integration.service

echo "Starting the service"
sudo systemctl start HA_integration.service
