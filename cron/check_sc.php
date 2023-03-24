<?php
#!/usr/bin/php
echo "\033[36m";
echo "\n";
echo "      __  __                             _         \n";
echo "     |  \/  |                    /\     (_)        \n";
echo "     | \  / |   __ _  __  __    /  \     _   _ __  \n";
echo "     | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| \n";
echo "     | |  | | | (_| |  >  <   / ____ \  | | | |    \n";
echo "     |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    \n";
echo " \033[0m \n";
echo "          \033[45m S M A R T   T H E R M O S T A T \033[0m \n";
echo "\033[31m";
echo "********************************************************\n";
echo "* Controller Script Version 0.1 Build Date 13/02/2023  *\n";
echo "*          Last Modification Date 14/02/2023           *\n";
echo "*                                Have Fun - PiHome.eu  *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

//Set php script execution time in seconds
ini_set('max_execution_time', 60); 
$date_time = date('Y-m-d H:i:s');
$sc_script_txt = 'python3 /var/www/cron/controller.py';
$line = "--------------------------------------------------------------------------\n";

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Controller Script Status Check Script Started \n"; 
// Checking if System Controller script is running
exec("ps ax | grep '$sc_script_txt' | grep -v grep", $pids);
$nopids = count($pids);
if($nopids==0) { // Script not running
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python  Controller Script \033[41mNot Running\033[0m \n";
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Python Script for Controller \n";
        exec("$sc_script_txt </dev/null >/dev/null 2>&1 & ");
        exec("ps aux | grep '$sc_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
        echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - The PID is: \033[41m".$out[0]."\033[0m \n";
        $pid_details = exec("ps -p '$out[0]' -o lstart=");
        $query = "UPDATE system_controller SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
        $conn->query($query);
        echo mysqli_error($conn)."\n";
        $query = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`)
                  VALUES (0,0,0,'{$date_time}','Controller Script Started',NULL,'',NULL);";
        $conn->query($query);
        echo mysqli_error($conn)."\n";
} else {
        if($nopids>1) { // Proceed if more than one System Controller script running
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple Controller Scripts are Detected \033[41m$nopids\033[0m \n";
                $regex = preg_quote($sc_script_txt, '/');
                exec("ps -eo s,pid,cmd | grep 'T.*$regex' | grep -v grep | awk '{ print $2 }'", $tpids);
                $notpids=count($tpids);
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Number of Terminated Script Killed \033[41m$notpids\033[0m \n";
                foreach($tpids as $tpid){
                        exec("kill -9 $tpid 2> /dev/null"); // Kill all System Controller script ghost processes (in stat "T"(Terminated)). Common occurrence after running script in terminal and terminating by Ctrl+z
                }
                if($nopids-$notpids>1 || $nopids-$notpids==0) { // Proceed if none or more than one script runs
                        if($nopids-$notpids>1) { // Proceed if more than one active System Controller script
                                exec("ps -eo s,pid,cmd | grep '$sc_script_txt' | grep -v grep | awk '{ print $2 }'", $tpids);
                                $notpids=$nopids-$notpids;
                                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple Active Controller Script are Running \033[41m$notpids\033[0m \n";
                                foreach($tpids as $tpid){
                                        exec("kill -9 $tpid 2> /dev/null"); // Kill all System Controller scripts
                                }
                        }
                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - All Script Killed. Started New \n";
                        exec("$sc_script_txt </dev/null >/dev/null 2>&1 & ");
                        exec("ps aux | grep '$sc_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
                }
        }
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Controller Script is \033[0;32;40mRunning\033[0m \n";
        exec("ps -eo s,pid,cmd | grep '$sc_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The PID is: \033[0;32;40m" . $out[0]."\033[0m \n";
        $pid_details = exec("ps -p '$out[0]' -o lstart=");
        $query = "UPDATE system_controller SET pid = '{$out[0]}', pid_running_since = '{$pid_details}' LIMIT 1";
        $conn->query($query);
        echo mysqli_error($conn)."\n";
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Controller Script Status Check Script Ended \n"; 
echo "\033[32m***************************************************************************\033[0m";
echo "\n";
if(isset($conn)) { $conn->close();}
?>
