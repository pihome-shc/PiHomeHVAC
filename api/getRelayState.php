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

if(isset($_GET['relayname'])) {
        $relayname = $_GET['relayname'];
        $query = "SELECT state FROM relays where name = '{$relayname}' LIMIT 1;";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
        if(! $row) {
                http_response_code(400);
                echo json_encode(array("success" => False, "state" => "No Relay with that name found."));
        } else {
                if($row["state"] == "0") { $state = "OFF"; } else { $state = "ON"; } 
                http_response_code(200);
        	echo json_encode(array("success" => True, "state" => $state));
	}
} else {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "Data is incomplete."));
}
?>

