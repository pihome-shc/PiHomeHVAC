#!/bin/bash

#service_name:mosquitto.service
#app_name:Mosquitto Broker
#app_description:Install Mosquitto Broker


echo "Installing mosquitto"
sudo apt-get install -y mosquitto mosquitto-clients
sudo systemctl enable mosquitto

echo "Creating File"
sudo cat <<EOT >> /etc/mosquitto/conf.d/maxair.conf
per_listener_settings true
listener 1883 0.0.0.0
allow_anonymous false
password_file /etc/mosquitto/credentials
EOT

echo "Creating Password File"
sudo cat <<EOT >> /etc/mosquitto/credentials
admin:pihome
zigbee:pihome
EOT
sudo mosquitto_passwd -U /etc/mosquitto/credentials
echo -e "\nSetting file permissions"
sudo chmod 0700 /etc/mosquitto/credentials
sudo chown mosquitto: /etc/mosquitto/credentials

echo "Start mosquitto Service"
sudo systemctl start mosquitto

echo "Finished"

