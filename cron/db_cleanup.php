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
echo "*************************************************************\n";
echo "* Database Cleanup Script Version 0.1 Build Date 13/05/2018 *\n";
echo "* Update on 10/04/218                                       *\n";
echo "*                                      Have Fun - PiHome.eu *\n";
echo "*************************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

//Create a script running flag file
$running_flag = "/tmp/db_cleanup_running";
fopen($running_flag, 'w');
//Set php script execution time in seconds
ini_set('max_execution_time', 300); 
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Cleanup Script Started \n"; 

//Get the delete intervals
$query = "SELECT * FROM db_cleanup LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$interval_1 = $row['messages_in'];
$interval_2 = $row['nodes_battery'];
$interval_3 = $row['gateway_logs'];
$interval_4 = $row['relay_logs'];

//Delete Temperature Reocrds older then 3 Days.
$query = "DELETE FROM messages_in WHERE datetime < DATE_SUB(curdate(), INTERVAL ".$interval_1.");";
$result = $conn->query($query);
if (isset($result)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Records Deleted from Tables \n"; 
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature Records Delete from Tables Failed\n";
	echo mysql_error()."\n";
}

//Delete Node Battery status older then 3 months. 
$query = "DELETE FROM nodes_battery WHERE `update` < DATE_SUB(CURDATE(), INTERVAL ".$interval_2.");";
$result = $conn->query($query);
if (isset($result)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Node Battery Records Deleted from Tables \n"; 
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Node Battery Records Delete from Tables Failed\n";
	echo mysql_error()."\n";
}

//Delete Orphaned Node Battery.
$query = "DELETE FROM nodes_battery WHERE node_id NOT IN
	(SELECT nodes.node_id  FROM nodes UNION SELECT CONCAT(nodes.node_id,'-',mqtt_devices.child_id) AS node_id FROM mqtt_devices, nodes WHERE mqtt_devices.nodes_id = nodes.id);";
$result = $conn->query($query);
if (isset($result)) {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Orphaned Node Battery Records Deleted from Tables \n";
}else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Node Battery Records Delete Orphaned Records from Tables Failed\n";
        echo mysql_error()."\n";
}

//Delete Gateway Logs data older then 3 days. 
$query = "DELETE FROM `gateway_logs`
	WHERE pid_datetime < DATE_SUB(CURDATE(), INTERVAL ".$interval_3.") AND id != (
  		SELECT id
  		FROM (
    			SELECT id
    			FROM `gateway_logs`
    			ORDER BY id DESC
    			LIMIT 1
  		) myselect
	);";
$result = $conn->query($query);
if (isset($result)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Logs Records Deleted from Tables \n"; 
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Gateway Logs Records Delete from Tables Failed\n";
	echo mysql_error()."\n";
}

//Delete Relay Logs data older then 3 days. 
$query = "DELETE FROM relay_logs WHERE datetime < DATE_SUB(curdate(), INTERVAL ".$interval_4.");";
$result = $conn->query($query);
if (isset($result)) {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Relay Logs Records Deleted from Tables \n"; 
}else {
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Relay Logs Records Delete from Tables Failed\n";
	echo mysql_error()."\n";
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Database Cleanup Script Ended \n"; 
echo "\033[32m**************************************************************\033[0m  \n";

//Delete the script running flag file
unlink($running_flag);
if(isset($conn)) { $conn->close();}
?>
