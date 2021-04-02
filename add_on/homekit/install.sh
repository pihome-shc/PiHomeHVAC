#!/bin/bash

#service_name homebridge.service

bash /var/www/api/enable_rewrite.sh

echo "Installing the WebHooks Plugin"
sudo npm install -g homebridge-http-webhooks

echo "Checking For WebHooks Cache Directory"
DIR="/var/lib/homebridge/.node-persist/storage/"
if [ -d "$DIR" ]; then
  echo "Directory Found, Checking if Empty"
  FILE=""
  if [ "$(ls -A $DIR)" ]; then
     echo "Deleting existing Files"
     sudo rm -f -- /var/lib/homebridge/.node-persist/storage/*
  else
    echo "$DIR is Empty"
  fi
else
    echo "Creating Directory, Changing Ownership and Permissions"
    sudo mkdir /var/lib/homebridge/.node-persist
    sudo mkdir $DIR
    sudo chown homebridge:homebridge $DIR
    sudo chmod 755 $DIR
fi
echo "Backing Up and Updateing /var/lib/homebridge/config.json"
echo "Adding WebHooks Plugin and  Accessories for Each Zone"
FILE=/var/lib/homebridge/config.json
sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
/usr/bin/python3 config_json.py
echo "Restarting Homebridge"
sudo hb-service restart
