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
echo "****************************************************************\n";
echo "*   PiHome Migration Script Version 0.01 Build Date 17/04/2021 *\n";
echo "*   Last Modified on 31/07/2020                                *\n";
echo "*                                      Have Fun - PiHome.eu    *\n";
echo "****************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Database Migration Script Started \n"; 
$line = "--------------------------------------------------------------- \n";

require_once(__DIR__.'/../st_inc/dbStruct.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 400); 
$date_time = date('Y-m-d H:i:s');
echo $line;
//Check php version before doing anything else 
$version = explode('.', PHP_VERSION);
if ($version[0] > 7){
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Supported on php version 5.x or above you are running version \033[41m".phpversion()."\033[0m \n"; 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Please visit http://www.pihome.eu/2017/10/11/apache-php-mysql-raspberry-pi-lamp/ to install correction version. \n";
	exit();
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - php version \033[41m".phpversion()."\033[0m looks OK \n";
}

$settings = parse_ini_file(__DIR__.'/../st_inc/db_config.ini');
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
$db_selected = mysqli_select_db($conn, $dbname);
if ($db_selected) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database ".$dbname." Found \n";
        // create an image of the currently installed database, without VIEWS
        mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
        //dump all mysql database and save as sql file
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating Dump File for Exiting Database. \n";
        $dumpfname = $dbname . "_" . date("Y-m-d_H-i-s").".sql";
        $command = "mysqldump --host=$hostname --user=$dbusername ";
        if ($dbpassword) {
                $command.= "--password=". $dbpassword ." ";
                $command.= $dbname;
                $command.= " > " . $dumpfname;
                system($command);
                // compress sql file and unlink (delete) sql file after creating zip file.
                $zipfname = $dbname . "_" . date("Y-m-d_H-i-s").".zip";
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressing Database Dump File \033[41m".$dumpfname."\033[0m \n";
                $zip = new ZipArchive();
                if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
                        $zip->addFile($dumpfname,$dumpfname);
                        $zip->close();
                        unlink($dumpfname);
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressed Database Dump File \033[41m".$zipfname."\033[0m \n";
                }

        }
	// Rename the controller_relays table to relays
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Renaming Tables and modifying foreign keys.  \n";

	$query = "ALTER TABLE `temperature_sensors` DROP FOREIGN KEY IF EXISTS `FK_temperature_sensors_nodes`;";
        $conn->query($query);
	$query = "ALTER TABLE `controller_relays` DROP FOREIGN KEY IF EXISTS `FK_temperature_controller_relays`;";
        $conn->query($query);
	$query = "ALTER TABLE `zone_sensors` CHANGE COLUMN `temperature_sensor_id` `zone_sensor_id` int(11);";
        $conn->query($query);
	$query = "RENAME TABLE `temperature_sensors` TO `sensors`;";
        $conn->query($query);
	$query = "RENAME TABLE `controller_relays` TO `relays`;";
        $conn->query($query);
	$query = "ALTER TABLE `zone_sensors` DROP FOREIGN KEY IF EXISTS `FK_zone_sensors_temperature_sensors`;";
        $conn->query($query);
	$query = "ALTER TABLE `relays` ADD CONSTRAINT `FK_relays_nodes` FOREIGN KEY (`controler_id`) REFERENCES `nodes` (`id`);";
        $conn->query($query);
	$query = "ALTER TABLE `sensors` ADD CONSTRAINT `FK_sensors_nodes` FOREIGN KEY (`sensor_id`) REFERENCES `nodes` (`id`);";
        $conn->query($query);
	$query = "ALTER TABLE `zone_sensors` ADD CONSTRAINT `FK_zone_sensors_nodes` FOREIGN KEY (`zone_sensor_id`) REFERENCES `sensors` (`id`);";
        $conn->query($query);

	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Tables Successfully Modified\n";

 	//Apply the Migration Views file
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing Migration SQL View File to Database, This could take few minuts.  \n";
	// Name of the file
	$migratefilename = __DIR__.'/migrate_views.sql';
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$migratetempline = '';
	// Read in entire file
	$migratelines = file($migratefilename);
	// Loop through each line
	foreach ($migratelines as $migrateline){
	// Skip it if it's a comment
		if (substr($migrateline, 0, 2) == '--' || $migrateline == '')
			continue;
			// Add this line to the current segment
			$migratetempline .= $migrateline;
			// If it has a semicolon at the end, it's the end of the query
			if (substr(trim($migrateline), -1, 1) == ';'){
				// Perform the query
				$conn->query($migratetempline) or print("MySQL Database Error with Query ".$migratetempline.":". mysqli_error($conn)."\n");
				//mysqli_query($migratetempline) or print("MySQL Database Error with Query ".$migratetempline.":". mysqli_error($conn)."\n");
				// Reset temp variable to empty
				$migratetempline = '';
			}
		}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Views File \033[41m".$migratefilename."\033[0m Imported Successfully \n";

	//Update Version and build number 
	$query = "UPDATE system SET version = '{$version}', build = '{$build}' LIMIT 1;";
	$conn->query($query);

	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Updated Successfully \n";
}
if(isset($conn)) { $conn->close(); }
?>
