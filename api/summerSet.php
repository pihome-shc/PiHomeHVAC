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

$query = "SELECT status FROM summer LIMIT 1;";
$results = $conn->query($query);
$row = mysqli_fetch_assoc($results);
if(! $row) {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "No record found."));
} else {
        if(isset($_GET['state'])) {
                switch ($_GET['state']) {
                        case 'true':
                                $summer_status = 0;
                                break;
                        case 'false':
                                $summer_status = 1;
                                break;
                        case '1':
                                $summer_status = 0;
                                break;
                        case '0':
                                $summer_status = 1;
                                break;
                        default:
                                http_response_code(400);
                                echo json_encode(array("success" => False, "state" => "'state' parameter not correctly set."));
                                $summer_status = -1;
                }
                if($summer_status == 0 or $summer_status == 1) {
                        $query = "UPDATE summer SET status = '{$summer_status}';";
                        if($conn->query($query)){
                                http_response_code(200);
                                // Translate the native DB value back to the RTI-facing boolean.
                                if($summer_status == 0) {$rti_state = True;} else {$rti_state = False;}
                                echo json_encode(array("success" => True, "state" => $rti_state));
                        } else {
                                http_response_code(400);
                                echo json_encode(array("success" => False, "state" => "Update database error."));
                        }
                }
        } else {
                http_response_code(200);
                if($row['status'] == 0) {$summer_status = True; $on_off = 'on';} else {$summer_status = False; $on_off = 'off';}
                echo json_encode(array("success" => True, "state" => $summer_status, "state_str" => $on_off));
        }
}
?>
