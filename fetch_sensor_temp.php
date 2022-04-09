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

// This script is used to provide dynamic update of the control temperature displayed by the live temperature model
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

$id = $_GET['id'];
$query="SELECT sensor_id, sensor_child_id, sensor_type_id FROM sensors WHERE id = {$id} LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$id = $row['sensor_id'];
$child_id = $row['sensor_child_id'];
$sensor_type_id = $row['sensor_type_id'];
$query="SELECT node_id FROM nodes WHERE id  = {$id} LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$node_id = $row['node_id'];
// get the latest sensor temperature reading
$query="SELECT payload FROM messages_in where node_id = '".$node_id."' AND child_id = ".$child_id." ORDER BY id DESC LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$sensor_c = $row['payload'];
$unit = SensorUnits($conn,$sensor_type_id);
//echo number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit;
echo '&nbsp&nbsp<i class="ionicons ion-thermometer red"></i> - '.number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit;
?>
