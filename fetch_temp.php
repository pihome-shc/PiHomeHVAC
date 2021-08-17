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

// get the sensor details for the heating zone
$query="select sensors_id, sensor_child_id from zone_view where type LIKE 'Heating' LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$id = $row['sensors_id'];
$child_id = $row['sensor_child_id'];
// get the latest sensor temperature reading
$query="SELECT payload FROM messages_in where node_id = '".$id."' AND child_id = ".$child_id." ORDER BY id DESC LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
// return the temperature string to 1 decimal place
echo "&nbsp".number_format((float)$row['payload'], 1, '.', '')."&deg";
?>
