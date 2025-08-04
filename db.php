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

require_once(__DIR__.'/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

$what = $_GET['w'];   # what to do, override, schedule, away etc..
$opp =  $_GET['o'];   # insert, update, delete, ( active only device )
$wid = $_GET['wid'];  # which id

//Set Purge to 1 to mark records for deletion for Zone and all related records
if(($what=="zone") && ($opp=="delete")){

	//Delete Boost Records
	$query = "UPDATE boost SET boost.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);

	//Delete All Message Out records
	$query = "DELETE FROM messages_out WHERE zone_id = '".$wid."'";
	$conn->query($query);

	//Delete Override records
	$query = "UPDATE override SET override.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);

	//Delete Daily Time records
	$query = "UPDATE schedule_daily_time_zone SET schedule_daily_time_zone.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);

	//Delete Night Climat records
	$query = "UPDATE schedule_night_climat_zone SET schedule_night_climat_zone.purge='1' WHERE zone_id = '".$wid."'";
	$conn->query($query);

        //Delete Zone Sensors record
        $query = "UPDATE zone_sensors SET zone_sensors.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

        //Delete Zone Controller record
        $query = "UPDATE zone_relays SET zone_relays.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

        //Mark temperature sensor as un-allocated
        $query = "UPDATE `sensors` SET `zone_id`=0 WHERE `zone_id` = '".$wid."'";
        $conn->query($query);

        //Delete Average Sensors record
        $query = "UPDATE sensor_average SET zone_sensors.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

        //Delete Controller-Zone-Logs record
        $query = "UPDATE controller_zone_logs SET controller_zone_logs.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

        //Delete Add-On-Zone-Logs record
        $query = "UPDATE add_on_logs SET add_on_logs.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

        //Delete livetemp record
        $query = "UPDATE livetemp SET livetemp.purge='1' WHERE zone_id = '".$wid."'";
        $conn->query($query);

	//Delete Zone record
	$query = "UPDATE zone SET zone.purge='1', zone.sync='0' WHERE id = '".$wid."'";
	$conn->query($query);
}

//Holidays
//following variable set to 0 on start for array index.
$sch_time_index = '0';
if($what=="holidays"){
	if($opp=="active"){
		$query = "SELECT * FROM holidays WHERE id ='".$wid."'";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE holidays SET status='".$set."' WHERE id = '".$wid."'";
		$conn->query($query);
	}elseif ($opp=="delete") {
		$query = "SELECT * FROM schedule_daily_time_zone WHERE holidays_id = '".$wid."'";
		$results = $conn->query($query);
        	$hcount = $results->num_rows;
        	if ($hcount == 0) {
			while ($row = mysqli_fetch_assoc($results)) {
				$hid = $row['schedule_daily_time_id'];
				$schedule_time[$sch_time_index] = $hid;
				$sch_time_index = $sch_time_index+1;
			}
			$query = "UPDATE schedule_daily_time_zone SET schedule_daily_time_zone.purge = '1' WHERE holidays_id = '".$wid."';";
			$conn->query($query);
			for ($x = 0; $x <= $sch_time_index; $x++) {
				$query = "UPDATE schedule_daily_time set schedule_daily_time.purge = '1' WHERE id = '".$schedule_time[$x]."';";
				$conn->query($query);
			}
		}
                $query = "DELETE FROM holidays WHERE id = '".$wid."'";
                $conn->query($query);
	}
}

//Users accounts
if(($what=="user") && ($opp=="delete")){
		$query = "DELETE FROM user WHERE id = '".$wid."'";
		$conn->query($query);
}

//Heating Schedule
if($what=="schedule"){
	if($opp=="active"){
		$query = "SELECT * FROM schedule_daily_time WHERE id ='".$wid."'";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query  = "UPDATE schedule_daily_time SET sync = '0', status='".$set."' WHERE id = '".$wid."'";
		$conn->query($query);
	}elseif ($opp=="delete") {
		$query  = "UPDATE schedule_daily_time_zone SET schedule_daily_time_zone.purge = '1', schedule_daily_time_zone.sync = '0' WHERE schedule_daily_time_id = '".$wid."';";
		$conn->query($query);
		$query  = "UPDATE schedule_daily_time SET schedule_daily_time.purge = '1', schedule_daily_time.sync = '0' WHERE id = '".$wid."';";
		$conn->query($query);
	}
}

//update each schedule from model from homelist
if($what=="schedule_zone"){
	if($opp=="active"){
		$query = "SELECT * FROM schedule_daily_time_zone WHERE id ='".$wid."'";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
                if($da=="1"){ $set="0"; $dis="1"; }else{ $set="1"; $dis="0"; }
                $query  = "UPDATE schedule_daily_time_zone SET sync = '0', status='".$set."', disabled = '".$dis."' WHERE id = '".$wid."'";
		$conn->query($query);
	}
}
//Override
if($what=="override"){
	if($opp=="active"){
		//$time = date('H:i:s', time());
		$time = date("Y-m-d H:i:s");
		$query = "SELECT * FROM override WHERE zone_id ='".$wid."'";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query = "UPDATE override SET status = '{$set}', sync = '0', time = '{$time}' WHERE zone_id = '{$wid}' LIMIT 1";
		$conn->query($query);
	}
}
//Boost
if($what=="boost"){
        if($opp=="active"){
                $query = "SELECT * FROM boost WHERE status = '1' limit 1;";
                $result = $conn->query($query);
                $boost_row = mysqli_fetch_assoc($result);
                $boost_status = $boost_row['status'];
                $boost_time = $boost_row['time'];
                if ($boost_status == 1){
                        $time = $boost_time;
                }else {
                        $time = date("Y-m-d H:i:s");
                }
                $query = "SELECT * FROM boost WHERE id ='".$wid."';";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                $boost_status= $row['status'];
                $zone_id=$row['zone_id'];
                if($boost_status=="1"){ $set="0"; }else{ $set="1";}
                $query = "UPDATE boost SET status = '{$set}', sync = '0', time = '{$time}' WHERE id = '{$wid}' LIMIT 1";
                $conn->query($query);
                //this line update message out
                $query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0', sync = '0' WHERE zone_id = '{$zone_id}' AND node_id = {$row['boost_button_id']}
                        AND child_id = ".$row['boost_button_child_id']++." LIMIT 1";
                $conn->query($query);
                $query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0', sync = '0' WHERE zone_id = '{$zone_id}' AND node_id = {$row['boost_button_id']}
                        AND child_id = ".$row['boost_button_child_id']." LIMIT 1";
                $conn->query($query);
                //HVAC mode - only allow 1 active boost, so clear all others
                if ((settings($conn, 'mode') & 0b1) == 1 and $set == "1") {
                        $query = "UPDATE boost SET status = '0', sync = '0' WHERE id <> '{$wid}';";
                        $conn->query($query);
                }
        }
        if($opp=="delete"){
                //get list of Boost console Id and Child ID
                $query = "select * from boost WHERE id = '".$wid."';";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                $boost_button_id= $row['boost_button_id'];
                $boost_button_child_id=$row['boost_button_child_id'];
                //delete from message_out related to this boost.
                $query = "DELETE FROM messages_out WHERE node_id = '".$boost_button_id."' AND child_id = '".$boost_button_child_id++."' LIMIT 1;";
                $conn->query($query);
                $query = "DELETE FROM messages_out WHERE node_id = '".$boost_button_id."' AND child_id = '".$boost_button_child_id."' LIMIT 1;";
                $conn->query($query);
                //Now Mark for deletion from Boost
                $query = "DELETE FROM boost WHERE id = '".$wid."' LIMIT 1;";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="add"){
                $datetime = date("Y-m-d H:i:s");
                if ($wid == 0) { $zone_id = $_GET['zone_id']; } else { $hvac_mode = $_GET['zone_id']; }
                $boost_time = $_GET['boost_time'];
                $boost_temperature = $_GET['boost_temperature'];
                $boost_console_id = $_GET['boost_console_id'];
                $boost_button_child_id = $_GET['boost_button_child_id'];
                $query = "SELECT id FROM nodes WHERE node_id = '".$boost_console_id."' LIMIT 1;";
                $nresult = $conn->query($query);
                $nodes_row = mysqli_fetch_assoc($nresult);
                $n_id = $nodes_row['id'];
                //If boost Console is selected then add to messages_out table.
                if ($boost_console_id != '0'){
                        $query = "INSERT INTO `messages_out`(`sync`, `purge`, `n_id`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`)
                                VALUES ('0', '0', '{$n_id}', '{$boost_console_id}', {$boost_button_child_id}, '1', '0', '2', '0', '0', '{$datetime}', '{$zone_id}')";
                        $conn->query($query);
                        $query = "INSERT INTO `messages_out`(`sync`, `purge`, `n_id`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`)
                                VALUES ('0', '0', '{$n_id}', '{$boost_console_id}', {$boost_button_child_id} + 1, '1', '0', '2', '0', '0', '{$datetime}', '{$zone_id}')";
                        $conn->query($query);
                }
                //Add record to Boost table
                if ($wid == 0) {
                        $query = "INSERT INTO `boost`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`, `hvac_mode`)
                                VALUES ('0', '0', '0', '{$zone_id}', '{$datetime}', '{$boost_temperature}', '{$boost_time}', '{$boost_console_id}', '{$boost_button_child_id}', '0')";
                } else {
                        $query = "SELECT id FROM zone WHERE name = 'HVAC' limit 1;";
                        $result = $conn->query($query);
                        $zone_row = mysqli_fetch_assoc($result);
                        $zone_id = $zone_row['id'];
                        $query = "INSERT INTO `boost`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`, `hvac_mode`)
                                VALUES ('0', '0', '0', '{$zone_id}', '{$datetime}', '{$boost_temperature}', '{$boost_time}', '{$boost_console_id}', '{$boost_button_child_id}',
                                '{$hvac_mode}')";
                }
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="update"){
                $datetime = date("Y-m-d H:i:s");
                if ((settings($conn, 'mode') & 0b1) == 0) {
                        $sel_query = "SELECT boost.id, boost.`status`, boost.sync, boost.zone_id, zone_idx.index_id, zone_type.category, zone.name,
                        boost.temperature, boost.minute, n.id AS n_id, boost_button_id, boost_button_child_id, hvac_mode, ts.sensor_type_id
                        FROM boost
                        JOIN nodes n ON n.node_id = boost.boost_button_id
                        JOIN zone ON boost.zone_id = zone.id
                        JOIN zone zone_idx ON boost.zone_id = zone_idx.id
                        JOIN zone_type ON zone_type.id = zone.type_id
                        JOIN zone_sensors zs ON zone.id = zs.zone_id
                        JOIN sensors ts ON zs.zone_sensor_id = ts.id
                        ORDER BY index_id ASC, minute ASC;";
                } else {
                        $sel_query = "SELECT boost.*, ts.sensor_type_id
                        FROM boost
                        JOIN zone_sensors zs ON boost.zone_id = zs.zone_id
                        JOIN sensors ts ON zs.zone_sensor_id = ts.id
                        ORDER BY hvac_mode ASC;";
                }
                $results = $conn->query($sel_query);
                while ($row = mysqli_fetch_assoc($results)) {
                        $id = $row['id'];
                        $n_id = $row['n_id'];
                        if ((settings($conn, 'mode') & 0b1) == 0) {
                                $input1 = 'minute'.$id;
                                $input2 = 'temperature'.$id;
                                $input3= 'boost_button_id'.$id;
                                $input4 = 'boost_button_child_id'.$id;
                                $input5 = 'id'.$id;
                                $input6 = 'hvac_mode'.$id;
                                $input7 = 'sensor_type'.$id;
                                $minute = $_GET[$input1];
                                $temperature = $_GET[$input2];
                                $boost_button_id = $_GET[$input3];
                                $boost_button_child_id = $_GET[$input4];
                                $zone_id = $_GET[$input5];
                                $hvac_mode = $_GET[$input6];
                                $sensor_type = $_GET[$input7];
                        } else {
                                $input1 = 'minute'.$id;
                                $input2 = 'temperature'.$id;
                                $input3 = 'zone_id'.$id;
                                $input4 = 'hvac_mode'.$id;
                                $input5 = 'sensor_type'.$id;
                                $minute = $_GET[$input1];
                                $temperature = $_GET[$input2];
                                $boost_button_id = 0;
                                $boost_button_child_id = 0;
                                $zone_id = $_GET[$input3];
                                $hvac_mode = $_GET[$input4];
                                $sensor_type = $_GET[$input5];
                        }
                        //Update Boost table
                        $upd_query = "UPDATE boost SET minute = '".$minute."', temperature = '".SensorToDB($conn, $temperature, $sensor_type)."', boost_button_id = '".$boost_button_id."',
                                      boost_button_child_id = '".$boost_button_child_id."', hvac_mode = '".$hvac_mode."' WHERE id='".$row['id']."' LIMIT 1;";
                        $conn->query($upd_query);
                        $update_error=0;
                        if(!$conn->query($upd_query)){
                                $update_error=1;
                        }
                }
                if ((settings($conn, 'mode') & 0b1) == 0 && $update_error == 0) {
                        $query = "SELECT * FROM boost WHERE boost_button_id != 0 ORDER BY id asc;";
                        $results = $conn->query($query);
                        $cnt = 1;
                        while ($row = mysqli_fetch_assoc($results)) {
                                $zone_id = $row['zone_id'];
                                $boost_button_id = $row['boost_button_id'];
                                $update_query = "UPDATE `messages_out` SET `child_id` = ".$cnt.", `sent` = 0 WHERE `node_id` = '".$boost_button_id."' AND `child_id` = ".$cnt.";";
                                if(!$conn->query($update_query)){
                                        $update_error=1;
                                        break;
                                } else {
                                        $cnt = $cnt + 1;
                                        $update_query = "UPDATE `messages_out` SET `child_id` = ".$cnt.", `sent` = 0 WHERE `node_id` = '".$boost_button_id."' AND `child_id` = ".$cnt.";";
                                        if(!$conn->query($update_query)){
                                                $update_error=1;
                                                break;
                                        }
                                }
                                $cnt = $cnt + 1;
                        }
                        if($update_error==0) {
                                $update_query = "UPDATE `messages_out` SET `sent` = 0 WHERE `node_id` = '".$boost_button_id."' AND `child_id` = 7;";
                                if(!$conn->query($update_query)){
                                        $update_error=1;
                                }
                        }
                }
                if($update_error==0){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$upd_query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $upd_query));
                        return;
                }
        }
}

//Offset
if($what=="offset"){
        if($opp=="delete"){
                $query = "DELETE FROM schedule_time_temp_offset WHERE id = '".$wid."' LIMIT 1;";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="add"){
                $schedule_daily_time_id = $_GET['schedule_daily_time_id'];
                $low_temperature = $_GET['low_temperature'];
                $high_temperature = $_GET['high_temperature'];
                $start_time_offset = $_GET['start_time_offset'];
                $sensor_id = $_GET['sensor_id'];
                $status = $_GET['status'];
        	if ($status=='true'){$status = '1';} else {$status = '0';}
		$query = "INSERT INTO `schedule_time_temp_offset`(`sync`, `purge`, `status`, `schedule_daily_time_id`, `low_temperature`, `high_temperature`, `start_time_offset`, `sensors_id`) VALUES (0,0,'{$status}','{$schedule_daily_time_id}','{$low_temperature}','{$high_temperature}','{$start_time_offset}','{$sensor_id}')";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
	}
        if($opp=="update"){
                $sel_query = "SELECT * FROM schedule_time_temp_offset ORDER BY id asc;";
                $results = $conn->query($sel_query);
                while ($row = mysqli_fetch_assoc($results)) {
                        $id = $row['id'];
			$input1 = 'low_temp'.$id;
                        $input2 = 'high_temp'.$id;
                        $input3 = 'offset_id'.$id;
                        $input4 = 'sensors_id'.$id;
                        $input5 = 'sch_id'.$id;
                        $input6 = 'checkbox_offset'.$id;
                        $enabled =  $_GET[$input6];
                        if ($enabled=='true'){$enabled = '1';} else {$enabled = '0';}
			$low_temperature = $_GET[$input1];
                        $high_temperature = $_GET[$input2];
                        $start_time_offset = $_GET[$input3];
                        $sensors_id = $_GET[$input4];
                        $schedule_daily_time_id = $_GET[$input5];
			if ($sensors_id == 0) {
				$sensor_type = 1;
			} else {
                		$query = "SELECT sensor_type_id FROM sensors WHERE id = ".$sensors_id." LIMIT 1;";
                		$sresult = $conn->query($query);
				$srow = mysqli_fetch_assoc($sresult);
				$sensor_type = $srow['sensor_type_id'];
			}
                        $upd_query = "UPDATE schedule_time_temp_offset SET schedule_daily_time_id = '".$schedule_daily_time_id."', status = '".$enabled."', low_temperature = '".SensorToDB($conn, $low_temperature, $sensor_type)."', high_temperature = '".SensorToDB($conn, $high_temperature, $sensor_type)."', start_time_offset = '".$start_time_offset."', sensors_id = '".$sensors_id."' WHERE id='".$row['id']."' LIMIT 1;";
	                if($conn->query($upd_query)){
        	                header('Content-type: application/json');
                	        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        	return;
	                }else{
        	                header('Content-type: application/json');
                	        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        	return;
                	}
		}
	}
}

//Software Install
if($what=="sw_install"){
        if($opp=="add"){
		$restart_schedule = 0;
		$installpath = $wid;
		if (file_exists($installpath)) {
        		$contents = file_get_contents($installpath);
        		$searchfor = 'restart_scheduler';
        		$pattern = preg_quote($searchfor, '/');
        		$pattern = "/^.*$pattern.*\$/m";
        		if(preg_match_all($pattern, $contents, $matches)){
                		$str = implode("\n", $matches[0]);
                		$status = explode(':',$str)[1];
        		}
			if(strpos($status, 'yes') !== false) { $restart_schedule = 1; }
		}
                $query = "INSERT INTO `sw_install` (`script`, `pid`, `start_datetime`, `stop_datetime`, `restart_schedule`) VALUES ('{$installpath}', NULL, NULL, NULL, {$restart_schedule});";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//HTTP Messages
if($what=="http_msg"){
        if($opp=="delete"){
                //get list of Boost console Id and Child ID
                $query = "DELETE FROM `http_messages` WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="add"){
                $http_id = $_GET['http_id'];
                $add_msg_type = $_GET['add_msg_type'];
                $http_command = $_GET['http_command'];
                $http_parameter = $_GET['http_parameter'];
                if ($wid == 1) {
                        $add_on_zone_name = $http_id;
                        $query = "SELECT id, relay_id FROM zone_view WHERE name = '".$add_on_zone_name."' LIMIT 1";
                        $result = $conn->query($query);
                        $row = mysqli_fetch_assoc($result);
                        $zone_id = $row['id'];
                        $relay_id = $row['relay_id'];
                        $query = "SELECT node_id FROM nodes WHERE id = ".$relay_id." LIMIT 1";
                        $nresult = $conn->query($query);
                        $nrow = mysqli_fetch_assoc($nresult);
                        $node_id = $nrow['node_id'];
                } else {
                        $node_id = $http_id;
                        $add_on_zone_name = "";
			$zone_id = 0;
                }

                //Add record to http_messages table
                $query = "INSERT INTO `http_messages`(`sync`, `purge`, `zone_id`, `node_id`, `message_type`, `command`, `parameter`) VALUES ('0', '0', '{$zone_id}', '{$node_id}', '{$add_msg_type}', '{$http_command}', '{$http_parameter}')";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Nodes
if($what=="node"){
	if($opp=="delete"){
                //Get id from nodes table
                $query = "SELECT * FROM nodes WHERE id = '".$wid."' LIMIT 1";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                $node_id = $row['node_id'];
                //delete any associated sensors
                $query = "DELETE FROM sensors WHERE sensor_id = '".$wid."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }
                //delete any associated relays
                $query = "DELETE FROM relays WHERE relay_id = '".$wid."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }
                //delete any associated messages_out
                $query = "DELETE FROM messages_out WHERE node_id = '".$node_id."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }
                //delete any associated battery node data
                $query = "DELETE FROM nodes_battery WHERE node_id = '".$node_id."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }
                //Now delete from Nodes
                $query = "DELETE FROM nodes WHERE id = '".$wid."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }
                if($delete_error==0){
            		header('Content-type: application/json');
            		echo json_encode(array('Success'=>'Success','Query'=>$query));
            		return;
        	}else{
            		header('Content-type: application/json');
            		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            		return;
        	}
	}
	if($opp=="add"){
		$datetime = date("Y-m-d H:i:s");
		$node_type = $_GET['node_type'];
		$node_id = $_GET['add_node_id'];
		$node_child_id = $_GET['nodes_max_child_id'];
		$node_name = $_GET['node_name'];
                $notice_interval = $_GET['notice_interval'];
		//Add record to Nodes table
		$query = "INSERT INTO `nodes`(`sync`, `purge`, `type`, `node_id`, `max_child_id`, `sub_type`, `name`, `last_seen`, `notice_interval`, `min_value`, `status`, `ms_version`, `sketch_version`, `repeater`) VALUES ('0', '0', '{$node_type}', '{$node_id}', '{$node_child_id}', '0', '{$node_name}', '{$datetime}', '{$notice_interval}', '0', 'Active', '0', '0', '0')";
		if($conn->query($query)){
            		header('Content-type: application/json');
            		echo json_encode(array('Success'=>'Success','Query'=>$query));
            		return;
        	}else{
            		header('Content-type: application/json');
            		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            		return;
        	}
	}
}

//Controller Relays
if($what=="relay"){
        if($opp=="delete"){
                $query = "SELECT relay_id, relay_child_id FROM relays WHERE id = '".$wid."';";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                $relay_id = $row['relay_id'];
                $relay_child_id = $row['relay_child_id'];

                //Get id from nodes table
                $query = "SELECT node_id FROM nodes WHERE id = '".$relay_id."' LIMIT 1";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                $node_id = $row['node_id'];

                //delete any associated messages_out data
                $query = "DELETE FROM messages_out WHERE node_id = '".$node_id."' AND child_id = ".$relay_child_id.";";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }

		//delete the relay record
                $query = "DELETE FROM relays WHERE id = '".$wid."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }
                if($delete_error==0){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Temperature Sensors
if($what=="sensor"){
        if($opp=="delete"){
                $query = "DELETE FROM sensors WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//MQTT Devices
if($what=="mqtt_device"){
        if($opp=="delete"){
                $delete_error = 0;
                // delete any associated battery records
                $query = "DELETE FROM battery WHERE `node_id` = (SELECT CONCAT(n.node_id,'-',mqtt_devices.child_id) AS node_id
                        FROM mqtt_devices
                        JOIN nodes n ON mqtt_devices.nodes_id = n.id
                        WHERE mqtt_devices.id = ".$wid.");";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }

		// delete the mqtt_device record
                $query = "DELETE FROM mqtt_devices WHERE id = '".$wid."';";
                if($conn->query($query)){
                        $delete_error = 0;
                }else{
                        $delete_error = 1;
                }

		if($delete_error == 0){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Zone Types
if($what=="zone_type"){
	if($opp=="delete"){
		//Delete from Zone Type
		$query = "UPDATE zone_type SET zone_type.purge='1' WHERE id = '".$wid."'";
		$conn->query($query);
		if($conn->query($query)){
			header('Content-type: application/json');
			echo json_encode(array('Success'=>'Success','Query'=>$query));
			return;
		}else{
			header('Content-type: application/json');
			echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
			return;
		}
	}
	if($opp=="add"){
		$zone_type = $_GET['zone_type'];
		$zone_category = $_GET['zone_category'];
		//Add record to zone_type table
                $query = "INSERT INTO `zone_type`(`sync`, `purge`, `type`, `category`) VALUES ('0', '0', '{$zone_type}', '{$zone_category}')";
		if($conn->query($query)){
			header('Content-type: application/json');
			echo json_encode(array('Success'=>'Success','Query'=>$query));
			return;
		}else{
			header('Content-type: application/json');
			echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
			return;
		}
	}
}

//Sensor Types
if($what=="sensor_type"){
        if($opp=="delete"){
                //Delete from Sensor Type
                $query = "UPDATE sensor_type SET sensor_type.purge='1' WHERE id = '".$wid."'";
                $query = "DELETE FROM sensor_type WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="add"){
                $sensor_type = $_GET['sensor_type'];
                $sensor_units = $_GET['sensor_units'];
                //Add record to sensor_type table
                $query = "INSERT INTO `sensor_type`(`sync`, `purge`, `type`, `units`) VALUES ('0', '0', '{$sensor_type}', '{$sensor_units}')";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Away
if($what=="away"){
	if($opp=="active"){
		$time = date("Y-m-d H:i:s");
		$query = "SELECT * FROM away";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		$da= $row['status'];
		if($da=="1"){ $set="0"; }else{ $set="1"; }
		$query = "UPDATE away SET status = '{$set}', sync = '0', start_datetime = '{$time}' LIMIT 1";
                if(!$conn->query($query)){
                	header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                	return;
                }
		// if 'Away@ is switching OFF, then restore and Switch type zones to previous state
		if($set == "0") {
		        $query = "SELECT z.id, zt.category, state, zcs.schedule
				FROM zone_relays
				JOIN zone z ON z.id = zone_relays.zone_id
				JOIN zone_type zt ON zt.id = z.type_id
				JOIN zone_current_state zcs ON zcs.zone_id = z.id
				WHERE zt.category = 2;";
        		$results = $conn->query($query);
			while ($zrow = mysqli_fetch_assoc($results)) {
				$zone_id = $zrow['id'];
				$zone_state = $zrow['state'];
				$sch_active = $zrow['schedule'];
                                if($zone_state == 1){
	                                if ($sch_active == 0) {
        	                                $mode = 114;
                	                } else {
                        	                $mode = 74;
                                	}
             	                   	$query = "UPDATE zone_current_state SET mode  = {$mode}, status = {$zone_state} WHERE zone_id = {$zone_id};";
                	           	if(!$conn->query($query)){
                        			header('Content-type: application/json');
                        			echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        			return;
                                	}
                			$query = "UPDATE zone SET zone_state = {$zone_state} WHERE id = {$zone_id} LIMIT 1;";
                			if(!$conn->query($query)){
			                        header('Content-type: application/json');
                        			echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        			return;
                			}
					$query = "UPDATE messages_out SET payload = '{$zone_state}', datetime = '{$time}', sent = '0' WHERE zone_id = {$zone_id};";
                                        if(!$conn->query($query)){
			                        header('Content-type: application/json');
                        			echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        			return;
                                        }
				}
			}
		}
		// update the 'Away Button" message if it exists
		$query = "UPDATE messages_out SET payload = '{$set}', datetime = '{$time}', sent = '0' WHERE zone_id = '0' AND node_id = {$row['away_button_id']} AND child_id = {$row['away_button_child_id']} LIMIT 1";
                if($conn->query($query)){
                      	header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
	}
}

//toggle system controller mode
if($what=="sc_mode"){
        if($opp=="active"){
                $query = "SELECT `sc_mode` FROM `system_controller` LIMIT 1";
                $results = $conn->query($query);
                $row = mysqli_fetch_assoc($results);
                $sc_mode= $row['sc_mode'];
                switch ($sc_mode) {
                	case 0:
                        	$new_sc_mode = 1;
                                break;
                        case 1:
                                $new_sc_mode = 2;
                                break;
                        case 2:
                                $new_sc_mode = 3;
                                break;
                        case 3:
                               	$new_sc_mode = 4;
                                break;
                        case 4:
                                if (settings($conn, 'mode') == 0) { $new_sc_mode = 0; } else { $new_sc_mode = 5; }
                                break;
                        case 5:
                                if (settings($conn, 'mode') == 0) { $new_sc_mode = 0; } else { $new_sc_mode = 6; }
                                break;
                        case 6:
                                if (settings($conn, 'mode') == 0) { $new_sc_mode = 0; } else { $new_sc_mode = 7; }
                                break;
                        case 7:
                                $new_sc_mode = 0;
                                break;
                        default:
                                $new_sc_mode = 0;
           	}
                $query = "UPDATE system_controller SET sc_mode = {$new_sc_mode} LIMIT 1";
                $conn->query($query);
        }

        if($opp=="update"){
                $query = "UPDATE system_controller SET sc_mode = {$wid} LIMIT 1";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//add_on
if($what=="add_on"){
	$time = date("Y-m-d H:i:s");
	$query = "SELECT schedule FROM zone_current_state WHERE zone_id = {$wid}";
        $result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
        $sch_active = $row['schedule'];
	$query = "SELECT zone.zone_state, zone_type.category FROM zone, zone_type WHERE (zone_type.id = zone.type_id) AND zone.id = {$wid} LIMIT 1;";
	$result = $conn->query($query);
	$zrow = mysqli_fetch_assoc($result);
	$da = $zrow['zone_state'];
	$category = $zrow['category'];
        if($opp=="update"){
                $query = "SELECT * FROM messages_out WHERE zone_id = {$wid}";
                $results = $conn->query($query);
                while ($row = mysqli_fetch_assoc($results)) {
                        $id= $row['id'];
                        $node_id = $row['node_id'];
                        $query = "SELECT type FROM nodes WHERE node_id = '{$node_id}' LIMIT 1";
                        $nresults = $conn->query($query);
                        $nrow = mysqli_fetch_assoc($nresults);
                        if ($nrow['type'] == 'Tasmota') {
                                if($da == 1){ $message_type="0"; }else{ $message_type="1"; }
                                $query = "SELECT  command, parameter FROM http_messages WHERE node_id = '{$node_id}' AND message_type = '{$message_type}' LIMIT 1;";
                                $hresults = $conn->query($query);
                                $hrow = mysqli_fetch_assoc($hresults);
                                $set =  $message_type;
                                $payload = $hrow['command']." ".$hrow['parameter'];
                        } else {
                                if($da==1){
                                        $set=0;
                                        $payload = "0";
                                }else{
                                        $set=1;
                                        $payload = "1";
                                }
                        }
                        $query = "UPDATE messages_out SET payload = '{$payload}', datetime = '{$time}', sent = 0 WHERE id = {$id};";
                        if($conn->query($query)){
                                $update_error=0;
                        }else{
                                $update_error=1;
                        }

                        $query = "UPDATE zone_relays SET state = {$set} WHERE zone_id = {$wid};";
                        if($conn->query($query)){
                                $update_error=0;
                        }else{
                                $update_error=1;
                        }
                        //if switch type zone then force GUI status update
                        if ($category == 2) {
                                if ($sch_active == 0) {
                                        if ($set == 0) { $mode = 0; } else { $mode = 114; }
                                } else {
                                        if ($set == 0) { $mode = 75; } else { $mode = 74; }
                                }
                                $query = "UPDATE zone_current_state SET mode  = {$mode}, status = {$set} WHERE zone_id = {$wid};";
                                if($conn->query($query)){
                                        $update_error=0;
                                }else{
                                        $update_error=1;
                                }
                        }
                }

                $query = "UPDATE zone SET zone_state = {$set} WHERE id = {$wid} LIMIT 1;";
                if($conn->query($query)){
                        $update_error=0;
                }else{
                        $update_error=1;
                }

                if($sch_active == 1) {
                        $query = "UPDATE override SET status = 1 WHERE zone_id = {$wid} LIMIT 1;";
                        if($conn->query($query)){
                                $update_error=0;
                        }else{
                                $update_error=1;
                        }
                }

		if($update_error==0){
                      header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="toggle"){
                if ($da == 0) { $new_state = 1; } else { $new_state = 0; }
                $query = "SELECT `relays`.`relay_id`, `relays`.`relay_child_id` FROM `relays`, `zone_relays` WHERE (`relays`.`id` = `zone_relays`.`zone_relay_id`) AND `zone_relays`.`zone_id` = '{$wid}';";
                $results = $conn->query($query);
                while ($row = mysqli_fetch_assoc($results)) {
                        $relay_id = $row['relay_id'];
                        $relay_child_id = $row['relay_child_id'];
                        $query = "SELECT node_id, type FROM nodes WHERE id = '{$relay_id}' LIMIT 1;";
                        $result = $conn->query($query);
                        $node = mysqli_fetch_array($result);
                        $relay_node_id = $node['node_id'];
                        $type = $node['type'];
                        if (strpos($type, 'Tasmota') !== false) {
                                if ($new_state == 0) { $http_status = "Power OFF"; } else { $http_status = "Power ON"; }
                                $query = "UPDATE messages_out SET payload = '{$http_status}', sent = 0 where node_id = '{$relay_node_id}' AND child_id = '{$relay_child_id}';";
                        } else {
                                $query = "UPDATE messages_out SET payload = '{$new_state}', sent = 0 where node_id = '{$relay_node_id}' AND child_id = '{$relay_child_id}';";
                        }
                        $conn->query($query);
                        if ($conn->query($query)) {
                                $update = 0;
                        } else {
                                $update = 1;
                        }
                }
                $query = "UPDATE zone_relays SET state = '{$new_state}' WHERE zone_id = '{$wid}';";
                if ($conn->query($query)) {
                        $update_error = 0;
                } else {
                        $update_error = 1;
                }

		//if switch type zone then force GUI status update
                if ($category == 2) {
	                if ($sch_active == 0) {
        	                if ($new_state == 0) { $mode = 0; } else { $mode = 114; }
                        } else {
                                if ($new_state == 0) { $mode = 75; } else { $mode = 74; }
                        }
                        $query = "UPDATE zone_current_state SET mode  = {$mode}, status = {$new_state}, status_prev = {$da}, add_on_toggle = 1 WHERE zone_id = {$wid};";
                  	if ($conn->query($query)) {
                                $update_error = 0;
                        } else {
                                $update_error = 1;
                        }
		}
                $query = "UPDATE zone SET zone_state = '{$new_state}' where id = '{$wid}';";
                $conn->query($query);
                if ($conn->query($query)) {
                	$update = 0;
                } else {
                        $update = 1;
                }

                //if a schedule is running then place in override mode (will be cleared by controller.php when the schedule ends)
		if($sch_active == 1) {
                	$query = "UPDATE override SET status = 1 where zone_id = '{$wid}';";
                        $conn->query($query);
                        if ($conn->query($query)) {
                                $update = 0;
                        } else {
                                $update = 1;
                        }
		}

                if ($update_error == 0) {
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                } else {
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        } //end if($opp=="toggle"){
}

//update units
if($what=="units"){
	if($opp=="update"){
        $query = "UPDATE `system` SET `c_f`=" . $_GET['val'] . ";";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
}

//update system mode
if($what=="system_mode"){
        if($opp=="update"){
        $query = "UPDATE `system` SET `mode`=" . $_GET['val'] . ";";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
        }
}

//update live temperature
if($what=="live_temp"){
        if($opp=="update"){
        $query = "UPDATE `livetemp` SET `temperature`=" . SensorToDB($conn,$_GET['livetemp_c'],1) . ", `active`=" . $_GET['active'] . ";";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
        }
}
//update language
if($what=="lang"){
	if($opp=="update"){
                $lang_val = $_GET['lang_val'];
                $query = "UPDATE `system` SET `language`='" . $lang_val . "';";
                if (file_exists('/var/www/languages/'.$lang_val.'.php')) {
                        if($conn->query($query)){
                                setcookie("PiHomeLanguage", $lang_val, time()+(3600*24*90));
                                header('Content-type: application/json');
                                echo json_encode(array('Success'=>'Success','Query'=>$query));
                                return;
                        }else{
                                header('Content-type: application/json');
                                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                                return;
                        }
                } else {
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }
	}
}

//update user email address
if($what=="user_email"){
        if($opp=="update"){
                $email_add = $_GET['email_add'];
                $user_id = $_SESSION['user_id'];
                $query = "UPDATE `user` SET `email`= '{$email_add}' WHERE id = '{$user_id}';";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
                }
        }
}

//System Controller Settings
if($what=="system_controller_settings"){
	$datetime = date("Y-m-d H:i:s");
	$status = $_GET['status'];
	$name = $_GET['name'];
	$heat_relay_id = $_GET['heat_relay_id'];
	if ($_GET['wid'] == 1) {
	        $cool_relay_id = $_GET['cool_relay_id'];
        	$fan_relay_id = $_GET['fan_relay_id'];
		$overrun = "0";
                $overrun = "0";
                $weather_sensor_id = 0;
	} else {
                $cool_relay_id = 0;
                $fan_relay_id = 0;
		$overrun = $_GET['overrun'];
                $weather_factoring = $_GET['weather_factoring'];
                $weather_sensor_id = $_GET['weather_sensor_id'];
	}
	$hysteresis_time = $_GET['hysteresis_time'];
	$max_operation_time = $_GET['max_operation_time'];
	if ($status=='true'){$status = '1';} else {$status = '0';}

        $query = "SELECT * FROM relays WHERE id ='".$heat_relay_id."' LIMIT 1";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
	$heat_controler_id = $row['relay_id'];
        $heat_controler_child_id = $row['relay_child_id'];
        $query = "SELECT node_id, type FROM nodes WHERE id ='".$heat_controler_id."' LIMIT 1";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
	$heat_node_id = $row['node_id'];
        $heat_node_type = $row['type'];

        if ($_GET['wid'] == 1) {
	        $query = "SELECT * FROM relays WHERE id ='".$cool_relay_id."' LIMIT 1";
        	$results = $conn->query($query);
	        $row = mysqli_fetch_assoc($results);
        	$cool_controler_id = $row['relay_id'];
	        $cool_controler_child_id = $row['relay_child_id'];
        	$query = "SELECT node_id FROM nodes WHERE id ='".$cool_controler_id."' LIMIT 1";
	        $results = $conn->query($query);
        	$row = mysqli_fetch_assoc($results);
	        $cool_node_id = $row['node_id'];
		//Check messages_out for System Controller
        	$query = "SELECT * FROM messages_out WHERE node_id='".$cool_node_id."' AND child_id='".$cool_controler_child_id."' LIMIT 1;";
		$result = $conn->query($query);
		if (mysqli_num_rows($result)==0){
			//Update messages_out for System Controller.
			$query = "INSERT INTO `messages_out`(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES (0, 0, '".$cool_node_id."', '".$cool_controler_child_id."', 1, 1, 2, 0, 0, '".$datetime."', 0);";
			$conn->query($query);
		}

        	$query = "SELECT * FROM relays WHERE id ='".$fan_relay_id."' LIMIT 1";
	        $results = $conn->query($query);
        	$row = mysqli_fetch_assoc($results);
	        $fan_controler_id = $row['relay_id'];
        	$fan_controler_child_id = $row['relay_child_id'];
	        $query = "SELECT node_id FROM nodes WHERE id ='".$fan_controler_id."' LIMIT 1";
        	$results = $conn->query($query);
	        $row = mysqli_fetch_assoc($results);
        	$fan_node_id = $row['node_id'];
		//Check messages_out for System Controller
        	$query = "SELECT * FROM messages_out WHERE node_id='".$fan_node_id."' AND child_id='".$fan_controler_child_id."' LIMIT 1;";
		$result = $conn->query($query);
		if (mysqli_num_rows($result)==0){
			//Update messages_out for System Controller.
			$query = "INSERT INTO `messages_out`(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES (0, 0, '".$fan_node_id."', '".$fan_controler_child_id."', 1, 1, 2, 0, 0, '".$datetime."', 0);";
			$conn->query($query);
		}
	}

	//Check messages_out for System Controller
        $query = "SELECT * FROM messages_out WHERE node_id='".$heat_node_id."' AND child_id='".$heat_controler_child_id."' LIMIT 1;";
	$result = $conn->query($query);
	if (mysqli_num_rows($result)==0){
		//Update messages_out for System Controller.
		$query = "INSERT INTO `messages_out`(`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES (0, 0, '".$heat_node_id."', '".$heat_controler_child_id."', 1, 1, 2, 0, 0, '".$datetime."', 0);";
		$conn->query($query);
	}

	$query = "SELECT * FROM system_controller LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
		//No record in system_controller table, so add
		$query = "INSERT INTO `system_controller` VALUES (1,1,0,0,1,1,'".$name."',".$heat_node_id.",".$hysteresis_time.",".$max_operation_time.",".$overrun.",now(),0,0,".$heat_relay_id.",".$cool_relay_id.",".$fan_relay_id.",0,".$weather_factoring.",".$weather_sensor_id.", NULL, NULL);";
	} else {
		//Update system_controller Setting
		$query = "UPDATE system_controller SET status = ".$status.", name = '".$name."', node_id = ".$heat_node_id.", hysteresis_time = ".$hysteresis_time.", max_operation_time = ".$max_operation_time.", overrun = ".$overrun.", heat_relay_id = ".$heat_relay_id.", cool_relay_id = ".$cool_relay_id.", fan_relay_id = ".$fan_relay_id.", weather_factoring = ".$weather_factoring.", weather_sensor_id = ".$weather_sensor_id." WHERE ID = 1;";
	}
	if($conn->query($query)){
                // Checking if System Controller script is running and restart it
                $controller_script_txt = 'python3 /var/www/cron/controller.py';
                exec("ps ax | grep '$controller_script_txt' | grep -v grep", $pids);
                if (count($pids) > 0) {
                        exec("sudo pkill -f '$controller_script_txt'");
                }

		header('Content-type: application/json');
		echo json_encode(array('Success'=>'Success','Query'=>$query));
		return;
	}else{
		header('Content-type: application/json');
		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
		return;
	}
}

//update openweather
if($what=="openweather"){
	if($opp=="update"){
        if($_GET['rad_CityZip']=='City') {
            $query = "UPDATE `system` SET `country`='" . $_GET['sel_Country'] . "'
                    ,`openweather_api`='" . $_GET['inp_APIKEY'] . "'
                    ,`city`='" . $_GET['inp_City'] . "',`zip`=NULL;";
            if($conn->query($query)){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
            }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
            }
        }else if($_GET['rad_CityZip']=='Zip') {
            $query = "UPDATE `system` SET `country`='" . $_GET['sel_Country'] . "'
                    ,`openweather_api`='" . $_GET['inp_APIKEY'] . "'
                    ,`zip`='" . $_GET['inp_Zip'] . "',`city`=NULL;";
            if($conn->query($query)){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
            }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
            }
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Invalid value for rad_CityZip.\r\n$_GET=' . print_r($_GET)));
            return;
        }
	}
}

//Setup PiConnect
if($what=="setup_piconnect"){
	//Set away to 0
	$query = "UPDATE away SET `sync`='0';";
	$result = $conn->query($query);
	//Update System Controller to sync
	$query = "UPDATE system_controller SET `sync`='0';";
	$result = $conn->query($query);
	//Update system_controller logs to sync
	$query = "UPDATE controller_zone_logs SET `sync`='0';";
	$result = $conn->query($query);
	//upate boost records to sync
	$query = "UPDATE boost SET `sync` ='0';";
	$result = $conn->query($query);
	//update from protection to sync
	$query = "UPDATE frost_protection SET `sync`='0';";
	$result = $conn->query($query);
	//update gateway to sync
	$query = "UPDATE gateway SET `sync`='0';";
	$result = $conn->query($query);
	//update gateway logs NOT sync
	$query = "UPDATE gateway_logs SET `sync`='1';";
	$result = $conn->query($query);
	//update messages in history to NOT sync
	$query = "UPDATE messages_in SET `sync`='1';";
	$result = $conn->query($query);
	//update nodes to sync
	$query = "UPDATE nodes SET `sync`='0';";
	$result = $conn->query($query);
	//update nodes battery to NOT sync
	$query = "UPDATE nodes_battery SET `sync`='1';";
	$result = $conn->query($query);
	//update schedule daily time to sync
	$query = "UPDATE schedule_daily_time SET `sync`='0';";
	$result = $conn->query($query);
	//update schedule dailt time for zone to sync
	$query = "UPDATE schedule_daily_time_zone SET `sync`='0';";
	$result = $conn->query($query);
	//update schedule night climate time to sync
	$query = "UPDATE schedule_night_climate_time SET `sync`='0';";
	$result = $conn->query($query);
	//update schedule night climate zone to sync
	$query = "UPDATE schedule_night_climat_zone SET `sync`='0';";
	$result = $conn->query($query);
	//update weather to sync
	$query = "UPDATE weather SET `sync`='0';";
	$result = $conn->query($query);
	//update zone to sync
	$query = "UPDATE zone SET `sync`='0';";
	$result = $conn->query($query);
	//update zone logs to NOT to sync
	$query = "UPDATE zone_logs SET `sync`='1';";
	$result = $conn->query($query);
	//update Systems settings to sync
	$query = "UPDATE system SET `sync`='0';";
	$result = $conn->query($query);
	//Update PiConnect API Status and key
	$api_key = $_GET['api_key'];
	$status = $_GET['status'];
	if ($status=='true'){$status = '1';}else {$status = '0';}
        $query = "SELECT * FROM piconnect LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
                //No record in frost_protction table, so add
                $query = "INSERT INTO piconnect VALUES(1, '".$status."', 'http', 'www.pihome.eu', '/piconnect/mypihome.php', '".$api_key."');";
        } else {
                $query = "UPDATE piconnect SET status = '".$status."', api_key = '".$api_key."';";
        }
	if($conn->query($query)){
		header('Content-type: application/json');
		echo json_encode(array('Success'=>'Success','Query'=>$query));
		return;
	}else{
		header('Content-type: application/json');
		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
		return;
	}

}

//Database Backup
if($what=="db_backup"){
        $query = "SELECT `password` FROM email LIMIT 1;";
        $result = $conn->query($query);
	if (mysqli_num_rows($result) > 0){
		$row = mysqli_fetch_assoc($result);
		$p_password = dec_passwd($row['password']);
        	shell_exec("nohup python3 start_backup.py ".$p_password." >/dev/null 2>&1");
		$info_message = "Data Base Backup Request Started, This process may take some time complete..." ;
	}
}

//Code Update
if($what=="code_update"){
        shell_exec("nohup python3 /var/www/cron/move_files.py > /dev/null 2>&1 &");
        $info_message = "Code Module Update Request Started, This process may take some time complete..." ;
}

//Check for Updates
if($what=="check_updates"){
        exec("python3 /var/www/cron/update_code.py > /dev/null 2>&1 &");
}

//Database Update
if($what=="database_update"){
        shell_exec("nohup php apply_database_update.php >/dev/null 2>&1");
        $info_message = "Database Update Request Started, This process may take some time complete..." ;
}

//Setup Backup e-mail
if($what=="backup_email_update"){
	$backup_email = $_GET['backup_email'];
	$query = "UPDATE system SET backup_email = '".$backup_email."';";
	if($conn->query($query)){
		header('Content-type: application/json');
		echo json_encode(array('Success'=>'Success','Query'=>$query));
		return;
	}else{
		header('Content-type: application/json');
		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
		return;
	}
}

//Reboot System
if($what=="reboot"){

	//Stop Cron Service
	//systemctl stop cron.service
	//exec("systemctl stop cron.service");

	//Kill Gateway Process
	$query = "SELECT * FROM gateway where status = 1 order by id asc LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$gw_pid = $row['pid'];
	exec("kill -9 $gw_pid");

        //Kill Controller Process
        $query = "SELECT * FROM system_controller where status = 1 order by id asc LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $sc_pid = $row['pid'];
        exec("kill -9 $sc_pid");

	//Stop MySQL/MariaDB Service
	//systemctl stop mysql.service
	//exec("systemctl stop mysql.service");

        $info_message = "Server is rebooting <small> Please Do not Refresh... </small>";
        $query = "UPDATE system SET reboot = 1;";
        $conn->query($query);
}

//Shutdown System
if($what=="shutdown"){

	/*
	//Stop Cron Service
	//systemctl stop cron.service
	exec("systemctl stop cron.service");

	//Kill Gateway Process
	$query = "SELECT * FROM gateway where status = 1 order by id asc LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$gw_pid = $row['pid'];
	exec("kill -9 $gw_pid");

	//Stop MySQL/MariaDB Service
	//systemctl stop mysql.service
	exec("systemctl stop mysql.service");
	*/
	//Shutdown System
	$info_message = "Server is Shutting down <small> Please Do not Refresh... </small>";
        $query = "UPDATE system SET shutdown = 1;";
        $conn->query($query);
}


//Search for network gateway
if($what=="find_gw"){
	//shell_exec("nohup python /var/www/cron/find_mygw/find_mygw.py");
	$query = "UPDATE gateway SET find_gw = '1' where status = 1;";
	$conn->query($query);
}

//Restart MySensors Gateway
if($what=="resetgw"){
	$query = "UPDATE gateway SET reboot = '1';";
	$conn->query($query);
}

//Setup Smart Home Gateway
if($what=="setup_gateway"){
	$status = $_GET['status'];
        $enable_outgoing = $_GET['enable_outgoing'];
	$gw_type = $_GET['gw_type'];
	$gw_location = $_GET['gw_location'];
	$gw_port = $_GET['gw_port'];
	$gw_timout = $_GET['gw_timout'];
        $gw_heartbeat = $_GET['gw_heartbeat'];
	if ($status=='true'){$status = '1';}else {$status = '0';}
        if ($enable_outgoing=='true'){$enable_outgoing = '1';}else {$enable_outgoing = '0';}
        $query = "SELECT * FROM gateway LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
                //No record in gateway, so add
                $query = "INSERT INTO `gateway` VALUES (1,1,0,0,'".$gw_type."','".$gw_location."','".$gw_port."','".$gw_timout."','".$gw_heartbeat."', 0, 0, 0, 0, 0,'".$enable_outgoing."');";
        } else {
		$query = "UPDATE gateway SET status = '".$status."', `sync` = '0', type = '".$gw_type."', location = '".$gw_location."', port = '".$gw_port."', timout = '".$gw_timout."', heartbeat_timeout = '".$gw_heartbeat."', enable_outgoing = '".$enable_outgoing."' where ID = 1;";
	}
	if($conn->query($query)){
		header('Content-type: application/json');
		echo json_encode(array('Success'=>'Success','Query'=>$query));
		return;
	}else{
		header('Content-type: application/json');
		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
		return;
	}
}


//network Settings
if($what=="setup_network"){
        $n_primary = $_GET['n_primary'];
	$n_ap_mode = $_GET['n_ap_mode'];
        $n_int_num = $_GET['n_int_num'];
        $n_int_type = $_GET['n_int_type'];
        $n_mac = $_GET['n_mac'];
        $n_hostname = $_GET['n_hostname'];
        $n_ip = $_GET['n_ip'];
        $n_gateway = $_GET['n_gateway'];
        $n_net_mask = $_GET['n_net_mask'];
        $n_dns1 = $_GET['n_dns1'];
        $n_dns2 = $_GET['n_dns2'];

        //Check interface already exists in table
        $query = "SELECT * FROM `network_settings` WHERE `interface_num` = '".$n_int_num."' LIMIT 1;";
        $result = $conn->query($query);
        if (mysqli_num_rows($result)==0){
                //No record, so add
                $query = "INSERT INTO `network_settings`(`sync`, `purge`, `primary_interface`, `ap_mode`, `interface_num`, `interface_type`, `mac_address`, `hostname`, `ip_address`, `gateway_address`, `net_mask`, `dns1_address`, `dns2_address`) VALUES (0,0,'".$n_primary."',".$n_ap_mode.",'".$n_int_num."','".$n_int_type."','".$n_mac."','".$n_hostname."','".$n_ip."','".$n_gateway."','".$n_net_mask."','".$n_dns1."','".$n_dns2."');";
        } else {
                //Update
                $query = "UPDATE `network_settings` SET primary_interface = '".$n_primary."', ap_mode = ".$n_ap_mode.", interface_type = '".$n_int_type."', mac_address = '".$n_mac."', hostname = '".$n_hostname."', ip_address = '".$n_ip."', gateway_address = '".$n_gateway."', net_mask = '".$n_net_mask."', dns1_address = '".$n_dns1."', dns2_address = '".$n_dns2."' where interface_num = '".$n_int_num."';";
        }
        if($conn->query($query)){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//Setup E-mail Setting
if($what=="setup_email"){
	$status = $_GET['status'];
	$e_smtp = $_GET['e_smtp'];
        $e_port = $_GET['e_port'];
	$e_username = $_GET['e_username'];
	$e_password = enc_passwd($_GET['e_password']);
	$e_from_address = $_GET['e_from_address'];
	$e_to_address = $_GET['e_to_address'];
	if ($status=='true'){$status = '1';} else {$status = '0';}

	//search for exiting record
	$query = "SELECT * FROM email LIMIT 1;";
	$result = $conn->query($query);
	if (mysqli_num_rows($result)==0){
		//Inset New Record
		$query = "INSERT INTO email (`sync`, `purge`, smtp, port, username, password, `from`, `to`, status) VALUES (0, 0, '".$e_smtp."', '".$e_port."', '".$e_username."', '".$e_password."', '".$e_from_address."', '".$e_to_address."', '".$status."');";
	} else {
		//Update Exiting Record
		$row = mysqli_fetch_assoc($result);
		$e_id= $row['id'];
		$query = "Update email SET smtp = '".$e_smtp."',port = '".$e_port."', username = '".$e_username."', password = '".$e_password."', `from` = '".$e_from_address."', `to` = '".$e_to_address."', status = '".$status."' where ID = '".$e_id."';";
	}
	if($conn->query($query)){
		header('Content-type: application/json');
		echo json_encode(array('Success'=>'Success','Query'=>$query));
		return;
	}else{
		header('Content-type: application/json');
		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
		return;
	}

}

//Setup Graph Setting
if($what=="setup_graph"){
	$sel_query = "SELECT id, sensor_id, name, graph_num, min_max_graph, name AS sname
			FROM sensors
			WHERE sensor_type_id = 1
                        UNION
                        SELECT sensor_average.id, sensor_average.sensor_id, zone.name, sensor_average.graph_num, sensor_average.min_max_graph, zone.name AS sname
                        FROM sensor_average, zone
                        WHERE sensor_average.zone_id = zone.id
 			UNION
			SELECT 0 AS id, '' AS sensor_id, 'Outside Temp' AS name, '' AS graph_num, enable_archive AS min_max_graph, 'zzz' AS sname
			FROM weather
	ORDER BY sname ASC;";
        $results = $conn->query($sel_query);
        $update_error = 0;
        while ($row = mysqli_fetch_assoc($results) and $update_error == 0) {
                $input1 = 'graph_num'.$row['id'];
                $graph_num =  $_GET[$input1];
                $input2 = 'checkbox_enable_graph'.$row['id'];
                $enabled =  $_GET[$input2];
                if ($enabled=='true'){$enabled = '1';} else {$enabled = '0';}
		if ($row['id'] == 0) {
			$query = "UPDATE weather SET enable_archive = ".$enabled." LIMIT 1;";
		} elseif (strpos($row['sensor_id'], "zavg_") !== false) {
                        $query = "UPDATE sensor_average SET graph_num = ".$graph_num.", min_max_graph = ".$enabled." WHERE id = ".$row['id']." LIMIT 1;";
		} else {
	                $query = "UPDATE sensors SET graph_num = ".$graph_num.", min_max_graph = ".$enabled." WHERE id = ".$row['id']." LIMIT 1;";
		}
                if(!$conn->query($query)){
                        $update_error = 1;
                }
        }
        if($update_error == 0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//update sensors to show
if($what=="show_sensors"){
        $sel_query = "SELECT * FROM sensors ORDER BY id asc;";
        $results = $conn->query($sel_query);
        while ($row = mysqli_fetch_assoc($results)) {
                $checkbox = 'checkbox'.$row['id'];
                $show_it =  $_GET[$checkbox];
                if ($show_it=='true'){$show_it = '1';} else {$show_it = '0';}
                $checkbox_msg_in = 'checkbox_msg_in'.$row['id'];
                $msg_in =  $_GET[$checkbox_msg_in];
                if ($msg_in=='true'){$msg_in = '1';} else {$msg_in = '0';}
                $query = "UPDATE sensors SET show_it = '".$show_it."', message_in ='".$msg_in."' WHERE id = '".$row['id']."' LIMIT 1;";
                $update_error=0;
                if(!$conn->query($query)){
                        $update_error=1;
                }
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//update Node Alerts Notice Interval
if($what=="node_alerts"){
        $update_error=0;
        $sel_query = "SELECT * FROM nodes where status = 'Active' ORDER BY node_id asc";
        $results = $conn->query($sel_query);
        while ($row = mysqli_fetch_assoc($results) and $update_error == 0) {
                $node_id = $row['node_id'];
                if(isset($_GET["interval".$node_id])) {
                        $notice_interval =  $_GET["interval".$node_id];
                        $query = "UPDATE nodes SET notice_interval = '".$notice_interval."' WHERE node_id='".$row['node_id']."' LIMIT 1;";
                        if(!$conn->query($query)){
                                $update_error=1;
                        }
                }
                if(isset($_GET["min_value".$node_id])) {
                        $min_value =  $_GET["min_value".$node_id];
                        if($min_value != 'N/A'){
                                $query = "UPDATE nodes SET min_value = '".$min_value."' WHERE node_id='".$row['node_id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $update_error=1;
                                }
                        }
                }
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

if($what=="mqtt"){
	if($opp=="delete"){
        $query = "DELETE FROM `mqtt` WHERE `id`=" . $_GET['wid'] . ";";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
	if($opp=="add"){
        $query = "INSERT INTO `mqtt` 
            (`id`, `name`, `ip`, `port`, `username`, `password`, `enabled`, `type`) VALUES 
            (NULL,
             '" . $_GET['inp_Name'] . "',
             '" . $_GET['inp_IP'] . "',
             " . $_GET['inp_Port'] . ",
             '" . $_GET['inp_Username'] . "',
             '" . enc_passwd($_GET['inp_Password']) . "',
             " . $_GET['sel_Enabled'] . ",
             " . $_GET['sel_Type'] . ");";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
	if($opp=="edit"){
        $query = "UPDATE `mqtt` SET 
            `name`='" . $_GET['inp_Name'] . "',
            `ip`='" . $_GET['inp_IP'] . "',
            `port`=" . $_GET['inp_Port'] . ",
            `username`='" . $_GET['inp_Username'] . "',
            `password`='" . enc_passwd($_GET['inp_Password']) . "',
            `enabled`=" . $_GET['sel_Enabled'] . ",
            `type`=" . $_GET['sel_Type'] . "
            WHERE `id`=" . $_GET['inp_id'] . ";";
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
}

//update Time zone
if($what=="time_zone"){
	if($opp=="update"){
		$time_zone_val = $_GET['time_zone_val'];

		$query = "UPDATE `system` SET `timezone`='" . $time_zone_val . "';";
		exec("sudo timedatectl set-timezone $time_zone_val");
                // Checking if gateway script is running
                $gateway_script_txt = 'python3 /var/www/cron/gateway.py';
                exec("ps ax | grep '$gateway_script_txt' | grep -v grep", $pids);
                if (count($pids) > 0) {
                        exec("sudo pkill -f '$gateway_script_txt'");
                }
                // Checking if DS18b20 script is running
                $ds18b20_script_txt = 'python3 /var/www/cron/gpio_ds18b20.py';
                exec("ps ax | grep '$ds18b20_script_txt' | grep -v grep", $pids);
                if (count($pids) > 0) {
                        exec("sudo pkill -f '$ds18b20_script_txt'");
                }
                // Checking if GPIO Switch script is running
                $switch_script_txt = 'python3 /var/www/cron/gpio_switch.py';
                exec("ps ax | grep '$switch_script_txt' | grep -v grep", $pids);
                if (count($pids) > 0) {
                        exec("sudo pkill -f '$switch_script_txt'");
                }
                // Checking if System Controller script is running
                $controller_script_txt = 'python3 /var/www/cron/controller.py';
                exec("ps ax | grep '$controller_script_txt' | grep -v grep", $pids);
                if (count($pids) > 0) {
                        exec("sudo pkill -f '$controller_script_txt'");
                }
        if($conn->query($query)){
            header('Content-type: application/json');
            echo json_encode(array('Success'=>'Success','Query'=>$query));
            return;
        }else{
            header('Content-type: application/json');
            echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            return;
        }
	}
}

if($what=="job"){
        $job_error=0;
        if($opp=="update"){
                $sel_query = "SELECT * FROM jobs ORDER BY id asc";
                $results = $conn->query($sel_query);
                while ($row = mysqli_fetch_assoc($results) and $update_error == 0) {
                        $id = $row['id'];
                        if(isset($_GET["jobs_name".$id])) {
                                $job_name =  $_GET["jobs_name".$id];
                                $query = "UPDATE jobs SET job_name = '".$job_name."' WHERE id='".$row['id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $job_error=1;
                                }
                        }
                        if(isset($_GET["jobs_script".$id])) {
                                $job_script =  $_GET["jobs_script".$id];
                                $query = "UPDATE jobs SET script = '".$job_script."' WHERE id='".$row['id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $job_error=1;
                                }
                        }
                        if(isset($_GET["checkbox_enabled".$id])) {
                                $enabled =  $_GET["checkbox_enabled".$id];
                                if ($enabled=='true'){$enabled = '1';} else {$enabled = '0';}
                                $query = "UPDATE jobs SET enabled = '".$enabled."' WHERE id='".$row['id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $job_error=1;
                                }
                        }
                        if(isset($_GET["checkbox_log".$id])) {
                                $log_it =  $_GET["checkbox_log".$id];
                                if ($log_it=='true'){$log_it = '1';} else {$log_it = '0';}
                                $query = "UPDATE jobs SET log_it = '".$log_it."' WHERE id='".$row['id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $job_error=1;
                                }
                        }
                        if(isset($_GET["jobs_time".$id])) {
                                $job_time =  $_GET["jobs_time".$id];
                                $query = "UPDATE jobs SET time = '".$job_time."' WHERE id='".$row['id']."' LIMIT 1;";
                                if(!$conn->query($query)){
                                        $job_error=1;
                                }
                        }
                }
        }
        if($opp=="delete"){
                //Now delete from jobs
                $query = "DELETE FROM jobs WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        $job_error=0;
                }
        }
        if($opp=="add"){
               	$enabled = $_GET['enabled'];
                if ($enabled=='true'){$enabled = '1';} else {$enabled = '0';}
               	$job_name = $_GET['job_name'];
                $job_script = $_GET['job_script'];
                $job_time = $_GET['job_time'];
                $log_it = $_GET['log_it'];
                if ($log_it=='true'){$log_it = '1';} else {$log_it = '0';}
                //Add record to Nodes table
                $query = "INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`) VALUES ('".$job_name."', '".$job_script."','".$enabled."','".$log_it."', '".$job_time."','');";
                if($conn->query($query)){
                        $job_error=0;
                }
        }
        if($job_error==0){
                my_exec("/usr/bin/sudo /bin/systemctl restart pihome_jobs_schedule.service");
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//Set Buttons
if($what=="set_buttons"){
        $sel_query = "SELECT * FROM button_page ORDER BY id asc;";
        $results = $conn->query($sel_query);
        while ($row = mysqli_fetch_assoc($results)) {
                $page =  $_GET['page_type'.$row['id']];
                $index =  $_GET['index'.$row['id']];
                $query = "UPDATE button_page SET page = ".$page.", index_id = ".$index." WHERE id = '".$row['id']."' LIMIT 1;";
                $update_error=0;
                if(!$conn->query($query)){
                        $update_error=1;
                }
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//Set DB Cleanup
if($what=="set_db_cleanup"){
        $query = "SELECT column_name
                FROM INFORMATION_SCHEMA.COLUMNS
                WHERE TABLE_SCHEMA = 'maxair' AND table_name = 'db_cleanup' AND ordinal_position > 3
                ORDER BY ordinal_position;";
        $results = $conn->query($query);
	$x = 0;
        while ($row = mysqli_fetch_assoc($results)) {
                $column_name = $row['column_name'];
                $period = $_GET["period".$x];
                $interval = $_GET["set_interval".$x];
                $query = "UPDATE db_cleanup SET ".$column_name." = '".$period." ".$interval."' WHERE id = 1 LIMIT 1;";
                $update_error=0;
                if(!$conn->query($query)){
                        $update_error=1;
                }
		$x = $x + 1;
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//enable graph categories to be displayed
if($what=="enable_graphs"){
        $mask = 0;
        for ($x = 0; $x <=  6; $x++) {
                $checkbox = 'checkbox_graph'.$x;
                $enabled =  $_GET[$checkbox];
                if ($enabled=='true'){$enabled = 1;} else {$enabled = 0;}
                $mask = $mask + ($enabled << $x);
	}
        $query = "UPDATE graphs SET mask = '".$mask."' LIMIT 1;";
        $update_error=0;
        if(!$conn->query($query)){
        	$update_error=1;
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//update GitHub Repository URL
if($what=="set_repository"){
        $repository_id =  $_GET['repository_id'];
        $query = "UPDATE repository SET status = IF(id=".$repository_id.", 1, 0);";
        $update_error=0;
        if(!$conn->query($query)){
                $update_error=1;
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//set max cpu temperature
if($what=="set_max_cpu_temp"){
        $max_cpu_temp =  $_GET['max_cpu_temp'];
        $query = "UPDATE system SET max_cpu_temp = ".$max_cpu_temp.";";
        $update_error=0;
        if(!$conn->query($query)){
                $update_error=1;
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//set page refresh rate
if($what=="page_refresh_rate"){
        $page_refresh =  $_GET['new_refresh'];
        $query = "UPDATE system SET page_refresh = ".$page_refresh.";";
        $update_error=0;
        if(!$conn->query($query)){
                $update_error=1;
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//Sensor Limits
if($what=="sensor_limits"){
        if($opp=="delete"){
                $query = "DELETE FROM sensor_limits WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Set Theme
if($what=="set_theme"){
        $theme_id =  $_GET['theme_id'];
        $query = "UPDATE system SET theme = '".$theme_id."';";
        $update_error=0;
        if(!$conn->query($query)){
                $update_error=1;
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//Delete Theme
if($what=="theme"){
        if($opp=="delete"){
                $query = "DELETE FROM theme WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Auto Backup
if($what=="auto_backup"){
        if($opp=="update"){
	        $frequency = $_GET['fval1']." ".$_GET['set_f'];
        	$rotation = $_GET['rval1']." ".$_GET['set_r'];
        	$destination = $_GET['dest'];
                $enabled = $_GET['checkbox1'];
                if ($enabled=='true'){$enabled = '1';} else {$enabled = '0';}
                $email_backup = $_GET['checkbox2'];
                if ($email_backup=='true'){$email_backup = '1';} else {$email_backup = '0';}
                $email_confirmation = $_GET['checkbox3'];
                if ($email_confirmation=='true'){$email_confirmation = '1';} else {$email_confirmation = '0';}
	        $query = "UPDATE auto_backup SET enabled = {$enabled}, frequency = '{$frequency}', rotation = '{$rotation}', destination = '{$destination}', email_backup = {$email_backup}, email_confirmation = {$email_confirmation};";
        	$update_error=0;
	        if(!$conn->query($query)){
        	        $update_error=1;
	        }
        	if($update_error==0){
                	header('Content-type: application/json');
	                echo json_encode(array('Success'=>'Success','Query'=>$query));
        	        return;
	        }else{
        	        header('Content-type: application/json');
                	echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
	                return;
        	}
        }
}

//Restore Database
if($what=="database_restore"){
        $filePath =  $_GET['wid'];
        $info_message = "Database Restore Request Started, This process may take some time complete..." ;
        shell_exec("nohup php restore_database.php ".$filePath.">/dev/null 2>&1 &");
        sleep(30);
}

//Sensor Messages
if($what=="sensor_message"){
        if($opp=="delete"){
                //Delete from Sensor Type
                $query = "DELETE FROM sensor_messages WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="add"){
                $msg_sensor_id = $_GET['msg_sensor_id'];
                $msg_id = $_GET['msg_id'];
                $msg_type_id = $_GET['msg_type_id'];
                $msg_text = $_GET['msg_text'];
                $msg_status_color = $_GET['msg_status_color'];
                //Add record to sensor_messages table
                $query = "INSERT INTO `sensor_messages`(`sync`, `purge`, `sensor_id`, `message_id`, `message`, `status_color`, `sub_type`)
                        VALUES ('0', '0', '{$msg_sensor_id}', '{$msg_id}', '{$msg_text}', '{$msg_status_color}', '{$msg_type_id}'); ";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//EBus Command
if($what=="ebus_command"){
        if($opp=="delete"){
                //Delete from Sensor Type
                $query = "DELETE FROM ebus_messages WHERE id = '".$wid."';";
                $conn->query($query);
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="add"){
                $ebus_sensor_id = $_GET['ebus_sensor_id'];
                $ebus_msg = $_GET['ebus_msg'];
                $ebus_position = $_GET['ebus_position'];
                $ebus_offset = $_GET['ebus_offset'];
                //Add record to ebus_messages table
                $query = "INSERT INTO `ebus_messages`(`sync`, `purge`, `message`, `sensor_id`, `position`, `offset`)
                        VALUES ('0', '0', '{$ebus_msg}', '{$ebus_sensor_id}', '{$ebus_position}', '{$ebus_offset}'); ";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//update livetemp zone
if($what=="update_livetemp_zone"){
        if($opp=="update"){
	        $query = "UPDATE `livetemp` SET `zone_id`=" . $_GET['zone_id'] . ";";
        	if($conn->query($query)){
	        	header('Content-type: application/json');
        	    	echo json_encode(array('Success'=>'Success','Query'=>$query));
	            	return;
        	}else{
            		header('Content-type: application/json');
            		echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
            		return;
        	}
	}
}

//Auto Image
if($what=="auto_image"){
        if($opp=="update"){
                $frequency = $_GET['fval1']." ".$_GET['set_ai_f'];
                $rotation = $_GET['rval1']." ".$_GET['set_ai_r'];
                $destination = $_GET['dest'];
                $enabled = $_GET['checkbox1'];
                if ($enabled=='true'){$enabled = '1';} else {$enabled = '0';}
                $email_confirmation = $_GET['checkbox3'];
                if ($email_confirmation=='true'){$email_confirmation = '1';} else {$email_confirmation = '0';}
                $query = "UPDATE auto_image SET enabled = {$enabled}, frequency = '{$frequency}', rotation = '{$rotation}', destination = '{$destination}', email_confirmation = {$email_confirmation};";
                $update_error=0;
                if(!$conn->query($query)){
                        $update_error=1;
                }
                if($update_error==0){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Toggle Relay
if($what=="toggle_relay"){
        if($opp=="update"){
                $r_id =  $_GET['wid'];
                $update_error = 0;
                $query = "SELECT relay_id, relay_child_id FROM relays WHERE id = {$r_id} LIMIT 1;";
                $result = $conn->query($query);
                $row = mysqli_fetch_assoc($result);
                $relay_id = $row['relay_id'];
                $relay_child_id = $row['relay_child_id'];
                $query = "SELECT payload FROM messages_out WHERE n_id = {$relay_id} AND child_id = {$relay_child_id} LIMIT 1;";
                $result = $conn->query($query);
                $row = mysqli_fetch_assoc($result);
                if ($row['payload'] == "0") { $new_state = "1"; } else { $new_state = "0"; }
                $query = "UPDATE messages_out SET payload = {$new_state}, sent = 0 WHERE n_id = {$relay_id} AND child_id = {$relay_child_id};";
                if (!$conn->query($query)) { $db_error = 1; }
                if ($db_error == 0) {
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                } else {
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="exit"){
                $relay_map =  $_GET['relay_map'];
                $db_error = 0;
                $query = "SELECT relay_id, relay_child_id FROM relays ORDER BY relay_id, relay_child_id DESC;";
                $results = $conn->query($query);
                $count = $results->num_rows;
                if ($count != 0) {
			$n = 0;
                	while ($row = mysqli_fetch_assoc($results)) {
                                $payload = $relay_map & (1 << $n++);
                        	$query = "UPDATE messages_out SET payload = {$payload}, sent = 0 WHERE n_id = {$row['relay_id']} AND child_id = {$row['relay_child_id']};";
                                if (!$conn->query($query)) { $db_error = 1; }
                        }
                }
                $query = "UPDATE system  SET test_mode = 0;";
                if (!$conn->query($query)) { $db_error = 1; }
                if ($db_error == 0) {
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
        if($opp=="enter"){
                $query = "UPDATE system SET test_mode = 1;";
                $db_error = 0;
                if(!$conn->query($query)){
                        $db_error = 1;
                }
                $test_mode = 0;
		while ($test_mode != 2) {
	                $query = "SELECT test_mode FROM system LIMIT 1;";
        	        $result = $conn->query($query);
                	$row = mysqli_fetch_assoc($result);
	                $test_mode = $row['test_mode'];
		}
                if($db_error == 0){
	                $query = "SELECT relay_id, relay_child_id FROM relays;";
        	        $results = $conn->query($query);
	                $count = $results->num_rows;
        	        if ($count != 0) {
                	        while ($row = mysqli_fetch_assoc($results)) {
			                $query = "UPDATE messages_out SET payload = 0, sent = 0 WHERE n_id = {$row['relay_id']} AND child_id = {$row['relay_child_id']};";
			                if(!$conn->query($query)){
                        			$db_error = 1;
					}
				}
			}
		}
                if ($db_error == 0) {
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                } else {
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//False Run Time
if($what=="set_false_datetime"){
        $date =  $_GET['date'];
        $time =  $_GET['time'];
        $schedule_test =  $_GET['sch_test_enabled'];
        if ($schedule_test=='true'){$schedule_test = 3;} else {$schedule_test = 0;}
        $query = "UPDATE system SET test_mode = ".$schedule_test.", test_run_time = '".$date." ".$time."';";
        $update_error=0;
        if($conn->query($query)){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//update hide sensors and or relays
if($what=="hide_sensor_relay"){
	if($opp=="update"){
		$user_query = "SELECT `id`, `fullname`, `username` FROM `user` WHERE `username` NOT LIKE 'admin' ORDER by `id`;";
	        $user_results = $conn->query($user_query);
		$user_rowcount = mysqli_num_rows($user_results);
                $update_error = 0;
                $sel_query = "SELECT `sensors`.`id`, `sensors`.`name`, 'Sensor' AS type, `user_display`
                                FROM `sensors`
                                WHERE `sensors`.`zone_id` = 0
                                UNION
                                SELECT `relays`.`id`, `relays`.`name`, 'Relay' AS type, `user_display`
                                FROM `relays`
                                JOIN `zone_relays` zr ON `relays`.`id` = zr.zone_relay_id
                                LEFT JOIN `zone` z ON `z`.`id` = `zr`.`zone_id`
                                WHERE `z`.`type_id` IS NULL OR `z`.`type_id` != 2;";
	        for ($x = 0; $x < $user_rowcount; $x++) {
	                $results = $conn->query($sel_query);
			while ($row = mysqli_fetch_assoc($results)) {
				$user_display = $row["user_display"];
				if (strpos($row["type"], "Sensor") !== false) { $id = $x."s".$row["id"]; } else { $id = $x."r".$row["id"]; }
				$checkbox = 'checkbox'.$id;
				$hide_it =  $_GET[$checkbox];
				if ($hide_it=='true'){
					//set bit for user
					$user_display = $user_display | pow(2,$x);
				} else {
					//clear bit for user
					$user_display = $user_display & ~pow(2,$x);
				}
				if (strpos($row["type"], "Sensor") !== false) {
		               		$query = "UPDATE sensors SET user_display = ".$user_display." WHERE id = ".$row['id']." LIMIT 1;";
				} else {
                	                $query = "UPDATE relays SET user_display = ".$user_display." WHERE id = ".$row['id']." LIMIT 1;";
				}
			        if(!$conn->query($query)){
			        	$update_error=1;
				}
			}
		}
	        if ($update_error==0){
        	        header('Content-type: application/json');
                	echo json_encode(array('Success'=>'Success','Query'=>$query));
	                return;
        	} else {
                	header('Content-type: application/json');
        	        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
	                return;
		}
        }
}

//Openweather Update
if($what=="openweather_update"){
        if($opp=="update"){
	        $CityZip =  $_GET['CityZip'];
        	$inp_City_Zip =  $_GET['inp_City_Zip'];
                $country_code =  $_GET['country_code'];
	        $inp_APIKEY =  $_GET['inp_APIKEY'];
		if ($CityZip == 0) {
	        	$query = "UPDATE system SET country = '".$country_code."', city = '".$inp_City_Zip."', zip = NULL, openweather_api = '".$inp_APIKEY."';";
		} else {
                        $query = "UPDATE system SET country = '".$country_code."', city = NULL, zip = '".$inp_City_Zip."', openweather_api = '".$inp_APIKEY."';";
		}
        	$update_error=0;
	        if($conn->query($query)){
        	        header('Content-type: application/json');
                	echo json_encode(array('Success'=>'Success','Query'=>$query));
	                return;
        	}else{
                	header('Content-type: application/json');
	                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
        	        return;
        	}
	}
}

//Delete MQTT Connection
if($what=="mqtt_connection"){
        if($opp=="delete"){
                $query = "DELETE FROM mqtt WHERE id = '".$wid."';";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//Add or Update MQTT Connection
if($what=="mqtt_broker"){
	$conn_id =  $_GET['conn_id'];
        $inp_Name =  $_GET['inp_Name'];
        $inp_Ip =  $_GET['inp_Ip'];
        $inp_Port =  $_GET['inp_Port'];
        $inp_Username =  $_GET['inp_Username'];
        $inp_Password =  enc_passwd($_GET['inp_Password']);
        $inp_Enabled =  $_GET['inp_Enabled'];
        $inp_Type =  $_GET['inp_Type'];

        if($conn_id == "0"){
        	$query = "INSERT INTO `mqtt`(`name`, `ip`, `port`, `username`, `password`, `enabled`, `type`)
			VALUES ('{$inp_Name}', '{$inp_Ip}', '{$inp_Port}', '{$inp_Username}', '{$inp_Password}', '{$inp_Enabled}', '{$inp_Type}')";
	} else {
        	$query = "UPDATE mqtt SET name = '".$inp_Name."', ip = '".$inp_Ip."', Port = '".$inp_Port."', username = '".$inp_Username."', password = '".$inp_Password."',
			enabled = '".$inp_Enabled."', type = '".$inp_Type."'
			WHERE id = ".$conn_id.";";
	}
        if($conn->query($query)){
        	header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
        	header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//Graph Archiving
if($what=="setup_graph_archive"){
        if($opp=="update"){
	        $archive_status = $_GET['archive_status'];
        	$graph_archive_file = $_GET['graph_archive_file'];
	        if ($archive_status=='true'){$archive_status = '1';} else {$archive_status = '0';}
		$query = "UPDATE graphs SET archive_enable = '".$archive_status."', archive_file = '".$graph_archive_file."';";
                if($conn->query($query)){
                        header('Content-type: application/json');
                        echo json_encode(array('Success'=>'Success','Query'=>$query));
                        return;
                }else{
                        header('Content-type: application/json');
                        echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                        return;
                }
        }
}

//update Node Alerts Notice Interval
if($what=="node_max_child_id"){
        if($opp=="update"){
	        $update_error=0;
        	$sel_query = "SELECT * FROM nodes where status = 'Active' ORDER BY node_id asc";
	        $results = $conn->query($sel_query);
        	while ($row = mysqli_fetch_assoc($results) and $update_error == 0) {
                	if(isset($_GET["max_child_id".$row['node_id']])) {
                        	$max_child_id =  $_GET["max_child_id".$row['node_id']];
        	               	$query = "UPDATE nodes SET max_child_id = '".$max_child_id."' WHERE node_id='".$row['node_id']."' LIMIT 1;";
                	        if(!$conn->query($query)){
                        		$update_error=1;
				}
                        }
                }
        }
        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}

//enable logging of zone_current_state to a file
if($what=="enable_zone_current_state_logs"){
        if($opp=="update"){
                $update_error=0;
                $sel_query = "SELECT zone_id FROM zone_current_state;";
                $results = $conn->query($sel_query);
                while ($row = mysqli_fetch_assoc($results) and $update_error == 0) {
                        $checkbox = 'checkbox_zone_current_state'.$row['zone_id'];
                        $enabled =  $_GET[$checkbox];
                        if ($enabled=='true'){$enabled = 1;} else {$enabled = 0;}
                        $query = "UPDATE zone_current_state SET log_it = '".$enabled."' WHERE zone_id='".$row['zone_id']."' LIMIT 1;";
                        if(!$conn->query($query)){
                                $update_error=1;
                        }
                }
        }

        if($update_error==0){
                header('Content-type: application/json');
                echo json_encode(array('Success'=>'Success','Query'=>$query));
                return;
        }else{
                header('Content-type: application/json');
                echo json_encode(array('Message'=>'Database query failed.\r\nQuery=' . $query));
                return;
        }
}
?>
<?php if(isset($conn)) { $conn->close();} ?>
