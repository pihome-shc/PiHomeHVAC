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


This script is used to provide dynamic update of data dispayed for the Sensor Last 24hrs display
*/

require_once(__DIR__.'/st_inc/session.php');
require_once(__DIR__.'/st_inc/connection.php');

if(isset($_GET['id'])) {
	$id = $_GET['id'];
	$query = "SELECT * FROM relays WHERE id = '{$id}' LIMIT 1;";
	$result = $conn->query($query);
	$relay_log_Array = Array();
	$rrow = mysqli_fetch_assoc($result);
        if(! $rrow) {
                echo json_encode(array("success" => False, "state" => "No Relay with that id found."));
	} else {
		$query = "SELECT * FROM relay_logs WHERE relay_id = {$id} ORDER BY id DESC;";
	        $lresults = $conn->query($query);
		if (mysqli_num_rows($lresults) == 0) {
			echo json_encode(array("success" => False, "state" => "No Log found for this Relay."));
		} else {
			while ($lrow = mysqli_fetch_assoc($lresults)) {
       				$relay_log_Array[$id][] = $lrow;
        		}
			echo json_encode(array("success" => True, "state" => $relay_log_Array));
		}
	}
} else {
        echo json_encode(array("success" => False, "state" => "Data is incomplete."));
}
?>
