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

$modes = array("off", "timer", "ce", "hw", "both");

$query = "SELECT sc_mode FROM system_controller LIMIT 1;";
$results = $conn->query($query);
$row = mysqli_fetch_assoc($results);
if(isset($_GET['mode'])) {
        if(isset($_GET['mode'])) {
        	$mode = $_GET['mode'];
		if(! $row) {
	        	http_response_code(400);
        		echo json_encode(array("success" => False, "state" => "No record found."));
		} else {
			if (($mode >= 0 && $mode <= 4) || in_array(strtolower($mode), $modes)) {
				if (!is_numeric($mode)) {
					$offset = array_search($mode, $modes);
				} else {
					$offset = $mode;
				}
	        		$query = "UPDATE system_controller SET sc_mode = '{$offset}';";
		        	$conn->query($query);
        			if($conn->query($query)){
                			http_response_code(200);
	                		echo json_encode(array("success" => True, "mode" => $mode));
		        	} else {
        		        	http_response_code(400);
                			echo json_encode(array("success" => False, "mode" => "Update database error."));
		        	}
			} else {
                                http_response_code(400);
                                echo json_encode(array("success" => False, "mode" => "'mode' parameter not correctly set."));
                                $mode = -1;
			}
		}
	}
} else {
        http_response_code(200);
        echo json_encode(array("success" => True, "mode" => strtoupper($modes[$row['sc_mode']])));
}
?>

