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
echo "*   MaxAir Datase Script Version 0.01 Build Date 20/12/2020   *\n";
echo "*   Last Modified on 13/09/2022                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Install Script Started \n";
$line = "--------------------------------------------------------------- \n";

//Set php script execution time in seconds
ini_set('max_execution_time', 400); 
$date_time = date('Y-m-d H:i:s');

//Check php version before doing anything else 
$version = explode('.', PHP_VERSION);
if ($version[0] > 8){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Supported on php version 5.x to version 8.x you are running version \033[41m".phpversion()."\033[0m \n"; 
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

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Checking if Database Exits \n";
$query = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '{$dbname}' LIMIT 1;";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount == 0) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Does not Exist \n"; 
	$query = "CREATE DATABASE {$dbname};";
	$result = $conn->query($query);
	if ($result) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Created Successfully!!! \n"; 
	}else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Error Creating Database \n"; 
		mysqli_error($conn). "\n";
	}
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Already Exist. \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Create Dump File for Exiting Database. \n";
	//dump all mysql database and save as sql file
	$dumpfname = $dbname . "_" . date("Y-m-d_H-i-s").".sql";
	$command = "mysqldump --ignore-table=$dbname.backup --add-drop-table --host=$hostname --user=$dbusername ";
	if ($dbpassword)
        $command.= "--password=". $dbpassword ." ";
		$command.= $dbname;
		$command.= " > " . $dumpfname;
		system($command);
		// compress sql file and unlink (delete) sql file after creating zip file. 
		$zipfname = $dbname . "_" . date("Y-m-d_H-i-s").".zip";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Compressing Dump File \033[41m".$dumpfname."\033[0m \n";
		$zip = new ZipArchive();
		if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
			$zip->addFile($dumpfname,$dumpfname);
			$zip->close();
			unlink($dumpfname);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Compressed Dump File \033[41m".$zipfname."\033[0m \n";
		}
        $output = shell_exec('sudo systemctl stop pihome_jobs_schedule.service');
        echo $output;
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Dropping MySQL DataBase \033[41m".$dbname."\033[0m  \n";
        $query = "DROP DATABASE {$dbname};";
        $result = $conn->query($query);
        if ($result) {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Dropped Successfully!!! \n";
        }else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Error Dropping Database \n";
                mysqli_error($conn). "\n";
        }
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating MySQL DataBase \033[41m".$dbname."\033[0m  \n";
        $query = "CREATE DATABASE {$dbname};";
        $result = $conn->query($query);
        if ($result) {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Created Successfully!!! \n";
        }else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Error Creating Database \n";
                mysqli_error($conn). "\n";
        }
}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Importing SQL File to Database, This could take few minuts. \n";
	// Sop the jobs service
	$output = shell_exec('sudo systemctl stop pihome_jobs_schedule.service');
	echo $output;
	// Name of the file
	$filename = __DIR__.'/MySQL_Database/maxair_mysql_database.sql';
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
	$lines = file($filename);
	// Loop through each line
	$x = 1;
	$y = count($lines) - 1;
	foreach ($lines as $line){
		show_status($x, $y);
		$x = $x + 1;
	// Skip it if it's a comment
		if (substr($line, 0, 2) == '--' || $line == '')
			continue;
			// Add this line to the current segment
			$templine .= $line;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($line), -1, 1) == ';'){
				// Perform the query
				$conn->query($templine) or print("MySQL Database Error with Query ".$templine.":". mysqli_error($conn)."\n");
				//mysqli_query($templine) or print("MySQL Database Error with Query ".$templine.":". mysqli_error($conn)."\n");
				// Reset temp variable to empty
				$templine = '';
			}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase File \033[41m".$filename."\033[0m Imported Successfully \n";
	//Table View 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Creating Table View \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Importing SQL Table View File to Database, This could take few minuts.  \n";
	// Name of the file
	$tableviewfilename = __DIR__.'/MySQL_Database/MySQL_View.sql';
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$viewtempline = '';
	// Read in entire file
	$viewlines = file($tableviewfilename);
	$x = 1;
	$y = count($viewlines);
	// Loop through each line
	foreach ($viewlines as $viewline){
	// Skip it if it's a comment
                show_status($x, $y);
                $x = $x + 1;
		if (substr($viewline, 0, 2) == '--' || $viewline == '')
			continue;
			// Add this line to the current segment
			$viewtempline .= $viewline;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($viewline), -1, 1) == ';'){
				// Perform the query
				$conn->query($viewtempline) or print("MySQL Database Error with Query ".$viewtempline.":". mysqli_error($conn)."\n");
				//mysqli_query($viewtempline) or print("MySQL Database Error with Query ".$viewtempline.":". mysqli_error($conn)."\n");
				// Reset temp variable to empty
				$viewtempline = '';
			}
	}
	
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase DataBase File \033[41m".$tableviewfilename."\033[0m Imported Successfully \n";

//Job Scheduling
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Installing Scheduling \n";
$output = shell_exec('sudo bash /var/www/cron/install_jobs.sh');
echo $output;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Finished Installing Scheduling \n";

// Check/Add sudoers file /etc/sudoers.d/maxair
$rval = os_info();
if (strpos($rval["ID_LIKE"], "debian") !== false) {
	$web_user_name = "www-data";
} else {
	$web_user_name = "http";
}
$sudoersfile = '/etc/sudoers.d/maxair';
$message = $web_user_name.' ALL=(ALL) NOPASSWD:/sbin/iwlist wlan0 scan
'.$web_user_name.' ALL=(ALL) NOPASSWD:/sbin/reboot
'.$web_user_name.' ALL=(ALL) NOPASSWD:/sbin/shutdown -h now
'.$web_user_name.' ALL=(ALL) NOPASSWD:/bin/mv myfile1.tmp /etc/wpa_supplicant/wpa_supplicant.conf
'.$web_user_name.' ALL=(ALL) NOPASSWD:/sbin/ifconfig eth0
'.$web_user_name.' ALL=/bin/systemctl
'.$web_user_name.' ALL=NOPASSWD: /bin/systemctl
'.$web_user_name.' ALL=(ALL) NOPASSWD:/usr/bin/pkill
';
if (file_exists($sudoersfile)) {
        $output = shell_exec('cat '.$sudoersfile);
        if ($message==$output) {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Valid maxair sudoers Exist, No changes required \n";
        } else {
                //Remove existing sudoers File
                echo exec('rm -f '.$sudoersfile);
                //Save updated sudoers File
                file_put_contents($sudoersfile, $message);
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - maxair sudoers file Replaced. \n";
        }
} else {
        //Save new sudoers File
        file_put_contents($sudoersfile, $message);
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - New maxair sudoers file Added. \n";
}

// Add User table data 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating User Table.  \n";
$query_user = "REPLACE INTO `user` (`id`, `account_enable`, `fullname`, `username`, `email`, `password`, `cpdate`, `account_date`, `admin_account`) VALUES(1, 1, 'Administrator', 'admin', '', '0f5f9ba0136d5a8588b3fc70ec752869', 'date1', 'date2', 1);";
$query_user = str_replace("date1",$date_time,$query_user);
$query_user = str_replace("date2",$date_time,$query_user);
$results = $conn->query($query_user);
if ($results) {
	echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Add \033[41mUser\033[0m Data  Succeeded \n";
} else {
      	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Add \033[41mUser\033[0m Data Failed \n";
}

// Add System table data 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating System Table.  \n";
$query_system = "REPLACE INTO `system` (`id`, `sync`, `purge`, `name`, `version`, `build`, `update_location`, `update_file`, `update_alias`, `country`, `language`, `city`, `zip`, `openweather_api`, `backup_email`, `ping_home`, `timezone`, `shutdown`, `reboot`, `c_f`, `mode`) VALUES (2, 1, 0, 'MaxAir - Smart Thermostat', 'version_val', 'build_val', 'http://www.pihome.eu/updates/', 'current-release-versions.php', 'pihome', 'IE', 'en', 'Portlaoise', NULL, 'aa22d10d34b1e6cb32bd6a5f2cb3fb46', '', b'1', 'Europe/Dublin', 0, 0, 0, 0);";
$query_system = str_replace("version_val",$version,$query_system);
$query_system = str_replace("build_val",$build,$query_system);
$results = $conn->query($query_system);
if ($results) {
        echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Add \033[41mSystem\033[0m Data Succeeded \n";
} else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Add \033[41mSystem\033[0m Data Failed \n";
}

//Adding Away Record 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Away Status\n";
$datetime = date('Y-m-d H:i:s');
$query_system = "insert INTO `away` (`sync`, `purge`, `status`, start_datetime, `end_datetime`, `away_button_id`, `away_button_child_id`) VALUES (0, 0, 0, '$datetime', '$datetime', 0, 0);";
$results = $conn->query($query_system);
if ($results) {
        echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status Record Added \033[41mAway\033[0m Data  Succeeded \n";
} else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Away Status \033[41mAway\033[0m Data Failed \n";
}

//Adding GPIO 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding GPIO\n";
$datetime = date('Y-m-d H:i:s');
$query_system = "insert INTO `nodes` (`sync`, `purge`, `type`, node_id, `max_child_id`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES (0, 0, 'GPIO', 0, 0, 'GPIO Controller', '$datetime', 0, '0', 'Active', 0, 0, 0);";
$results = $conn->query($query_system);
if ($results) {
	echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - GPIO Added \033[41mGPIO\033[0m Data  Succeeded \n";
	$node_id = $conn->insert_id;
} else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - GPIO \033[41mGPIO\033[0m Data Failed \n";
}

//Adding System Controller Record
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding System Controller Record\n";
$datetime = date('Y-m-d H:i:s');
$query_system = "insert INTO `system_controller` (`id`, `sync`, `purge`, `mode`, `status`, `active_status`, `name`, `hysteresis_time`, `max_operation_time`, `overrun`, `datetime`, `sc_mode`, `sc_mode_prev`, `heat_relay_id`, `cool_relay_id`, `fan_relay_id`) VALUES (1, 0, 0, 0, 1, 0, 'Gas Boiler', 3, 60, 2, '$datetime', 0, 0, 0, 0, 0);";
$results = $conn->query($query_system);
if ($results) {
        echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Record Added \033[41mSystem Controller\033[0m Data  Succeeded \n";
} else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller \033[41mSystem Controller\033[0m Data Failed \n";
}

//Adding Virtual Gateway Record
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Virtual Gateway Record\n";
$datetime = date('Y-m-d H:i:s');
$query_gateway = "INSERT INTO `gateway`(`id`, `status`, `sync`, `purge`, `type`, `location`, `port`, `timout`, `pid`, `pid_running_since`, `reboot`, `find_gw`,
                `version`, `enable_outgoing`)
                VALUES (1,1,0,0,'virtual','','','0','','',0,'0','1.0',1);";
$results = $conn->query($query_gateway);
if ($results) {
        echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Virtual Gateway Record Added \033[41mVirtual Gateway\033[0m Data  Succeeded \n";
} else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Virtual Gateway \033[41mVirtual\033[0m Data Failed \n";
}

//Adding Zone Type Records 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Zone Type\n";
$datetime = date('Y-m-d H:i:s');
$query_zone_type = "insert INTO `zone_type` (`purge`, `sync`, `type`, `category`) VALUES (0, 0, 'Heating', 0), (0, 0, 'Water', 0), (0, 0, 'Immersion', 1), (0, 0, 'Lamp', 2), (0, 0, 'HVAC', 3), (0, 0, 'Humidity', 1);";
$results = $conn->query($query_zone_type);
if ($results) {
		echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Type Records Added \033[41mZone Type\033[0m Data  Succeeded \n";
} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Type Records \033[41mZone Type\033[0m Data Failed \n";
}

//Adding Initial Network Settings Record, (needed by gateway.py) 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Initial Network Settings Record\n";
$query_network_settings = "INSERT INTO `network_settings`(`sync`, `purge`, `primary_interface`, `ap_mode`, `interface_num`, `interface_type`, `mac_address`, `hostname`, `ip_address`, `gateway_address`, `net_mask`, `dns1_address`, `dns2_address`) VALUES (0, 0, 1, 0, 0, 'wlan0', '', '', '10.0.0.100', '10.0.0.1', '255.255.255.0', '', '');";
$results = $conn->query($query_network_settings);
if ($results) {
		echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Network Settings Record Added \033[41mNetwork Settings\033[0m Data  Succeeded \n";
} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Network Settings Record \033[41mNetwork Settings\033[0m Data Failed \n";
}

//Adding job scheduling records
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Job Scheduling Records\n";
$query_job_scheduling = "INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`) ";
$query_job_scheduling .= "VALUES ('shutdown_reboot','/var/www/cron/shutdown_reboot.py',1,0,'15','',now()),";
$query_job_scheduling .= "('check_sc','/var/www/cron/check_sc.php',1,0,'60','',now()),";
$query_job_scheduling .= "('controller','/var/www/cron/controller.php',1,0,'60','',now()),";
$query_job_scheduling .= "('db_cleanup','/var/www/cron/db_cleanup.php',1,0,'02:00','',now()),";
$query_job_scheduling .= "('check_gw','/var/www/cron/check_gw.php',1,0,'60','',now()),";
$query_job_scheduling .= "('system_c','/var/www/cron/system_c.php',1,0,'300','',now()),";
$query_job_scheduling .= "('weather_update','/var/www/cron/weather_update.php',1,0,'1800','',now()),";
$query_job_scheduling .= "('reboot_wifi','/var/www/cron/reboot_wifi.sh',1,0,'120','',now()),";
$query_job_scheduling .= "('check_ds18b20','/var/www/cron/check_ds18b20.php',0,0,'60','',now()),";
$query_job_scheduling .= "('notice','/var/www/cron/notice.py',0,0,'60','',now()),";
$query_job_scheduling .= "('sw_install','/var/www/cron/sw_install.py',1,0,'10','',now()),";
$query_job_scheduling .= "('update_code','/var/www/cron/update_code.py',1,0,'00:00','',now());";
$results = $conn->query($query_job_scheduling);
if ($results) {
                echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Job Scheduling Records Added \033[41mJobs\033[0m Data  Succeeded \n";
} else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Job Scheduling Records \033[41mJobs\033[0m Data Failed \n";
}

//Adding sensor type records
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Sensor Type Records\n";
$query_sensor_type = "INSERT INTO `sensor_type`(`id`, `sync`, `purge`, `type`) ";
$query_sensor_type .= "VALUES (1,0,0,'Temperature'),";
$query_sensor_type .= "(2,0,0,'Humidity');";
$results = $conn->query($query_sensor_type);
if ($results) {
                echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Sensor Type Records Added \033[41mSensor Types\033[0m Data  Succeeded \n";
} else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Sensor Type Records \033[41mSensor Types\033[0m Data Failed \n";
}

//check if database_updates table already exist
$query = "SELECT * FROM information_schema.tables WHERE table_schema = 'maxair' AND table_name = 'database_backup' LIMIT 1;";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount == 0) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - database_backup Table Does Not Exist, Creating it.\n";
        $query = "CREATE TABLE IF NOT EXISTS `database_backup` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `sync` tinyint(4) NOT NULL,
        `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
        `status` tinyint(4),
        `backup_name` char(50) COLLATE utf8_bin DEFAULT NULL,
        `name` char(50) COLLATE utf8_bin DEFAULT NULL,
         PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;";
        if ($conn->query($query)) {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Backup Table Created.  \n";
        } else {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Failed to Create Backup Table.  \n";
        }
} else {
	$query = "SELECT * FROM `database_backup`;";
	$result = $conn->query($query);
	$ucount=mysqli_num_rows($result);
	if ($ucount > 0){
	        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Deleting Existing Database Update Records. \n";
		$query = "DELETE FROM `database_backup`;";
		$results = $conn->query($query);
		if ($results) {
                	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Deleting \033[41mDatabase Updates\033[0m Data  Succeeded \n";
		} else {
                	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Deleting \033[41mDatabase Updates\033[0m Data Failed \n";
		}
	} else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - NO Existing Database Update Records Found. \n";
	}
}

// Check for database updates
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Check for Database Updates.  \n";
$update_dir = __DIR__.'/MySQL_Database/database_updates';
$ffs = scan_db_update_dir($update_dir);
if ($ffs) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Updates Found. \n";
        foreach($ffs as $ff){
               	// save the update info to the database_updates table
                $query = "INSERT INTO `database_backup`(`sync`, `purge`, `status`, `backup_name`, `name`) VALUES ('0','0','0','".$ff."','".$ff."');";
               	if ($conn->query($query)) {
                    	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Un-applied Update Information Added to Table. \n";
                } else {
                       	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Failed to Add Update to Table. \n";
                }
               	// Apply the Update file
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing Update SQL to Database.  \n";
                // Name of the file
                $updatefilename = __DIR__.'/MySQL_Database/database_updates/'.$ff;
                // Temporary variable, used to store current query
                $updatetempline = '';
                // Read in entire file
                $updatelines = file($updatefilename);
                // Loop through each line
                foreach ($updatelines as $updateline){
                       	// Skip it if it's a comment
                        if (substr($updateline, 0, 2) == '--' || $updateline == '')
                                continue;
                        // Add this line to the current segment
                        $updatetempline .= $updateline;
                        // If it has a semicolon at the end, it's the end of the query
                        if (substr(trim($updateline), -1, 1) == ';'){
                              	// Perform the query
                                $conn->query($updatetempline) or print("MySQL Database Error with Query ".$updatetempline.":". mysqli_error($conn)."\n");
                                //mysqli_query($updatetempline) or print("MySQL Database Error with Query ".$updatetempline.":". mysqli_error($conn)."\n");
                                // Reset temp variable to empty

                                $updatetempline = '';
                        }
                }
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Update File \033[41m".$updatefilename."\033[0m Applied. \n";
	}
} else {
       	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - There are No Database Updates to Apply.\n";
}

// Check if running Orange Pi OS and if so create a symlink for Adafruit Platform  Detect
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Check if Running Orange Pi OS.  \n";

$target = '/etc/orangepi-release';
$link = '/etc/armbian-release';

if (file_exists($target) && !file_exists($link)) {
    symlink($target, $link);
    echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Symlink \033[41m".$link."\033[0m Created. \n";
} else {
        if (file_exists($link)) {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Symlink already created.\n";
        } else {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Not running Orange Pi OS - symlink not required.\n";
        }
}

//
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database and Crontab Setup Completed.\n\t\t\tDo you want to continue with Time Zone, Language and Temperature Unit setup?\n\t\t\tEnter 'y' to continue or 'n' to finish with setup.\n";
$units = array('y' => 1, 'yes'=> 1, 'n'=> 0, 'no'=> 0);
$correct = 0;
while ($correct == 0) {
	$tzinput = trim(fgets(STDIN));
	$tzinput = strtolower($tzinput);
	if (array_key_exists($tzinput, $units)) {
		$tzname = $units[$tzinput];
		$correct = 1;
	} else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - !!!Wrong value, enter yes or no!!!\n";
	}
}

if ($tzname == 1) {
	// Set Time Zone
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - TimeZome Configuring Time Zone\n";
	$timezones = array();
	array_push($timezones, array('zone_name' =>   'Pacific/Midway'      , 'zone_info' => "(GMT-11:00) Midway Island"));
	array_push($timezones, array('zone_name' =>   'US/Samoa'             , 'zone_info' => "(GMT-11:00) Samoa"));
	array_push($timezones, array('zone_name' =>   'US/Hawaii'            , 'zone_info' => "(GMT-10:00) Hawaii"));
	array_push($timezones, array('zone_name' =>   'US/Alaska'            , 'zone_info' => "(GMT-09:00) Alaska"));
	array_push($timezones, array('zone_name' =>   'US/Pacific'           , 'zone_info' => "(GMT-08:00) Pacific Time (US &amp; Canada)"));
	array_push($timezones, array('zone_name' =>   'America/Tijuana'      , 'zone_info' => "(GMT-08:00) Tijuana"));
	array_push($timezones, array('zone_name' =>   'US/Arizona'           , 'zone_info' => "(GMT-07:00) Arizona"));
	array_push($timezones, array('zone_name' =>   'US/Mountain'          , 'zone_info' => "(GMT-07:00) Mountain Time (US &amp; Canada)"));
	array_push($timezones, array('zone_name' =>   'America/Chihuahua'    , 'zone_info' => "(GMT-07:00) Chihuahua"));
	array_push($timezones, array('zone_name' =>   'America/Mazatlan'     , 'zone_info' => "(GMT-07:00) Mazatlan"));
	array_push($timezones, array('zone_name' =>   'America/Mexico_City'  , 'zone_info' => "(GMT-06:00) Mexico City"));
	array_push($timezones, array('zone_name' =>   'America/Monterrey'    , 'zone_info' => "(GMT-06:00) Monterrey"));
	array_push($timezones, array('zone_name' =>   'Canada/Saskatchewan'  , 'zone_info' => "(GMT-06:00) Saskatchewan"));
	array_push($timezones, array('zone_name' =>   'US/Central'           , 'zone_info' => "(GMT-06:00) Central Time (US &amp; Canada)"));
	array_push($timezones, array('zone_name' =>   'US/Eastern'           , 'zone_info' => "(GMT-05:00) Eastern Time (US &amp; Canada)"));
	array_push($timezones, array('zone_name' =>   'US/East-Indiana'      , 'zone_info' => "(GMT-05:00) Indiana (East)"));
	array_push($timezones, array('zone_name' =>   'America/Bogota'       , 'zone_info' => "(GMT-05:00) Bogota"));
	array_push($timezones, array('zone_name' =>   'America/Lima'         , 'zone_info' => "(GMT-05:00) Lima"));
	array_push($timezones, array('zone_name' =>   'America/Caracas'      , 'zone_info' => "(GMT-04:30) Caracas"));
	array_push($timezones, array('zone_name' =>   'Canada/Atlantic'      , 'zone_info' => "(GMT-04:00) Atlantic Time (Canada)"));
	array_push($timezones, array('zone_name' =>   'America/La_Paz'       , 'zone_info' => "(GMT-04:00) La Paz"));
	array_push($timezones, array('zone_name' =>   'America/Santiago'     , 'zone_info' => "(GMT-04:00) Santiago"));
	array_push($timezones, array('zone_name' =>   'Canada/Newfoundland'  , 'zone_info' => "(GMT-03:30) Newfoundland"));
	array_push($timezones, array('zone_name' =>   'America/Buenos_Aires' , 'zone_info' => "(GMT-03:00) Buenos Aires"));
	array_push($timezones, array('zone_name' =>   'Greenland'            , 'zone_info' => "(GMT-03:00) Greenland"));
	array_push($timezones, array('zone_name' =>   'Atlantic/Stanley'     , 'zone_info' => "(GMT-02:00) Stanley"));
	array_push($timezones, array('zone_name' =>   'Atlantic/Azores'      , 'zone_info' => "(GMT-01:00) Azores"));
	array_push($timezones, array('zone_name' =>   'Atlantic/Cape_Verde'  , 'zone_info' => "(GMT-01:00) Cape Verde Is."));
	array_push($timezones, array('zone_name' =>   'Africa/Casablanca'    , 'zone_info' => "(GMT) Casablanca"));
	array_push($timezones, array('zone_name' =>   'Europe/Dublin'        , 'zone_info' => "(GMT) Dublin"));
	array_push($timezones, array('zone_name' =>   'Europe/Lisbon'        , 'zone_info' => "(GMT) Lisbon"));
	array_push($timezones, array('zone_name' =>   'Europe/London'        , 'zone_info' => "(GMT) London"));
	array_push($timezones, array('zone_name' =>   'Africa/Monrovia'      , 'zone_info' => "(GMT) Monrovia"));
	array_push($timezones, array('zone_name' =>   'Europe/Amsterdam'     , 'zone_info' => "(GMT+01:00) Amsterdam"));
	array_push($timezones, array('zone_name' =>   'Europe/Belgrade'      , 'zone_info' => "(GMT+01:00) Belgrade"));
	array_push($timezones, array('zone_name' =>   'Europe/Berlin'        , 'zone_info' => "(GMT+01:00) Berlin"));
	array_push($timezones, array('zone_name' =>   'Europe/Bratislava'    , 'zone_info' => "(GMT+01:00) Bratislava"));
	array_push($timezones, array('zone_name' =>   'Europe/Brussels'      , 'zone_info' => "(GMT+01:00) Brussels"));
	array_push($timezones, array('zone_name' =>   'Europe/Budapest'      , 'zone_info' => "(GMT+01:00) Budapest"));
	array_push($timezones, array('zone_name' =>   'Europe/Copenhagen'    , 'zone_info' => "(GMT+01:00) Copenhagen"));
	array_push($timezones, array('zone_name' =>   'Europe/Ljubljana'     , 'zone_info' => "(GMT+01:00) Ljubljana"));
	array_push($timezones, array('zone_name' =>   'Europe/Madrid'        , 'zone_info' => "(GMT+01:00) Madrid"));
	array_push($timezones, array('zone_name' =>   'Europe/Paris'         , 'zone_info' => "(GMT+01:00) Paris"));
	array_push($timezones, array('zone_name' =>   'Europe/Prague'        , 'zone_info' => "(GMT+01:00) Prague"));
	array_push($timezones, array('zone_name' =>   'Europe/Rome'          , 'zone_info' => "(GMT+01:00) Rome"));
	array_push($timezones, array('zone_name' =>   'Europe/Sarajevo'      , 'zone_info' => "(GMT+01:00) Sarajevo"));
	array_push($timezones, array('zone_name' =>   'Europe/Skopje'        , 'zone_info' => "(GMT+01:00) Skopje"));
	array_push($timezones, array('zone_name' =>   'Europe/Stockholm'     , 'zone_info' => "(GMT+01:00) Stockholm"));
	array_push($timezones, array('zone_name' =>   'Europe/Vienna'        , 'zone_info' => "(GMT+01:00) Vienna"));
	array_push($timezones, array('zone_name' =>   'Europe/Warsaw'        , 'zone_info' => "(GMT+01:00) Warsaw"));
	array_push($timezones, array('zone_name' =>   'Europe/Zagreb'        , 'zone_info' => "(GMT+01:00) Zagreb"));
	array_push($timezones, array('zone_name' =>   'Europe/Athens'        , 'zone_info' => "(GMT+02:00) Athens"));
	array_push($timezones, array('zone_name' =>   'Europe/Bucharest'     , 'zone_info' => "(GMT+02:00) Bucharest"));
	array_push($timezones, array('zone_name' =>   'Africa/Cairo'         , 'zone_info' => "(GMT+02:00) Cairo"));
	array_push($timezones, array('zone_name' =>   'Africa/Harare'        , 'zone_info' => "(GMT+02:00) Harare"));
	array_push($timezones, array('zone_name' =>   'Europe/Helsinki'      , 'zone_info' => "(GMT+02:00) Helsinki"));
	array_push($timezones, array('zone_name' =>   'Europe/Istanbul'      , 'zone_info' => "(GMT+02:00) Istanbul"));
	array_push($timezones, array('zone_name' =>   'Asia/Jerusalem'       , 'zone_info' => "(GMT+02:00) Jerusalem"));
	array_push($timezones, array('zone_name' =>   'Europe/Kiev'          , 'zone_info' => "(GMT+02:00) Kyiv"));
	array_push($timezones, array('zone_name' =>   'Europe/Minsk'         , 'zone_info' => "(GMT+02:00) Minsk"));
	array_push($timezones, array('zone_name' =>   'Europe/Riga'          , 'zone_info' => "(GMT+02:00) Riga"));
	array_push($timezones, array('zone_name' =>   'Europe/Sofia'         , 'zone_info' => "(GMT+02:00) Sofia"));
	array_push($timezones, array('zone_name' =>   'Europe/Tallinn'       , 'zone_info' => "(GMT+02:00) Tallinn"));
	array_push($timezones, array('zone_name' =>   'Europe/Vilnius'       , 'zone_info' => "(GMT+02:00) Vilnius"));
	array_push($timezones, array('zone_name' =>   'Asia/Baghdad'         , 'zone_info' => "(GMT+03:00) Baghdad"));
	array_push($timezones, array('zone_name' =>   'Asia/Kuwait'          , 'zone_info' => "(GMT+03:00) Kuwait"));
	array_push($timezones, array('zone_name' =>   'Africa/Nairobi'       , 'zone_info' => "(GMT+03:00) Nairobi"));
	array_push($timezones, array('zone_name' =>   'Asia/Riyadh'          , 'zone_info' => "(GMT+03:00) Riyadh"));
	array_push($timezones, array('zone_name' =>   'Europe/Moscow'        , 'zone_info' => "(GMT+03:00) Moscow"));
	array_push($timezones, array('zone_name' =>   'Asia/Tehran'          , 'zone_info' => "(GMT+03:30) Tehran"));
	array_push($timezones, array('zone_name' =>   'Asia/Baku'            , 'zone_info' => "(GMT+04:00) Baku"));
	array_push($timezones, array('zone_name' =>   'Europe/Volgograd'     , 'zone_info' => "(GMT+04:00) Volgograd"));
	array_push($timezones, array('zone_name' =>   'Asia/Muscat'          , 'zone_info' => "(GMT+04:00) Muscat"));
	array_push($timezones, array('zone_name' =>   'Asia/Tbilisi'         , 'zone_info' => "(GMT+04:00) Tbilisi"));
	array_push($timezones, array('zone_name' =>   'Asia/Yerevan'         , 'zone_info' => "(GMT+04:00) Yerevan"));
	array_push($timezones, array('zone_name' =>   'Asia/Kabul'           , 'zone_info' => "(GMT+04:30) Kabul"));
	array_push($timezones, array('zone_name' =>   'Asia/Karachi'         , 'zone_info' => "(GMT+05:00) Karachi"));
	array_push($timezones, array('zone_name' =>   'Asia/Tashkent'        , 'zone_info' => "(GMT+05:00) Tashkent"));
	array_push($timezones, array('zone_name' =>   'Asia/Kolkata'         , 'zone_info' => "(GMT+05:30) Kolkata"));
	array_push($timezones, array('zone_name' =>   'Asia/Kathmandu'       , 'zone_info' => "(GMT+05:45) Kathmandu"));
	array_push($timezones, array('zone_name' =>   'Asia/Yekaterinburg'   , 'zone_info' => "(GMT+06:00) Ekaterinburg"));
	array_push($timezones, array('zone_name' =>   'Asia/Almaty'          , 'zone_info' => "(GMT+06:00) Almaty"));
	array_push($timezones, array('zone_name' =>   'Asia/Dhaka'           , 'zone_info' => "(GMT+06:00) Dhaka"));
	array_push($timezones, array('zone_name' =>   'Asia/Novosibirsk'     , 'zone_info' => "(GMT+07:00) Novosibirsk"));
	array_push($timezones, array('zone_name' =>   'Asia/Bangkok'         , 'zone_info' => "(GMT+07:00) Bangkok"));
	array_push($timezones, array('zone_name' =>   'Asia/Jakarta'         , 'zone_info' => "(GMT+07:00) Jakarta"));
	array_push($timezones, array('zone_name' =>   'Asia/Krasnoyarsk'     , 'zone_info' => "(GMT+08:00) Krasnoyarsk"));
	array_push($timezones, array('zone_name' =>   'Asia/Chongqing'       , 'zone_info' => "(GMT+08:00) Chongqing"));
	array_push($timezones, array('zone_name' =>   'Asia/Hong_Kong'       , 'zone_info' => "(GMT+08:00) Hong Kong"));
	array_push($timezones, array('zone_name' =>   'Asia/Kuala_Lumpur'    , 'zone_info' => "(GMT+08:00) Kuala Lumpur"));
	array_push($timezones, array('zone_name' =>   'Australia/Perth'      , 'zone_info' => "(GMT+08:00) Perth"));
	array_push($timezones, array('zone_name' =>   'Asia/Singapore'       , 'zone_info' => "(GMT+08:00) Singapore"));
	array_push($timezones, array('zone_name' =>   'Asia/Taipei'          , 'zone_info' => "(GMT+08:00) Taipei"));
	array_push($timezones, array('zone_name' =>   'Asia/Ulaanbaatar'     , 'zone_info' => "(GMT+08:00) Ulaan Bataar"));
	array_push($timezones, array('zone_name' =>   'Asia/Urumqi'          , 'zone_info' => "(GMT+08:00) Urumqi"));
	array_push($timezones, array('zone_name' =>   'Asia/Irkutsk'         , 'zone_info' => "(GMT+09:00) Irkutsk"));
	array_push($timezones, array('zone_name' =>   'Asia/Seoul'           , 'zone_info' => "(GMT+09:00) Seoul"));
	array_push($timezones, array('zone_name' =>   'Asia/Tokyo'           , 'zone_info' => "(GMT+09:00) Tokyo"));
	array_push($timezones, array('zone_name' =>   'Australia/Adelaide'   , 'zone_info' => "(GMT+09:30) Adelaide"));
	array_push($timezones, array('zone_name' =>   'Australia/Darwin'     , 'zone_info' => "(GMT+09:30) Darwin"));
	array_push($timezones, array('zone_name' =>   'Asia/Yakutsk'         , 'zone_info' => "(GMT+10:00) Yakutsk"));
	array_push($timezones, array('zone_name' =>   'Australia/Brisbane'   , 'zone_info' => "(GMT+10:00) Brisbane"));
	array_push($timezones, array('zone_name' =>   'Australia/Canberra'   , 'zone_info' => "(GMT+10:00) Canberra"));
	array_push($timezones, array('zone_name' =>   'Pacific/Guam'         , 'zone_info' => "(GMT+10:00) Guam"));
	array_push($timezones, array('zone_name' =>   'Australia/Hobart'     , 'zone_info' => "(GMT+10:00) Hobart"));
	array_push($timezones, array('zone_name' =>   'Australia/Melbourne'  , 'zone_info' => "(GMT+10:00) Melbourne"));
	array_push($timezones, array('zone_name' =>   'Pacific/Port_Moresby' , 'zone_info' => "(GMT+10:00) Port Moresby"));
	array_push($timezones, array('zone_name' =>   'Australia/Sydney'     , 'zone_info' => "(GMT+10:00) Sydney"));
	array_push($timezones, array('zone_name' =>   'Asia/Vladivostok'     , 'zone_info' => "(GMT+11:00) Vladivostok"));
	array_push($timezones, array('zone_name' =>   'Asia/Magadan'         , 'zone_info' => "(GMT+12:00) Magadan"));
	array_push($timezones, array('zone_name' =>   'Pacific/Auckland'     , 'zone_info' => "(GMT+12:00) Auckland"));
	array_push($timezones, array('zone_name' =>   'Pacific/Fiji'         , 'zone_info' => "(GMT+12:00) Fiji"));
	$zoneid=1;
	echo "TZ ID\t TZ Information\n";
	foreach($timezones as $timezone) {
		echo $zoneid."\t".$timezone['zone_info']."\n";
		$zoneid +=1;
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - TimeZone !!!Please Enter Time Zone ID and press Enter!!!\n";
	$correct = 0;
	while ($correct == 0) {
		$tzinput = trim(fgets(STDIN));
		if (array_key_exists($tzinput-1, $timezones)) {
			$tzname = $timezones[$tzinput-1]['zone_name'];
			echo exec("timedatectl set-timezone $tzname");
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - TimeZone Updated System Time Zone to \033[41m".$tzname."\033[0m.\n";
			$query = "UPDATE `system` SET `timezone`='" . $tzname . "';";
			$conn->query($query) or print("MySQL Database Error with Query ".$query.":". mysqli_error($conn)."\n");
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - TimeZone Updated Database Value to \033[41m".$tzname."\033[0m.\n";
			$correct = 1;
		} else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - TimeZone !!!Wrong value, IDs 1 to 112!!!\n";
		}
	}

	// Set Language
	if ($handle = opendir('/var/www/languages')) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Language Setting Up MaxAir Language\n";
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Language Listing all available languages:\n";
		$lnglist = array();
		while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
				$entry=basename($entry,".php");
				array_push($lnglist, $entry);
			}
		}
		closedir($handle);
		sort($lnglist);
		$zoneid=1;
		echo "ID\tLanguage\n";
		foreach($lnglist as $item) {
			echo $zoneid."\t".$item."\n";
			$zoneid +=1;
		}
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Language !!!Please Enter Language ID and press Enter!!!\n";
	$correct = 0;
	while ($correct == 0) {
		$tzinput = trim(fgets(STDIN));
		if (array_key_exists($tzinput-1, $lnglist)) {
			$tzname = $lnglist[$tzinput-1];
			$query = "UPDATE `system` SET `language`='" . $tzname . "';";
			$conn->query($query) or print("MySQL Database Error with Query ".$query.":". mysqli_error($conn)."\n");
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Language Updated Database Value to \033[41m".$tzname."\033[0m.\n";
			$correct = 1;
		} else {
			$zoneid=count($lnglist);
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Language !!!Wrong value, IDs 1 to $zoneid!!!\n";
		}
	}

	// Set Temperature Unit
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Unit Setup\n";
	$units = array('Celsius', 'Fahrenheit');
	echo "ID\tUnit\n";
	echo "1\t".$units[0]."\n";
	echo "2\t".$units[1]."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Unit !!!Please Enter Temperature Unit ID and press Enter!!!\n";
	$correct = 0;
	while ($correct == 0) {
		$tzinput = trim(fgets(STDIN));
		if (array_key_exists($tzinput-1, $units)) {
			$tzname = $units[$tzinput-1];
			if ($tzname == 'Celsius'){$sunit=0;}else{$sunit=1;}
			$query = "UPDATE `system` SET `c_f`='" . $sunit . "';";
			$conn->query($query) or print("MySQL Database Error with Query ".$query.":". mysqli_error($conn)."\n");
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Unit Updated Database Value to \033[41m".$tzname."\033[0m.\n";
			$correct = 1;
		} else {
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Unit !!!Wrong value, IDs 1 and 2!!!\n";
		}
	}

        // Add MaxAir Banner to starup screen
        if( strpos(file_get_contents("/etc/profile"),"sudo python3 /var/www/cron/login.py") === false) {
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Adding Startup Banner\n";
                $fp = fopen('/etc/profile', 'a');
                fwrite($fp, 'sudo python3 /var/www/cron/login.py');
                fclose($fp);
        }
}

echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Install Script Ended \n"; 
echo "\033[32m****************************************************************************************\033[0m  \n";

// scan directory and return array of files and folder names
function scan_dir($dir) {
        $ignored = array('.', '..', 'updates.txt');

        $files = array();
        foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored)) continue;
                $files[$file] = filemtime($dir . '/' . $file);
        }
        $files = array_keys($files);
        return ($files) ? $files : false;
}

// scan directory and return array of files sorted by name timestamp
function scan_db_update_dir($dir) {
        $ignored = array('.', '..', 'example.sql');

        $files = array();
        foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored)) continue;
                // create a key value based on the first 6 characters of the filename
                if (ctype_digit(substr($file,0,6))) {
                        $x = intval(substr($file,0,2)) + (intval(substr($file,2,2)) * 31) + (intval(substr($file,4,2)) * 366);
                        $files[$x] = $file;
                }
        }
        // sort ascending by key value
        ksort($files);
        return ($files) ? $files : false;
}

// command line spinner function
function show_status($done, $total, $size=30) {

    static $start_time;

    // if we go over our bound, just ignore it
    if($done > $total) return;

    if(empty($start_time)) $start_time=time();
    $now = time();

    $perc=(double)($done/$total);

    $bar=floor($perc*$size);

    $status_bar="\r[";
    $status_bar.=str_repeat("=", $bar);
    if($bar<$size){
        $status_bar.=">";
        $status_bar.=str_repeat(" ", $size-$bar);
    } else {
        $status_bar.="=";
    }

    $disp=number_format($perc*100, 0);

    $status_bar.="] $disp%  $done/$total";

    $rate = ($now-$start_time)/$done;
    $left = $total - $done;
    $eta = round($rate * $left, 2);

    $elapsed = $now - $start_time;

    $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";

    echo "$status_bar  ";

    flush();

    // when done, send a newline
    if($done == $total) {
        echo "\n";
    }
}

function os_info() {
        if (strtolower(substr(PHP_OS, 0, 5)) === 'linux')
        {
            $vars = array();
            $files = glob('/etc/*-release');

            foreach ($files as $file)
            {
                $lines = array_filter(array_map(function($line) {

                    // split value from key
                    $parts = explode('=', $line);

                    // makes sure that "useless" lines are ignored (together with array_filter)
                    if (count($parts) !== 2) return false;

                    // remove quotes, if the value is quoted
                    $parts[1] = str_replace(array('"', "'"), '', $parts[1]);
                    return $parts;

                }, file($file)));

                foreach ($lines as $line)
                    $vars[$line[0]] = $line[1];
            }

        return $vars;
        }
}
?>
