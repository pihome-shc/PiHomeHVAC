#!/bin/bash

echo "Installing Phyton modules"
REQUIREMENTS=requirements.txt
pip3 install -r $REQUIREMENTS

echo "Creating service for auto start"
cp HA_integration.service /etc/systemd/system/HA_integration.service
systemctl enable HA_integration.service

echo "Starting the service"
systemctl start HA_integration.service
