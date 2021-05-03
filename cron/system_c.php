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
echo "********************************************************\n";
echo "* System Temperature Version 0.4 Build Date 31/03/2018 *\n";
echo "* Update on 07/02/2021                                 *\n";
echo "*                                 Have Fun - PiHome.eu *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

$date_time = date('Y-m-d H:i:s');
$output = shell_exec("uname -a");
if (strpos($output, 'orangepi') !== false || strpos($output, 'pineh64') !== false) {
        $system_c = (exec ("cat /sys/class/thermal/thermal_zone0/temp|cut -c1-2"));
} elseif(strpos($output, 'beaglebone') !== false) {
        $id = (exec ("ls /sys/bus/w1/devices/ | grep  28-"));
        $system_c = (exec ("cat /sys/bus/w1/devices/".$id."/w1_slave | grep t= | cut -c30,31,32"))/10;
} else {
        $system_c = exec ("vcgencmd measure_temp | cut -c6,7,8,9");
}
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Temperature: ". $system_c."\n";

if ($system_c == 0) {
	//do nothing
}else {
	$query = "INSERT INTO messages_in (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `payload`, `datetime`) VALUES ('0', '0', '0', '0','0', '{$system_c}', '{$date_time}')";
	$conn->query($query);
}
if(isset($conn)) { $conn->close();} 
?>
