#!/bin/bash

cd /var/www/add_on/MySensors_RPi_Gateway
source ./config.conf

echo "Cloning MySensor repository from GitHub"
git clone https://github.com/mysensors/MySensors.git --branch master
cd MySensors

echo "Configuring MySensor"
./configure --my-transport=$MY_TRANSPORT --my-rf24-channel=$MY_RF24_CHANNEL —-my-gateway=$MY_GATEWAY --my-port=$MY_PORT --my-rf24-ce-pin=$MY_RF24_CE_PIN --my-rf24-cs-pin=$MY_RF24_CS_PIN --my-rf24-irq-pin=$MY_RF24_IRQ_PIN --my-leds-err-pin=$MY_LEDS_ERR_PIN --my-leds-rx-pin=$MY_LEDS_RX_PIN --my-leds-tx-pin=$MY_LEDS_TX_PIN

echo "Building MySensor Gateway"
make

echo "Installing MySensor Gateway"
make install

echo "Enable log pipe. Use cat /tmp/mysgw.pipe to view the log"
sed -i.bak "s/log_pipe=0.*/log_pipe=1/g" /etc/mysensors.conf

echo "Enabeling and starting the service"
systemctl enable mysgw.service
systemctl start mysgw.service

echo "Updating Gateway configuration in the database"
cd ..
python3 db_config.py

echo "Restarting scheduler"
systemctl restart pihome_jobs_schedule.service