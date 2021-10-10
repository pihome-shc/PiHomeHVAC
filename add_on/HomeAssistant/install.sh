#!/bin/bash
#service_name:HA_integration.service
#app_name:Install Home Assistant Integration
#app_description:This add-on requires Home Assistant with Mosquito Broker running on a separate device.

MODEL=$(tr -d '\0' </proc/device-tree/model)
if [[ $MODEL == *"Raspberry"* ]]; then
  echo "Model: Raspberry"
  REQUIREMENTS=requirements_RPi.txt
else
  echo "Model: generic SBC"
  REQUIREMENTS=requirements.txt
fi

PYTHON3DEV=$( apt-cache pkgnames python3-dev )
if [[ $PYTHON3DEV == *"python3-dev"* ]]; then
  echo "Python3-dev is already installed"
else
  echo "Installing Python3-dev"
  apt-get install -y python3-dev
fi

echo "Installing Phyton modules"
pip3 install -r $REQUIREMENTS

echo "Creating service for auto start"
cp HA_integration.service /etc/systemd/system/HA_integration.service
systemctl enable HA_integration.service

echo "Starting the service"
systemctl start HA_integration.service
