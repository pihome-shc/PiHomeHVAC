#!/bin/bash

echo "Enabling Rewrite"

restart_apache_1=0
echo "Backing Up and Modifying /etc/apache2/sites-available/000-default.conf"
FILE=/etc/apache2/sites-available/000-default.conf
if grep -q "DocumentRoot /var/www/html" $FILE
then
    sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    sudo sed -i "s|/var/www/html|/var/www|" "$FILE"
    restart_apache_1=1
fi

if ! grep -q "<Directory /var/www/api>" $FILE
then
    if [ $restart_apache_1 == 0 ]; then
        sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    fi
    sudo awk '/DocumentRoot/{print $0 RS "" RS "        <Directory /var/www/api>"\
      RS "              Options Indexes FollowSymLinks" \
      RS "              AllowOverride All" \
      RS "              Require all granted" \
      RS "        </Directory>";next}1' $FILE > tmp && mv tmp $FILE

    restart_apache_1=1
fi

if [ $restart_apache_1 == 0 ]; then
     echo "000-default.conf Already Modified"
fi

restart_apache_2=0
echo "Backing Up and Modifying /etc/apache2/sites-enabled/000-default.conf"
FILE=/etc/apache2/sites-enabled/000-default.conf
if grep -q "DocumentRoot /var/www/html" $FILE
then
    sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    sudo sed -i "s|/var/www/html|/var/www|" "$FILE"
    restart_apache_2=1
fi

if ! grep -q "<Directory /var/www/api>" $FILE
then
    if [ $restart_apache_2 == 0 ]; then
        sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    fi
    sudo awk '/DocumentRoot/{print $0 RS "" RS "        <Directory /var/www/api>"\
      RS "              Options Indexes FollowSymLinks" \
      RS "              AllowOverride All" \
      RS "              Require all granted" \
      RS "        </Directory>";next}1' $FILE > tmp && mv tmp $FILE

    restart_apache_2=1
fi

if [ $restart_apache_2 == 0 ]; then
     echo "000-default.conf Already Modified"
fi

echo "Enabling Rewrite"
restart_apache_3=0
rewrite_link=/etc/apache2/mods-enabled/rewrite.load
if [ -L ${rewrite_link} ] ; then
   if [ -e ${rewrite_link} ] ; then
      echo "mod_rewrite Already Enabled"
   else
      echo "Broken link"
   fi
elif [ -e ${rewrite_link} ] ; then
   echo "Not a link"
else
    echo "Enabling mod_rewrite"
    sudo a2enmod rewrite >/dev/null 2>&1
    restart_apache_3=1
fi

if [ $restart_apache_1 == 1 ] ||  [ $restart_apache_2 == 1 ] ||  [ $restart_apache_3 == 1 ]; then
    echo "Restarting the apache service"
    sudo systemctl restart apache2
fi
