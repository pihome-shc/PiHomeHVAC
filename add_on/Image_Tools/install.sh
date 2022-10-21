#!/bin/bash

#app_name:Install Image Tools
#app_description:Install Creation Tools
#restart_scheduler:yes

echo "Create 'auto_image' table and default entry. Updating the Job in the database"
cd /var/www/add_on/Image_Tools
python3 db_config.py

echo "Download tools from GitHub to /var/www/cron/image_tools and set permisions"
mkdir ./tools
git clone https://github.com/scruss/RonR-RaspberryPi-image-utils.git "./tools"

chown -R www-data:www-data ./tools
chmod +x ./tools/image-*
sudo cp -p tools/image-* /usr/local/bin
sudo rm -R ./tools

echo "Done"
