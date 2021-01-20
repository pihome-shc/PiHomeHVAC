<?php 
#!/usr/bin/php
echo "\033[36m";
echo "\n";
echo "           __  __                             _         \n";
echo "          |  \/  |                    /\     (_)        \n";
echo "          | \  / |   __ _  __  __    /  \     _   _ __  \n";
echo "          | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| \n";
echo "          | |  | | | (_| |  >  <   / ____ \  | | | |    \n";
echo "          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    \n";
echo " \033[0m \n";
echo "                \033[45m S M A R T   T H E R M O S T A T \033[0m \n";
echo "\033[31m";
echo "***************************************************************\n";
echo "*   PiHome Install Script Version 0.3 Build Date 31/01/2018   *\n";
echo "*   Last Modified on 13/05/2018                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Install Script Started \n"; 
echo "---------------------------------------------------------------------------------------- \n";

//Set php script execution time in seconds
ini_set('max_execution_time', 400); 
$date_time = date('Y-m-d H:i:s');
//Temporary File to save exiting CronJobs
$cronfile = '/tmp/crontab.txt';

//Check php version before doing anything else 
$version = explode('.', PHP_VERSION);
if ($version[0] > 7){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Supported on php version 5.x you are running version \033[41m".phpversion()."\033[0m \n"; 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Please visit http://www.pihome.eu/2017/10/11/apache-php-mysql-raspberry-pi-lamp/ to install correction version. \n";
	exit();
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PHP Version \033[41m".phpversion()."\033[0m Looks OK \n";
}

$settings = parse_ini_file(__DIR__.'/st_inc/db_config.ini');
foreach ($settings as $key => $setting) {
    // Notice the double $$, this tells php to create a variable with the same name as key
    $$key = $setting;
}

echo "\033[32mMake Sure you have correct MySQL/MariaDB credentials as following \033[0m\n";
echo "Hostname:     ".$hostname."\n";
echo "Database:     ".$dbname."\n";
echo "User Name:    ".$dbusername."\n";
echo "Password:     ".$dbpassword."\n";

//Test Connection to MySQL Server with Given Username & Password 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Testing Connection to MySQL/MariaDB Server. \n";
$conn = new mysqli($hostname, $dbusername, $dbpassword);
if ($conn->connect_error){
	die('Database Connecction Failed with Error: '.$conn->connect_error);
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Server Connection Successfull \n";
}

if (file_exists($cronfile)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab Removing old CronJobs File \033[41m ".$cronfile." \033[0m \n";
	unlink($cronfile);
}

//CronJobs
$output = shell_exec('crontab -l');
//Add CronJobs 
$message = '#
#             __  __                             _
#            |  \/  |                    /\     (_)
#            | \  / |   __ _  __  __    /  \     _   _ __
#            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
#            | |  | | | (_| |  >  <   / ____ \  | | | |
#            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
#
#                    S M A R T   T H E R M O S T A T
#*************************************************************************
#* MaxAir is a Linux based Central Heating Control systems. It runs from *
#* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *
#* extent permitted by applicable law. I take no responsibility for any  *
#* loss or damage to you or your property.                               *
#* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *
#* WHAT YOU ARE DOING                                                    *
#*************************************************************************
#
#

# Database Cleaup: Delete Temperature records older then 3 days.
# Delete Node Battery status older then 3 months.
# Delete Gateway Logs data older then 3 days.
# if you want to keep all data comments following line.
0 2 * * * /usr/bin/php /var/www/cron/db_cleanup.php  >/dev/null 2>&1

# Get CPU temperature and update database.
*/5 * * * * /usr/bin/php /var/www/cron/system_c.php >/dev/null 2>&1

# Update Weather from OpenWeather, Make sure you signup to openweather api
# and update api key in database->system table.
*/30 * * * * /usr/bin/php /var/www/cron/weather_update.php >/dev/null 2>&1

# Ping your gateway (home router) and if can not ping reboot wifi on Raspberry pi.
# make sure you modify /var/www/cron/reboot_wifi.sh with your gateway ip
# if you want to save log resuts un-commnets following line.
# */2 * * * * sh /var/www/cron/reboot_wifi.sh >>/var/www/cron/logs/reboot_wifi.log 2>&1
*/2 * * * * sh /var/www/cron/reboot_wifi.sh >/dev/null 2>&1

# If you are using Wireless setup with Smart Home Gateway then you need following crong job
# to  check and start Smart Home Gateway python script if its not running.
*/1 * * * * php /var/www/cron/check_gw.php >/dev/null 2>&1

# If you have Temperature Sensors Wired to Raspberry pi GPIO un-comment
# following line to read temperature sensors data.
# */1 * * * * python3 /var/www/cron/gpio_ds18b20.py >/dev/null 2>&1


# Main engine for PiHome Smart Heating, If you want to ouput logs then comment first line and uncomment second line.
*/1 * * * * /usr/bin/php /var/www/cron/controller.php >/dev/null 2>&1
# */1 * * * * /usr/bin/php /var/www/cron/controller.php >>/var/www/cron/logs/system_controller.log 2>&1

# If you signup for PiConnect - Simplify the Connected API Key you can save this key to PiConnect table to sync.
# your data with PiConnect this way you can mange your heating from
# http://www.pihome.eu/piconnect/ as todate this service is still under testing.
# email me at info@pihome.eu if you need help or more information about this.
*/1 * * * * /usr/bin/php /var/www/cron/piconnect.php >/dev/null 2>&1

#Following Script is deprecated
# @reboot sh /var/www/cron/gw.sh >/dev/null 2>&1

#Please add your cron jobs below this line
#-------------------------------------------------------------------------
';

if ($message==$output) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab CronJobs Exist, No changes required \n";
} else {
	//Save Temporary CronJobs File
	file_put_contents($cronfile, $message);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab CronJobs Added from \033[41m ".$cronfile." \033[0m File. \n";
	//Append Existing CronJobs to File
	file_put_contents($cronfile, $output, FILE_APPEND);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab Existing CronJobs Saved to \033[41m ".$cronfile." \033[0m File. \n";
	//Add CronJobs to CronTab
	echo exec('crontab '. $cronfile);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - \033[41mCronTab Make Sure no duplicate jobs running 'crontab -l' .\033[0m\n";
}
	
if (file_exists($cronfile)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - CronTab Removing CronJobs File \033[41m ".$cronfile." \033[0m \n";
	unlink($cronfile);
}

echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Install Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";
?>
