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
echo "**********************************************************************\n";
echo "*   MaxAir Database Update Script Version 0.01 Build Date 07/08/2021 *\n";
echo "*   Last Modified on 07/08/2021                                      *\n";
echo "*                                            Have Fun - PiHome.eu    *\n";
echo "**********************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Database Update Script Started \n"; 
$line = "---------------------------------------------------------------------- \n";

require_once(__DIR__.'/../st_inc/functions.php');

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

	//check if database_updates table already exist
	$query = "SELECT * FROM information_schema.tables WHERE table_schema = 'maxair' AND table_name = 'database_updates' LIMIT 1;";
	$result = $conn->query($query);
	$rowcount=mysqli_num_rows($result);
	if ($rowcount == 0) {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - database_updates Table Does Not Exist, Creating it.\n";
		$query = "CREATE TABLE IF NOT EXISTS `database_updates` (
		  `id` int(11) NOT NULL AUTO_INCREMENT,
		  `sync` tinyint(4) NOT NULL,
		  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
		  `status` tinyint(4),
		  `backup_name` char(50) COLLATE utf8_bin DEFAULT NULL,
		  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
		  PRIMARY KEY (`id`)
		) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;";
		if ($conn->query($query)) {
			        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Update Table Created.  \n";
		} else {
                                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Failed to Create Update Table.  \n";
		}
	}

	// Move any .sql files to their correct location
	$update_dir = '/var/www/database_updates';
	if (is_dir($update_dir)) {
	        $ffs = scan_dir($update_dir);
        	if ($ffs) {
			foreach($ffs as $ff){
                		$cmd = 'cp -r '.$update_dir.'/'.$ff.' /var/www/MySQL_Database/database_updates';
	                	exec($cmd);
				// Remove the database update file from the code_updates directory
                                $cmd = 'rm '.$update_dir.'/'.$ff;
                                exec($cmd);
			}
		}
	}

	// Check for updates
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Check for Updates.  \n";
	$update_dir = __DIR__.'/database_updates';
	$ffs = scan_dir($update_dir);
        if ($ffs) {
		$zipfname = '';
                foreach($ffs as $ff){
                	$query = "SELECT * FROM `database_updates` WHERE name = '".$ff."';";
                       	$result = $conn->query($query);
                       	$rowcount=mysqli_num_rows($result);
                       	if ($rowcount == 0) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Un-applied Updates Found. \n";
				if (strlen($zipfname) == 0) {
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
					        $zipfname = "database_backups/".$dbname . "_" . date("Y-m-d_H-i-s").".zip";
				        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressing Database Dump File \033[41m".$dumpfname."\033[0m \n";
					        $zip = new ZipArchive();
					        if($zip->open($zipfname,ZIPARCHIVE::CREATE)){
					        	$zip->addFile($dumpfname,$dumpfname);
					                $zip->close();
					                unlink($dumpfname);
				        	        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Compressed Database Dump File \033[41m".$zipfname."\033[0m \n";
				        	}
					}
				}
				// save the update info to the database_updates table
        	                $query = "INSERT INTO `database_updates`(`sync`, `purge`, `status`, `backup_name`, `name`) VALUES ('0','0','0','".substr($zipfname, strpos($zipfname, "/") + 1)."','".$ff."');";
 	                        if ($conn->query($query)) {
                	        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Un-applied Update Information Added to Table. \n";
                        	} else {
                                	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Failed to Add Update to Table. \n";
                                }
        			// Apply the Update file
        			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Importing Update SQL to Database.  \n";
        			// Name of the file
        			$updatefilename = __DIR__.'/database_updates/'.$ff;
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
                        } else {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Update File \033[41m".$ff."\033[0m Has Alreay Been Applied. \n";
			}
                }
        } else {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - database_updates directory is empty.\n";
	}

 	/* Apply the Migration Views file
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
	$conn->query($query); */

}
if(isset($conn)) { $conn->close(); }
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Updated Script Completed \n";
echo $line;
?>
