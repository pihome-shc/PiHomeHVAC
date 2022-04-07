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

// This script is used to provide dynamic update of the control temperature displayed by the live temperature model
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

if(isset($_GET['zone_id'])) { $zid = $_GET['zone_id']; }
if(isset($_GET['sensor_id'])) { $sid = $_GET['sensor_id']; }
if(isset($_GET['button_id'])) { $bid = $_GET['button_id']; }
if(isset($_GET['type'])) { $type = $_GET['type']; }

//following two variable set to 0 on start for array index.
$boost_index = '0';
$override_index = '0';

//following variable set to current day of the week.
$dow = idate('w');

//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
$system_controller_mode = settings($conn, 'mode') & 0b1;

//determine if using cyclic mode selection
$mode_select = settings($conn, 'mode') >> 0b1;

//query to check away status
$query = "SELECT * FROM away LIMIT 1";
$result = $conn->query($query);
$away = mysqli_fetch_array($result);
$away_status = $away['status'];

//query to check holidays status
$query = "SELECT * FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount > 0) {
	$holidays = mysqli_fetch_array($result);
        $holidays_status = $holidays['status'];
} else {
        $holidays_status = 0;
}

//---------------
//process  zones
//---------------
if ($type <= 5 || $type == 8) {
	$active_schedule = 0;
	if ($type == 8) {
		$query = "SELECT `zone`.`id`, `zone`.`name`, `zone_type`.`type`, `zone_type`.`category` FROM `zone`, `zone_type` WHERE `zone`.`id` = {$zid} AND (`zone`.`type_id` = `zone_type`.`id`) AND (`zone_type`.`category` = 1 OR `zone_type`.`category` = 2) LIMIT 1;";
	} else {
                $query = "SELECT `zone`.`id`, `zone`.`name`, `zone_type`.`type`, `zone_type`.`category` FROM `zone`, `zone_type` WHERE `zone`.`id` = {$zid} AND (`zone`.`type_id` = `zone_type`.`id`) AND (`zone_type`.`category` = 0 OR `zone_type`.`category` = 3 OR `zone_type`.`category` = 4) LIMIT 1;";
	}
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	$zone_id=$row['id'];
	$zone_name=$row['name'];
	$zone_type=$row['type'];
	$zone_category=$row['category'];

	//query to get zone current state
	$query = "SELECT * FROM zone_current_state WHERE zone_id = '{$zid}' LIMIT 1;";
	$result = $conn->query($query);
	$zone_current_state = mysqli_fetch_array($result);
	$zone_mode = $zone_current_state['mode'];
	$zone_temp_reading = $zone_current_state['temp_reading'];
	$zone_temp_target = $zone_current_state['temp_target'];
	$zone_temp_cut_in = $zone_current_state['temp_cut_in'];
	$zone_temp_cut_out = $zone_current_state['temp_cut_out'];
	$zone_ctr_fault = $zone_current_state['controler_fault'];
	$controler_seen = $zone_current_state['controler_seen_time'];
	$zone_sensor_fault = $zone_current_state['sensor_fault'];
	$sensor_seen = $zone_current_state['sensor_seen_time'];
	$temp_reading_time= $zone_current_state['sensor_reading_time'];
	$overrun= $zone_current_state['overrun'];
	if ($zone_category == 1 || $zone_category == 2) {
        	if ($zone_current_state['mode'] == 0) { $add_on_active = 0; } else { $add_on_active = 1; }
                if ($add_on_active == 1){$add_on_colour = "green";} elseif ($add_on_active == 0){$add_on_colour = "black";}
	}

        //get the current zone schedule status
        $rval=get_schedule_status($conn, $zone_id,$holidays_status,$away_status);
        $sch_status = $rval['sch_status'];
        $away_sch = $rval['away_sch'];

	//get the sensor id
	$query = "SELECT * FROM sensors WHERE zone_id = '{$zid}' LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$temperature_sensor_id=$sensor['sensor_id'];
	$temperature_sensor_child_id=$sensor['sensor_child_id'];
	$sensor_type_id=$sensor['sensor_type_id'];

	//get the node id
	$query = "SELECT node_id FROM nodes WHERE id = '{$temperature_sensor_id}' LIMIT 1;";
	$result = $conn->query($query);
	$nodes = mysqli_fetch_array($result);
	$zone_node_id=$nodes['node_id'];

	//query to get temperature from messages_in_view_24h table view
	$query = "SELECT * FROM messages_in WHERE node_id = '{$zone_node_id}' AND child_id = '{$temperature_sensor_child_id}' ORDER BY id desc LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$zone_c = $sensor['payload'];
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

	$zone_mode_main=floor($zone_mode/10)*10;
	$zone_mode_sub=floor($zone_mode%10);

	//Zone sub mode - running/ stopped different types
	/*	0 - stopped (above cut out setpoint or not running in this mode)
		1 - heating running
		2 - stopped (within deadband)
		3 - stopped (coop start waiting for system controller)
		4 - manual operation ON
		5 - manual operation OFF
	        6 - cooling running
		7 - fan running*/
        //get the current zone schedule status
        if ($zone_category == 1 || $zone_category == 2) {
                if ($sch_status =='1') {
                        $add_on_mode = $zone_current_state['mode'];
                } else {
                        if ($add_on_active == 0) { $add_on_mode = 0; } else { $add_on_mode = 114; }
                }

                if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = 90; }
                $rval=getIndicators($conn, $add_on_mode, $zone_temp_target);
        } else {
                if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = $zone_mode + 10; }
                $rval=getIndicators($conn, $zone_mode, $zone_temp_target);
        }
//---------------------------
//process standalone sensors
//---------------------------
} elseif ($type == 6 || $type == 7)  {
	$query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id,nodes.node_id, nodes.last_seen, nodes.notice_interval FROM sensors, nodes WHERE sensors.id = {$sid} AND (nodes.id = sensors.sensor_id) AND sensors.zone_id = 0 AND sensors.show_it = 1 AND sensors.pre_post = 0 LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	$sensor_id = $row['id'];
	$sensor_name = $row['name'];
	$sensor_child_id = $row['sensor_child_id'];
	$node_id = $row['node_id'];
	$node_seen = $row['last_seen'];
	$node_notice = $row['notice_interval'];
	$sensor_type_id = $row['sensor_type_id'];
	$shcolor = "green";
	if($node_notice > 0){
		$now=strtotime(date('Y-m-d H:i:s'));
	        $node_seen_time = strtotime($node_seen);
        	if ($node_seen_time  < ($now - ($node_notice*60))) { $shcolor = "red"; }
	}
	//query to get temperature from messages_in_view_24h table view
	$query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id desc LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$sensor_c = $sensor['payload'];
} elseif ($type == 9) {
//-------------------------
//process system controller
//-------------------------
	//GET BOILER DATA AND FAIL ZONES IF SYSTEM CONTROLLER COMMS TIMEOUT
	//query to get last system_controller operation time and hysteresis time
	$query = "SELECT * FROM system_controller LIMIT 1";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$sc_count=$result->num_rows;
        $system_controller_id = $row['id'];
	$system_controller_name = $row['name'];
	$system_controller_max_operation_time = $row['max_operation_time'];
	$system_controller_hysteresis_time = $row['hysteresis_time'];
	$sc_mode  = $row['sc_mode'];
        $sc_active_status  = $row['active_status'];
	$hvac_relays_state = $row['hvac_relays_state'];

	//Get data from nodes table
	$query = "SELECT * FROM nodes WHERE id = {$row['node_id']} AND status IS NOT NULL LIMIT 1";
	$result = $conn->query($query);
	$system_controller_node = mysqli_fetch_array($result);
	$system_controller_node_id = $system_controller_node['node_id'];
	$system_controller_seen = $system_controller_node['last_seen'];
	$system_controller_notice = $system_controller_node['notice_interval'];

	//Check System Controller Fault
	$system_controller_fault = 0;
	if($system_controller_notice > 0){
		$now=strtotime(date('Y-m-d H:i:s'));
	  	$system_controller_seen_time = strtotime($system_controller_seen);
	  	if ($system_controller_seen_time  < ($now - ($system_controller_notice*60))){
			$system_controller_fault = 1;
		}
	}
	if ($sc_count != 0) {
		//query to get last system_controller statues change time
		$query = "SELECT * FROM controller_zone_logs ORDER BY id desc LIMIT 1 ";
		$result = $conn->query($query);
		$system_controller_onoff = mysqli_fetch_array($result);
		$system_controller_last_off = $system_controller_onoff['stop_datetime'];

		//check if hysteresis is passed its time or not
		$hysteresis='0';
		if ($system_controller_mode == 0 && isset($system_controller_last_off)){
			$system_controller_last_off = strtotime( $system_controller_last_off );
			$system_controller_hysteresis_time = $system_controller_last_off + ($system_controller_hysteresis_time * 60);
			$now=strtotime(date('Y-m-d H:i:s'));
			if ($system_controller_hysteresis_time > $now){$hysteresis='1';}
		} else {
			$hysteresis='0';
		}

		if ($system_controller_mode == 1) {
                	switch ($sc_mode) {
                        	case 0:
                                	echo '<i class="fa fa-circle-o-notch">';
                                        break;
                                case 1:
					if ($active_schedule) {
                                               	if ($hvac_relays_state & 0b100) { $system_controller_colour="red"; } else { $system_controller_colour="blue"; }
					} else {
						$system_controller_colour="";
					}
                                        echo '<i class="ionicons ion-flame fa-1x '.$system_controller_colour.'">';
					break;
				case 2:
                                        if ($active_schedule) {
                                               	if ($hvac_relays_state & 0b010) { $system_controller_colour="blueinfo"; } else { $system_controller_colour="orange"; }
                                        } else {
                                                $system_controller_colour="";
                                        }
                                        echo '<i class="fa fa-snowflake-o fa-1x '.$system_controller_colour.'">';
                                        break;
                                case 3:
					if ($hvac_relays_state == 0b000) {
                                		if ($sc_active_status==1) {
                                       			$system_controller_colour="green";
                                		} elseif ($sc_active_status==0) {
                                       			$system_controller_colour="";
                                		}
						echo '<i class="fa fa-circle-o-notch fa-1x '.$system_controller_colour.'">';
					} elseif ($hvac_relays_state & 0b100) {
						echo '<i class="ionicons ion-flame fa-1x red">';
					} elseif ($hvac_relays_state & 0b010) {
						echo '<i class="fa fa-snowflake-o fa-1x blueinfo">';
					}
					break;
                                case 4:
                                        if ($hvac_relays_state == 0b000) {
                                              	$system_controller_colour="green";
                                                echo '<i class="fa fa-circle-o-notch fa-1x '.$system_controller_colour.'">';
                                        } elseif ($hvac_relays_state & 0b100) {
                                                echo '<i class="ionicons ion-flame fa-1x red">';
                                        } elseif ($hvac_relays_state & 0b010) {
                                                echo '<i class="fa fa-snowflake-o fa-1x blueinfo">';
                                        }
                                        break;
                                case 5:
                                        echo '<img src="images/hvac_fan_30.png" border="0"></h3>';
                                        break;
                                case 6:
					if ($hvac_relays_state & 0b100) { $system_controller_colour = "red"; } else { $system_controller_colour = "blue"; }
                                        echo '<i class="ionicons ion-flame fa-1x '.$system_controller_colour.'">';
                                        break;
                                case 7:
                                        if ($hvac_relays_state & 0b010) { $system_controller_colour = "blueinfo"; } else { $system_controller_colour = ""; }
                                        echo '<i class="fa fa-snowflake-o fa-1x '.$system_controller_colour.'">';
                                        break;
				default:
                                        echo '<i class="fa fa-circle-o-notch">';
                                }
		} else {
                       	if ($sc_active_status==1) {
				$system_controller_colour="red";
			} elseif ($sc_active_status==0) {
				$system_controller_colour="blue";
			}
			if ($sc_mode==0) {
                               	$system_controller_colour="";
                        }
                        echo '<i class="ionicons ion-flame fa-1x '.$system_controller_colour.'">';
		}
		if($system_controller_fault=='1') {echo'<i class="fa ion-android-cancel fa-1x red">';}
		elseif($hysteresis=='1') {echo'<i class="fa fa-hourglass fa-1x orange">';}
		else { echo'';}
	}
} elseif ($type == 10) {
	switch ($bid) {
        	case 1:
        		$query = "SELECT status FROM boost WHERE status = '1' LIMIT 1";
        		$result = $conn->query($query);
        		$boost_status=mysqli_num_rows($result);
        		if ($boost_status ==1) {$boost_status='red';} else {$boost_status='blue';}
        		echo '<i class="fa fa-circle fa-fw '.$boost_status.'">';
			break;
                case 2:
        		$query = "SELECT status FROM override WHERE status = '1' LIMIT 1";
        		$result = $conn->query($query);
        		$override_status=mysqli_num_rows($result);
        		if ($override_status==1) {$override_status='red';}else{$override_status='blue';}
        		echo '<i class="fa fa-circle fa-fw '.$override_status.'">';
                        break;
                case 3:
			$start_time_temp_offset = "";
			$offset_status='blue';
		        $query = "SELECT id FROM zone;";
		        $zresults = $conn->query($query);
		        $rowcount=mysqli_num_rows($zresults);
		        if ($rowcount > 0) {
				while ($zrow = mysqli_fetch_assoc($zresults)) {
					$zone_id = $zrow['id'];
                			$rval=get_schedule_status($conn, $zone_id,"0","0");
		                	$sch_status = $rval['sch_status'];
                			if ($sch_status == '1') {
						$query = "SELECT * FROM schedule_time_temp_offset WHERE schedule_daily_time_id = ".$rval['time_id']." AND status = 1 LIMIT 1";
						$oresult = $conn->query($query);
						if (mysqli_num_rows($oresult) > 0) {
							$orow = mysqli_fetch_array($oresult);
							$offset_status='red';
	        		                        $low_temp = $orow['low_temperature'];
        	                		        $high_temp = $orow['high_temperature'];
		                	                $sensors_id = $orow['sensors_id'];
                		        	        $start_time_offset = $orow['start_time_offset'];
                                			if ($sensors_id == 0) {
                                        			$node_id = 1;
			                                        $child_id = 0;
        			                        } else {
                	        		                $query = "SELECT sensor_id, sensor_child_id FROM sensors WHERE id = ".$sensors_id." LIMIT 1;";
                        	                		$sresult = $conn->query($query);
		                                	        $srow = mysqli_fetch_array($sresult);
                		                        	$sensor_id = $srow['sensor_id'];
	                        		                $child_id = $srow['sensor_child_id'];
        	                                		$query = "SELECT node_id FROM nodes WHERE id = ".$sensor_id." LIMIT 1;";
		                	                        $nresult = $conn->query($query);
                		        	                $nrow = mysqli_fetch_array($nresult);
                                			        $node_id = $nrow['node_id'];
			                                }
        			                        $query = "SELECT payload FROM `messages_in` WHERE `node_id` = '".$node_id."' AND `child_id` = ".$child_id." ORDER BY `datetime` DESC LIMIT 1;";
                	        		        $tresult = $conn->query($query);
		                        	        $rowcount=mysqli_num_rows($tresult);
                		                	if ($rowcount > 0) {
                                		        	$trow = mysqli_fetch_array($tresult);
	                                        		$outside_temp = $trow['payload'];
		        	                                if ($outside_temp >= $low_temp && $outside_temp <= $high_temp) {
                			                                $temp_span = $high_temp - $low_temp;
                        			                        $step_size = $start_time_offset/$temp_span;
                                	        		        $start_time_temp_offset = "Start -".($high_temp - $outside_temp) * $step_size;
		                                                } elseif ($outside_temp < $low_temp ) {
                		                                        $start_time_temp_offset = "Start -".$start_time_offset;
                                		        	} else {
									$start_time_temp_offset = "Start -0";
								}
                		                	}
						}
					}
				}
			}
//		        echo '<i class="fa fa-circle fa-fw '.$offset_status.'"></i></small><small class="statuszoon">'.$start_time_temp_offset.'&nbsp</small>';
                        break;
                case 4:
		        $query = "SELECT * FROM schedule_night_climate_time LIMIT 1";
		        $results = $conn->query($query);
		        $row = mysqli_fetch_assoc($results);
		        if ($row['status'] == 1) {$night_status='red';}else{$night_status='blue';}
        		echo '<i class="fa fa-circle fa-fw '.$night_status.'">';
                        break;
                case 5:
		        $query = "SELECT * FROM system_controller LIMIT 1";
		        $result = $conn->query($query);
		        $row = mysqli_fetch_array($result);
		        $sc_mode = $row['sc_mode'];

		        $query = "SELECT * FROM away LIMIT 1";
		        $result = $conn->query($query);
		        $away = mysqli_fetch_array($result);
		        if ($away['status']=='1') { $awaystatus="red"; } elseif ( $away['status']=='0' || $sc_mode == 0) { $awaystatus="blue"; }
		        if ($sc_mode != 0 ) { echo '<a style="font-style: normal;" href="javascript:active_away();">'; }
        		echo '<i class="fa fa-circle fa-fw '.$awaystatus.'">';
                        break;
                case 6:
		        $query = "SELECT status FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
		        $result = $conn->query($query);
		        $holidays_status=mysqli_num_rows($result);
		        if ($holidays_status=='1'){$holidaystatus="red";}elseif ($holidays_status=='0'){$holidaystatus="blue";}
        		echo '<i class="fa fa-circle fa-fw '.$holidaystatus.'">';
                        break;
	}
}

switch ($type) {
        case 1:
                // return the temperature string to 1 decimal place
                if ($sensor_type_id == 3) {
                        if ($zone_c == 0) { echo 'OFF'; } else { echo 'ON'; }
                } else {
                        $unit = SensorUnits($conn,$sensor_type_id);
                        echo number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit;
                }
                break;
        case 2:
                        echo '<i class="fa fa-circle fa-fw ' . $rval['status'] . '">';
                break;
        case 3:
                        if ($sensor_type_id != 3) { echo $rval['target']; }
                break;
        case 4:
                        echo '<i class="fa ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' fa-fw">';
                break;
        case 5:
                        if($overrun == 1) { echo '<i class="fa ion-ios-play-outline orange fa-fw">'; }
                break;
        case 6:
                // return the temperature string to 1 decimal place
                if ($sensor_type_id == 3) {
                        if ($sensor_c == 0) { echo 'OFF'; } else { echo 'ON'; }
                } else {
                        $unit = SensorUnits($conn,$sensor_type_id);
                        echo number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit;
                }
                break;
        case 7:
                        echo '<i class="fa fa-circle fa-fw '.$shcolor.'">';
                break;
        case 8:
                if (($zone_category == 1 && $sensor_type_id != 3)) {
               		$unit = SensorUnits($conn,$sensor_type_id);
                        echo number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit;
                } elseif ($zone_category == 1 && $sensor_type_id == 3) {
                	if ($add_on_active == 0) { echo 'OFF'; } else { echo 'ON'; }
                } else {
                        echo '<i class="fa fa-power-off fa-1x '.$add_on_colour.'">';
                }
                break;
        default:
}
?>
