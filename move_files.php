<?php
/*
             __  __                             _
            |  \/  |                    /\     (_)
            | \  / |   __ _  __  __    /  \     _   _ __
            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
            | |  | | | (_| |  >  <   / ____ \  | | | |
            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
                    S M A R T   T H E R M O S T A T
*************************************************************************"
* MaxAir is a Linux based Central Heating Control systems. It runs from *"
* a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/

// This script is used to move files in the code_updates directory to their propper locations

function moveFolderFiles($dir){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

    foreach($ffs as $ff){
        // Move everything in the directory except the placeholder file
        if (strcmp($ff, 'updates.txt') !== 0) {
                // Check if db_config.ini has been updated and if so update the version and build values in the 'system' table
                if (strcmp($ff, 'db_config.ini') === 0) {
                        $settings = parse_ini_file($dir.'/db_config.ini');
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
                        mysqli_select_db($conn, $dbname) or die('Error Selecting MySQL Database: ' . mysqli_error($conn));
                        $query = "UPDATE `system` SET `version`='".$version."',`build`='".$build."';";
                        $results = $conn->query($query);
                        if ($results) {
                                echo  "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Update \033[41mSystem\033[0m Data  Succeeded \n";
                        } else {
                                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DataBase Update \033[41mSystem\033[0m Data Failed \n";
                        }
                }
                // Move updated files propper locations
                if (strlen($dir) > 21) { $cmd = 'cp -R '.$dir.' /var/www'; } else { $cmd = 'cp '.$dir.'/'.$ff.' /var/www'; }
                exec($cmd);
        }
        if(is_dir($dir.'/'.$ff)) moveFolderFiles($dir.'/'.$ff);
    }
}

// directory used to hold downloaded copies of files last updated
$update_dir = __DIR__.'/code_updates';

// copy all updated files to propper locations
moveFolderFiles($update_dir);

// tidy up the update directory and kill any long running scripts that have been updated, they should auto-restart
$ffs = scandir($update_dir);

unset($ffs[array_search('.', $ffs, true)]);
unset($ffs[array_search('..', $ffs, true)]);

$gpio_ds18b20_found = 0;
$gateway_found = 0;
$jobs_schedule_found = 0;
// prevent empty ordered elements
if (count($ffs) > 0) {
        foreach($ffs as $ff){
                if (strcmp($ff, 'updates.txt') !== 0) {
                        if (strcmp($ff, 'gateway.py') === 0) { $gateway_found = 1; }
                        if (strcmp($ff, 'gpio_ds18b20.py') === 0) { $gpio_ds18b20_found = 1; }
                        if (strcmp($ff, 'jobs_schedule.py') === 0) { $jobs_schedule_found = 1; }
                        if (is_dir($ff)) { $cmd = 'rm -R '.$update_dir.'/'.$ff; } else { $cmd = 'rm '.$update_dir.'/'.$ff; }
                        exec($cmd);
                }
        }
        if ($gateway_found == 1) {
                $script_txt = 'python3 /var/www/cron/gateway.py';
                //Check if Porocess is running and get its PID
                exec("ps aux | grep '$script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
                if (count($out) > 0) {
                        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Gateway PID is: \033[41m".$out[0]."\033[0m \n";
                        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Stopping Python Gateway Script \n";
                        //Kill Gateway Python Scrip PID
                        exec("kill -9 '$out[0]'");
                } else {
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway is DISABLED \n";
                }
        }
        if ($gpio_ds18b20_found == 1) {
                $script_txt = 'python3 /var/www/cron/gpio_ds18b20.py';
                //Check if Porocess is running and get its PID
                exec("ps aux | grep '$script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
                if (count($out) > 0) {
                        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - DS18b20 PID is: \033[41m".$out[0]."\033[0m \n";
                        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Stopping Python DS18b20 Script \n";
                        //Kill DS18b20 Python Scrip PID
                        exec("kill -9 '$out[0]'");
                } else {
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - DS18b20 is DISABLED \n";
                }
        }
        if ($jobs_schedule_found == 1) {
                $script_txt = 'python3 /var/www/cron/jobs_schedule.py';
                //Check if Porocess is running and get its PID
                exec("ps aux | grep '$script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
                if (count($out) > 0) {
                        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Jobs Schedule PID is: \033[41m".$out[0]."\033[0m \n";
                        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - Stopping Python Jobs Schedule Script \n";
                        //Kill DS18b20 Python Scrip PID
                        exec("kill -9 '$out[0]'");
                } else {
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Jobs Schedule is DISABLED \n";
                }
        }
}
?>
