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
echo "**********************************************************\n";
echo "*       EMS Script Version 0.1 Build Date 05/02/2026     *\n";
echo "*          Last Modification Date 05/02/2026             *\n";
echo "*                                Have Fun - PiHome.eu    *\n";
echo "**********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php'); 

//Set php script execution time in seconds
ini_set('max_execution_time', 60); 
$date_time = date('Y-m-d H:i:s');
$ems_script_txt = 'python3 /var/www/cron/ems/rc10_control.py';
$line = "--------------------------------------------------------------------------\n";

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python EMS Script Status Check Script Started \n"; 

// Checking if RC10 Control script is running
exec("ps ax | grep '$ems_script_txt' | grep -v grep", $pids);
$nopids = count($pids);
if($nopids==0) { // Script not running
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python  EMS Script \033[41mNot Running\033[0m \n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Starting Python Script for EMS \n";
	exec("$ems_script_txt </dev/null >/dev/null 2>&1 & ");
	exec("ps aux | grep '$ems_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
	echo "\033[36m".date('Y-m-d H:i:s')."\033[0m - The PID is: \033[41m".$out[0]."\033[0m \n";
} else {
	if($nopids>1) { // Proceed if more than one EMS script running
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple EMS Scripts are Detected \033[41m$nopids\033[0m \n";
		$regex = preg_quote($ems_script_txt, '/');
		exec("ps -eo s,pid,cmd | grep 'T.*$regex' | grep -v grep | awk '{ print $2 }'", $tpids);
		$notpids=count($tpids);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Number of Terminated Script Killed \033[41m$notpids\033[0m \n";
		foreach($tpids as $tpid){
			exec("kill -9 $tpid 2> /dev/null"); // Kill all EMS script ghost processes (in stat "T"(Terminated)). Common occurrence after running script in terminal and terminating by Ctrl+z
		}
		if($nopids-$notpids>1 || $nopids-$notpids==0) { // Proceed if none or more than one script runs
			if($nopids-$notpids>1) { // Proceed if more than one active EMS script 
				exec("ps -eo s,pid,cmd | grep '$ems_script_txt' | grep -v grep | awk '{ print $2 }'", $tpids);
				$notpids=$nopids-$notpids;
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Multiple Active EMS Script are Running \033[41m$notpids\033[0m \n";
				foreach($tpids as $tpid){
					exec("kill -9 $tpid 2> /dev/null"); // Kill all EMS scripts
				}
			}
			echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - All Script Killed. Started New \n";
			exec("$ems_script_txt </dev/null >/dev/null 2>&1 & ");
			exec("ps aux | grep '$ems_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
		}
	}
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python EMS Script is \033[0;32;40mRunning\033[0m \n";
        exec("ps -eo s,pid,cmd | grep '$ems_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $out);
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - The PID is: \033[0;32;40m" . $out[0]."\033[0m \n";
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python EMS Script Status Check Script Ended \n"; 
echo "\033[32m***************************************************************************\033[0m";
echo "\n";
?>
