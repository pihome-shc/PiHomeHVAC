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
echo "*   MaxAir Restore Database Script Version 0.01               *\n";
echo "*   Build Date 21/06/2022                                     *\n";
echo "*   Last Modified on 21/06/2022                               *\n";
echo "*                                      Have Fun - PiHome.eu   *\n";
echo "***************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Restore Script Started \n";
$line = "--------------------------------------------------------------- \n";

//Set php script execution time in seconds
ini_set('max_execution_time', 400);
$date_time = date('Y-m-d H:i:s');

if(isset($argv[1])) {
	$fileName = $argv[1];
	//Check php version before doing anything else
	$version = explode('.', PHP_VERSION);
	if ($version[0] > 8){
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

	$db_selected = mysqli_select_db($conn, $dbname);

        //stop the Job scheduler
	$output = shell_exec('sudo systemctl stop pihome_jobs_schedule.service');
	echo $output;
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Dropping MySQL DataBase \033[41m".$dbname."\033[0m  \n";
	$query = "DROP DATABASE {$dbname};";
	$result = $conn->query($query);
	if ($result) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Dropped Successfully!!! \n";
	} else {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Error Dropping Database \n";
	        mysqli_error($conn). "\n";
	}
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Creating MySQL DataBase \033[41m".$dbname."\033[0m  \n";
	$query = "CREATE DATABASE {$dbname};";
	$result = $conn->query($query);
	if ($result) {
	        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase \033[41m".$dbname."\033[0m Created Successfully!!! \n";
	} else {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Error Creating Database \n";
	        mysqli_error($conn). "\n";
	}

	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase Importing SQL File to Database, This could take few minuts. \n";
	// Select database
	mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Temporary variable, used to store current query
	$templine = '';
	// Read in entire file
        if (strpos($fileName, '.gz') !== false) {
		$lines = gzfile($fileName);
	} else {
		$zip = new ZipArchive;
		$res = $zip->open($fileName);
		if ($res === TRUE) {
			$filename = $zip->getNameIndex(0);
  			$zip->extractTo('./');
  			$zip->close();
		}
	        // Read in entire file
        	$lines = file($filename);
		unlink($filename);
	}
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
	//start the Job scheduler
        $output = shell_exec('sudo systemctl start pihome_jobs_schedule.service');
        echo $output;
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MySQL DataBase File \033[41m".$fileName."\033[0m Imported Successfully \n";
} else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - No MySQL Database Backup File Specified \n";
}
echo "---------------------------------------------------------------------------------------- \n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Restore Database Script Ended \n";
echo "\033[32m****************************************************************************************\033[0m  \n";
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
?>
