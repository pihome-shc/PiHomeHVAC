<?php
#!/usr/bin/php
$line =  "---------------------------------------------------------------------------------------- \n";
echo "\033[36m";
echo "\n";
echo "                 __  __                             _         \n";
echo "                |  \/  |                    /\     (_)        \n";
echo "                | \  / |   __ _  __  __    /  \     _   _ __  \n";
echo "                | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| \n";
echo "                | |  | | | (_| |  >  <   / ____ \  | | | |    \n";
echo "                |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    \n";
echo " \033[0m \n";
echo "                      \033[45m S M A R T   T H E R M O S T A T \033[0m \n";
echo "\033[31m";
echo "***********************************************************************\n";
echo "*   MaxAir Database Update Script Version 0.01 Build Date 20/12/2020   *\n";
echo "*   Last Modified on 24/01/2022                                        *\n";
echo "*                                               Have Fun - PiHome.eu   *\n";
echo "************************************************************************\n";
echo "\033[0m";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Database Update Script Started \n";
echo $line;

//Set php script execution time in seconds
ini_set('max_execution_time', 400);
$date_time = date('Y-m-d H:i:s');

$settings = parse_ini_file('/var/www/st_inc/db_config.ini');
foreach ($settings as $key => $setting) {
    // Notice the double $$, this tells php to create a variable with the same name as key
    $$key = $setting;
}

//Get dbname for commandline ot else will be the default of 'maxair'
if(isset($argv[1])) { $dbname = $argv[1]; }

echo "\033[32mMake Sure you have correct MySQL/MariaDB credentials as following \033[0m\n";
echo "Hostname:     ".$hostname."\n";
echo "Database:     ".$dbname."\n";
echo "User Name:    ".$dbusername."\n";
echo "Password:     ".$dbpassword."\n";

//Test Connection to MySQL Server with Given Username & Password
echo "\n\033[36m".date('Y-m-d H:i:s'). "\033[0m - Testing Connection to MySQL/MariaDB Server. \n";
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
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Not Found \n";
}else {
        // Select database
        mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
	// Check for database updates
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Check for Database Updates.  \n";
	$update_dir = '/var/www/MySQL_Database/database_updates';
	$ffs = scan_db_update_dir($update_dir);
	if ($ffs) {
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Updates Found. \n";
		$count = 0;
	        foreach($ffs as $ff){
			$query = "SELECT name FROM database_backup WHERE name = '{$ff}' AND backup_name = '{$ff}';";
			$results = $conn->query($query);
			$rowcount=mysqli_num_rows($results);
			if ($rowcount == 0) {
				$count++;
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
		                $updatefilename = '/var/www/MySQL_Database/database_updates/'.$ff;
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
                                                try {
                                                        // Perform the query
                                                        $conn->query($updatetempline) or print("MySQL Database Error with Query ".$updatetempline.":". mysqli_error($conn)."\n");
                                                        //mysqli_query($updatetempline) or print("MySQL Database Error with Query ".$updatetempline.":". mysqli_error($conn)."\n");
                                                        // Reset temp variable to empty
                                                        $update_applied = 1;
                                                }
                                                catch(Exception $e) {
                                                        $update_applied = 0;
                                                }
                                                $updatetempline = '';
                                        }
                                }
                                if ($update_applied == 1) {
                                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Update File \033[41m".$updatefilename."\033[0m Applied. \n";
                                } else {
                                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Update File \033[44m".$updatefilename."\033[0m NOT Applied. \n";
                                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Message: " .$e->getMessage()."\033[0m \n";
                                }
			}
		}
		if ($count == 0) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - NO Database Updates To Apply. \n"; }
	}
}
echo $line;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - MaxAir Database Update Script Ended \n";
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
?>
