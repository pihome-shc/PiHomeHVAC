#!/bin/bash
#=================================================================
echo "           __  __                             _        "
echo "          |  \/  |                    /\     (_)       "
echo "          | \  / |   __ _  __  __    /  \     _   _ __ "
echo "          | |\/| |  / _' | \ \/ /   / /\ \   | | |  __|"
echo "          | |  | | | (_| |  >  <   / ____ \  | | | |   "
echo "          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|   "
echo ""
echo "                S M A R T   T H E R M O S T A T "
echo "*************************************************************************"
echo "* MaxAir is LINUX  based Central Heating Control systems. It runs from  *"
echo "* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *"
echo "* extent permitted by applicable law. I take no responsibility for any  *"
echo "* loss or damage to you or your property.                               *"
echo "* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTIL UNLESS YOU KNOW  *"
echo "* WHAT YOU ARE DOING                                                    *"
echo "*************************************************************************"
echo
echo "                                                       Have Fun - PiHome "
echo " - Boiler Reset/Power Cycle  Script Started ";
echo "   $(date)"
echo
echo "*************************************************************************"
echo

read -p "Do you want to install Boiler Reset/Power Cycling? y/n? " -n 1 -r
echo    # (optional) move to a new line
if [[ $REPLY =~ ^[Yy]$ ]]
then
    echo "Creating reset table if it does not exist"
    sudo mysql -u root -ppassw0rd maxair < ./boiler_support.sql

    echo "Modifying settingslist.php file"
    sed -i -e '/<!-- placeholder -->/r  ./settingslist_add.txt'  /var/www/settingslist.php

    echo "Modifying model.php file"
    sed -n -i -e '/Offset Modal/r ./model_add.txt' -e 1x -e '2,${x;p}' -e '${x;p}' /var/www/model.php

    echo "Modifying db.php file"
    awk -i inplace '/?>/ {while (0 < getline X < FI) print X} 1' FI="/db_add.txt" /var/www/db.php

    echo "Modifying request.js file"
    sed -n -i -e '/function db_backup()/r ./request_add.txt' -e 1x -e '2,${x;p}' -e '${x;p}' /var/www/js/request.js

    echo "Creating lo.php file"
    sudo cp ./lo.php /var/www/languages
fi
