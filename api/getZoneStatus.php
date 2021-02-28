<?php
/*
-- ------------------------------------------------------------------------
--     __  __                             _
--    |  \/  |                    /\     (_)
--    | \  / |   __ _  __  __    /  \     _   _ __
--    | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
--    | |  | | | (_| |  >  <   / ____ \  | | | |
--    |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
--
--          S M A R T   T H E R M O S T A T
--
-- *************************************************************************
-- * MaxAir is a Linux based Central Heating Control systems. It runs from *
-- * a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *
-- * extent permitted by applicable law. I take no responsibility for any  *
-- * loss or damage to you or your property.                               *
-- * DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *
-- * WHAT YOU ARE DOING                                                    *
-- *************************************************************************

getZoneStatus.php
When called returns the current status of the Zone passed as parameter.

E.g.
http://192.168.1.2/api/getZoneStatus?zonename=Living%20Room

{"success":true,"status":"0","temp":"18.3","datetime":"2021-02-28 20:53:39","bat_voltage":"2.50","bat_level":"43.00"}
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

if(isset($_GET['zonename'])) {
        $zonename = $_GET['zonename'];
        $query = "SELECT * FROM zone_view where name = '{$zonename}' LIMIT 1;";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
        if(! $row) {
                http_response_code(400);
                echo json_encode(array("success" => False, "state" => "No Zone with that name found."));
        } else {
			$zone_id=$row['id'];
        	$zone_sensor_id=$row['sensors_id'];

	        //query to get temperature from messages_in_view_24h table view
        	$query = "SELECT * FROM zone_current_state WHERE id = '{$zone_id}' ORDER BY id desc LIMIT 1;";
	        $result = $conn->query($query);
	        $zone = mysqli_fetch_array($result);
        	if(! $zone) {
                	http_response_code(400);
                	echo json_encode(array("success" => False, "state" => "Zone with this ID."));
        	} else {
				$zone_status = $zone['status'];
        		$zone_temp = $zone['temp_reading'];
				$zone_temp_time = $zone['sensor_reading_time'];

				//query to get battery info from nodes_battery table
				$query = "SELECT * FROM nodes_battery WHERE node_id = '{$zone_sensor_id}' ORDER BY id desc LIMIT 1;";
				$result = $conn->query($query);
				$node = mysqli_fetch_array($result);
				if(! $node) {
                	http_response_code(200);
        			echo json_encode(array("success" => True, "status" => $zone_status, "temp" => $zone_temp, "datetime" => $zone_temp_time));
				} else {
					$zone_bat_voltage = $node['bat_voltage'];
					$zone_bat_level = $node['bat_level'];
        			http_response_code(200);
        			echo json_encode(array("success" => True, "status" => $zone_status, "temp" => $zone_temp, "datetime" => $zone_temp_time, "bat_voltage" => $zone_bat_voltage, "bat_level" => $zone_bat_level));
				}
			}
	}
} else {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "Data is incomplete."));
}
?>

