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

$timestamp = date("Y-m-d H:i:s", time());
$query = "SELECT status FROM away LIMIT 1;";
$results = $conn->query($query);
$row = mysqli_fetch_assoc($results);
if(! $row) {
        http_response_code(400);
        echo json_encode(array("success" => False, "state" => "No record found."));
} else {
        if(isset($_GET['state'])) {
                switch ($_GET['state']) {
                        case 'true':
                                $away_status = 1;
                                break;
                        case 'false':
                                $away_status = 0;
                                break;
                        case '1':
                                $away_status = 1;
                                break;
                        case '0':
                                $away_status = 0;
                                break;
                        default:
                                http_response_code(400);
                                echo json_encode(array("success" => False, "state" => "'state' parameter not correctly set."));
                                $away_status = -1;
                }
                if($away_status == 0 or $away_status == 1) {
                        if($away_status == 0) {
                                $query = "UPDATE away SET status = '{$away_status}', end_datetime = '{$timestamp}';";
                        } else {
                                $query = "UPDATE away SET status = '{$away_status}';";
                        }
                        $conn->query($query);
                        if($conn->query($query)){
                                http_response_code(200);
                                if($away_status == 1) {$away_status = True;} else {$away_status = False;}
                                echo json_encode(array("success" => True, "state" => $away_status));
                        } else {
                                http_response_code(400);
                                echo json_encode(array("success" => False, "state" => "Update database error."));
                        }
                }
        } else {
                http_response_code(200);
                if($row['status'] == 1) {$away_status = True; $on_off = 'on';} else {$away_status = False; $on_off = 'off';}
                echo json_encode(array("success" => True, "state" => $away_status, "state_str" => $on_off));
        }
}
?>

