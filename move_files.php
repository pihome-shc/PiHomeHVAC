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
                $cmd = 'cp -r '.$dir.'/'.$ff.' /var/www && rm -R '.$dir.'/'.$ff;
                exec($cmd);
        }
        if(is_dir($dir.'/'.$ff)) moveFolderFiles($dir.'/'.$ff);
    }
}

moveFolderFiles('/var/www/code_updates');
?>
