#!/bin/bash

#service_name:zigbee2mqtt.service
#app_name:Zigbee2MQTT Integration
#app_description:Integrate Zigbee2MQTT

# Set up Node.js repository, install Node.js and required dependencies.
# NOTE 1: Older i386 hardware can work with [unofficial-builds.nodejs.org](https://unofficial-builds.nodejs.org/download/release/v20.9.0/ e.g. Version 20.9.0 should work.
# NOTE 2: For Ubuntu see installing through Snap below.
echo "Installing curl"
sudo apt-get install -y curl
sudo curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
echo "Installing nodejs"
sudo apt-get install -y nodejs git make g++ gcc libsystemd-dev
echo "Enabling corepack"
corepack enable

# Verify that the correct Node.js version has been installed
node --version  # Should output V20.x, V22.X

echo "Create zigbee2mqtt directory"
# Create a directory for zigbee2mqtt and set your user as owner of it
sudo mkdir /opt/zigbee2mqtt
sudo chown -R ${USER}: /opt/zigbee2mqtt

echo "Get zigbee2mqtt from GitHub"
# Clone Zigbee2MQTT repository
git clone --depth 1 https://github.com/Koenkk/zigbee2mqtt.git /opt/zigbee2mqtt

echo "Installing dependencies"
# Install dependencies (as user "pi")
cd /opt/zigbee2mqtt
pnpm install --frozen-lockfile

# Create service
echo "Creating service for auto start"
sudo cp /var/www/add_on/zigbee2mqtt/zigbee2mqtt.service /etc/systemd/system/zigbee2mqtt.service
sudo systemctl enable zigbee2mqtt.service

echo -e "From the command line enter 'sudo systemctl start zigbee2mqtt.service'.\nThen browse to xxx.xxx.xxx.xxx:8080 to complete the 'onboarding' stage of the install process."
