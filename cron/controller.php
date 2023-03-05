<?php
#!/usr/bin/php
//set to display debug messages if called with any parameter
if(isset($argv[1])) { $debug_msg = $argv[1]; } else { $debug_msg = -1; }
if ($debug_msg >= 0) {
	echo "\033[36m";
	echo "\n";
	echo "           __  __                             _         \n";
	echo "          |  \/  |                    /\     (_)        \n";
	echo "          | \  / |   __ _  __  __    /  \     _   _ __  \n";
	echo "          | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| \n";
	echo "          | |  | | | (_| |  >  <   / ____ \  | | | |    \n";
	echo "          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    \n";
	echo " \033[0m \n";
	echo "                \033[45m S M A R T   T H E R M O S T A T \033[0m \n";
	echo "\033[31m \n";
	echo "******************************************************************\n";
	echo "*   System Controller Script Version 0.01 Build Date 19/10/2020  *\n";
	echo "*   Update on 10/02/2022                                         *\n";
	echo "*                                        Have Fun - PiHome.eu    *\n";
	echo "******************************************************************\n";
	echo " \033[0m \n";
}

$line_len = 100; //length of seperator lines

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

//Set php script execution time in seconds
ini_set('max_execution_time', 40);
$date_time = date('Y-m-d H:i:s');

//set to indicate controller condition
$start_cause ='';
$stop_cause = '';
$add_on_start_cause ='';
$add_on_stop_cause = '';

//initialise for when used as test variables in none HVAC system
$hvac_state = 0; // 0 = COOL, 1 = HEAT
$cool_relay_type = '';
$fan_relay_type = '';

//array_walk function used to process any pump relays
//array_walk specifies that this function can only take 2 parameters, so unable to pass $conn
function process_pump_relays($command, $relay_id)
{
	global $conn;
	$query = "SELECT * FROM `relays` WHERE `id` = $relay_id LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$relay_id = $row['relay_id']; 
        $relay_child_id = $row['relay_child_id'];
        $relay_type = $row['type'];
        $relay_on_trigger = $row['on_trigger'];
	$query = "SELECT * FROM nodes WHERE id = ".$relay_id." AND status IS NOT NULL LIMIT 1;";
	$result = $conn->query($query);
	$relay_node = mysqli_fetch_array($result);
	$relay_node_id = $relay_node['node_id'];
        $relay_node_type = $relay_node['type'];
        /************************************************************************************
	Pump Wired to Raspberry Pi GPIO Section: Pump Connected Raspberry Pi GPIO.
	*************************************************************************************/
	if ( $relay_node_type == 'GPIO'){
		if ($relay_on_trigger == 1) {
			$relay_on = '1'; //GPIO value to write to turn on attached relay
			$relay_off = '0'; // GPIO value to write to turn off attached relay
		} else {
                        $relay_on = '0'; //GPIO value to write to turn on attached relay
                        $relay_off = '1'; // GPIO value to write to turn off attached relay
		}
    		$relay_status = ($command == '1') ? $relay_on : $relay_off;
    		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Pump: GIOP Relay Status: \033[41m".$relay_status. "\033[0m (".$relay_on."=On, ".$relay_off."=Off) \n";
                $query = "UPDATE messages_out SET sent = '0', payload = '{$command}' WHERE node_id ='$relay_node_id' AND child_id = '$relay_child_id';";
                $conn->query($query);
	}

        /************************************************************************************
	Pump Wired over I2C Interface Make sure you have i2c Interface enabled 
	*************************************************************************************/
	if ( $relay_node_type == 'I2C'){
		exec("python3 /var/www/cron/i2c/i2c_relay.py ".$relay_node_id." ".$relay_child_id." ".$command);
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Pump Relay Board: ".$relay_node_id. " Relay No: ".$relay_child_id." Status: ".$command." \n";
	}

        /************************************************************************************
	Pump Wireless Section: MySensors Wireless or MQTT Relay module for your Pump control.
	*************************************************************************************/
	if ( $relay_node_type == 'MySensor' ||  $relay_node_type == 'MQTT'){
		//update messages_out table with sent status to 0 and payload to as zone status.
		$query = "UPDATE messages_out SET sent = '0', payload = '{$command}' WHERE node_id ='$relay_node_id' AND child_id = '$relay_child_id';";
		$conn->query($query);
	}

        /************************************************************************************
	Sonoff Switch Section: Tasmota WiFi Relay module for your Zone control.
	*************************************************************************************/
	if ( $relay_node_type == 'Tasmota'){
       		$query = "SELECT * FROM http_messages WHERE zone_id = '$zone_id' AND message_type = '$command' LIMIT 1;";
              	$result = $conn->query($query);
       	        $http = mysqli_fetch_array($result);
        	$add_on_msg = $http['command'].' '.$http['parameter'];
       	        $query = "UPDATE messages_out SET sent = '0', payload = '{$add_on_msg}' WHERE node_id ='$relay_node_id' AND child_id = '$relay_child_id';";
        	$conn->query($query);
       	}
}

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Controller Script Started \n";

//Only for debouging
if ($debug_msg == 1) {
	$query = "select zone.id as tz_id, zone.name, zone.status as tz_status, zone_type.type, zone_type.category FROM zone, zone_type WHERE (zone.type_id = zone_type.id) AND status = 1 AND zone.`purge`= 0 ORDER BY index_id asc; ";
	$results = $conn->query($query);
	while ($row = mysqli_fetch_assoc($results)) {
		echo "ID ".$row['tz_id']."\n";
		echo "Name ".$row['name']."\n";
		echo "Status ".$row['tz_status']."\n";
		echo "Type ".$row['category']."\n";
		echo "Category ".$row['category']."\n";
		if ($row["category"] == 1 OR $row["category"] == 2) { echo "Found\n"; }
	}
}

//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
$system_controller_mode = settings($conn, 'mode') & 0b1;

//query to check system controller status
$query = "SELECT * FROM system_controller LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$system_controller_id = $row['id'];
$system_controller_status = $row['status'];
$system_controller_active_status = $row['active_status'];
$system_controller_hysteresis_time = $row['hysteresis_time'];
$system_controller_max_operation_time = $row['max_operation_time'] * 60;
$system_controller_overrun_time = $row['overrun'];
$sc_mode = $row['sc_mode'];
$sc_mode_prev  = $row['sc_mode_prev'];
$sc_weather_factoring  = $row['weather_factoring'];
$sc_weather_sensor_id  = $row['weather_sensor_id'];
//calulate system controller on time in seconds
if ($system_controller_active_status == 1) {
        $now=strtotime(date('Y-m-d H:i:s'));
        $system_controller_on_time = $now - strtotime($row['datetime']);
} else {
         $system_controller_on_time = 0;
}
if ($debug_msg == 1) { echo "System Controller on time - ".$system_controller_on_time." seconds\n"; }

//Get data from relays table
$query = "SELECT * FROM relays WHERE id = ".$row['heat_relay_id']." LIMIT 1;";
$result = $conn->query($query);
$heat_relay = mysqli_fetch_array($result);
$heat_relay_id = $heat_relay['relay_id'];
$heat_relay_child_id = $heat_relay['relay_child_id'];
$heat_relay_on_trigger = $heat_relay['on_trigger'];
if ($heat_relay_on_trigger == 1) {
	$heat_relay_on = '1'; //GPIO value to write to turn on attached relay
        $heat_relay_off = '0'; // GPIO value to write to turn off attached relay
} else {
	$heat_relay_on = '0'; //GPIO value to write to turn on attached relay
	$heat_relay_off = '1'; // GPIO value to write to turn off attached relay
}

//Get data from nodes table
$query = "SELECT * FROM nodes WHERE id = ".$heat_relay_id." AND status IS NOT NULL LIMIT 1;";
$result = $conn->query($query);
$heat_relay_node = mysqli_fetch_array($result);
$heat_relay_node_id = $heat_relay_node['node_id'];
$heat_relay_seen = $heat_relay_node['last_seen'];
$heat_relay_notice = $heat_relay_node['notice_interval'];
$heat_relay_type = $heat_relay_node['type'];
//system operating in HVAC Mode
if ($system_controller_mode == 1) {
        //Relay Control
        // 0 = off
        // 1 = timer
        // 2 = auto
        // 3 = fan
        // 4 = heat
	// 5 = cool

        //Get data from relays table
        $query = "SELECT * FROM relays WHERE id ='".$row['cool_relay_id']."' LIMIT 1;";
        $result = $conn->query($query);
        $cool_relay = mysqli_fetch_array($result);
        $cool_relay_id = $cool_relay['relay_id'];
        $cool_relay_child_id = $cool_relay['relay_child_id'];
	$cool_relay_on_trigger = $cool_relay['on_trigger'];
	if ($cool_relay_on_trigger == 1) {
        	$cool_relay_on = '1'; //GPIO value to write to turn on attached relay
        	$cool_relay_off = '0'; // GPIO value to write to turn off attached relay
	} else {
        	$cool_relay_on = '0'; //GPIO value to write to turn on attached relay
        	$cool_relay_off = '1'; // GPIO value to write to turn off attached relay
	}

        //Get data from nodes table
        $query = "SELECT * FROM nodes WHERE id = ".$cool_relay_id." AND status IS NOT NULL LIMIT 1;";
        $result = $conn->query($query);
        $cool_relay_node = mysqli_fetch_array($result);
        $cool_relay_node_id = $cool_relay_node['node_id'];
        $cool_relay_seen = $cool_relay_node['last_seen'];
        $cool_relay_notice = $cool_relay_node['notice_interval'];
        $cool_relay_type = $cool_relay_node['type'];

        //Get data from relays table
        $query = "SELECT * FROM relays WHERE id ='".$row['fan_relay_id']."' LIMIT 1;";
        $result = $conn->query($query);
        $fan_relay = mysqli_fetch_array($result);
        $fan_relay_id = $fan_relay['relay_id'];
        $fan_relay_child_id = $fan_relay['relay_child_id'];
        $fan_relay_on_trigger = $fan_relay['on_trigger'];
        if ($cool_relay_on_trigger == 1) {
                $fan_relay_on = '1'; //GPIO value to write to turn on attached relay
                $fan_relay_off = '0'; // GPIO value to write to turn off attached relay
        } else {
                $fan_relay_on = '0'; //GPIO value to write to turn on attached relay
                $fan_relay_off = '1'; // GPIO value to write to turn off attached relay
        }

        //Get data from nodes table
        $query = "SELECT * FROM nodes WHERE id = ".$fan_relay_id." AND status IS NOT NULL LIMIT 1;";
        $result = $conn->query($query);
        $fan_relay_node = mysqli_fetch_array($result);
        $fan_relay_node_id = $fan_relay_node['node_id'];
        $fan_relay_seen = $fan_relay_node['last_seen'];
        $fan_relay_notice = $fan_relay_node['notice_interval'];
        $fan_relay_type = $fan_relay_node['type'];
}

if ($system_controller_mode == 0) {
        if ($sc_mode == 0) {
                $current_sc_mode = "OFF";
        } elseif ($sc_mode == 1) {
                $current_sc_mode = "TIMER";
        } elseif ($sc_mode == 2) {
                $current_sc_mode = "CE";
        } elseif ($sc_mode == 3) {
                $current_sc_mode = "HW";
        } elseif ($sc_mode == 4) {
                $current_sc_mode = "BOTH";
        }
        if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Operating in Boiler Mode \n"; }
} else {
        if ($sc_mode == 0) {
                $current_sc_mode = "OFF";
                $timer_mode = "";
        } elseif ($sc_mode == 1) {
                $current_sc_mode = "TIMER";
                $timer_mode = "(HEAT)";
        } elseif ($sc_mode == 2) {
                $current_sc_mode = "TIMER";
                $timer_mode = "(COOL)";
        } elseif ($sc_mode == 3) {
                $current_sc_mode = "TIMER";
                $timer_mode = "(AUTO)";
        } elseif ($sc_mode == 4) {
                $current_sc_mode = "AUTO";
                $timer_mode = "";
        } elseif ($sc_mode == 5) {
                $current_sc_mode = "FAN ONLY";
                $timer_mode = "";
        } elseif ($sc_mode == 6) {
                $current_sc_mode = "HEAT";
                $timer_mode = "";
        } elseif ($sc_mode == 7) {
                $current_sc_mode = "COOL";
                $timer_mode = "";
        }
        if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Operating in HVAC Mode - ".$current_sc_mode.$timer_mode."\n"; }
}

//query to check away status
$query = "SELECT * FROM away LIMIT 1";
$result = $conn->query($query);
$away = mysqli_fetch_array($result);
$away_status = $away['status'];

//query to check holidays status
$query = "SELECT * FROM holidays WHERE '".$date_time."' between start_date_time AND end_date_time AND status = '1' LIMIT 1";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount > 0) {
	$holidays = mysqli_fetch_array($result);
	$holidays_status = $holidays['status'];
}else {
	$holidays_status = 0;
}

//query to get last system controller statues change time
$query = "SELECT * FROM controller_zone_logs WHERE zone_id = '".$system_controller_id."' ORDER BY id desc LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$cz_logs_count = mysqli_num_rows($result);
if ($cz_logs_count > 0){
	$system_controller_start_datetime = $row['start_datetime'];
	$system_controller_stop_datetime = $row['stop_datetime'];
	$system_controller_expoff_datetime = $row['expected_end_date_time'];
}

//query to get last system controller OFF time
/*
$query = "SELECT * FROM controller_zone_logs WHERE zone_id = '".$system_controller_id."' AND stop_datetime IS NOT NULL ORDER BY id desc LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$system_controller_stop_datetime = $row['stop_datetime'];
*/

//query to active network gateway address
$query = "SELECT gateway_address FROM network_settings WHERE primary_interface = 1 LIMIT 1;";
$result = $conn->query($query);
$network = mysqli_fetch_array($result);
if (mysqli_num_rows($result) > 0){
        $n_gateway = $network['gateway_address'];
        $base_addr = substr($n_gateway,0,strrpos($n_gateway,'.')+1);
} else {
        $base_addr = '000.000.000.000';
}

//query to check the live temperature status
$query = "SELECT * FROM livetemp LIMIT 1";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount > 0) {
        $livetemp = mysqli_fetch_array($result);
        $livetemp_zone_id = $livetemp['zone_id'];
        $livetemp_active = $livetemp['active'];
        $livetemp_c = $livetemp['temperature'];
} else {
        $livetemp_zone_id = "";
        $livetemp_active = 0;
        $livetemp_c = 0;
}

//following variable set to 0 on start for array index.
$system_controller = (array) null;
$system_controller_index = '0';
$command_index = '0';
$current_time = date('H:i:s');

//following variable set to current day of the week.
$dow = idate('w');
if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Day of the Week: \033[41m".$dow. "\033[0m \n"; }
if ($debug_msg >= 0 ) { for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n"; }
$sch_active = 0; //set to indicate no active schedules
$query = "SELECT zone.id, zone.status, zone.zone_state, zone.name, zone_type.type, zone_type.category, zone.max_operation_time FROM zone, zone_type WHERE zone.type_id = zone_type.id order by index_id asc;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
        $zone_status=$row['status'];
        $zone_state = $row['zone_state'];
        $zone_id=$row['id'];
        $zone_name=$row['name'];
        $zone_type=$row['type'];
	$zone_category = $row['category'];
        $zone_max_operation_time=$row['max_operation_time'];

        //get the zone controllers for this zone to array
	$query = "SELECT zone_relays.id AS zc_id, cid.node_id as relay_id, zr.relay_child_id, zr.on_trigger, zr.type AS relay_type_id, zone_relays.zone_relay_id, zone_relays.state, zone_relays.current_state, ctype.`type`
	FROM zone_relays
	join relays zr on zone_relay_id = zr.id
	join nodes ctype on zr.relay_id = ctype.id
	join nodes cid on zr.relay_id = cid.id
	WHERE zone_id = '$zone_id';";
        $cresult = $conn->query($query);
        $index = 0;
        $zone_controllers=[];
        while ($crow = mysqli_fetch_assoc($cresult)) {
                $zone_controllers[$index] = array('zc_id' =>$crow['zc_id'], 'controler_id' =>$crow['relay_id'], 'controler_child_id' =>$crow['relay_child_id'], 'relay_type_id' =>$crow['relay_type_id'], 'controler_on_trigger' =>$crow['on_trigger'], 'controller_relay_id' =>$crow['zone_relay_id'], 'zone_controller_state' =>$crow['state'], 'zone_controller_current_state' =>$crow['current_state'], 'zone_controller_type' =>$crow['type'], 'manual_button_override' >=0);
                $index = $index + 1;
        }
	//query to check if zone_current_state record exists tor the zone
	$query = "SELECT * FROM zone_current_state WHERE zone_id = {$zone_id} LIMIT 1;";
	$result = $conn->query($query);
	if (mysqli_num_rows($result)==0){
		//No record in zone_current_statw table, so add
		$query = "INSERT INTO zone_current_state (id, `sync`, `purge`, `zone_id`, `mode`, `status`, `status_prev`, `temp_reading`, `temp_target`, `temp_cut_in`, `temp_cut_out`, `controler_fault`, `controler_seen_time`, `sensor_fault`, `sensor_seen_time`, `sensor_reading_time`, `overrun`) VALUES('{$zone_id}', 0, 0, '{$zone_id}', 0, 0, 0, 0, 0, 0, 0, 0, NULL , 0, NULL, NULL, 0);";
		$conn->query($query);
	}

	//query to get zone previous running status
	$query = "SELECT * FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
	$result = $conn->query($query);
	$zone_current_state = mysqli_fetch_array($result);
	$zone_status_current = $zone_current_state['status'];
        $zone_status_prev = $zone_current_state['status_prev'];
	$zone_overrun_prev = $zone_current_state['overrun'];
	$zone_current_mode = $zone_current_state['mode'];
	if (($zone_id == $livetemp_zone_id) && ($livetemp_active == 1) && ($zone_current_mode == '0')) {
                $query = "UPDATE livetemp SET active = 0 WHERE zone_id = {$zone_id};";
                $conn->query($query);
        }

	// process if a sensor is attached to this zone
	if ($zone_category == 0 || $zone_category == 1 || $zone_category == 3 || $zone_category == 4 || $zone_category == 5) {
                $query = "SELECT zone_sensors.*, sensors.sensor_id, sensors.sensor_child_id, sensors.sensor_type_id, sensors.frost_controller FROM  zone_sensors, sensors WHERE (zone_sensors.zone_sensor_id = sensors.id) AND zone_sensors.zone_id = '{$zone_id}' LIMIT 1;";
                $result = $conn->query($query);
                $sensor_rowcount=mysqli_num_rows($result);
                if ($sensor_rowcount != 0) {
                        $sensor = mysqli_fetch_array($result);
                        $zone_min_c=$sensor['min_c'];
                        $zone_max_c=$sensor['max_c'];
                        $zone_hysteresis_time=$sensor['hysteresis_time'];
                        $zone_sp_deadband=$sensor['sp_deadband'];
                        $zone_sensor_id=$sensor['sensor_id'];
                        $zone_sensor_child_id=$sensor['sensor_child_id'];
                        $default_c =$sensor['default_c'];
                        $sensor_type_id = $sensor['sensor_type_id'];
                        $hvac_frost_controller = $sensor['frost_controller'];
                        $zone_maintain_default=$sensor['default_m'];

                        $query = "SELECT node_id, name FROM nodes WHERE id = '{$zone_sensor_id}' LIMIT 1;";
                        $result = $conn->query($query);
                        $nodes = mysqli_fetch_array($result);
                        $zone_node_id=$nodes['node_id'];
                        $node_name=$nodes['name'];

                        //query to get temperature from messages_in_view_24h table view
                        $query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$zone_node_id}' AND child_id = {$zone_sensor_child_id} LIMIT 1;";
                        $result = $conn->query($query);
                        $rowcount=mysqli_num_rows($result);
                        if ($rowcount == 0) {
                                // catch GPIO switch sensor not changed in last 24 hours, so get previous value
                                if (strpos($node_name, 'Switch') !== false) {
                                        $query = "SELECT * FROM messages_in  WHERE node_id = '{$zone_node_id}' AND child_id = {$zone_sensor_child_id} ORDER BY datetime desc LIMIT 1;";
                                        $result = $conn->query($query);
                                        $rowcount=mysqli_num_rows($result);
                                }
                        }
                        if ($rowcount > 0) {
                                $msg_out = mysqli_fetch_array($result);
                                $zone_c = $msg_out['payload'];
                                $temp_reading_time = $msg_out['datetime'];
                        } else {
                                $zone_c = "";
                                $temp_reading_time = "";
                        }
                }
        }
        // only process active zones with a sensor or a category 2 type zone
        if ($zone_status == 1 && ($sensor_rowcount != 0 || $zone_category == 2)) {
                $rval=get_schedule_status($conn, $zone_id,$holidays_status,$away_status);
                $sch_status = $rval['sch_status'];
                $sch_name = $rval['sch_name'];
                $away_sch = $rval['away_sch'];
                if ($sch_active == 0 && $sch_status == '1') { $sch_active = 1; }
                if($rval['sch_count'] == 0){
                        $sch_status = 0;
                        $sch_c = 0;
                        $sch_holidays = '0';
                }else{
                        $sch_end_time = date('H:i:s', $rval['end_time']);
                        $sch_status = $rval['sch_status'];
                        $time_id = $rval['time_id'];
                        $query = "SELECT temperature, coop, holidays_id FROM schedule_daily_time_zone WHERE schedule_daily_time_id = {$time_id} AND zone_id = {$zone_id} LIMIT 1;";
                        $result = $conn->query($query);
                        $schedule = mysqli_fetch_array($result);
                        $sch_c = $schedule['temperature'];
                        $sch_coop = $schedule['coop'];

                        if ($schedule['holidays_id']>0) {
                                $sch_holidays = '1';
                        }else{
                                $sch_holidays = '0';
                        }
                }

                //query to check override status and get temperature from override table
                $query = "SELECT * FROM override WHERE zone_id = {$zone_id} LIMIT 1;";
                $result = $conn->query($query);
                if (mysqli_num_rows($result) != 0){
                        $override = mysqli_fetch_array($result);
                        $zone_override_status = $override['status'];
                        $override_c = $override['temperature'];
                }else {
                        $zone_override_status = '0';
                }

                if ($zone_category <> 3) {
                        $manual_button_override = 0;
	              	//Calculate zone fail using the zone_controllers array
        	        for ($crow = 0; $crow < count($zone_controllers); $crow++){
                	        $zone_controler_id = $zone_controllers[$crow]["controler_id"];
                        	$zone_controler_child_id = $zone_controllers[$crow]["controler_child_id"];
	                        $zone_fault = 0;
        	                $zone_ctr_fault = 0;
                	        $zone_sensor_fault = 0;

	                        //Get data from nodes table
        	                $query = "SELECT * FROM nodes WHERE node_id ='$zone_controler_id' AND status IS NOT NULL LIMIT 1;";
                	        $result = $conn->query($query);
                        	$controler_node = mysqli_fetch_array($result);
	                        $controler_type = $controler_node['type'];
        	                $controler_seen = $controler_node['last_seen'];
                	        $controler_notice = $controler_node['notice_interval'];
                        	if($controler_notice > 0){
                                	$now=strtotime(date('Y-m-d H:i:s'));
	                                $controler_seen_time = strtotime($controler_seen);
        	                        if ($controler_seen_time  < ($now - ($controler_notice*60))){
                	                        $zone_fault = 1;
                        	                $zone_ctr_fault = 1;
                                	        if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone valve communication timeout for This Zone. Node Last Seen: ".$controler_seen."\n"; }
	                                }
        	                }

                                // if add-on controller then process state change from GUI or api call
                                if ($zone_category == 2) {
//                                        $current_state = $zone_controllers[$crow]["zone_controller_current_state"];
                                        $current_state = $zone_status_prev;
                                        $add_on_state = $zone_controllers[$crow]["zone_controller_state"];
                                        $zone_controler_child_id = $zone_controllers[$crow]["controler_child_id"];
                                        if ($zone_current_mode == "74" || $zone_current_mode == "75") {
                                                if ($sch_status == 1) {
                                                        if ($current_state != $add_on_state) {
                                                                $query = "UPDATE override SET status = 1, sync = '0' WHERE zone_id = {$zone_id};";
                                                                $conn->query($query);
                                                        }
                                                } else {
                                                        if ($zone_override_status == 1) {
                                                                $query = "UPDATE override SET status = 0, sync = '0' WHERE zone_id = {$zone_id};";
                                                                $conn->query($query);
                                                        }
                                                }
                                        }
                                        // check is switch has manually changed the ON/OFF state
                                        // for zones with multiple controllers - only capture the first change
                                        if ($controler_type == 'Tasmota' && $manual_button_override == 0) {
                                                if ($base_addr == '000.000.000.000') {
                                                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - NO Gateway Address is Set \n";
                                                } else {
                                                        $query = "SELECT * FROM http_messages WHERE zone_id = '{$zone_id}' AND message_type = 1 LIMIT 1;";
                                                        $result = $conn->query($query);
                                                        $http = mysqli_fetch_array($result);
                                                        $url = "http://".$base_addr.$zone_controler_child_id."/cm?cmnd=power";
                                                        $ch=curl_init();
                                                        $timeout=1;
                                                        curl_setopt($ch, CURLOPT_URL, $url);
                                                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                                        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
                                                        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
                                                        $result=curl_exec($ch);
                                                        curl_close($ch);
                                                        if (strlen($result) > 0) {
                                                                $result = utf8_encode($result);
                                                                $resp = json_decode($result, true);
                                                                if ($resp[strtoupper($http['command'])] == 'ON' ) {
                                                                        $new_add_on_state = 1;
                                                                } else {
                                                                        $new_add_on_state = 0;
                                                                }
                                                                if ($manual_button_override == 0 && $current_state != $new_add_on_state) {
                                                                        $manual_button_override = 1;
                                                                }
                                                        }
                                                }
                                        }
                                        if ($debug_msg == 1 || $debug_msg == 2) { echo "zone_controler_id ".$zone_controler_id.", zone_current_mode ".$zone_current_mode.", current_state ".$current_state.", add_on_state ".$add_on_state.", manual_button_override ".$manual_button_override.", sch_status ".$sch_status."\n"; }
                                }
                                $zone_controllers[$crow]['manual_button_override'] = $manual_button_override;
                	} // end for ($crow = 0; $crow < count($zone_controllers); $crow++)

			// if there has been an external update to any of the relays associated with this zone (both Tasmota and MySensor), then update MaxAir to capture the new state
			// will update the following tables - messages_out, zone, zone_relays, zone_current state and override
			// the Home screen will be updated once this script has executed
                        if ($manual_button_override == 1) {
                                $add_on_state = $new_add_on_state;
                                $zone_c = $new_add_on_state;
                                $query = "SELECT * FROM messages_out WHERE zone_id = '{$zone_id}'";
                                $results = $conn->query($query);
                                while ($row = mysqli_fetch_assoc($results)) {
                                        $id= $row['id'];
                                        $node_id = $row['node_id'];
                                        $query = "SELECT type FROM nodes WHERE node_id = '{$node_id}' LIMIT 1";
                                        $nresults = $conn->query($query);
                                        $nrow = mysqli_fetch_assoc($nresults);
                                        if ($nrow['type'] == 'Tasmota') {
                                                if($add_on_state == 0){ $message_type="0"; }else{ $message_type="1"; }
                                                $query = "SELECT  command, parameter FROM http_messages WHERE node_id = '{$node_id}' AND message_type = '{$message_type}' LIMIT 1;";
                                                $hresults = $conn->query($query);
                                                $hrow = mysqli_fetch_assoc($hresults);
                                                $set =  $message_type;
                                                $payload = $hrow['command']." ".$hrow['parameter'];
                                        } else {
                                                if($add_on_state == 0){
                                                        $set="0";
                                                        $payload = "0";
                                                }else{
                                                        $set="1";
                                                        $payload = "1";
                                                }
                                        }
                                        $time = date("Y-m-d H:i:s");
                                        $query = "UPDATE messages_out SET payload = '{$payload}', datetime = '{$time}', sent = '0' WHERE id = '{$id}';";
                                        $conn->query($query);
                                }

                                $query = "UPDATE zone_relays SET state = '{$set}', current_state = '{$set}' WHERE zone_id = '{$zone_id}';";
                                $conn->query($query);
                                if ($sch_active == '0') {
                                        if ($add_on_state == 0) { $mode = 0; } else { $mode = 114; }
                                } else {
                                        if ($add_on_state == 0) { $mode = 75; } else { $mode = 74; }
                                }
                                $query = "UPDATE zone_current_state SET mode  = '{$mode}', status = '{$set}', status_prev = '{$zone_status_current}' WHERE zone_id = '{zone_id}';";
                                $conn->query($query);

                                $query = "UPDATE zone SET zone_state = {$set} WHERE id = {$zone_id};";
                                $conn->query($query);
                                $zone_state = $set;
                                if ($sch_status == 1) {
                                        if ($zone_override_status == 0) {
                                                $query = "UPDATE override SET status = 1, sync = '0' WHERE zone_id = {$zone_id};";
                                                $conn->query($query);
                                        }
                                } else {
                                        if ($zone_override_status == 1) {
                                                $query = "UPDATE override SET status = 0, sync = '0' WHERE zone_id = {$zone_id};";
                                                $conn->query($query);
                                        }
                                }
                        }
                } else {
                        $zone_fault = 0;
                        $zone_ctr_fault = 0;
                        $zone_sensor_fault = 0;
                        $manual_button_override = 0;
                        $controler_seen = "";
                }

		//query to check boost status and get temperature from boost table
		$query = "SELECT * FROM boost WHERE zone_id = {$zone_id} AND status = 1 LIMIT 1;";
		$result = $conn->query($query);
		if (mysqli_num_rows($result) != 0){
			$boost = mysqli_fetch_array($result);
			$boost_status = $boost['status'];
			$boost_time = $boost['time'];
			$boost_c = $boost['temperature'];
			$boost_minute = $boost['minute'];
			$boost_mode = $boost['hvac_mode'];
		} else {
			$boost_status = '0';
		}

		//check boost time is passed, if it passed then update db and set to boost status to 0
		if ($boost_status=='1'){
			$phpdate = strtotime( $boost_time );
			$boost_time = $phpdate + ($boost_minute * 60);
			$now=strtotime(date('Y-m-d H:i:s'));
			if (($boost_time > $now) && ($boost_status=='1')){
				$boost_active='1';
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Boost is Active for This Zone \n"; }
			}elseif (($boost_time < $now) && ($boost_status=='1')){
				$boost_active='0';
				//You can comment out if you dont have Boost Button Console installed.
				$query = "SELECT * FROM boost WHERE zone_id ={$row['id']} AND status = '1';";
				$bresults = $conn->query($query);
				$brow = mysqli_fetch_assoc($bresults);
				$brow['boost_button_id'];
				$brow['boost_button_child_id'];
				$query = "UPDATE messages_out SET payload = '{$boost_active}', sent = '0' WHERE zone_id = {$row['id']} AND node_id = {$brow['boost_button_id']} AND child_id = {$brow['boost_button_child_id']} LIMIT 1;";
				$conn->query($query);
				//update Boost Records in database
				$query = "UPDATE boost SET status = '{$boost_active}', sync = '0' WHERE zone_id = {$row['id']} AND status = '1';";
				$conn->query($query);
			}else {
				$boost_active='0';
			}
		}else {
			$boost_active='0';
		}

		//Check Zones with sensor associated
		if ($zone_category == 0 || $zone_category == 1 || $zone_category == 3 || $zone_category == 4 || $zone_category == 5) {
			//query to check night climate status and get temperature from night climate table
			//$query = "select * from schedule_night_climat_zone_view WHERE zone_id = {$zone_id} LIMIT 1;";
			$query = "SELECT * from schedule_night_climat_zone_view WHERE ((`end`>`start` AND CURTIME() between `start` AND `end`) OR (`end`<`start` AND CURTIME()<`end`) OR (`end`<`start` AND CURTIME()>`start`)) AND zone_id = {$zone_id} AND time_status = '1' AND tz_status = '1' AND (WeekDays & (1 << {$dow})) > 0 LIMIT 1;";
			$result = $conn->query($query);
			if (mysqli_num_rows($result) != 0){
				$night_climate = mysqli_fetch_array($result);
				$nc_time_status = $night_climate['time_status'];
				$nc_zone_status = $night_climate['tz_status'];
				$nc_zone_id = $night_climate['zone_id'];
				$nc_start_time = $night_climate['start'];
				$nc_end_time = $night_climate['end'];
				$nc_min_c = $night_climate['min_temperature'];
				$nc_max_c = $night_climate['max_temperature'];
				$nc_weekday = $night_climate['WeekDays'] & (1 << $dow);
				//work out slope of the temperature graph
				$query = "SELECT  sensors_id AS node_id, sensor_child_id AS child_id FROM zone_view WHERE id = ".$nc_zone_id." LIMIT 1;";
				$result = $conn->query($query);
				$row = mysqli_fetch_assoc($result);
				$node_id = $row['node_id'];
				$child_id = $row['child_id'];
				$query = "SELECT payload, datetime FROM  `messages_in_view_24h` WHERE node_id = ".$node_id." AND child_id = ".$child_id.";";
				$result = $conn->query($query);
				$index = 0;
				$temp1 = 0;
				$temp2 = 0;
				while ($row = mysqli_fetch_assoc($result)) {
        				if ($index < 10 ){
                				$temp1 += $row['payload'];
        				} else {
                				$temp2 += $row['payload'];
        				}
        				$index = $index + 1;
        				if( $index == 20 ) {
                				break;
        				}
				}
				$avg_temp1 = $temp1/10;
				$avg_temp2 = $temp2/10;
				if ($avg_temp2 < $avg_temp1) { $nc_slope = 1; } elseif($avg_temp2 == $avg_temp1) { $nc_slope = 0; } else { $nc_slope = -1; }

				//night climate time to add 10 minuts for record purpose
				$timestamp =strtotime(date('H:i:s')) + 60 *10;
				$nc_end_time_rc = date('H:i:s', $timestamp);
				$current_time = date('H:i:s');
				if (($away_sch == 0) && (TimeIsBetweenTwoTimes($current_time, $nc_start_time, $nc_end_time)) && ($nc_time_status =='1') && ($nc_zone_status =='1') && ($nc_weekday > 0)) {
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Night Climate Enabled for This Zone \n"; }
					$night_climate_status='1';
				} else {
					$night_climate_status='0';
				}
			}else {
				$night_climate_status='0';
			}

			//Get Weather Temperature
                        $weather_fact = 0;
                        if ($system_controller_mode == 0 && $sc_weather_factoring == 1) {
                                if ($sc_weather_sensor_id == 0) {
                                        $weather_sensor_node_id = 1;
                                        $weather_sensor_child_id = 0;
                                } else {
                                        $query = "SELECT sensor_id, sensor_child_id FROM sensors WHERE id = {$sc_weather_sensor_id} LIMIT 1;";
                                        $result = $conn->query($query);
                                        $sc_weather = mysqli_fetch_array($result);
                                        $weather_sensor_id = $sc_weather['sensor_id'];
                                        $weather_sensor_child_id = $sc_weather['sensor_child_id'];
                                        $query = "SELECT * FROM nodes WHERE id = {$weather_sensor_id} LIMIT 1;";
                                        $result = $conn->query($query);
                                        $sc_weather = mysqli_fetch_array($result);
                                        $weather_sensor_node_id = $sc_weather['node_id'];
                                }
                                $query = "SELECT * FROM messages_in WHERE node_id = '{$weather_sensor_node_id}' AND child_id = {$weather_sensor_child_id} ORDER BY id desc LIMIT 1";
                                $result = $conn->query($query);
                                $rowcount=mysqli_num_rows($result);
                                if($rowcount > 0) {
                                        $weather_temp = mysqli_fetch_array($result);
                                        $weather_c = $weather_temp['payload'];
                                        //    1    00-05    0.3
                                        //    2    06-10    0.4
                                        //    3    11-15    0.5
                                        //    4    16-20    0.6
                                        //    5    21-30    0.7
					if ($weather_c <= 5 ) {$weather_fact = 0.3;} elseif ($weather_c <= 10 ) {$weather_fact = 0.4;} elseif ($weather_c <= 15 ) {$weather_fact = 0.5;} elseif ($weather_c <= 20 ) {$weather_fact = 0.6;} elseif ($weather_c <= 30 ) {$weather_fact = 0.7;}
				}
			}
			//Following to decide which temperature is target temperature
                        if ($livetemp_active=='1' && $livetemp_zone_id == $zone_id) {
                                $target_c=$livetemp_c;
                        } elseif ($boost_active=='1') {
				$target_c=$boost_c;
			} elseif ($night_climate_status =='1') {
				$target_c=$nc_min_c;
			} elseif($zone_override_status=='1') {
				$target_c=$override_c;
			} elseif($sch_status=='0') {
				$target_c=$default_c;
			} else {
                                $target_c=$sch_c;
			}
			//calculate cutin/cut out temperatures
			$temp_cut_out_rising = $target_c - $weather_fact - $zone_sp_deadband;
       	                $temp_cut_out_falling = $target_c - $weather_fact + $zone_sp_deadband;
               	        $temp_cut_in = $target_c - $weather_fact - $zone_sp_deadband;
			if ($night_climate_status == '0') {
				$temp_cut_out = $target_c - $weather_fact;
			} else {
       	                        $temp_cut_out = $nc_max_c - $weather_fact;
			}
			//check if hysteresis is passed its time or not
			$hysteresis='0';
			// only ptocess hysteresis for EU systems when stop time is set
			if ($system_controller_mode == 0 && isset($system_controller_stop_datetime)){
				$system_controller_time = strtotime( $system_controller_stop_datetime );
				$hysteresis_time = $system_controller_time + ($system_controller_hysteresis_time * 60);
				$now=strtotime(date('Y-m-d H:i:s'));
				if ($hysteresis_time > $now){
					$hysteresis='1';
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Hysteresis time: ".date('Y-m-d H:i:s',$hysteresis_time)." \n"; }
				} else {
					$hysteresis='0';
				}
			}

			//Check sensor notice interval and notice logic
			$query = "SELECT * FROM nodes WHERE id ='$zone_sensor_id' AND status IS NOT NULL LIMIT 1;";
			$result = $conn->query($query);
			$sensor_node = mysqli_fetch_array($result);
			$sensor_seen = $sensor_node['last_seen']; //not using this cause it updates on battery update
			$sensor_notice = $sensor_node['notice_interval'];
			if($sensor_notice > 0) {
				$now=strtotime(date('Y-m-d H:i:s'));
				$sensor_seen_time = strtotime($temp_reading_time); //using time from messages_in
				if ($sensor_seen_time  < ($now - ($sensor_notice*60))){
					$zone_fault = 1;
					$zone_sensor_fault = 1;  
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Temperature sensor communication timeout for This Zone. Last temperature reading: ".$temp_reading_time."\n"; }
				}
			}

			//Check system controller notice interval and notice logic
			if($heat_relay_notice > 0){
				$now=strtotime(date('Y-m-d H:i:s'));
				$heat_relay_seen_time = strtotime($heat_relay_seen);
				if ($heat_relay_seen_time  < ($now - ($heat_relay_notice*60))){
					$zone_fault = 1;
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller controler communication timeout. System Controller Last Seen: ".$heat_relay_seen."\n"; }
				}
			}
                //create array zone states, used to determine if new zone log table entry is required
                $z_state[$zone_id] = $zone_state;
		// end Check Zone category 0 or 1
		} 

                // check frost protection linked to this zone controller
                if ($system_controller_mode == 0) {
                        $frost_controller = $zone_controllers[0]["controller_relay_id"];
                } else {
                        $frost_controller = $hvac_frost_controller;
                }
                $query = "SELECT sensors.sensor_id, sensors.sensor_child_id, sensors.name AS sensor_name, sensors.frost_temp, relays.name AS controller_name FROM sensors, relays WHERE (sensors.frost_controller = relays.id) AND frost_controller = ".$frost_controller.";";
                $fresults = $conn->query($query);
                $frost_active = 0;
                $frost_target_c = 99;
                while ($row = mysqli_fetch_assoc($fresults)) {
                        $frost_c = $row["frost_temp"];
                        $query = "SELECT node_id FROM nodes WHERE id = ".$row['sensor_id']." LIMIT 1;";
                        $result = $conn->query($query);
                        $frost_sensor_node = mysqli_fetch_array($result);
                        $frost_sensor_node_id = $frost_sensor_node['node_id'];
                        //query to get temperature from messages_in_view_24h table view
                        $query = "SELECT * FROM messages_in_view_24h WHERE node_id = '".$frost_sensor_node_id."' AND child_id = ".$row['sensor_child_id']." LIMIT 1;";
                        $result = $conn->query($query);
                        $rowcount=mysqli_num_rows($result);
                        if ($rowcount != 0) {
	                        $msg_in = mysqli_fetch_array($result);
        	                $frost_sensor_c = $msg_in['payload'];
                	        //enable frost protection if any sensor temparature attached to the zone is below the threshold
                        	if (($frost_sensor_c < $frost_c-$zone_sp_deadband) && ($frost_c != 0)) {
                                	$frost_active = 1;
	                                //use the lowest value if multiple values
        	                        if ($frost_c < $frost_target_c) { $frost_target_c = $frost_c; }
                	        } else if (($frost_sensor_c >= $frost_target_c-$zone_sp_deadband) && ($frost_sensor_c < $frost_target_c)) {
                        	        $frost_active = 2;
                                	//use the lowest value if multiple values
                                	if ($frost_c < $frost_target_c) { $frost_target_c = $frost_c; }
                        	}
                        	if ($debug_msg == 1) { echo "Sensor Name - ".$row['sensor_name'].", Frost Target Temperture - ".$frost_target_c.", Frost Sensor Temperature - ".$frost_sensor_c."\n"; }
                	}
		}

		//initialize variables
		$zone_mode = 0;
                if ($sc_mode != 0 && $away_status=='1' && $away_sch == 1) { $active_sc_mode = 1; } else { $active_sc_mode = $sc_mode; }
//		$hvac_state = 0; // 0 = COOL, 1 = HEAT
		if ($zone_fault == '0'){
			if ($zone_category == 0) {
	                        //check system controller not in OFF mode
        	                if ($sc_mode != 0) {
					if ($frost_active == 1){
						$zone_status="1";
						$zone_mode = 21;
						$start_cause="Frost Protection";
						$zone_state= 1;
					} elseif ($frost_active == 2) {
						$zone_status=$zone_status_prev;
						$zone_mode = 22 - $zone_status_prev;
						$start_cause="Frost Protection Deadband";
						$stop_cause="Frost Protection Deadband";
						$zone_state = $zone_status_prev;
					} elseif ($frost_active == 0 && $zone_c < $zone_max_c && $hysteresis=='0') {
						//system controller has not exceeded max running timw
						if (($system_controller_on_time < $system_controller_max_operation_time) || ($system_controller_max_operation_time == 0)) {
							if ($active_sc_mode == 4 || ($active_sc_mode == 2 && strpos($zone_type, 'Heating') !== false)  || ($active_sc_mode == 3 && strpos($zone_type, 'Water') !== false)) {
       	                                        		if ($zone_c < $temp_cut_out_rising) {
               	                                	        	$zone_status="1";
                       	                	                        $zone_mode = 141;
                               		                                $start_cause="Manual Start";
                        	               	                        $zone_state = 1;
                	                               	        }
        	                                               	if (($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)){
	                                                               	$zone_status=$zone_status_prev;
                                                                	$zone_mode = 142 - $zone_status_prev;
       	                                                	        $start_cause="Manual Target Deadband";
               	                                	                $stop_cause="Manual Target Deadband";
                       	                	                        $zone_state = $zone_status_prev;
                               		                        }
                        	               	                if (($zone_c >= $temp_cut_out)){
                	                               	                $zone_status="0";
        	                                               	        $zone_mode = 140;
	                                                               	$stop_cause="Manual Target C Achieved";
                                                                	$zone_state = 0;
       	                                                	}
							} elseif ($away_status=='0' || ($away_status=='1' && $away_sch == 1)) {
								if (($holidays_status=='0') || ($sch_holidays=='1')) {
									if (($sch_active) && ($zone_override_status=='1')) {
										$zone_status="0";
										$stop_cause="Override Finished";
                                                                        	if ($zone_c < $temp_cut_out_rising){
                                                                                	$zone_status="1";
                                                                                        $zone_mode = 71;
                                                                                        $start_cause="Schedule Override Started";
                                                                                        $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
                                                                                        $zone_state = 1;
                                                                               	}
                                                                                if ($zone_c >= $temp_cut_out_rising && ($zone_c < $temp_cut_out)){
                                                                                	$zone_status=$zone_status_prev;
                                                                                        $zone_mode = 72 - $zone_status_prev;
                                                                                        $start_cause="Schedule Override Target Deadband";
                                                                                        $stop_cause="Schedule Override Target Deadband";
                                                                                        $zone_state = $zone_status_prev;
                                                                               	}
                                                                                if ($zone_c >= $temp_cut_out){
                                                                                	$zone_status="0";
                                                                                        $zone_mode = 70;
                                                                                        $stop_cause="Schedule Override Target C Achieved";
                                                                                        $zone_state = 0;
                                                                            	}
									} elseif($boost_status=='0'){
										$zone_status="0";
										$stop_cause="Boost Finished";
										if ($night_climate_status =='0') {
											if (($sch_status =='1') && ($zone_c < $temp_cut_out_rising) && (($sch_coop == 0) ||($system_controller_active_status == "1"))){
												$zone_status="1";
												$zone_mode = 81;
												$start_cause="Schedule Started";
												$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
												$zone_state = 1;
											}
											if (($system_controller_mode == 0) && ($sch_status =='1') && ($zone_c < $temp_cut_out_rising)&&($sch_coop == 1 && $system_controller_mode == 0)&&($system_controller_active_status == "0")){
												$zone_status="0";
												$zone_mode = 83;
												$stop_cause="Coop Start Schedule Waiting for Controller Start";
												$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
												$zone_state = 0;
											}
											if (($sch_status =='1') && ($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)){
												$zone_status=$zone_status_prev;
												$zone_mode = 82 - $zone_status_prev;
												$start_cause="Schedule Target Deadband";
												$stop_cause="Schedule Target Deadband";
												$zone_state = $zone_status_prev;
											}
											if (($sch_status =='1') && ($zone_c >= $temp_cut_out)){
												$zone_status="0";
												$zone_mode = 80;
												$stop_cause="Schedule Target C Achieved";
												$zone_state = 0;
											}
											if (($sch_status =='0') &&($sch_holidays=='1')){
												$zone_status="0";
												$zone_mode = 40;
												$stop_cause="Holidays - No Schedule";
												$zone_state = 0;
											}
											if (($sch_status =='0') && ($sch_holidays=='0')) {
												$zone_status="0";
												$zone_mode = 0;
												$stop_cause="No Schedule";
												$zone_state = 0;
											}
										}elseif(($night_climate_status=='1') && ($zone_c < $temp_cut_out_rising)){
											$zone_status="1";
											$zone_mode = 51;
											$start_cause="Night Climate";
											$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
											$zone_state = 1;
										}elseif(($night_climate_status=='1') && ($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)){
											$zone_status=$zone_status_prev;
											$zone_mode = 52 - $zone_status_prev;
											$start_cause="Night Climate Deadband";
											$stop_cause="Night Climate Deadband";
											$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
											$zone_state = $zone_status_prev;
										}elseif(($night_climate_status=='1') && ($zone_c >= $temp_cut_out)){
											$zone_status="0";
											$zone_mode = 50;
											$stop_cause="Night Climate C Reached";
											$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
											$zone_state = 0;
										}
									}elseif (($boost_status=='1') && ($zone_c < $temp_cut_out_rising)) {
										$zone_status="1";
										$zone_mode = 61;
										$start_cause="Boost Active";
										$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
										$zone_state = 1;
									}elseif (($boost_status=='1') && ($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)) {
										$zone_status=$zone_status_prev;
										$zone_mode = 62 - $zone_status_prev;
										$start_cause="Boost Target Deadband";
										$stop_cause="Boost Target Deadband";
										$zone_state = $zone_status_prev;
									}elseif (($boost_status=='1') && ($zone_c >= $temp_cut_out)) {
										$zone_status="0";
										$zone_mode = 60;
										$stop_cause="Boost Target C Achived";
										$zone_state = 0;
									} //end if($boost_status=='0')
								}elseif(($holidays_status=='1') && ($sch_holidays=='0')){
									$zone_status="0";
									$zone_mode = 40;
									$stop_cause="Holiday Active";
									$zone_state = 0;
								}
							}elseif($away_status=='1' && $away_sch == 0){
								$zone_status="0";
								$zone_mode = 90;
								$stop_cause="Away Active";
								$zone_state = 0;
							}
						} elseif ($system_controller_max_operation_time != 0) {
                                        	        $zone_status="0";
       	                        	                $zone_mode = (floor($zone_status_prev/10)*10) + 8;
               	        	                        $stop_cause="Max Running Time Exceeded - Hysteresis active";
                	       	                        $zone_state = 0;
						}
					}elseif($zone_c >= $zone_max_c){
						$zone_status="0";
						$zone_mode = 30;
						$stop_cause="Zone Reached its Max Temperature ".$zone_max_c;
						$zone_state = 0;
					} elseif ($hysteresis == '1' && floor($zone_status_prev%10) != 8) {
						$zone_status="0";
						$zone_mode = 100;
						$stop_cause="Hysteresis active ";
						$zone_state = 0;
					}
				} else {
					$zone_status="0";
					$zone_mode = 0;
					$stop_cause="System is OFF";
					$zone_state = 0;
				}

			// process category 3 zone (HVAC)
			} elseif ($zone_category == 3 || $zone_category == 4) {
                                //check system controller not in OFF mode
                                if ($sc_mode != 0) {
	                                if ($frost_active == 1){
        	                                $zone_status="1";
                	                        $zone_mode = 21;
                        	                $start_cause="Frost Protection";
                                	        $zone_state= 1;
						$hvac_state = 1;
	                                } elseif ($frost_active == 2) {
        	                                $zone_status=$zone_status_prev;
                	                        $zone_mode = 22 - $zone_status_prev;
                        	                $start_cause="Frost Protection Deadband";
                                	        $stop_cause="Frost Protection Deadband";
                                        	$zone_state = $zone_status_prev;
						$hvac_state = 1;
        	                        } elseif (($frost_active == 0) && ($zone_c < $zone_max_c) && ($zone_c > $zone_min_c)) {
						if ($away_status=='0' || ($away_status=='1' && $away_sch == 1)){
							if (($holidays_status=='0') || ($sch_holidays=='1')) {
								if ($boost_status=='0') {
        			        	                        $zone_status="0";
                			        	                $stop_cause="Boost Finished";
									switch ($active_sc_mode) {
        	                			        	        case 0: // OFF
                	                			        	        $zone_status="0";
                        	                			        	$zone_mode = 0;
	                                	                			$stop_cause="HVAC OFF ";
			                                        	        	$zone_state = 0;
			        	                                        	break;
                        	                                                case 1: // TIMER mode HEAT Only
											if ($sch_status == '1') {
	                                	                                                if ($zone_c <= $temp_cut_out_rising) {
        	                                	                                                $zone_status="1";
                	                                	                                        $zone_mode = 81;
                        	                                	                                $start_cause="HVAC Heat Cycle Started ";
                                	                                	                        $zone_state = 1;
                                        	                                	                $hvac_state = 1;
                                                	                                	} elseif ($zone_c > $temp_cut_out_rising) {
                                                        	                                	$zone_status="0";
	                                                                	                        $zone_mode = 80;
        	                                                                	                $stop_cause="HVAC Climate C Reached ";
                	                                                                	        $zone_state = 0;
													$hvac_state = 1;
                                	                	                                }
											}
                                                	                                break;
                                                        	                case 2: // TIMER mode COOL Only
                                                                	                if ($sch_status == '1') {
                                                                        	                if ($zone_c >= $temp_cut_out_falling) {
                                                                                	                $zone_status="1";
                                                                                        	        $zone_mode = 86;
                                                                                                	$start_cause="HVAC Cool Cycle Started ";
	                                                                                                $zone_state = 1;
        	                                                                                        $hvac_state = 0;
                	                                                                        } elseif ($zone_c < $temp_cut_out_falling) {
                        	                                                                        $zone_status="0";
                                	                                                                $zone_mode = 80;
                                        	                                                        $stop_cause="HVAC Climate C Reached ";
                                                	                                                $zone_state = 0;
													$hvac_state = 0;
                                                                	                        }
                                                                        	        }
                                                                                	break;
	                                                                        case 3: // TIMER mode AUTO 
        	                                                                        if ($sch_status == '1') {
                	                                                                        if ($zone_c <= $temp_cut_out_rising) {
                        	                                                                        $zone_status="1";
                                	                                                                $zone_mode = 81;
                                        	                                                        $start_cause="HVAC Heat Cycle Started ";
                                                	                                                $zone_state = 1;
                                                        	                                        $hvac_state = 1;
                                                                	                        } elseif ($zone_c > $temp_cut_out_rising && $zone_c < $temp_cut_out_falling) {
                                                                        	                        $zone_status="0";
                                                                                	                $zone_mode = 80;
                                                                                        	        $stop_cause="HVAC Climate C Reached ";
                                                                                                	$zone_state = 0;
	                                                                                        } elseif ($zone_c >= $temp_cut_out_falling) {
        	                                                                                        $zone_status="1";
                	                                                                                $zone_mode = 86;
                        	                                                                        $start_cause="HVAC Cool Cycle Started ";
                                	                                                                $zone_state = 1;
                                        	                                                        $hvac_state = 0;
                                                	                                        }
                                                        	                        }
                                                                	                break;
			        	                                	case 4: // AUTO mode
        			        	                                	if ($zone_c <= $temp_cut_out_rising) {
                			        	                                	$zone_status="1";
	                                               			        	        $zone_mode = 121;
			                                                        		$start_cause="HVAC Heat Cycle Started ";
			        		                                                $zone_state = 1;
        			        		                                        $hvac_state = 1;
                			        		                        } elseif ($zone_c > $temp_cut_out_rising && $zone_c < $temp_cut_out_falling) {
                        			        		                        $zone_status="0";
                                                	       		        		$zone_mode = 120;
        		                                	                		$stop_cause="HVAC Climate C Reached ";
	                		                        	                	$zone_state = 0;
	        	                		                	        } elseif ($zone_c >= $temp_cut_out_falling) {
        	        	                		                	        $zone_status="1";
                                       	        	                			$zone_mode = 126;
	        		                                	        	        $start_cause="HVAC Cool Cycle Started ";
        	        		                                	        	$zone_state = 1;
	        	                		                        	        $hvac_state = 0;
	        		                        		                }
        	        		                        		        break;
										case 5: // FAN mode
		        	        		                        	$zone_status="1";
											if ($sch_status == 1) {
												$zone_mode = 87;
											} else {
               			                		        			$zone_mode = 127;
											}
			                               			        	$start_cause="HVAC Fan Only ";
					                                	        $zone_state = 1;
									 		break;
	        	        	        		                case 6: // HEAT mode
											if ($zone_c >= $temp_cut_out_rising) {
	        	        	        	        		                $zone_status="0";
        	        	        	        	        		        if ($sch_status == 1) {
	                        	        	        	        		        $zone_mode = 80;
	                	        	        	        	        	} else {
        	                	        	        	        	        	$zone_mode = 120;
	                	                	        		        	}
	        	        		                                		$stop_cause="HVAC Climate C Reached ";
			        	                		                        $zone_state = 0;
											} elseif ($zone_c < $temp_cut_out_rising) {
                			        	                        	        $zone_status="1";
												if ($system_controller_active_status == '1') {
		                			        	                        	if ($sch_status == 1) {
                		        			        	                        	$zone_mode = 81;
	                                						                } else {
        	                                        						        $zone_mode = 121;
                	                                						}
												} else {
				                	                        		        if ($sch_status == 1) {
                				        	                        		        $zone_mode = 83;
		                        		        			                } else {
        		                        		                			        $zone_mode = 123;
                		                        		        			}
												}
        	                		                        		        $start_cause="HVAC Heat Cycle Started ";
                	                		                        		$zone_state = 1;
											}
											$hvac_state = 1;
                		                        			        break;
		                		                        	case 7: // COOL mode
        		                		                        	if ($zone_c <= $temp_cut_out_falling) {
                		                		                        	$zone_status="0";
		                		                		                if ($sch_status == 1) {
        		                		                		                $zone_mode = 80;
                		                		                		} else {
                                		        		                		$zone_mode = 120;
		                	                		                	}
				                                        	                $stop_cause="HVAC Climate C Reached ";
        				                                        	        $zone_state = 0;
	                				                                } elseif ($zone_c > $temp_cut_out_falling) {
        	                				                                $zone_status="1";
	        	                				                        if ($sch_status == 1) {
        	        	                				                        $zone_mode = 86;
                	        	                				        } else {
                        	        	                				        $zone_mode = 126;
		                                		                		}
		        		                        	                        $start_cause="HVAC Cool Cycle Started ";
        		        		                        	                $zone_state = 1;
                		        		                        	}
	                	        	        		                $hvac_state = 0;
        	                	        	        		        break;
									} // end switch
								} elseif ($boost_status=='1') { // end boost == 0
        	        		                	        switch ($boost_mode) {
                	        		                	        case '3': // FAN Boost
                        	        		                	        $zone_status="1";
                                	        		                	$zone_mode = 67;
	                                        	        		        $start_cause="FAN Boost Active";
											$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
			                                                	        $zone_state = 1;
        			                                                	break;
	        	        		                                case '4': // HEAT Boost
											if ($zone_c < $temp_cut_out_rising) {
	        	        	        		                                $zone_status="1";
        	        	        	        		                        $zone_mode = 61;
                	        	        	        		                $start_cause="HEAT Boost Active";
                        	        	        	        		        $expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
                                	        	        	        		$zone_state = 1;
		                                                	        	}elseif (($zone_c >= $temp_cut_out)) {
        		                                                	        	$zone_status="0";
	        	        	                                                	$zone_mode = 60;
		        	                	                                        $stop_cause="HEAT Boost Target C Achived";
        		        	                	                                $zone_state = 0;
                		        	                	                }
											$hvac_state = 1;
                                		        			     	break;
	                                		        	        case '5': // COOL Boost
        	                                		        	        if ($zone_c > $temp_cut_out_falling) {
                	                                		        	        $zone_status="1";
                        	                                		        	$zone_mode = 66;
	                                	                                		$start_cause="COOL Boost Active";
			                                        	                        $expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
        			                                        	                $zone_state = 1;
                			                                        	}elseif (($zone_c <= $temp_cut_out_falling)) {
                        			                                        	$zone_status="0";
	                        			                                        $zone_mode = 60;
        	                        			                                $stop_cause="COOL Boost Target C Achived";
                	                        			                        $zone_state = 0;
                        	                        			        }
											$hvac_state = 0;
	                                        	                		break;
									} // end switch
								} // boost = 1
                		                        } elseif (($holidays_status=='1') && ($sch_holidays=='0')) {
                        		                	$zone_status="0";
                                		                $zone_mode = 40;
                                        		        $stop_cause="Holiday Active";
                                                		$zone_state = 0;
		                                        } // end holidays
        		                     	} elseif ($away_status=='1' && $away_sch == 0){ // end away = 0
                		                	$zone_status="0";
                        		                $zone_mode = 90;
                                		        $stop_cause="Away Active";
                                        		$zone_state = 0;
	                                	}
        	                        } elseif ($zone_c >= $zone_max_c) {
                	                        $zone_status="0";
                        	                $zone_mode = 30;
                                	        $stop_cause="Zone Reached its Max Temperature ".$zone_max_c;
                                        	$zone_state = 0;
	                                } elseif ($zone_c <= $zone_min_c) {
        	                                $zone_status="0";
                	                        $zone_mode = 130;
                        	                $stop_cause="Zone Reached its Min Temperature ".$zone_min_c;
                                	        $zone_state = 0;
	                                }
                                } else {
                                        $zone_status="0";
                                        $zone_mode = 0;
                                        $stop_cause="System is OFF";
                                        $zone_state = 0;
                                }
			//process Zones with NO System Controller and a Positive Sensor Gradient
			} elseif ($zone_category == 1 && $sensor_type_id <> 3) {
				if ($sc_mode != 0) {
					if ($frost_active == 1){
						$zone_status="1";
						$zone_mode = 21;
						$add_on_start_cause="Frost Protection";
						$zone_state= 1;
					} elseif ($frost_active == 2) {
						$zone_status=$zone_status_prev;
						$zone_mode = 22 - $zone_status_prev;
						$add_on_start_cause="Frost Protection Deadband";
						$add_on_stop_cause="Frost Protection Deadband";
						$zone_state = $zone_status_prev;
					} elseif ($frost_active == 0 && $zone_c < $zone_max_c) {
						if ($away_status=='0' || ($away_status=='1' && $away_sch == 1)) {
							if (($holidays_status=='0') || ($sch_holidays=='1')) {
		        	                                if ($zone_maintain_default == 1 && $sch_status =='0') {
                			                                if ($zone_c < $temp_cut_out_rising) {
                                			                        $zone_status="1";
                                                			        $zone_mode = 141;
		                                        	                $add_on_start_cause="Maintain Default Start";
										$add_on_stop_cause="Maintain Default Start";
                		                                	        $zone_state = 1;
	                                		                }
        	                                        		if (($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)){
			                                                        $zone_status=$zone_status_prev;
                			                                        $zone_mode = 142 - $zone_status_prev;
                                			                        $add_on_start_cause="Maintain Default Target Deadband";
                                        	        		        $add_on_stop_cause="Maintain Default Target Deadband";
		                                	                        $zone_state = $zone_status_prev;
                		                        	        }
                                		                	if (($zone_c >= $temp_cut_out)){
                                                		        	$zone_status="0";
			                                                        $zone_mode = 140;
										$add_on_start_cause="Maintain Default Target C Achieved";
        	        		                                        $add_on_stop_cause="Maintain Default Target C Achieved";
                	                		                        $zone_state = 0;
                        	                        		}

	                                                	} elseif (($sch_active) && ($zone_override_status=='1')) {
        	                                                	$zone_status="0";
                	                                                $stop_cause="Override Finished";
                        	                                        if ($zone_c < $temp_cut_out_rising){
                                	                                	$zone_status="1";
                                        	                                $zone_mode = 71;
                                                	                        $add_on_start_cause="Schedule Override Started";
                                                        	                $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
                                                                	        $zone_state = 1;
	                                                                }
        	                                                       	if ($zone_c >= $temp_cut_out_rising && ($zone_c < $temp_cut_out)){
                	                                                	$zone_status=$zone_status_prev;
                        	                                                $zone_mode = 72 - $zone_status_prev;
                                	                                        $add_on_start_cause="Schedule Override Target Deadband";
                                        	                                $add_on_stop_cause="Schedule Override Target Deadband";
                                                	                        $zone_state = $zone_status_prev;
                                                        	        }
                                                                	if ($zone_c >= $temp_cut_out){
                                                               			$zone_status="0";
	                                                                        $zone_mode = 70;
        	                                                                $add_on_stop_cause="Schedule Override Target C Achieved";
                	                                                        $zone_state = 0;
                        	                                        }
                                	                        } elseif($boost_status=='0'){
									$zone_status="0";
									$add_on_stop_cause="Boost Finished";
									if ($night_climate_status =='0') {
										if (($sch_status =='1' && $zone_c < $temp_cut_out_rising)){
											$zone_status="1";
											$zone_mode = 111;
											$add_on_start_cause = "Schedule Started";
											$add_on_stop_cause="Schedule Started";
											$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
											$zone_state = 1;
										}
										if (($sch_status =='1') && ($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)){
											$zone_status=$zone_status_prev;
											$zone_mode = 82 - $zone_status_prev;
											$add_on_start_cause="Schedule Target Deadband";
											$add_on_stop_cause="Schedule Target Deadband";
											$zone_state = $zone_status_prev;
										}
										if (($sch_status =='1') && ($zone_c >= $temp_cut_out)){
											$zone_status="0";
											$zone_mode = 80;
											$add_on_stop_cause="Schedule Target C Achieved";
											$zone_state = 0;
										}
										if (($sch_status =='0') &&($sch_holidays=='1')){
											$zone_status="0";
											$zone_mode = 40;
											$add_on_stop_cause="Holidays - No Schedule";
											$zone_state = 0;
										}
										if (($sch_status =='0') && ($sch_holidays=='0')) {
											$zone_status="0";
											$zone_mode = 0;
											$add_on_stop_cause="No Schedule";
											$zone_state = 0;
										}
									}elseif(($night_climate_status=='1') && ($zone_c < $temp_cut_out_rising)){
										$zone_status="1";
										$zone_mode = 51;
										$add_on_start_cause="Night Climate";
										$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
										$zone_state = 1;
									}elseif(($night_climate_status=='1') && ($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)){
										$zone_status=$zone_status_prev;
										$zone_mode = 52 - $zone_status_prev;
										$add_on_start_cause="Night Climate Deadband";
										$add_on_stop_cause="Night Climate Deadband";
										$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
										$zone_state = $zone_status_prev;
									}elseif(($night_climate_status=='1') && ($zone_c >= $temp_cut_out)){
										$zone_status="0";
										$zone_mode = 50;
										$add_on_stop_cause="Night Climate C Reached";
										$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
										$zone_state = 0;
									}
								}elseif (($boost_status=='1') && ($zone_c < $temp_cut_out_rising)) {
									$zone_status="1";
									$zone_mode = 61;
									$add_on_start_cause="Boost Active";
									$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
									$zone_state = 1;
								}elseif (($boost_status=='1') && ($zone_c >= $temp_cut_out_rising) && ($zone_c < $temp_cut_out)) {
									$zone_status=$zone_status_prev;
									$zone_mode = 62 - $zone_status_prev;
									$add_on_start_cause="Boost Target Deadband";
									$add_on_stop_cause="Boost Target Deadband";
									$zone_state = $zone_status_prev;
								}elseif (($boost_status=='1') && ($zone_c >= $temp_cut_out)) {
									$zone_status="0";
									$zone_mode = 60;
									$add_on_stop_cause="Boost Target C Achived";
									$zone_state = 0;
								}
							}elseif(($holidays_status=='1') && ($sch_holidays=='0')){
								$zone_status="0";
								$zone_mode = 40;
								$add_on_stop_cause="Holiday Active";
								$zone_state = 0;
							}
						}elseif($away_status=='1' && $away_sch == 0){
							$zone_status="0";
							$zone_mode = 90;
							$add_on_stop_cause="Away Active";
							$zone_state = 0;
						}
					}elseif($zone_c >= $zone_max_c){
						$zone_status="0";
						$zone_mode = 30;
						$add_on_stop_cause="Zone Reached its Max Temperature ".$zone_max_c;
						$zone_state = 0;
					}
                                } else {
                                        $zone_status="0";
                                        $zone_mode = 0;
                                        $add_on_stop_cause="System is OFF";
                                        $zone_state = 0;
                                }
			// process Binary type zone
			} elseif (($zone_category == 1 || $zone_category == 5) && $sensor_type_id == 3) {
	                        //check system controller not in OFF mode
        	                if ($sc_mode != 0) {
					if ($active_sc_mode == 4 || $active_sc_mode == 2){
                                       		if ($zone_c == 1) {
                                	        	$zone_status="1";
       	                	                        $zone_mode = 141;
               		                                $add_on_start_cause="Manual Start";
               	               	                        $zone_state = 1;
       	                               	        }
					} elseif ($away_status=='0' || ($away_status=='1' && $away_sch == 1)) {
						if (($holidays_status=='0') || ($sch_holidays=='1')) {
                                                        if (($sch_active) && ($zone_override_status=='1')) {
                                                                $zone_status="0";
                                                                $stop_cause="Override Finished";
                                                                if ($zone_c < $temp_cut_out_rising){
                                                                        $zone_status="1";
                                                                        $zone_mode = 71;
                                                                        $start_cause="Schedule Override Started";
                                                                        $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
                                                                        $zone_state = 1;
                                                                }
                                                                if ($zone_c >= $temp_cut_out_rising && ($zone_c < $temp_cut_out)){
                                                                        $zone_status=$zone_status_prev;
                                                                        $zone_mode = 72 - $zone_status_prev;
                                                                        $start_cause="Schedule Override Target Deadband";
                                                                        $stop_cause="Schedule Override Target Deadband";
                                                                        $zone_state = $zone_status_prev;
                                                                }
                                                                if ($zone_c >= $temp_cut_out){
                                                                        $zone_status="0";
                                                                        $zone_mode = 70;
                                                                        $stop_cause="Schedule Override Target C Achieved";
                                                                        $zone_state = 0;
                                                                }
                                                        } elseif($boost_status=='0'){
								$zone_status="0";
								$add_on_stop_cause="Boost Finished";
								if ($sch_status =='1') {
									$sensor_state = intval($zone_c);
									$zone_status="1";
									$zone_mode = 111;
									$add_on_start_cause = "Schedule Started";
									$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
									$zone_state = $sensor_state;
								}
								if (($sch_status =='0') &&($sch_holidays=='1')){
									$zone_status="0";
									$zone_mode = 40;
									$add_on_stop_cause="Holidays - No Schedule";
									$zone_state = 0;
								}
								if (($sch_status =='0') && ($sch_holidays=='0')) {
									$zone_status="0";
									$zone_mode = 0;
									$add_on_stop_cause="No Schedule";
									$zone_state = 0;
								}
							}
						}elseif(($holidays_status=='1') && ($sch_holidays=='0')){
							$zone_status="0";
							$zone_mode = 40;
							$add_on_stop_cause="Holiday Active";
							$zone_state = 0;
						}
					}elseif($away_status=='1' && $away_sch == 0){
						$zone_status="0";
						$zone_mode = 90;
						$add_on_stop_cause="Away Active";
						$zone_state = 0;
						}
				} else {
					$zone_status="0";
					$zone_mode = 0;
					$add_on_stop_cause="System is OFF";
					$zone_state = 0;
				}
			//process Zones with NO System Controller and a Negative Sensor Gradient
			} elseif ($zone_category == 5 && $sensor_type_id <> 3) {
				if ($sc_mode != 0) {
					if ($away_status=='0' || ($away_status=='1' && $away_sch == 1)) {
						if (($holidays_status=='0') || ($sch_holidays=='1')) {
                                                                if ($zone_maintain_default == 1 && $sch_status =='0') {
                                                                        if ($zone_c > $temp_cut_out_falling) {
                                                                                $zone_status="1";
                                                                                $zone_mode = 141;
                                                                                $add_on_start_cause="Maintain Default Start";
                                                                                $zone_state = 1;
                                                                        }
                                                                        if ($zone_c <= $temp_cut_out_falling && ($zone_c > $temp_cut_out)){
                                                                                $zone_status=$zone_status_prev;
                                                                                $zone_mode = 142 - $zone_status_prev;
                                                                                $add_on_start_cause="Maintain Default Target Deadband";
                                                                                $add_on_stop_cause="Maintain Default Target Deadband";
                                                                                $zone_state = $zone_status_prev;
                                                                        }
                                                                        if ($zone_c <= $temp_cut_out){
                                                                                $zone_status="0";
                                                                                $zone_mode = 140;
                                                                                $add_on_stop_cause="Maintain Default Target C Achieved";
                                                                                $zone_state = 0;
                                                                        }

                	                               	} elseif (($sch_active) && ($zone_override_status=='1')) {
                        	                               	$zone_status="0";
                                	                        $stop_cause="Override Finished";
                                        	                if ($zone_c > $temp_cut_out_falling){
                                                	              	$zone_status="1";
                                                        	        $zone_mode = 71;
                                                                	$start_cause="Schedule Override Started";
	                                                                $expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
        	                                                        $zone_state = 1;
                	                                        }
                        	                                if ($zone_c <= $temp_cut_out_falling && ($zone_c > $temp_cut_out)){
                                	                              	$zone_status=$zone_status_prev;
                                        	                        $zone_mode = 72 - $zone_status_prev;
                                                	                $start_cause="Schedule Override Target Deadband";
                                                        	        $stop_cause="Schedule Override Target Deadband";
                                                                	$zone_state = $zone_status_prev;
	                                                        }
        	                                                if ($zone_c <= $temp_cut_out){
                	                                        	$zone_status="0";
                        	                                        $zone_mode = 70;
                                	                                $stop_cause="Schedule Override Target C Achieved";
                                        	                        $zone_state = 0;
                                                	        }
	                                              	} elseif($boost_status=='0'){
								$zone_status="0";
								$add_on_stop_cause="Boost Finished";
								if ($night_climate_status =='0') {
									if (($sch_status =='1' && $zone_c > $temp_cut_out_falling)){
										$zone_status="1";
										$zone_mode = 111;
										$add_on_start_cause = "Schedule Started";
										$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
										$zone_state = 1;
									}
									if (($sch_status =='1') && ($zone_c <= $temp_cut_out_falling) && ($zone_c > $temp_cut_out)){
										$zone_status=$zone_status_prev;
										$zone_mode = 112 - $zone_status_prev;
										$add_on_start_cause="Schedule Target Deadband";
										$add_on_stop_cause="Schedule Target Deadband";
										$zone_state = $zone_status_prev;
									}
									if (($sch_status =='1') && ($zone_c <= $temp_cut_out)){
										$zone_status="0";
										$zone_mode = 110;
										$add_on_stop_cause="Schedule Target C Achieved";
										$zone_state = 0;
									}
									if (($sch_status =='0') &&($sch_holidays=='1')){
										$zone_status="0";
										$zone_mode = 40;
										$add_on_stop_cause="Holidays - No Schedule";
										$zone_state = 0;
									}
									if (($sch_status =='0') && ($sch_holidays=='0')) {
										$zone_status="0";
										$zone_mode = 0;
										$add_on_stop_cause="No Schedule";
										$zone_state = 0;
									}
								}elseif(($night_climate_status=='1') && ($zone_c > $temp_cut_out_falling)){
									$zone_status="1";
									$zone_mode = 51;
									$add_on_start_cause="Night Climate";
									$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
									$zone_state = 1;
								}elseif(($night_climate_status=='1') && ($zone_c <= $temp_cut_out_falling) && ($zone_c > $temp_cut_out)){
									$zone_status=$zone_status_prev;
									$zone_mode = 52 - $zone_status_prev;
									$add_on_start_cause="Night Climate Deadband";
									$add_on_stop_cause="Night Climate Deadband";
									$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
									$zone_state = $zone_status_prev;
								}elseif(($night_climate_status=='1') && ($zone_c <= $temp_cut_out)){
									$zone_status="0";
									$zone_mode = 50;
									$add_on_stop_cause="Night Climate C Reached";
									$expected_end_date_time=date('Y-m-d '.$nc_end_time_rc.'');
									$zone_state = 0;
								}
							}elseif (($boost_status=='1') && ($zone_c > $temp_cut_out_falling)) {
								$zone_status="1";
								$zone_mode = 61;
								$add_on_start_cause="Boost Active";
								$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
								$zone_state = 1;
							}elseif (($boost_status=='1') && ($zone_c <= $temp_cut_out_falling) && ($zone_c > $temp_cut_out)) {
								$zone_status=$zone_status_prev;
								$zone_mode = 62 - $zone_status_prev;
								$add_on_start_cause="Boost Target Deadband";
								$add_on_stop_cause="Boost Target Deadband";
								$zone_state = $zone_status_prev;
							}elseif (($boost_status=='1') && ($zone_c <= $temp_cut_out)) {
								$zone_status="0";
								$zone_mode = 60;
								$add_on_stop_cause="Boost Target C Achived";
								$zone_state = 0;
							}
						}elseif(($holidays_status=='1') && ($sch_holidays=='0')){
							$zone_status="0";
							$zone_mode = 40;
							$add_on_stop_cause="Holiday Active";
							$zone_state = 0;
						}
					}elseif($away_status=='1' && $away_sch == 0){
						$zone_status="0";
						$zone_mode = 90;
						$add_on_stop_cause="Away Active";
						$zone_state = 0;
					}
                                } else {
                                        $zone_status="0";
                                        $zone_mode = 0;
                                        $add_on_stop_cause="System is OFF";
                                        $zone_state = 0;
                                }
			// process Zone Category 2 Switch type zone
			} elseif ($zone_category == 2) {
				if ($sc_mode != 0) {
					if ($away_status=='1' && $away_sch == 0){
						$zone_status="0";
						$zone_mode = 90;
          					$zone_state = 0;
						$add_on_stop_cause="Away Active";
					} elseif (($holidays_status=='1') && ($sch_holidays=='0')){
						$zone_status="0";
						$zone_mode = 40;
            					$zone_state = 0;
						$add_on_stop_cause="Holiday Active";
					} elseif(($boost_status=='0') && ($zone_current_mode == 64)){
						$zone_status="0";
            					$zone_mode = 0;
            					$zone_state = 0;
						$add_on_stop_cause="Boost Finished";
	        			} elseif (($zone_state == '0') && ($zone_override_status == '0') && ($zone_status_prev == 1)) {
        	                                $zone_status="0";
						$zone_mode = 0;
          					$zone_state = 0;
						$add_on_stop_cause="Manual Stop";
	        			} elseif ($sch_status == '0' && $zone_state == '0' && $boost_status == '0') {
					  	$zone_status="0";
						$zone_mode = 0;
						$add_on_stop_cause="No Schedule";
        				} elseif ($sch_status =='1') {
						if ($zone_override_status=='0') {
					  		$zone_status="1";
							$zone_mode = 111;
        	  					$zone_state = 1;
							$add_on_start_cause = "Schedule Started";
							$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
						} else {
	                                	        $zone_status = (($add_on_state == 1) || ($zone_state == 1)) ? "1":"0" ;
        	                                	$zone_mode = 75 - $add_on_state;
	                	                        $zone_state = $add_on_state;
							if ($zone_mode == 74) {
                	        	                	$add_on_start_cause="Manual Override ON State";
							} elseif ($zone_mode == 75) {
                                	                        $add_on_stop_cause="Manual Override OFF State";
							}
                                	        	$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
						}
					} elseif ($boost_status=='1') {
					  	$zone_status="1";
						$zone_mode = 64;
						$add_on_start_cause="Boost Active";
          					$zone_state = 1;
						$expected_end_date_time=date('Y-m-d H:i:s', $boost_time);
						$expected_end_date_time=date('Y-m-d '.$sch_end_time.'');
					} elseif ($zone_state == 1) {
						if ($zone_current_mode == 111) {
	                        	                $zone_status="0";
        	                        	        $zone_mode = 0;
	                                        	$zone_state = 0;
	                	                        $add_on_stop_cause="Schedule Finished";
        	                               	} elseif ($zone_current_mode == 74 || $zone_current_mode == 75) {
                	                                $zone_status="0";
                        	                        $zone_mode = 0;
                                	                $zone_state = 0;
                                        	        $add_on_stop_cause="Override Finished";
						} else {
        	                                	$zone_status="1";
                	                        	$zone_mode = 114;
	                	                        $zone_state = 1;
                                	        	$add_on_start_cause="Manual Start";
						}
					}
                                } else {
                                        $zone_status="0";
                                        $zone_mode = 0;
                                        $add_on_stop_cause="System is OFF";
                                        $zone_state = 0;
                                }
			} // end process
		} else {
			$zone_status="0";
			$zone_mode = 10;
			$zone_state = 0;
			$stop_cause="Zone fault";
		}
                $query = "UPDATE zone SET sync = '0', zone_state = {$zone_state} WHERE id = '{$zone_id}' LIMIT 1";
                $conn->query($query);
		if ($debug_msg == 1) { echo "sch_status ".$sch_status.", zone_state ".$zone_state.", boost_status ".$boost_status.", override_status ".$zone_override_status.", zone_current_mode ".$zone_current_mode.", zone_status_prev ".$zone_status_prev."\n"; }
		// Update the individual zone controller states for controllers associated with this zone
		for ($crow = 0; $crow < count($zone_controllers); $crow++){
			$zone_controllers[$crow]['zone_controller_state'] = $zone_state;
		} // for ($crow = 0; $crow < count($zone_controllers); $crow++)

		//Update temperature values fore zone current status table (frost protection and overtemperature)
		if (floor($zone_mode/10) == 2 ) { $target_c= $frost_target_c;$temp_cut_out_rising = $frost_target_c-$zone_sp_deadband; $temp_cut_out = $frost_target_c;}
		if (floor($zone_mode/10) == 3 ) { $target_c= $zone_max_c;$temp_cut_out_rising = 0; $temp_cut_out = 0;}
		//reset if temperature control is not active
		if ((floor($zone_mode/10) == 0 ) || (floor($zone_mode/10) == 1 ) || (floor($zone_mode/10) == 4 ) || (floor($zone_mode/10) == 9 )||(floor($zone_mode/10) == 10 ))  { $target_c= 0;$temp_cut_out_rising = 0; $temp_cut_out = 0;}

		//***************************************************************************************
		//update zone_current_state table
		//***************************************************************************************
		//Zone Main Mode
		/*	0 - idle
			10 - fault
			20 - frost
			30 - overtemperature
			40 - holiday
			50 - nightclimate
			60 - boost
			70 - override
			80 - sheduled
			90 - away
			100 - hysteresis 
			110 - Add-On
			120 - HVAC
                        130 - undertemperature
                        140 - manual*/

			//Zone sub mode - running/ stopped different types
		/*	0 - stopped (above cut out setpoint or not running in this mode)
			1 - heating running 
			2 - stopped (within deadband) 
			3 - stopped (coop start waiting for the system_controller)
			4 - manual operation ON
			5 - manual operation OFF
			6 - cooling running 
                        7 - HVAC Fan Only
                        8 - Max Running Time Exceeded - Hysteresis active*/

                if ($debug_msg == 1) {
                        echo "zone_id - ".$zone_id."\n";
                        echo "zone_status - ".$zone_status."\n";
                        echo "zone_c - ".$zone_c."\n";
                }
                if ($zone_category == 3 && !empty($zone_c)) {
			$query = "UPDATE zone_current_state SET `sync` = 0, mode = {$zone_mode}, status = {$zone_status}, status_prev = '{$zone_status_current}', temp_reading = '{$zone_c}', temp_target = {$target_c},temp_cut_in = {$temp_cut_out_rising}, temp_cut_out = {$temp_cut_out}, controler_fault = {$zone_ctr_fault}, sensor_fault  = {$zone_sensor_fault}, sensor_seen_time = '{$sensor_seen}', sensor_reading_time = '{$temp_reading_time}' WHERE zone_id ={$zone_id} LIMIT 1;";
                } elseif ($zone_category == 2) {
                        $query = "UPDATE zone_current_state SET `sync` = 0, mode = {$zone_mode}, status = {$zone_status}, status_prev = '{$zone_status_current}', controler_fault = {$zone_ctr_fault}, controler_seen_time = '{$controler_seen}' WHERE zone_id ={$zone_id} LIMIT 1;";
                } elseif ($zone_category == 1 && !empty($zone_c)) {
                        $query = "UPDATE zone_current_state SET `sync` = 0, mode = {$zone_mode}, status = {$zone_status}, status_prev = '{$zone_status_current}', temp_reading = '{$zone_c}', temp_target = {$target_c}, controler_fault = {$zone_ctr_fault}, controler_seen_time = '{$controler_seen}', sensor_fault  = {$zone_sensor_fault}, sensor_seen_time = '{$sensor_seen}', sensor_reading_time = '{$temp_reading_time}' WHERE zone_id ={$zone_id} LIMIT 1;";
		} elseif (!empty($zone_c)) {
	                $query = "UPDATE zone_current_state SET `sync` = 0, mode = {$zone_mode}, status = {$zone_status}, status_prev = '{$zone_status_current}', temp_reading = '{$zone_c}', temp_target = {$target_c},temp_cut_in = {$temp_cut_in}, temp_cut_out = {$temp_cut_out}, controler_fault = {$zone_ctr_fault}, controler_seen_time = '{$controler_seen}', sensor_fault  = {$zone_sensor_fault}, sensor_seen_time = '{$sensor_seen}', sensor_reading_time = '{$temp_reading_time}' WHERE zone_id ={$zone_id} LIMIT 1;";
		}
                $conn->query($query);

		if ($debug_msg >= 0 ) {
	                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Name     \033[41m".$zone_name."\033[0m \n";
        	        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Type     \033[41m".$zone_type."\033[0m \n";
                	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ID       \033[41m".$zone_id. "\033[0m \n";
		}
		$conn->query($query);
		if ($zone_category == 1) {
			if ($debug_msg >= 0 ) {
                        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Mode     \033[41m".$zone_mode."\033[0m \n";
	                	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Sensor Reading     \033[41m".intval($zone_c)."\033[0m \n";
			}
                } elseif ($zone_category == 2) {
			if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Mode     \033[41m".$zone_mode."\033[0m \n"; }
		} else {
			if ($debug_msg >= 0 ) {
                        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Mode     \033[41m".$zone_mode."\033[0m \n";
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Sensor Reading     \033[41m".$zone_c."\033[0m \n";
			}
			if ($debug_msg >= 0 ) {
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Weather Factor     \033[41m".$weather_fact."\033[0m \n";
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: DeadBand           \033[41m".$zone_sp_deadband."\033[0m \n";
				if ($zone_category == 5) {
					echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Cut In Temperature        \033[41m".$temp_cut_out_rising."\033[0m \n";
				} else {
                                        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Cut In Temperature        \033[41m".$temp_cut_out_falling."\033[0m \n";
				}
				echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Cut Out Temperature       \033[41m".$temp_cut_out."\033[0m \n";
			}
		}
                for ($crow = 0; $crow < count($zone_controllers); $crow++){
                        $zone_controler_id = $zone_controllers[$crow]["controler_id"];
                        $zone_controler_child_id = $zone_controllers[$crow]["controler_child_id"];
			if ($zone_controllers[$crow]["relay_type_id"] == 5) { $zp = "Pump"; } else { $zp = "Zone"; }
                        if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Controller: \033[41m".$zone_controler_id."\033[0m Controller Child: \033[41m".$zone_controler_child_id."\033[0m ".$zp." Status: \033[41m".$zone_status."\033[0m \n"; }
                }
		if ($zone_category == 0 || $zone_category == 3 || $zone_category == 4) {
			if ($zone_status=='1') {
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Start Cause: ".$start_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n"; }
				if (floor($zone_mode/10)*10 == 80) {
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Running Schedule \033[41m".$sch_name."\033[0m \n"; }
				}
			}
			if ($zone_status=='0') {
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Stop Cause: ".$stop_cause." - Target C:\033[41m".$target_c."\033[0m Zone C:\033[31m".$zone_c."\033[0m \n"; }
				if ($zone_mode == 30 || floor($zone_mode/10)*10 == 80) { 
					if ($debug_msg >= 0 ) {echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Running Schedule \033[44m".$sch_name."\033[0m \n"; }
				}
			}
		} else {
			if ($zone_status=='1') {
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Start Cause: ".$add_on_start_cause."\033[0m \n"; }
                                if ($sch_status =='1') { 
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Running Schedule \033[41m".$sch_name."\033[0m \n"; }
				}
			}
			if ($zone_status=='0') {
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ".$zone_name." Stop Cause: ".$add_on_stop_cause."\033[0m \n"; }
                                if ($zone_mode == 30 || floor($zone_mode/10)*10 == 80) { 
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Running Schedule \033[44m".$sch_name."\033[0m \n"; }
				}
			}
		}
		//Pass data to zone commands loop
                $zone_commands[$command_index] = (array('controllers' =>$zone_controllers, 'zone_id' =>$zone_id, 'zone_name' =>$zone_name, 'zone_category' =>$zone_category, 'zone_status'=>$zone_status, 'zone_status_prev'=>$zone_status_prev, 'zone_overrun_prev'=>$zone_overrun_prev, 'zone_override_status'=>$zone_override_status));
		$command_index = $command_index+1;
		//process Zone Cat 0 logs
		if ($zone_category == 0 OR $zone_category == 3 || $zone_category == 4){
			//all zone status to system controller array and increment array index
			$system_controller[$system_controller_index] = $zone_status;
			$system_controller_index = $system_controller_index+1;
			//all zone ids and status to multidimensional Array. and increment array index.
			$zone_log[$zone_id] = $zone_status;
			//process Zone Cat 1 and 2 logs
		} else {
			// Process Logs Category 1, 2 and 4 logs if zone status has changed
			// zone switching ON
                        $mode_1 = floor($zone_current_mode/10)*10;
                        $mode_2 = floor($zone_mode/10)*10;
                        if ($zone_current_mode != $zone_mode) {
				if (($mode_1 == 110 && $mode_2== 140) || ($mode_1 == 140 && $mode_2== 110)) {
                                	$query = "UPDATE add_on_logs SET stop_datetime = '{$date_time}', stop_cause = '{$add_on_stop_cause}'
                                        	  WHERE `zone_id` = '{$zone_id}' ORDER BY id DESC LIMIT 1";
                                	$result = $conn->query($query);
                                	if ($result) {
                                        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table updated Successfully. \n"; }
                                	}else {
                                        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table update failed. \n"; }
                                	}
                                	if($zone_mode == 111 || $zone_mode == 114 || $zone_mode == 21 ||  $zone_mode == 10) {
                                        	$query = "INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                   	  `expected_end_date_time`) VALUES ('0', '0', '{$zone_id}', '{$date_time}', '{$add_on_start_cause}', NULL, NULL,
                                                   	  '{$expected_end_date_time}');";
                                	} else {
                                        	$query = "INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`,
                                                   	  `expected_end_date_time`) VALUES ('0', '0', '{$zone_id}', '{$date_time}', '{$add_on_start_cause}', NULL, NULL, NULL);";
                                	}
                                	$result = $conn->query($query);
                                	if ($result) {
                                        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table updated Successfully. \n"; }
                                	}else {
                                        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table update failed. \n"; }
                                	}
				}
			} elseif($zone_status_prev == '0' &&  ($zone_status == '1' || $zone_state  == '1')) {
				if($zone_mode == 111 || $zone_mode == 114 || $zone_mode == 21 ||  $zone_mode == 10 ||  $zone_mode == 141) {
					$aoquery = "INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$zone_id}', '{$date_time}', '{$add_on_start_cause}', NULL, NULL, NULL);";
				} else {
					$aoquery = "INSERT INTO `add_on_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$zone_id}', '{$date_time}', '{$add_on_start_cause}', NULL, NULL,'{$expected_end_date_time}');";
				}
				$result = $conn->query($aoquery);
				if ($result) {
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table added Successfully. \n"; }
				}else {
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table addition failed. \n"; }
				}
			// zone switching OFF
			} elseif ($zone_status_prev == '1' &&  $zone_status == '0') {
				$query = "UPDATE add_on_logs SET stop_datetime = '{$date_time}', stop_cause = '{$add_on_stop_cause}' WHERE `zone_id` = '{$zone_id}' ORDER BY id DESC LIMIT 1";
				$result = $conn->query($query);
				if ($result) {
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table updated Successfully. \n"; }
				}else {
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Add-On Log table update failed. \n"; }
				}
			}
		} //end process Zone Cat 1 and 2 logs
		if ($debug_msg >= 0 ) { for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n"; }
        } else { //end if($zone_status == 1)
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Name     \033[41m".$zone_name."\033[0m \n";
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: Type     \033[41m".$zone_type."\033[0m \n";
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: ID       \033[41m".$zone_id. "\033[0m \n";
                echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Error          \033[41mZONE NOT PROCESSED DUE TO MISSING ASSOCIATED RECORDS\033[0m \n";
                for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n";
        }
} //end of while loop

/***************************************************************************************
                                   Zone Commands loop
 ***************************************************************************************/
$pump_relays = array();
for ($row = 0; $row < count($zone_commands); $row++){
        $zone_id = $zone_commands[$row]["zone_id"];
        $zone_category = $zone_commands[$row]["zone_category"];
        $zone_name = $zone_commands[$row]["zone_name"];
        $zone_status = $zone_commands[$row]["zone_status"];
        $zone_status_prev = $zone_commands[$row]["zone_status_prev"];
        $zone_overrun_prev = $zone_commands[$row]["zone_overrun_prev"];
        $zone_override_status = $zone_commands[$row]["zone_override_status"];
        $controllers = $zone_commands[$row]["controllers"];

	//Zone category 0 and system controller is not requested calculate if overrun needed
        if($zone_category == 0 && $cz_logs_count > 0 && !in_array("1", $system_controller)) {
		//overrun time <0 - latch overrun for the zone zone untill next system controller start
		if($system_controller_overrun_time < 0){
			$zone_overrun = (($zone_status_prev == 1)||($zone_overrun_prev == 1)||($zone_override_status == 1 && !in_array("1", $system_controller))) ? 1:0;
		// overrun time = 0 - overrun not needed
		}else if ($system_controller_overrun_time == 0){
			$zone_overrun = 0;
		// overrun time > 0
		}else {
			$now=strtotime(date('Y-m-d H:i:s'));
			//when switching off system controller
			if($system_controller_active_status == 1) {
				$overrun_end_time = $now + ($system_controller_overrun_time * 60);
				$zone_overrun = $zone_status_prev;
			//system controller was switched of previous script run
			} else {
				$overrun_end_time = strtotime( $system_controller_stop_datetime ) + ($system_controller_overrun_time * 60);
				// if overrun flag was switched on when system controller was switching on and ovverrun did not pass keep overrun on
				if(($now < $overrun_end_time)&&($zone_overrun_prev)){
					$zone_overrun = 1;
				}else {
					$zone_overrun = 0;
				}
			}
		}
	        if($zone_overrun<>$zone_overrun_prev){
        	        $query = "UPDATE zone_current_state SET `sync` = 0, overrun = {$zone_overrun} WHERE id ={$zone_id} LIMIT 1;";
                	$conn->query($query);
	        }
        	if($zone_overrun == 1){
                	//zone status needs to be 1 when in overrun mode
	                $query = "UPDATE zone_current_state SET status = 1, status_prev = '{$zone_status_current}' WHERE id ={$zone_id} LIMIT 1;";
        	        $conn->query($query);

                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone ".$zone_id. " circulation pump overrun active. \n"; }
	                if ($system_controller_overrun_time > 0) {
        	                if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Overrun end time ".date('Y-m-d H:i:s',$overrun_end_time). " \n"; }
                	} else{
                        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Overrun will end on the next System Controller start. \n"; }
	                }
        	}
	}else {
		//if zone is not category 0 or system controller is running overrun not needed
		$zone_overrun = 0;
	}
	$zone_command = (($zone_status == 1) || ($zone_overrun == 1)) ? 1:0 ;
	//process all the zone relays associated with this zone
        for ($crow = 0; $crow < count($controllers); $crow++){
		$zc_id = $controllers[$crow]["zc_id"];
                $zone_controler_id = $controllers[$crow]["controler_id"];
                $zone_controler_child_id = $controllers[$crow]["controler_child_id"];
		$controller_relay_id = $controllers[$crow]["controller_relay_id"];
                $zone_relay_type_id = $controllers[$crow]["relay_type_id"];
                $zone_on_trigger = $controllers[$crow]["controler_on_trigger"];
                $zone_controller_type = $controllers[$crow]["zone_controller_type"];
                $manual_button_override = $controllers[$crow]["manual_button_override"];
		$zone_controller_state = $controllers[$crow]["zone_controller_state"];
		$zone_command = (($zone_controller_state == 1) || ($zone_overrun == 1)) ? 1:0 ;
		if ($zone_on_trigger == 1) {
			$relay_on = '1'; //GPIO value to write to turn on attached relay
			$relay_off = '0'; // GPIO value to write to turn off attached relay
		} else {
                        $relay_on = '0'; //GPIO value to write to turn on attached relay
                        $relay_off = '1'; // GPIO value to write to turn off attached relay
		}

		if ($debug_msg == 1) { 
			echo $zone_controler_id."-".$zone_controler_child_id.", ".$zone_controller_state.", ".$manual_button_override."\n"; 
			echo "relay_type_id - ".$zone_relay_type_id."\n";
			echo "zone_overrun - ".$zone_overrun.", manual_button_override - ".$manual_button_override.", zone_command - ".$zone_command.", zone_status_prev - ".$zone_status_prev."\n";
		}
//		if (($manual_button_override == 0) || ($manual_button_override == 1 && $zone_command == 0)) {
		//process zone relays
		if ($zone_relay_type_id == 0) {
			if ((($manual_button_override == 0) || ($manual_button_override == 1 && $zone_command == 0)) && ($zone_command != $zone_status_prev || $zone_controller_type == 'MySensor')) {
				/***************************************************************************************
				Zone Valve Wired to Raspberry Pi GPIO Section: Zone Valve Connected Raspberry Pi GPIO.
				****************************************************************************************/
				if ($zone_controller_type == 'GPIO'){
			    		$relay_status = ($zone_command == '1') ? $relay_on : $relay_off;
			    		if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone: GIOP Relay Status: \033[41m".$relay_status. "\033[0m (".$relay_on."=On, ".$relay_off."=Off) \n"; }
                        	        $query = "UPDATE messages_out SET sent = '0', payload = '{$zone_command}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
                                	$conn->query($query);
				}

				/***************************************************************************************
				Zone Valve Wired over I2C Interface Make sure you have i2c Interface enabled 
				****************************************************************************************/
				if ($zone_controller_type == 'I2C'){
					exec("python3 /var/www/cron/i2c/i2c_relay.py ".$zone_controler_id." ".$zone_controler_child_id." ".$zone_command);
					if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Relay Broad: ".$zone_controler_id. " Relay No: ".$zone_controler_child_id." Status: ".$zone_command." \n"; }
				}

				/***************************************************************************************
				Zone Valve Wireless Section: MySensors Wireless or MQTT Relay module for your Zone Valve control.
				****************************************************************************************/
				if ($zone_controller_type == 'MySensor' || $zone_controller_type == 'MQTT'){
					//update messages_out table with sent status to 0 and payload to as zone status.
					$query = "UPDATE messages_out SET sent = '0', payload = '{$zone_command}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
					$conn->query($query);
				}
				/***************************************************************************************
				Sonoff Switch Section: Tasmota WiFi Relay module for your Zone control.
				****************************************************************************************/
        			if ($zone_controller_type == 'Tasmota'){
                			$query = "SELECT * FROM http_messages WHERE zone_id = '$zone_id' AND message_type = '$zone_command' LIMIT 1;";
	                		$result = $conn->query($query);
		        	        $http = mysqli_fetch_array($result);
        		        	$add_on_msg = $http['command'].' '.$http['parameter'];
	        		        $query = "UPDATE messages_out SET sent = '0', payload = '{$add_on_msg}' WHERE node_id ='$zone_controler_id' AND child_id = '$zone_controler_child_id' LIMIT 1;";
        	        		$conn->query($query);
	        		}

				if ($zone_category <> 3) {
					if ($zone_override_status == 0) {
						$query = "UPDATE zone_relays SET state = {$zone_command}, current_state = {$zone_command} WHERE id = {$zc_id} LIMIT 1;";
						$conn->query($query);
					}
				}
			} //end if ($manual_button_override == 0) {
		} elseif ($zone_relay_type_id == 5) { //end if ($zone_relay_type_id == 0)
			if (empty($pump_relays)) { //add first pump type relay
				$pump_relays = array($controller_relay_id=>$zone_command);
                        } elseif (array_key_exists($controller_relay_id, $pump_relays) && $zone_command == 1) {
                                $pump_relays[$controller_relay_id] = $zone_command;
			}
		}
	} //end for ($crow = 0; $crow < count($controllers); $crow++)
} //end for ($row = 0; $row < count($zone_commands); $row++)

//process any pump relays
if (!empty($pump_relays)) {
	array_walk($pump_relays, "process_pump_relays");
}
//For debug info only
if ($debug_msg == 1) {
        echo "zone_log Array and Count\n";
        print_r ($zone_log);
        echo count($zone_log)."\n";
        echo "z_state Array and Count\n";
        print_r ($z_state);
        echo count($z_state)."\n";
        echo "system_controller Array\n";
        print_r ($system_controller);
        echo "zone_controllers Array\n";
        print_r ($zone_controllers);
        print_r ($zone_commands);
        echo "pump_relays Array\n";
        print_r ($pump_relays);
}
if (isset($system_controller_stop_datetime)) {
	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Switched Off At: ".$system_controller_stop_datetime. "\n";}
}
if (isset($expected_end_date_time)){
	if ($debug_msg >= 0 ) {
		echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Expected End Time: ".$expected_end_date_time. "\n";
        	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller ON Time: \033[41m".$system_controller_on_time."\033[0m seconds\n";
	}
}
/***********************************
      System Controller On section
/***********************************/
//Search inside array if any value is set to 1 then we need to update db with system controller status
if (in_array("1", $system_controller)) {
        if ($system_controller_mode == 1) {
                if  ($hvac_state == 0){
                        $on_relay_id = $cool_relay_id;
                        $on_relay_child_id = $cool_relay_child_id;
                        $on_relay_type = $cool_relay_type;
                        $on_relay_on = $cool_relay_on;
                        $on_relay_off = $cool_relay_off;
                        $off_relay_id = $heat_relay_id;
                        $off_relay_child_id = $heat_relay_child_id;
                        $off_relay_type = $heat_relay_type;
                        $off_relay_on = $heat_relay_on;
                        $off_relay_off = $heat_relay_off;
                } else {
                        $on_relay_id = $heat_relay_id;
                        $on_relay_child_id = $heat_relay_child_id;
                        $on_relay_type = $heat_relay_type;
                        $on_relay_on = $heat_relay_on;
                        $on_relay_off = $heat_relay_off;
                        $off_relay_id = $cool_relay_id;
                        $off_relay_child_id = $cool_relay_child_id;
                        $off_relay_type = $cool_relay_type;
                        $off_relay_on = $cool_relay_on;
                        $off_relay_off = $cool_relay_off;
                }
        } else {
                $on_relay_id = $heat_relay_id;
                $on_relay_child_id = $heat_relay_child_id;
                $on_relay_type = $heat_relay_type;
                $on_relay_on = $heat_relay_on;
                $on_relay_off = $heat_relay_off;
                $off_relay_id = $heat_relay_id;
                $off_relay_child_id = $heat_relay_child_id;
                $off_relay_type = $heat_relay_type;
                $off_relay_on = $heat_relay_on;
                $off_relay_off = $heat_relay_off;
        }

	$new_system_controller_status='1';
	//change relay states on change
	if (($system_controller_active_status != $new_system_controller_status) || ($active_sc_mode != $sc_mode_prev) || ($off_relay_type == 'MySensor') || ($on_relay_type == 'MySensor')){
		//update system controller active status to 1
		$query = "UPDATE system_controller SET sync = '0', active_status = '{$new_system_controller_status}', sc_mode_prev = '{$active_sc_mode}' WHERE id ='1' LIMIT 1";
		$conn->query($query);

		/**************************************************************************************************
		System Controller Wirelss Section:	MySensors Wireless or MQTT Relay module for your System Controller
		***************************************************************************************************/
		//update messages_out table with sent status to 0 and payload to as system controller status.
                if ($active_sc_mode != 5) { //process if NOT  HVAC fan only mode
	        	if ($system_controller_mode == 1 && ($off_relay_type == 'MySensor' || $off_relay_type == 'MQTT')){
                                $query = "SELECT node_id FROM nodes WHERE id = '$off_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
        	        	$query = "UPDATE messages_out SET sent = '0', payload = '0' WHERE node_id ='{$node_id}' AND child_id = '{$off_relay_child_id}' LIMIT 1;";
	        	        $conn->query($query);
        	        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Node ID: \033[41m".$node_id."\033[0m Child ID: \033[41m".$off_relay_child_id."\033[0m \n"; }
		        }
			if ($on_relay_type == 'MySensor' || $on_relay_type == 'MQTT'){
                                $query = "SELECT node_id FROM nodes WHERE id = '$on_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
				$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$on_relay_child_id}' LIMIT 1;";
				$conn->query($query);
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Node ID: \033[41m".$node_id."\033[0m Child ID: \033[41m".$on_relay_child_id."\033[0m \n"; }
			}
		}
        	if ($system_controller_mode == 1 && ($fan_relay_type == 'MySensor' || $fan_relay_type == 'MQTT')){
                       	$query = "SELECT node_id FROM nodes WHERE id = '$fan_relay_id' LIMIT 1;";
                        $result = $conn->query($query);
                        $nodes = mysqli_fetch_array($result);
                        $node_id = $nodes['node_id'];
        		$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$fan_relay_child_id}' LIMIT 1;";
	                $conn->query($query);
        	        if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Node ID: \033[41m".$node_id."\033[0m Child ID: \033[41m".$fan_relay_child_id."\033[0m \n"; }
	        }

		/*****************************************************
		System Controller Wired to Raspberry Pi GPIO Section.
		******************************************************/
		if ($active_sc_mode != 5) { //process if NOT  HVAC fan only mode
			if ($system_controller_mode == 1 && $off_relay_type == 'GPIO'){
                                $query = "SELECT node_id FROM nodes WHERE id = '$off_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
                                $query = "UPDATE messages_out SET sent = '0', payload = '0' WHERE node_id ='{$node_id}' AND child_id = '{$off_relay_child_id}' LIMIT 1;";
                                $conn->query($query);
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller GIOP: \033[41m".$off_relay_child_id. "\033[0m Status: \033[41m".$off_relay_off."\033[0m (".$off_relay_on."=On, ".$off_relay_off."=Off) \n"; }
			}
        		if ($on_relay_type == 'GPIO'){
                                $query = "SELECT node_id FROM nodes WHERE id = '$on_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
                		$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$on_relay_child_id}' LIMIT 1;";
                                $conn->query($query);
	                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller GIOP: \033[41m".$on_relay_child_id. "\033[0m Status: \033[41m".$on_relay_on."\033[0m (".$on_relay_on."=On, ".$on_relay_off."=Off) \n"; }
	        	}
		}
	        if ($system_controller_mode == 1 && $fan_relay_type == 'GPIO'){
                        $query = "SELECT node_id FROM nodes WHERE id = '$fan_relay_id' LIMIT 1;";
                        $result = $conn->query($query);
                        $nodes = mysqli_fetch_array($result);
                        $node_id = $nodes['node_id'];
                	$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$fan_relay_child_id}' LIMIT 1;";
                        $conn->query($query);
                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller GIOP: \033[41m".$fan_relay_child_id. "\033[0m Status: \033[41m".$fan_relay_on."\033[0m (".$fan_relay_on."=On, ".$fan_relay_off."=Off) \n"; }
	        }

		/******************************************************************************************
		System Controller Wired over I2C Interface Make sure you have i2c Interface enabled 
		*******************************************************************************************/
                if ($active_sc_mode != 5) { //process if NOT  HVAC fan only mode
		        if ($system_controller_mode == 1 && $off_relay_type == 'I2C'){
        		        exec("python3 /var/www/cron/i2c/i2c_relay.py" .$off_relay_id." ".$off_relay_child_id." 0");
                		if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller I2C Rrelay Board: \033[41m".$off_relay_id."\033[0m Relay ID: \033[41m".$off_relay_child_id."\033[0m \n"; }
		        }
			if ($on_relay_type == 'I2C'){
				exec("python3 /var/www/cron/i2c/i2c_relay.py" .$on_relay_id." ".$on_relay_child_id." 1"); 
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller I2C Rrelay Board: \033[41m".$on_relay_id."\033[0m Relay ID: \033[41m".$on_relay_child_id."\033[0m \n"; }
			}
		}
	        if ($system_controller_mode == 1 && $fan_relay_type == 'I2C'){
        	        exec("python3 /var/www/cron/i2c/i2c_relay.py" .$fan_relay_id." ".$fan_relay_child_id." 1");
                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller I2C Rrelay Board: \033[41m".$fan_relay_id."\033[0m Relay ID: \033[41m".$fan_relay_child_id."\033[0m \n"; }
	        }
	}
	if ($system_controller_active_status != $new_system_controller_status) {
        	foreach($zone_log as $key => $value) {
       			//insert date and time into Log table so we can record system controller start date and time.
			if($value  == 1) {
	                	if (isset($expected_end_date_time)) {
        	        		$bsquery = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$key}', '{$date_time}', '{$start_cause}', NULL, NULL,'{$expected_end_date_time}');";
                		} else {
                        		$bsquery = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$key}', '{$date_time}', '{$start_cause}', NULL, NULL,NULL);";
	                	}
        	        	$result = $conn->query($bsquery);
                		if ($result) {
                        		if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table added Successfully. \n"; }
	                	}else {
        	                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table addition failed. \n"; }
                		}
			}
		} // end foreach($zone_log as $key => $value)
		//insert date and time into system controller log table so we can record system controller start date and time.
                if (isset($expected_end_date_time)) {
        	        $bsquery = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$system_controller_id}', '{$date_time}', '{$start_cause}', NULL, NULL,'{$expected_end_date_time}');";
                } else {
                        $bsquery = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$system_controller_id}', '{$date_time}', '{$start_cause}', NULL, NULL,NULL);";
		}
		$result = $conn->query($bsquery);
		if ($result) {
			if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Log table added Successfully. \n"; }
		}else {
			if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System System Log table addition failed. \n"; }
		}
	} else {
		foreach($zone_log as $key => $value) {
        	        if($value != $z_state[$key]) {
				if($value  == 0) {
                        		$query = "UPDATE controller_zone_logs SET stop_datetime = '{$date_time}', stop_cause = '{$stop_cause}' WHERE `zone_id` = '{$key}' ORDER BY id DESC LIMIT 1;";
				} else {
		                        if (isset($expected_end_date_time)) {
        		                        $query = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$key}', '{$date_time}', '{$start_cause}', NULL, NULL,'{$expected_end_date_time}');";
                		        } else {
                        		        $query = "INSERT INTO `controller_zone_logs`(`sync`, `purge`, `zone_id`, `start_datetime`, `start_cause`, `stop_datetime`, `stop_cause`, `expected_end_date_time`) VALUES ('0', '0', '{$key}', '{$date_time}', '{$start_cause}', NULL, NULL,NULL);";
	                        	}
				}
                	        $result = $conn->query($query);
                        	if ($result) {
                                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table updated Successfully. \n"; }
	                        }else {
        	                        if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table update failed. \n"; }
				}
                        }
		}
	}
// end system_controller ON section
/************************************
      System Controller Off section
/************************************/
}else{
	$new_system_controller_status='0';
        //change relay states on change
        if (($system_controller_active_status != $new_system_controller_status) || ($active_sc_mode != $sc_mode_prev) || ($zone_current_mode != $zone_mode) || ($zone_current_mode != $zone_mode) || ($heat_relay_type == 'MySensor') || ($cool_relay_type == 'MySensor') || ($fan_relay_type == 'MySensor')){
		//update system controller active status to 0
		$query = "UPDATE system_controller SET sync = '0', active_status = '{$new_system_controller_status}', sc_mode_prev = '{$active_sc_mode}' WHERE id ='1' LIMIT 1";
		$conn->query($query);

		/****************************************************************************************************
		System Controller Wirelss Section:	MySensors Wireless or MQTT Relay module for your System Controller
		*****************************************************************************************************/
		if ($heat_relay_type == 'MySensor' || $heat_relay_type == 'MQTT'){
			//update messages_out table with sent status to 0 and payload to as system controller status.
                        $query = "SELECT node_id FROM nodes WHERE id = '$heat_relay_id' LIMIT 1;";
                        $result = $conn->query($query);
                        $nodes = mysqli_fetch_array($result);
                        $node_id = $nodes['node_id'];
			$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$heat_relay_child_id}' LIMIT 1;";
			$conn->query($query);
			if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Node ID: \033[41m".$node_id."\033[0m Child ID: \033[41m".$heat_relay_child_id."\033[0m \n"; }
		}
        	if ($system_controller_mode == 1){
                	//update messages_out table with sent status to 0 and payload to as system controller status.
			if ($cool_relay_type == 'MySensor' || $cool_relay_type == 'MQTT') { // HVAC cool relay OFF
                                $query = "SELECT node_id FROM nodes WHERE id = '$cool_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
        	                $query = "UPDATE messages_out SET sent = '0', payload = '0' WHERE node_id ='{$node_id}' AND child_id = '{$cool_relay_child_id}' LIMIT 1;";
				$conn->query($query);
			}
	                if ($fan_relay_type == 'MySensor' || $fan_relay_type == 'MQTT') {
                                $query = "SELECT node_id FROM nodes WHERE id = '$fan_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
				if ($active_sc_mode == 5) { // HVAC fan ON if set to fan mode, else turn OFF
	        	                $query = "UPDATE messages_out SET sent = '0', payload = '1' WHERE node_id ='{$node_id}' AND child_id = '{$fan_relay_child_id}' LIMIT 1;";
				} else {
        				$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$fan_relay_child_id}' LIMIT 1;";
				}
        		        $conn->query($query);
                		if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Node ID: \033[41m".$node_id."\033[0m Child ID: \033[41m".$fan_relay_child_id."\033[0m \n"; }
			}
        	}

		/*****************************************************
		System Controller Wired to Raspberry Pi GPIO Section.
		******************************************************/
		if ($heat_relay_type == 'GPIO'){
                        $query = "SELECT node_id FROM nodes WHERE id = '$heat_relay_id' LIMIT 1;";
                        $result = $conn->query($query);
                        $nodes = mysqli_fetch_array($result);
                        $node_id = $nodes['node_id'];
        		$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$heat_relay_child_id}' LIMIT 1;";
                        $conn->query($query);
			if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller GIOP: \033[41m".$heat_relay_child_id. "\033[0m Status: \033[41m".$heat_relay_off."\033[0m (".$heat_relay_on."=On, ".$heat_relay_off."=Off) \n"; }
		}
	        if ($system_controller_mode == 1){
        	        if ($cool_relay_type == 'GPIO') { // HVAC cool relay OFF
                                $query = "SELECT node_id FROM nodes WHERE id = '$cool_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
                                $query = "UPDATE messages_out SET sent = '0', payload = '0' WHERE node_id ='{$node_id}' AND child_id = '{$cool_relay_child_id}' LIMIT 1;";
				$conn->query($query);
                		if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller GIOP: \033[41m".$cool_relay_child_id. "\033[0m Status: \033[41m".$cool_relay_off."\033[0m (".$cool_relay_on."=On, ".$cool_relay_off."=Off) \n"; }
			}
			if ($fan_relay_type == 'GPIO') {
                                $query = "SELECT node_id FROM nodes WHERE id = '$fan_relay_id' LIMIT 1;";
                                $result = $conn->query($query);
                                $nodes = mysqli_fetch_array($result);
                                $node_id = $nodes['node_id'];
				if ($active_sc_mode == 5) { // HVAC fan ON if set to fan mode, else turn OFF
                                        $query = "UPDATE messages_out SET sent = '0', payload = '1' WHERE node_id ='{$node_id}' AND child_id = '{$fan_relay_child_id}' LIMIT 1;";
				} else {
                			$query = "UPDATE messages_out SET sent = '0', payload = '{$new_system_controller_status}' WHERE node_id ='{$node_id}' AND child_id = '{$fan_relay_child_id}' LIMIT 1;";
				}
				$conn->query($query);
	                	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller GIOP: \033[41m".$fan_relay_child_id. "\033[0m Status: \033[41m".$fan_relay_off."\033[0m (".$fan_relay_on."=On, ".$fan_relay_off."=Off) \n"; }
			}
        	}

		/***************************************************************************************
		System Controller Wired over I2C Interface Make sure you have i2c Interface enabled 
		****************************************************************************************/
		if ($heat_relay_type == 'I2C'){
                	exec("python3 /var/www/cron/i2c/i2c_relay.py" .$heat_relay_id." ".$heat_relay_child_id." 0");
	                if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller I2C Rrelay Board: \033[41m".$heat_relay_id."\033[0m Relay ID: \033[41m".$heat_relay_child_id."\033[0m \n"; }
		}
	        if ($system_controller_mode == 1){
        	        if ($cool_relay_type == 'I2C') { // HVAC cool relay OFF
                	        exec("python3 /var/www/cron/i2c/i2c_relay.py" .$cool_relay_id." ".$cool_relay_child_id." 0");
                		if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller I2C Rrelay Board: \033[41m".$cool_relay_id."\033[0m Relay ID: \033[41m".$cool_relay_child_id."\033[0m \n"; }
	                }
        	        if ($fan_relay_type == 'I2C') {
				if ($active_sc_mode == 5) { // HVAC fan ON if set to fan mode, else turn OFF
					exec("python3 /var/www/cron/i2c/i2c_relay.py" .$fan_relay_id." ".$fan_relay_child_id." 1");
				} else {
        		                exec("python3 /var/www/cron/i2c/i2c_relay.py" .$fan_relay_id." ".$fan_relay_child_id." 0");
				}
				if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller I2C Rrelay Board: \033[41m".$fan_relay_id."\033[0m Relay ID: \033[41m".$fan_relay_child_id."\033[0m \n"; }
			}
		}

		//Update last record with system controller stop date and time in System Controller Log table.
                if ($system_controller_active_status != $new_system_controller_status){
                        foreach($zone_log as $key => $value) {
                                if($value != $z_state[$key]) {
                        		$query = "UPDATE controller_zone_logs SET stop_datetime = '{$date_time}', stop_cause = '{$stop_cause}' WHERE `zone_id` = '{$key}' ORDER BY id DESC LIMIT 1;";
                                        $result = $conn->query($query);
                                        if ($result) {
                                                if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table updated Successfully. \n"; }
                                        }else {
                                                if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Zone Log table update failed. \n"; }
                                        }
                                }
                        }
			$query = "UPDATE controller_zone_logs SET stop_datetime = '{$date_time}', stop_cause = '{$stop_cause}' WHERE `zone_id` = '{$system_controller_id}' ORDER BY id DESC LIMIT 1;";
                        $result = $conn->query($query);
                        if ($result) {
                                if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Log table updated Successfully. \n"; }
                        }else {
                                if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Log table update failed. \n"; }
                        }
                }
	}
}

//if HVAC mode get the heat, cool and fan relay on/off state
if ($system_controller_mode == 1) {
	if ($new_system_controller_status=='1') {
		if ($active_sc_mode != 5) {
			$query = "SELECT name FROM relays WHERE relay_id = '$on_relay_id' AND relay_child_id = '$on_relay_child_id'LIMIT 1;";
                	$result = $conn->query($query);
	                $h_relay = mysqli_fetch_array($result);
        	        if (strpos($h_relay['name'], 'Heat') !== false) { $hvac_relays_state = 0b101; } else { $hvac_relays_state = 0b011; }
	      	} else {
        		$hvac_relays_state = 0b001;
		}
	} else {
		 if ($active_sc_mode == 5) { $hvac_relays_state = 0b001; } else { $hvac_relays_state = 0b000; }
	}
        $query = "UPDATE system_controller SET hvac_relays_state = '{$hvac_relays_state}'";
        $result = $conn->query($query);
        if ($result) {
        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller HVAC Relay State updated Successfully. \n"; }
        } else {
        	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller HVAC Relay State update failed. \n"; }
        }
	if ($debug_msg == 1) {
		if ($debug_msg >= 0 ) {
			echo "hvac_state - ".$hvac_state."\n";
			echo "hvac_relays_state - ".$hvac_relays_state."\n";
			if ($hvac_relays_state != 0) { echo "On Relay Name - ".$h_relay['name']."\n"; }
		}
	}
}

/********************************************************************************************************************************************************************
Following section is Optional for States collection
I thank you for not commenting it out as it will help me to allocate time to keep this systems updated.
I am using CPU serial as salt and then using MD5 hasing to get unique reference, i have no other intention if you want you can set variable to anything you like
/********************************************************************************************************************************************************************/
$start_time = '23:58:00';
$end_time = '00:00:00';
if (TimeIsBetweenTwoTimes($current_time, $start_time, $end_time)) {
	$query = "select * from user LIMIT 1;";
	$result = $conn->query($query);
	$user_row = mysqli_fetch_array($result);
	$email = $user_row['email'];
	for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Calling Home \n";
	//$external_ip = file_get_contents('http://www.pihome.eu/piconnect/myip.php');
	$external_ip = exec ("curl -s checkip.amazonaws.com");
	$pi_serial = exec ("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2");
	$cpu_model = exec ("cat /proc/cpuinfo | grep 'model name' | cut -d ' ' -f 3-");
	$cpu_model = urlencode($cpu_model);
	$hardware = exec ("cat /proc/cpuinfo | grep Hardware | cut -d ' ' -f 2");
	$revision = exec ("cat /proc/cpuinfo | grep Revision | cut -d ' ' -f 2");
	$uid = UniqueMachineID($pi_serial);
	$ph_version = settings($conn, 'version');
	$ph_build = settings($conn, 'build');
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - External IP Address: ".$external_ip."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Serial: " .$pi_serial."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Hardware: " .$hardware."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi CPU Model: " .$cpu_model."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi Revision: " .$revision."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Version: " .$ph_version."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Build: " .$ph_build."\n";
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Raspberry Pi UID: " .$uid."\n";
	$url="https://www.pihome.eu/piconnect/callhome.php?ip=${external_ip}&serial=${uid}&cpu_model=${cpu_model}&hardware=${hardware}&revision=${revision}&ph_version=${ph_version}&ph_build=${ph_build}&email=${email}";
	//echo $url."\n";
	$result = url_get_contents($url);
	$result = file_get_contents($url);
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - PiHome Says: ".$result."\n";
	for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n";
}

if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Active Status: \033[41m".$new_system_controller_status."\033[0m \n"; }
if ($system_controller_mode == 0) {
	if ($debug_msg >= 0 ) { echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - System Controller Hysteresis Status: \033[41m".$hysteresis."\033[0m \n"; }
}
if ($debug_msg >= 0 ) { 
	for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n"; 
	echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Purging marked records. \n";
}
$query = purge_tables();
foreach(preg_split("/((\r?\n)|(\r\n?))/", $query) as $line){
	$conn->query($line);
}
for ($i = 0; $i < $line_len; $i++){ echo "-"; } echo "\n";
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Controller Script Ended \n";
echo "\033[32m"; for ($i = 0; $i < $line_len; $i++){ echo "*"; } echo "\033[0m  \n";
if(isset($conn)) { $conn->close();}
?>
