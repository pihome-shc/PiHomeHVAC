#!/bin/bash

#app_name:Install Image Tools
#app_description:Install Creation Tools
#restart_scheduler:yes

echo "Create 'auto_image' table and default entry. Updating the Job in the database"
cd /var/www/add_on/Image_Tools
python3 db_config.py

echo "Copy scripts to /usr/local/bin and set permisions"

chown -R www-data:www-data ./image_utils
chmod +x ./image_utils/image-*
sudo cp -p ./image_utils/image-* /usr/local/bin

echo "Done"
