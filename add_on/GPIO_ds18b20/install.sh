#!/bin/bash

#app_name:Install GPIO 1-Wire Sensor
#app_description:Install Support for DS18b20 1-Wire Temperature Sensor

echo "Updating the Jobs in the database"
python3 db_config.py

echo "Restarting scheduler"
systemctl restart pihome_jobs_schedule.service

if lsmod | grep w1_gpio &> /dev/null ;
then
    echo "The 1-Wire kernel module is loaded"
else
    if [ 'grep 'dtoverlay=w1-gpio' /boot/config.txt' ]
        then
            modprobe w1-gpio
            modprobe w1-therm
            echo "The 1-Wire kernel module is now loaded"
            echo "w1-gpio" >> /etc/modules
            echo "w1-therm" >> /etc/modules
    else
            echo "Editing /boot/config.txt to load the 1-Wire kernel module at boot"
            echo "dtoverlay=w1-gpio" >> /boot/config.txt
            echo "w1-gpio" >> /etc/modules
            echo "w1-therm" >> /etc/modules
            echo "Rebooting the system"
            reboot
    fi
fi





