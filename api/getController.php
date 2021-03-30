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
getController.php
When called returns the current status of the system controller.
E.g.
http://192.168.1.2/api/getController
{"success":true,"state":"ON"}
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

$query = "SELECT * FROM controller_zone_logs WHERE zone_id = '1' ORDER BY id desc LIMIT 1;";
$results = $conn->query($query);
$row = mysqli_fetch_assoc($results);
if(! $row) {
	http_response_code(400);
	echo json_encode(array("success" => False, "state" => "No data found"));
} else {
	$stop_time=$row['stop_datetime'];
	if($stop_time == "") {
		http_response_code(200);
		echo json_encode(array("success" => TRUE, "state" => "ON"));
	} else {
		http_response_code(200);
		echo json_encode(array("success" => TRUE, "state" => "OFF"));
	}
}
?>
