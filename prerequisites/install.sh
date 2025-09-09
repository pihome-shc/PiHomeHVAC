#!/bin/bash

echo "Installation Script Starting"

echo "Check if pip3 is Installed"
var=$(which pip3)
if [ -z "$var" ]
then
  echo "Installing pip3"
  sudo apt-get install python3-pip
else
  echo "pip3 already installed"
fi

sudo apt -y install python3-mysqldb
sudo apt -y install libgpiod2 python3-libgpiod gpiod
sudo apt -y install net-tools

echo "Installing adafruit-blinka"
sudo pip3 install adafruit-blinka --break-system-packages

# Install Python 3 dependances

echo "Installing w1thermsensor"
sudo pip3 install w1thermsensor --break-system-packages

echo "Installing Python schedule"
sudo pip3 install schedule --break-system-packages

echo "Installing paho-mqtt"
sudo pip3 install paho-mqtt --break-system-packages

echo "Installing python3-mysqldb"
sudo apt-get -y install python3-mysqldb

echo "Installing libgpiod"
sudo apt install -y libgpiod2 python3-libgpiod gpiod

echo "Installing apache2"
sudo apt -y install apache2 apache2-doc apache2-utils

echo "Installing mariadb"
sudo apt -y install mariadb-server mariadb-client
sudo mysql_secure_installation

echo "Installing PHP support"
sudo apt -y install php php-common
sudo apt -y install php-cli php-fpm php-json php-pdo php-mysql php-zip php-gd  php-mbstring php-curl php-xml php-pear php-bcmath
sudo apt -y install libapache2-mod-php
sudo apt -y install php8.2-mysqlnd
sudo apt-get install -y php-zip

echo "Creating users SQL file"
sudo cat <<EOT >> ./users.sql
CREATE USER 'piphpmyadmin'@'localhost' IDENTIFIED BY 'pihome2018';
GRANT ALL PRIVILEGES ON *.* TO 'piphpmyadmin'@'localhost';
CREATE USER 'phpmyadmin'@'localhost' IDENTIFIED BY 'pihome2018';
GRANT ALL PRIVILEGES ON *.* TO 'phpmyadmin'@'localhost';
CREATE USER 'maxairdbadmin'@'localhost' IDENTIFIED BY 'maxair2021';
GRANT ALL PRIVILEGES ON *.* TO 'maxairdbadmin'@'localhost';
EOT
sudo mysql -u root -ppassw0rd < ./users.sql
echo "Removing temp SQL file"
sudo rm -f ./users.sql

echo "Installing phpmyadmin"
DATA="$(wget https://www.phpmyadmin.net/home_page/version.txt -q -O-)"
URL="$(echo $DATA | cut -d ' ' -f 3)"
VERSION="$(echo $DATA | cut -d ' ' -f 1)"
wget https://files.phpmyadmin.net/phpMyAdmin/${VERSION}/phpMyAdmin-${VERSION}-all-languages.tar.gz
tar xvf phpMyAdmin-${VERSION}-all-languages.tar.gz >/dev/null 2>&1
sudo mv phpMyAdmin-*/ /usr/share/phpmyadmin
sudo rm  -f phpMyAdmin-${VERSION}-all-languages.tar.gz
sudo mkdir -p /var/lib/phpmyadmin/tmp
sudo chown -R www-data:www-data /var/lib/phpmyadmin
sudo mkdir /etc/phpmyadmin/
sudo cp /usr/share/phpmyadmin/config.sample.inc.php  /usr/share/phpmyadmin/config.inc.php

echo "Backing Up and Updateing /etc/apache2/conf-enabled/phpmyadmin.conf"
FILE=/etc/apache2/conf-enabled/phpmyadmin.conf
sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"

echo "Creating phpmyadmin File: $FILE"
cat <<EOT >> ./tmp.txt
Alias /phpmyadmin /usr/share/phpmyadmin

<Directory /usr/share/phpmyadmin>
    Options SymLinksIfOwnerMatch
    DirectoryIndex index.php

    <IfModule mod_php5.c>
        <IfModule mod_mime.c>
            AddType application/x-httpd-php .php
        </IfModule>
        <FilesMatch ".+\.php$">
            SetHandler application/x-httpd-php
        </FilesMatch>

        php_value include_path .
        php_admin_value upload_tmp_dir /var/lib/phpmyadmin/tmp
        php_admin_value open_basedir /usr/share/phpmyadmin/:/etc/phpmyadmin/:/var/lib/phpmyadmin/:/usr/share/php/php-gettext/:/usr/share/php/php-php-gettext/:/usr/share/javascript/:/usr/sh>
        php_admin_value mbstring.func_overload 0
    </IfModule>
    <IfModule mod_php.c>
        <IfModule mod_mime.c>
            AddType application/x-httpd-php .php
        </IfModule>
        <FilesMatch ".+\.php$">
            SetHandler application/x-httpd-php
        </FilesMatch>

        php_value include_path .
        php_admin_value upload_tmp_dir /var/lib/phpmyadmin/tmp
        php_admin_value open_basedir /usr/share/phpmyadmin/:/etc/phpmyadmin/:/var/lib/phpmyadmin/:/usr/share/php/php-gettext/:/usr/share/php/php-php-gettext/:/usr/share/javascript/:/usr/sh>
        php_admin_value mbstring.func_overload 0
    </IfModule>

</Directory>

# Authorize for setup
<Directory /usr/share/phpmyadmin/setup>
    <IfModule mod_authz_core.c>
        <IfModule mod_authn_file.c>
            AuthType Basic
            AuthName "phpMyAdmin Setup"
            AuthUserFile /etc/phpmyadmin/htpasswd.setup
        </IfModule>
        Require valid-user
    </IfModule>
</Directory>

# Disallow web access to directories that don't need it
<Directory /usr/share/phpmyadmin/templates>
    Require all denied
</Directory>
<Directory /usr/share/phpmyadmin/libraries>
    Require all denied
</Directory>
<Directory /usr/share/phpmyadmin/setup/lib>
    Require all denied
</Directory>
EOT
sudo mv ./tmp.txt /etc/apache2/conf-enabled/phpmyadmin.conf

PATTERN=/var/lib/phpmyadmin/tmp
FILE=/usr/share/phpmyadmin/config.inc.php
echo "Modifying File: $FILE"
if grep -q $PATTERN $FILE;
 then
     echo "TempDir already added to $FILE"
 else
  echo "\$cfg['TempDir'] = '/var/lib/phpmyadmin/tmp';" | sudo tee -a "$FILE"
fi
echo "Editing blowfish_secret"
randomBlowfishSecret=H2OxcGXxflSd8JwrwVlh6KW6s2rER63i
sudo sed -i "s|cfg\['blowfish_secret'\] = ''|cfg['blowfish_secret'] = '$randomBlowfishSecret'|" "$FILE"

FILE=/etc/apache2/sites-enabled/000-default.conf
echo "Modifying File: $FILE"
if grep -q "DocumentRoot /var/www/html" $FILE
then
    sudo cp -a -- "$FILE" "$FILE-$(date +"%Y%m%d-%H%M%S")"
    sudo sed -i "s|/var/www/html|/var/www|" "$FILE"
    HTML_FILE=/var/www/html/index.html
    if test -f "$HTML_FILE"; then
      sudo mv /var/www/html/index.html /var/www
    else
      echo "index.html does not exit"
    fi
    sudo rm -R /var/www/html
    echo "Restarting the apache service"
    sudo systemctl restart apache2
else
    echo "$FILE Already Modified"
fi

echo "Enabling Apache rewrite"
sudo a2enmod rewrite

echo "Installing log2ram as a service (only install on Raspberry Pi)"
VAR1=$(cat /proc/device-tree/model | awk '{print $1}')
VAR2="Raspberry"
if [[ "$VAR1" == "$VAR2" ]]; then
    cd /home/maxair
    wget https://github.com/azlux/log2ram/archive/master.tar.gz -O log2ram.tar.gz
    tar xf log2ram.tar.gz
    rm log2ram.tar.gz
    cd /home/maxair/log2ram-master
    ./install.sh
    cd ../
    rm -R log2ram-master

    FILE=/etc/log2ram.conf
    echo "Modifying File: $FILE"
    if grep -q "SIZE=40M" $FILE
    then
        sudo sed -i "s|/SIZE=40M|/SIZE=100M|" "$FILE"
    fi
fi

echo "Installation Script Completed"
