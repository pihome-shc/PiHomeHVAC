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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

if(isset($_GET['sensorname'])) {
        $sensorname = $_GET['sensorname'];
        $query = "SELECT sensor_id, sensor_child_id FROM sensors where name = '{$sensorname}' LIMIT 1;";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
        if(! $row) {
                http_response_code(400);
                echo json_encode(array("success" => False, "state" => "No Sensor with that name found."));
                exit();
        } else {
                $sensor_id=$row['sensor_id'];
                $child_id=$row['sensor_child_id'];

                //get the node_id for this sensor
                $query = "SELECT * FROM nodes WHERE id = ".$sensor_id." AND status IS NOT NULL LIMIT 1;";
                $result = $conn->query($query);
                $nodes = mysqli_fetch_assoc($result);
                if(! $nodes) {
                        http_response_code(400);
                        echo json_encode(array("success" => False, "state" => "No Matching Node found for this Sensor."));
                        exit();
                } else {
                        //query to get temperature from messages_in_view_24h table view
                        $node_id=$nodes['node_id'];
                }
        }
} else {
        $query = "SELECT `weather_sensor_id`, `n`.`node_id`, `s`.`sensor_child_id`
                FROM `system_controller` `sc`
                JOIN `sensors` `s` ON `s`.`id` = `sc`.`weather_sensor_id`
                JOIN `nodes` `n` ON `n`.`id` = `s`.`sensor_id`
                LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_assoc($result);
        if(! $row) {
                $node_id = "1";
                $child_id = 0;
        } else {
                $node_id = $row['node_id'];
                $child_id = $row['sensor_child_id'];
        }
}
$query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$node_id}' AND child_id = {$child_id} LIMIT 1;";
$result = $conn->query($query);
$sensor = mysqli_fetch_array($result);
if(! $sensor) {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "Sensor has not reported in the last 24 hours."));
} else {
        $sensor_temp = $sensor['payload'];
        $sensor_time = $sensor['datetime'];

        $query = "SELECT ROUND(MIN(payload), 2) AS min_temp, ROUND(MAX(payload), 2) AS max_temp
                FROM messages_in m
                WHERE node_id = '{$node_id}' AND child_id = {$child_id} AND DATE(m.datetime) = CURDATE() LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $min = $row['min_temp'];
        $max = $row['max_temp'];
        $query = "SELECT payload AS temp_1hour
                FROM messages_in
                WHERE node_id = '{$node_id}' AND child_id = {$child_id} AND datetime >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
                ORDER BY id ASC LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $temp_1hour = $row['temp_1hour'];
        $query = "SELECT payload AS temp_now
                FROM messages_in
                WHERE node_id = '{$node_id}' AND child_id = {$child_id} AND datetime >= DATE_SUB(NOW(),INTERVAL 1 HOUR)
                ORDER BY id DESC LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $temp_now = $row['temp_now'];
        $delta = round($temp_now - $temp_1hour, 2);
        http_response_code(200);
        echo json_encode(array("success" => True, "temp_actual" => $sensor_temp, "temp_datetime" => $sensor_time, "min" => $min, "max" => $max, "delta" => $delta));
}
?>
