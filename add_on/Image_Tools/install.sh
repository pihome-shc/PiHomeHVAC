#!/bin/bash

#app_name:Install Image Tools
#app_description:Install Creation Tools
#restart_scheduler:yes

cpath="$( cd "$( dirname "${BASH_SOURCE[0]}" )" >/dev/null 2>&1 && pwd )"
opt="X"
vdcfldd="N"

check_installed()
{
        if dpkg -s "dcfldd" | grep 'Status: install ok installed' >/dev/null 2>&1; then
                vdcfldd="Y"
        fi
}

dcfldd_config()
{
        echo "dcfldd Config"
        echo "dcfldd Status is " $vdcfldd
        if [ "$vdcfldd" = "N" ]; then
                echo "dcfldd not installed- now installing"
                apt -q install dcfldd
                echo "Recheck install Status"
                check_installed
                if [ "$vdcfldd" = "N" ]; then
                        echo ""
                        echo ""
                        echo "dcfldd failed to install. Check there is internet access"
                        echo "and try again"
                        echo "Press a key to continue"
                        read
                        menu
                fi
        fi
        echo "dcfldd is installed"
}

echo "Create 'auto_image' table and default entry. Updating the Job in the database"
cd /var/www/add_on/Image_Tools
python3 db_config.py

echo "Copy scripts to /usr/local/bin and set permisions"

OS=($(awk -F= '$1=="ID_LIKE" { print $2 ;}' /etc/os-release))
if [[ $OS =~ "debian" ]]; then
   chown -R www-data:www-data ./image_utils
elif [[ $OS =~ "arch" ]]; then
   chown -R http:http ./image_utils
fi
chmod +x ./image_utils/image-*
sudo cp -p ./image_utils/image-* /usr/local/bin

echo "Done"
