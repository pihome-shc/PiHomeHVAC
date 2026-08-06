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
http://192.168.1.2/api/getZoneRelayState?zonename=Downstairs

{{"success":true,"relays":[{"zone_name":"Downstairs","relay_name":"Downstairs","state":"0"}]}
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
        $query = "SELECT `r`.`name` AS `relay_name`, IF(`r`.`state` = 0,'OFF','ON') AS `state`, `z`.`name` AS `zone_name`
		FROM `zone_relays`
		JOIN `zone` `z` ON `z`.`id` = `zone_relays`.`zone_id`
		JOIN `relays` `r` ON `r`.`id` = `zone_relays`.`zone_relay_id`
		WHERE `z`.`name` = '{$zonename}';";
        $results = $conn->query($query);
	$rowcount=mysqli_num_rows($results);
	if ($rowcount == 0) {
                http_response_code(400);
                echo json_encode(array("success" => False, "state" => "No Zone with that name found OR No Relays Associate."));
        } else {
		$relays = array();
		while($row = mysqli_fetch_assoc($results)) {
			$relays[] = array(
                        "zone_name"        => $row['zone_name'],
                        "relay_name"        => $row['relay_name'],
                        "state" => $row['state']
                	);
                };
                http_response_code(200);
                echo json_encode(array("success" => True, "relays" => $relays));
	}
} else {
        $query = "SELECT `r`.`name` AS `relay_name`, IF(`r`.`state` = 0,'OFF','ON') AS `state`, `z`.`name` AS `zone_name`
                FROM `zone_relays`
                JOIN `zone` `z` ON `z`.`id` = `zone_relays`.`zone_id`
                JOIN `relays` `r` ON `r`.`id` = `zone_relays`.`zone_relay_id`;";
        $results = $conn->query($query);
        $rowcount=mysqli_num_rows($results);
        if ($rowcount == 0) {
                http_response_code(400);
                echo json_encode(array("success" => False, "state" => "No Zones OR No Relays Found."));
        } else {
                $relays = array();
                while($row = mysqli_fetch_assoc($results)) {
                        $relays[] = array(
                        "zone_name"        => $row['zone_name'],
                        "relay_name"        => $row['relay_name'],
                        "state" => $row['state']
                        );
                };
                http_response_code(200);
                echo json_encode(array("success" => True, "relays" => $relays));
        }
}
?>
