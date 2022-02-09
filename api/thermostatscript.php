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

//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
$system_controller_mode = settings($conn, 'mode') & 0b1;

$query = "SELECT sc_mode, sc_mode_prev FROM system_controller LIMIT 1;";
$results = $conn->query($query);
$row = mysqli_fetch_assoc($results);
if(! $row) {
        http_response_code(400);
       	echo json_encode(array("success" => False, "targetstate" => "No record found."));
} else {
        $mode_prev=$row['sc_mode'];
	if(isset($_GET['targetstate'])) {
		$state = intval($_GET['targetstate']);
		if(($system_controller_mode == 0 && $mode_status >= 0 && $mode_status <= 5) || ($system_controller_mode == 1 && $mode_status >= 0 && $mode_status <= 6)) {
                        switch ($state) {
                                case 0:
                                        $mode_status = 0;
                                        break;
                                case 1:
                                        $mode_status = 2;
                                        break;
                                case 2:
                                        $mode_status = 3;
                                        break;
                                case 3:
                                        $mode_status = 1;
                                        break;
                                default:
                                        http_response_code(400);
                                        echo json_encode(array("success" => False, "targetstate" => "'targetstate' parameter not correctly set."));
                                        $boost_status = -1;
                        }
        		$query = "UPDATE system_controller SET sc_mode = '{$mode_status}', sc_mode_prev = '{$mode_prev}' LIMIT 1;";
	        	$conn->query($query);
       			if($conn->query($query)){
               			http_response_code(200);
                		echo json_encode(array("success" => True, "targetstate" => $mode_status));
	        	} else {
       		        	http_response_code(400);
               			echo json_encode(array("success" => False, "targetstate" => "Update database error."));
	        	}
		}
	} elseif(isset($_GET['targettemperature'])) {
		$target_temp = floatval($_GET['targettemperature']);
                $query = "UPDATE livetemp SET active = 1, temperature = '{$target_temp}' LIMIT 1;";
                $conn->query($query);
                if($conn->query($query)){
                	http_response_code(200);
                        echo json_encode(array("success" => True, "targetstate" => $mode_status));
                } else {
                      	http_response_code(400);
                        echo json_encode(array("success" => False, "targetstate" => "Update database error."));
                }
	}
}
?>

