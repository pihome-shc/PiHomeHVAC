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

/* This script is used to provide dynamic update of data dispayed on the Home, OneTouch, LiveTempaerature and Sensors displays

The script is passed 2 parameters, an 'id' and a 'type'.
The id identifies the screen object to be written to and the type identifies attributes of the screen object.

For example to update a Zone tile on the home screen :-
	The 'id' would identify which Zone tile was to be updated using its 'zone_id'
	The 'type' would identify which attribule of the Zone tile was to be updated, eg type = 1 would update the temperature value
	and type = 2 would update the left most 'status' indicator.

The various routines will gater the data required to perform the update, this will be returned as a sting.
*/
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');
require_once(__DIR__.'/st_inc/session.php');

$theme = settings($conn, 'theme');
$rval = os_info();
if (array_key_exists('ID', $rval)) {
        if (strpos($rval["ID"], "debian") !== false || strpos($rval["ID"], "ubuntu") !== false) {
                $web_user_name = "www-data";
        } elseif (strpos($rval["ID"], "archarm") !== false) {
                $web_user_name = "http";
        }
} else {
        $web_user_name = "www-data";;
}

if(isset($_GET['id'])) { $id = $_GET['id']; }
if(isset($_GET['type'])) { $type = $_GET['type']; }

//following two variable set to 0 on start for array index.
$boost_index = '0';
$override_index = '0';

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

if ($type <= 5 || $type == 38) {
	//---------------
	//process  zones
	//---------------
	$active_schedule = 0;
	$query = "SELECT `zone`.`id`, `zone_type`.`category` FROM `zone`, `zone_type` WHERE `zone`.`id` = {$id} AND `zone`.`type_id` = `zone_type`.`id`;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	$zone_id=$row['id'];
	$zone_category=$row['category'];

	//query to get zone current state
	$query = "SELECT * FROM zone_current_state WHERE zone_id = '{$id}' LIMIT 1;";
	$result = $conn->query($query);
	$zone_current_state = mysqli_fetch_array($result);
	$zone_mode = $zone_current_state['mode'];
	$zone_temp_reading = $zone_current_state['temp_reading'];
        if ($zone_category == 2) { $zone_temp_target = ""; } else { $zone_temp_target = $zone_current_state['temp_target']; }
	$zone_temp_cut_in = $zone_current_state['temp_cut_in'];
	$zone_temp_cut_out = $zone_current_state['temp_cut_out'];
	$zone_ctr_fault = $zone_current_state['controler_fault'];
	$controler_seen = $zone_current_state['controler_seen_time'];
	$zone_sensor_fault = $zone_current_state['sensor_fault'];
	$sensor_seen = $zone_current_state['sensor_seen_time'];
	$temp_reading_time= $zone_current_state['sensor_reading_time'];
	$overrun= $zone_current_state['overrun'];
        $schedule = $zone_current_state['schedule'];

        //get the current zone schedule status
        $sch_status = $schedule & 0b1;
        $away_sch = ($schedule >> 1) & 0b1;

	if ($zone_category == 1 || $zone_category == 2  || $zone_category == 5) {
        	if ($zone_mode == 0) { $add_on_active = 0; } else { $add_on_active = 1; }
                if (($add_on_active == 1 && $away_status == 0) && $zone_category != 5) { $add_on_colour = "green"; } elseif ($add_on_active == 0 || ($add_on_active == 1 && $zone_category == 5)) {$add_on_colour = "black"; }
	}

	//get the sensor id
//	$query = "SELECT * FROM sensors WHERE zone_id = '{$id}';";
	$query = "SELECT * FROM `sensors` WHERE (now() < DATE_ADD(`last_seen`, INTERVAL `fail_timeout` MINUTE) OR `fail_timeout` = 0) AND zone_id = '{$id}';";
	$sresults = $conn->query($query);
        // catch startup condition where the sensors have not yet been read
        $sensor_count = mysqli_num_rows($sresults);
        if ($sensor_count == 0) {
                $query = "SELECT * FROM `sensors` WHERE zone_id = '{$zone_id}';";
                $sresults = $conn->query($query);
                $sensor_count = mysqli_num_rows($sresults);
        }
	$zone_c = 0;
        while ($srow = mysqli_fetch_assoc($sresults)) {
		$sensor_id = $srow['sensor_id'];
                $sensor_type_id = $srow['sensor_type_id'];
		$zone_c = $zone_c + $srow['current_val_1'];
	}
        if ($sensor_count > 0) { $zone_c = $zone_c/$sensor_count; }
	$unit = SensorUnits($conn,$sensor_type_id);
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
                if ($sch_status == 1) {
                        $add_on_mode = $zone_mode;
                } else {
                	if ($add_on_active == 0) {
                        	$add_on_mode = 0;
                        } elseif ($zone_category == 1 || $zone_category == 2) {
                        	$add_on_mode = $zone_mode;
                      	} else {
                        	$add_on_mode = 114;
                        }
                }
                if ($away_status == 1 && $away_sch == 1 ) { $add_on_mode = 90; }
                $rval=getIndicators($conn, $add_on_mode, $zone_temp_target);
        } else {
                if ($away_status == 1 && $away_sch == 1 ) {
                        if ($zone_category == 5) { $zone_mode = 90; } else { $zone_mode = $zone_mode + 10; }
                }
                $rval=getIndicators($conn, $zone_mode, $zone_temp_target);
        }
	//-------------------------------
	//process return strings by type
	//-------------------------------
	switch ($type) {
                case 1:
                        if ($zone_category != 2) {
				if ($sensor_type_id != 3) {
					if ($sensor_count > 1) { // add symbol to indicate that this is an average reading
						echo number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit.$lang['mean'];
					} else {
                               		 	echo number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit;
					}
                        	} else {
                                	if ($add_on_active == 0) { echo 'OFF'; } else { echo 'ON'; }
				}
                        } else {
                                echo '<i class="bi bi-power '.$add_on_colour.'" style="font-size: 1.4rem;">';
                        }
                        break;
	        case 2:
        		echo '<i class="bi bi-circle-fill '.$rval['status'].'" style="font-size: 0.55rem;">';
                	break;
	        case 3:
        	       	if ($sensor_type_id != 3) { echo $rval['target']; }
                	break;
	        case 4:
	        	echo '<i class="bi ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' bi-fw">';
                	break;
	        case 5:
        	        if($overrun == 1) { echo '<i class="bi bi-play-fill orange-red">'; }
                	break;
		// livetemp
		case 38:
			echo number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit;
			break;
	        default:
	}
} elseif ($type == 6 || $type == 7 || $type == 8)  {
	//---------------------------
	//process standalone sensors
	//---------------------------

        $query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id, sensors.current_val_1, sensors.current_val_2,
        	nodes.node_id, nodes.last_seen, nodes.notice_interval
                FROM sensors, nodes
                WHERE sensors.id = {$id} AND nodes.id = sensors.sensor_id AND (sensors.id NOT IN (SELECT zone_sensor_id FROM zone_sensors)) AND sensors.show_it = 1 LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	$sensor_id = $row['id'];
	$sensor_name = $row['name'];
	$sensor_child_id = $row['sensor_child_id'];
	$node_id = $row['node_id'];
	$node_seen = $row['last_seen'];
	$node_notice = $row['notice_interval'];
	$sensor_type_id = $row['sensor_type_id'];
	$shcolor = "#00C853";
	if($node_notice > 0){
		$now=strtotime(date('Y-m-d H:i:s'));
	        $node_seen_time = strtotime($node_seen);
        	if ($node_seen_time  < ($now - ($node_notice*60))) { $shcolor = "red"; }
	}
        //query to get sensor reading from messages_in table
        if ($type == 8) {
//                $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' AND sub_type = 1 ORDER BY id desc LIMIT 1;";
                $sensor_r = $row['current_val_2'];
        } else {
//                if ($sensor_type_id == 4) {
//                        $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' AND sub_type = 0 ORDER BY id desc LIMIT 1;";
//                } else {
//                        $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id desc LIMIT 1;";
//                }
                $sensor_r = $row['current_val_1'];
        }
//        $result = $conn->query($query);
//        $sensor = mysqli_fetch_array($result);
//        $sensor_r = $sensor['payload'];
        //-------------------------------
        //process return strings by type
        //-------------------------------
        switch ($type) {
                case 6:
                        // return the temperature string to 1 decimal place
                        if ($sensor_type_id == 3) {
                                $deg_msg = floor($sensor_r);
                                $query = "SELECT message FROM sensor_messages WHERE message_id = {$deg_msg} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                                $result = $conn->query($query);
                                $rowcount=$result->num_rows;
                                if ($rowcount == 0) {
                                        if ($sensor_r == 0) { echo 'OFF'; } else { echo 'ON'; }
                                } else {
                                        $sensor_message = mysqli_fetch_array($result);
                                        echo $sensor_message['message'];
                                }
                        } elseif ($sensor_type_id == 4) {
                                $deg_msg = floor($sensor_r);
                                $query = "SELECT message FROM sensor_messages WHERE message_id = {$deg_msg} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                                $result = $conn->query($query);
                                $sensor_message = mysqli_fetch_array($result);
                                echo $sensor_message['message'];
                        } else {
                                $unit = SensorUnits($conn,$sensor_type_id);
                                echo number_format(DispSensor($conn,$sensor_r,$sensor_type_id),1).$unit;
                        }
                        break;
                case 7:
                        if ($sensor_type_id == 4) {
                                $s_color = floor($sensor_r);
                                $query = "SELECT status_color FROM sensor_messages WHERE message_id = {$s_color} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                                $result = $conn->query($query);
                                $sensor_message = mysqli_fetch_array($result);
                                $shcolor = $sensor_message['status_color'];
                        }
                        echo '<i class="bi bi-circle-fill" style="font-size: 0.55rem; color: '.$shcolor.';">';
                        break;
                case 8:
                        if ($sensor_type_id == 4) {
                                $s_msg = floor($sensor_r);
                                $query = "SELECT message FROM sensor_messages WHERE message_id = {$s_msg} AND sub_type = 1 AND sensor_id = {$sensor_id} LIMIT 1;";
                                $result = $conn->query($query);
                                $right_message = mysqli_fetch_array($result);
                                echo $right_message['message'];
                        }
                        break;
	        default:
	}
} elseif ($type == 9 || $type == 10) {
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

	if(!is_null($row['node_id'])) {
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

			if ($type == 9) {
				if ($system_controller_mode == 1) {
        		        	switch ($sc_mode) {
                		        	case 0:
                        		        	echo '<i class="bi bi-power" style="font-size: 1.4rem;">';
                                		        break;
		                                case 1:
							if ($active_schedule) {
                		                               	if ($hvac_relays_state & 0b100) { $system_controller_colour="colorize-red"; } else { $system_controller_colour="colorize-blue"; }
							} else {
								$system_controller_colour="";
							}
                                                        echo '<i class="bi bi-fire '.$system_controller_colour.'" style="font-size: 1.4rem;">';
							break;
						case 2:
                	        	                if ($active_schedule) {
                        	        	               	if ($hvac_relays_state & 0b010) { $system_controller_colour="blueinfo"; } else { $system_controller_colour="orange-red"; }
                                	        	} else {
                                        	        	$system_controller_colour="";
	                                        	}
	        	                                echo '<i class="bi bi-snow '.$system_controller_colour.'" style="font-size: 1.4rem;">';
        	        	                        break;
                	        	        case 3:
							if ($hvac_relays_state == 0b000) {
                                				if ($sc_active_status==1) {
                                       					$system_controller_colour="#00C853";
	                                			} elseif ($sc_active_status==0) {
        	                               				$system_controller_colour="";
	                	                		}
								echo '<i class="bi bi-power '.$system_controller_colour.'" style="font-size: 1.4rem;">';
							} elseif ($hvac_relays_state & 0b100) {
                                                                echo '<i class="bi bi-fire red" style="font-size: 1.4rem;">';
							} elseif ($hvac_relays_state & 0b010) {
								echo '<i class="bi bi-snow blueinfo" style="font-size: 1.4rem;">';
							}
							break;
        	                        	case 4:
                	                        	if ($hvac_relays_state == 0b000) {
                        	                      		$system_controller_colour="#00C853";
	                        	                        echo '<i class="bi bi-power '.$system_controller_colour.'" style="font-size: 1.4rem;">';
        	                        	        } elseif ($hvac_relays_state & 0b100) {
                                                                echo '<i class="bi bi-fire red" style="font-size: 1.4rem;">';
	                        	                } elseif ($hvac_relays_state & 0b010) {
        	                        	                echo '<i class="bi bi-snow blueinfo" style="font-size: 1.4rem;">';
                	                        	}
	                	                        break;
        	                	        case 5:
                	                	        echo '<img src="images/hvac_fan_30.png" border="0"></h3>';
                        	                	break;
	                                	case 6:
							if ($hvac_relays_state & 0b100) { $system_controller_colour = "colorize-red"; } else { $system_controller_colour = "colorize-blue"; }
                                                        echo '<i class="bi bi-fire '.$system_controller_colour.'" style="font-size: 1.4rem;">';
                                                        break;
        	        	                        break;
                	        	        case 7:
                        	        	        if ($hvac_relays_state & 0b010) { $system_controller_colour = "blueinfo"; } else { $system_controller_colour = ""; }
                                	        	echo '<i class="bi bi-snow '.$system_controller_colour.'" style="font-size: 1.4rem;">';
	                                        	break;
						default:
        		                                echo '<i class="bi bi-power" style="font-size: 1.4rem;">';
                		                }
				} else {
        		               	if ($sc_active_status==1) {
						$system_controller_colour="red";
					} elseif ($sc_active_status==0) {
						$system_controller_colour="blueinfo";
					}
					if ($sc_mode==0) {
                		               	$system_controller_colour="";
                        		}
                                        echo '<i class="bi bi-fire '.$system_controller_colour.'" style="font-size: 1.4rem;">';
				}
			} elseif ($type == 10) {
				if($system_controller_fault=='1') {echo'<i class="bi bi-x-circle-fill red">';}
				elseif($hysteresis=='1') {echo'<i class="bi bi-hourglass-split orange-red">';}
				else { echo'';}
			}
		}
	} else {
		if ($type == 9) { echo '<i class="bi bi-fire" style="font-size: 1.4rem;">'; } else { echo''; }
	}
} elseif ($type == 11 || $type == 12) {
	//-------------------------------------------
	//process homelist and onetouch page buttons
	//-------------------------------------------
	switch ($id) {
        	case 1:
        		$query = "SELECT status FROM boost WHERE status = '1' LIMIT 1";
        		$result = $conn->query($query);
        		$boost_status=mysqli_num_rows($result);
        		if ($boost_status ==1) {$boost_status='red';} else {$boost_status='blueinfo';}
        		echo '<i class="bi bi-circle-fill '.$boost_status.'" style="font-size: 0.55rem;">';
			break;
                case 2:
        		$query = "SELECT status FROM override WHERE status = '1' LIMIT 1";
        		$result = $conn->query($query);
        		$override_status=mysqli_num_rows($result);
        		if ($override_status==1) {$override_status='red';}else{$override_status='blueinfo';}
        		echo '<i class="bi bi-circle-fill '.$override_status.'" style="font-size: 0.55rem;">';
                        break;
                case 3:
			$start_time_temp_offset = "";
			$offset_status='blueinfo';
		        $query = "SELECT id FROM zone;";
		        $zresults = $conn->query($query);
		        $rowcount=mysqli_num_rows($zresults);
		        if ($rowcount > 0) {
				while ($zrow = mysqli_fetch_assoc($zresults)) {
					$zone_id = $zrow['id'];
//                			$rval=get_schedule_status($conn, $zone_id,"0","0");
//		                	$sch_status = $rval['sch_status'];
                                        $query = "SELECT schedule, sch_time_id FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
                                        $result = $conn->query($query);
                                        $zcs = mysqli_fetch_array($result);
                                        $time_id = $zcs['sch_time_id'];
                                        $schedule = $zcs['schedule'];
                                        $sch_status = $schedule & 0b1;
                			if ($sch_status == 1) {
						$query = "SELECT * FROM schedule_time_temp_offset WHERE schedule_daily_time_id = ".$time_id." AND status = 1 LIMIT 1";
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
			if ($type == 11) {
				echo '<i class="bi bi-circle-fill '.$offset_status.'" style="font-size: 0.55rem;">';
			} elseif ($type == 12) {
				echo $start_time_temp_offset.'&nbsp';
			}
//		        echo '<i class="fa fa-circle fa-fw '.$offset_status.'"></i></small><small class="statuszoon">'.$start_time_temp_offset.'&nbsp</small>';
                        break;
                case 4:
		        $query = "SELECT * FROM schedule_night_climate_time LIMIT 1";
		        $results = $conn->query($query);
		        $row = mysqli_fetch_assoc($results);
		        if ($row['status'] == 1) {$night_status='red';}else{$night_status='blueinfo';}
        		echo '<i class="bi bi-circle-fill '.$night_status.'" style="font-size: 0.55rem;">';
                        break;
                case 5:
		        $query = "SELECT * FROM system_controller LIMIT 1";
		        $result = $conn->query($query);
		        $row = mysqli_fetch_array($result);
		        $sc_mode = $row['sc_mode'];

		        $query = "SELECT * FROM away LIMIT 1";
		        $result = $conn->query($query);
		        $away = mysqli_fetch_array($result);
		        if ($away['status']=='1') { $awaystatus="red"; } elseif ( $away['status']=='0' || $sc_mode == 0) { $awaystatus="blueinfo"; }
//		        if ($sc_mode != 0 ) { echo '<a style="font-style: normal;" href="javascript:active_away();">'; }
        		echo '<i class="bi bi-circle-fill '.$awaystatus.'" style="font-size: 0.55rem;">';
                        break;
                case 6:
		        $query = "SELECT COUNT(*) AS count_holiday_schedules FROM schedule_daily_time_zone JOIN holidays hs on schedule_daily_time_zone.holidays_id = hs.id WHERE hs.status = 1;";
        		$hresult = $conn->query($query);
        		$hrow = mysqli_fetch_array($hresult);
        		if ($hrow['count_holiday_schedules']== 0) {
                		$holidaystatus = "black";
			} else {
			        $query = "SELECT status FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
			        $result = $conn->query($query);
			        $holidays_status=mysqli_num_rows($result);
		        	if ($holidays_status=='1'){$holidaystatus="red";}elseif ($holidays_status=='0'){$holidaystatus="blueinfo";}
			}
        		echo '<i class="bi bi-circle-fill '.$holidaystatus.'" style="font-size: 0.55rem;">';
                        break;
	}
} elseif ($type == 13) {
	//------------
	//return time
	//------------
        $query = "SELECT `test_mode`, TIME_FORMAT(`test_run_time`, '%H:%i') AS test_run_time FROM `system`;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Session Timed Out';
        if ($row['test_mode'] == 0) {
        	if ($id == 0) { echo $username.'&nbsp;&nbsp; - '.date("H:i"); } else { echo '&nbsp;&nbsp;'.$username.'&nbsp;&nbsp; - '.date("H:i"); }
	} else {
                if ($id == 0) { echo $username.'&nbsp;&nbsp; - '.$row['test_run_time'].'&nbsp;(TEST TIME)'; } else { echo '&nbsp;&nbsp;'.$username.'&nbsp;&nbsp; - '.$row['test_run_time'].'&nbsp;(TEST TIME)'; }
	}
} elseif ($type == 14) {
	//---------------
	//return weather
	//---------------
        echo ShowWeather($conn);
} elseif ($type == 15) {
	//-----------------------------
	//return schedule running time
	//-----------------------------
        $query = "SELECT * FROM system_controller LIMIT 1";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $system_controller_id = $row['id'];

	$query="select date(start_datetime) as date,
        sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
        sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts,
        (sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
        from controller_zone_logs WHERE date(start_datetime) = CURDATE() AND zone_id = ".$system_controller_id." GROUP BY date(start_datetime) asc";
        $result = $conn->query($query);
        $system_controller_time = mysqli_fetch_array($result);
       	$system_controller_time_total = $system_controller_time['total_minuts'];
        $system_controller_time_on = $system_controller_time['on_minuts'];
        $system_controller_time_save = $system_controller_time['save_minuts'];
        if($system_controller_time_on >0){      echo ' <i class="bi bi-clock"></i>&nbsp'.secondsToWords(($system_controller_time_on)*60);}
} elseif ($type == 16) {
	//----------------------
	//process sensors by id
	//----------------------
        $query = "SELECT * FROM sensors ORDER BY sensor_id asc;";
        $results = $conn->query($query);
	while ($srow = mysqli_fetch_assoc($results)) {
	        $s_id = $srow['id'];
        	$s_name = $srow['name'];
	        $sensor_id = $srow['sensor_id'];
        	$sensor_child_id = $srow['sensor_child_id'];
	        $sensor_type_id = $srow['sensor_type_id'];
        	$sensor_current_val_1 = $srow['current_val_1'];
	        $query = "SELECT * FROM nodes where id = {$sensor_id} LIMIT 1;";
        	$nresult = $conn->query($query);
	        $nrow = mysqli_fetch_array($nresult);
        	$node_id = $nrow['node_id'];
	        $last_seen = $nrow['last_seen'];
		$batquery = "select * from nodes_battery where node_id = '{$node_id}' ORDER BY id desc limit 1;";
		$batresults = $conn->query($batquery);
		$bcount = mysqli_num_rows($batresults);
		if ($bcount > 0) { $brow = mysqli_fetch_array($batresults); }
		$unit = SensorUnits($conn,$sensor_type_id);
		echo '<div class="list-group-item">
			<div class="form-group row">
				<div class="text-start">&nbsp&nbsp'.$nrow['node_id'].'_'.$sensor_child_id.' - '.$s_name.'</div>
			</div>
			<div class="form-group row">';
				if ($bcount > 0) { echo '<div class="text-start">&nbsp&nbsp<i class="bi bi-battery-half"></i> '.round($brow ['bat_level'],0).'% - '.$brow ['bat_voltage'].'</div>'; } else { echo '<div class="text-start">&nbsp&nbsp<i class="bi bi-battery-half"></i></div>'; }
			echo '</div>
			<div class="form-group row">
				<div class="d-flex justify-content-between">';
        				if ($sensor_type_id != 4) { echo '<span class="text">&nbsp&nbsp<i class="bi bi-thermometer-half red"></i> - '.$sensor_current_val_1.$unit.'</span>';} else { echo '<span class="text">&nbsp&nbsp<i class="bi bi-thermometer-half red"></i></span>'; }
        				if (time() - strtotime($last_seen) > 60*60*24) { $disabled = "disabled"; $content_msg = $lang['no_sensors_last24h'];} else { $disabled = ""; $content_msg = "";}
	        			echo '<span class="text-muted small" data-bs-toggle="tooltip" title="'.$content_msg.'"><button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' fw-bolder btn-xs" onclick="sensor_last24h(`'.$s_id.'`, `'.$s_name.'`, `'.$node_id.'`, `'.$sensor_child_id.'`);" '.$disabled.'><em>'.$last_seen.'&nbsp</em></button>&nbsp</span>
        	                </div>
			</div>
		</div> ';
	}
} elseif ($type == 17) {
        //---------------------------------------
        //process running time for All Schedules
        //---------------------------------------
        $query = "SELECT SUM(`run_time`) AS run_time FROM `schedule_daily_time`;";
	$result = $conn->query($query);
        $row = mysqli_fetch_array($result);
	echo ' <i class="bi bi-clock"></i>&nbspAll Schedule:&nbsp' .secondsToWords($row['run_time']);
} elseif ($type == 18 || $type == 19) {
        //------------------------------------------------------
        //return the schedule status and temp for schedule by id
        //------------------------------------------------------
	if ($type == 18) { $holiday_id = 0; } else { $holiday_id = 1; }
        $query = "SELECT time_status, category, FORMAT(max(temperature),2) as max_c, sensor_type_id
                FROM schedule_daily_time_zone_view
                WHERE time_id = {$id} AND holidays_id = {$holiday_id} AND (tz_status = 1 OR (tz_status = 0 AND disabled = 1));";
        $results = $conn->query($query);
	$row = mysqli_fetch_array($results);
        if($row["time_status"] == 0){
                $shactive="bluesch";
        } else {
                $query = "SELECT schedule FROM zone_current_state WHERE sch_time_id = {$id} AND schedule = 1 LIMIT 1;";
                $result = $conn->query($query);
                $rowcount=mysqli_num_rows($result);
                if ($rowcount > 0) {
                        $shactive="redsch";
                } else {
                        $shactive="orangesch";
                }
        }
        $query = "SELECT  * FROM `schedule_daily_time_zone` WHERE `schedule_daily_time_id` = {$id};";
        $results = $conn->query($query);
        $count = mysqli_num_rows($results);
	$query = "SELECT COUNT(*) AS dis_cont FROM `schedule_daily_time_zone` WHERE ((`status` = 0 AND `disabled` = 1) OR (`status` = 0 AND `disabled` = 0)) AND `schedule_daily_time_id` = {$id};";
        $result = $conn->query($query);
        $sdrow = mysqli_fetch_array($result);
        if ($row["time_status"] == 0 || $sdrow["dis_cont"] == $count) {
                echo '<div class="circle bluesch_disable"><p class="schdegree">D</p></div>';
        } else {
                echo '<div class="circle ' . $shactive . '">';
                        if($row["category"] <> 2 && $row["sensor_type_id"] <> 3) {
                                $unit = SensorUnits($conn,$row['sensor_type_id']);
                                echo '<p class="schdegree">' . DispSensor($conn, number_format($row["max_c"], 1), $row["sensor_type_id"]) . $unit . '</p>';
                        }
                echo ' </div>';
        }
} elseif ($type == 20) {
        //----------------------------------------------
        //used by request.js when zone state is toggled
        //----------------------------------------------
        $query = "SELECT zone_id, schedule FROM zone_current_state WHERE sch_time_id = {$id} LIMIT 1;";
        $result = $conn->query($query);
	$row = mysqli_fetch_array($result);
        if ($row["schedule"] == 0) {
	        $shactive="orangesch_list";
	} else {
                $shactive="redsch_list";
	}

        $query = "SELECT sensor_type_id FROM sensors WHERE zone_id = '{$row['$zone_id']}' LIMIT 1;";
        $result = $conn->query($query);
        $sensor = mysqli_fetch_array($result);
        $sensor_type_id=$sensor['sensor_type_id'];

        $c_f = settings($conn, 'c_f');
        if ($c_f == 0) { $units = 'C'; } else { $units = 'F'; }

	if ($srow['disabled'] == 0) {
		echo '<div class="circle_list '. $shactive.'"> <p class="schdegree">'.number_format(DispSensor($conn,$srow['temperature'],$sensor_type_id),0).$unit.'</p></div>';
	} else {
		echo '<div class="circle_list bluesch_disable"> <p class="schdegree">D</p></div>';
	}
} elseif ($type == 21) {
        //------------------------------------------------------------
        //return the controller_zone_logs last entries for Recent Logs
        //------------------------------------------------------------
        $query = "SELECT 'System Controller' AS name, controller_zone_logs.* FROM controller_zone_logs,
                        (SELECT `zone_id`,max(`id`) AS mid
                                FROM controller_zone_logs
                                GROUP BY `zone_id`) max_id
                        WHERE controller_zone_logs.zone_id = max_id.zone_id
                        AND controller_zone_logs.id = max_id.mid
                        AND controller_zone_logs.zone_id = 1
                UNION
                SELECT zone.name,controller_zone_logs.* FROM controller_zone_logs, zone,
                        (SELECT `zone_id`,max(`id`) AS mid
                                FROM controller_zone_logs
                                GROUP BY `zone_id`) max_id
                        WHERE controller_zone_logs.zone_id = max_id.zone_id
                        AND controller_zone_logs.id = max_id.mid
                        AND controller_zone_logs.zone_id = zone.id;";
        $results = $conn->query($query);
                        while ($row = mysqli_fetch_assoc($results)) {
                                echo '<tr>
                                        <td class="col-2">'.$row["name"].'</td>
                                        <td class="col-2">'.$row["start_datetime"].'</td>
                                        <td class="col-2">'.$row["start_cause"].'</td>
                                        <td class="col-2">'.$row["stop_datetime"].'</td>
                                        <td class="col-2">'.$row["stop_cause"].'</td>
                                        <td class="col-2">'.$row["expected_end_date_time"].'</td>
                                </tr>';
                        }
} elseif ($type == 22) {
        //---------------------------
        //update System Uptime modal
        //---------------------------
	$uptime = (exec ("cat /proc/uptime"));
	$uptime=substr($uptime, 0, strrpos($uptime, ' '));
	echo '<div id="system_uptime">
		<p class="text-muted"> '.$lang["system_uptime_text"].' </p>
                &nbsp'.secondsToWords($uptime) . '<br/><br/>

        	<div class="list-group">
        		<span class="list-group-item" style="overflow:hidden;"><pre>';
                		$rval=my_exec("df -h");
	                        echo $rval['stdout'];
        	        echo '</pre></span>

                	<span class="list-group-item" style="overflow:hidden;"><pre>';
                 		$rval=my_exec("free -h");
	                        echo $rval['stdout'];
        	        echo '</pre></span>
          	</div>
	</div>';
} elseif ($type == 23) {
        //--------------------------------------
        //update display last 5 CPU temps modal
        //--------------------------------------
	echo '<div id="cpu_temps">';
        	$query = "select * from messages_in where node_id = '0' order by datetime desc limit 5";
                $results = $conn->query($query);
                echo '<div class="list-group">';
                	while ($row = mysqli_fetch_assoc($results)) {
                        	echo '<div class="list-group-item">
                                	<div class="d-flex justify-content-between">
                                        	<span>
                                               		<i class="bi bi-cpu-fill"></i> '.$row['datetime'].'
                                               	</span>
                                               	<span class="text-muted small"><em>'.number_format(DispSensor($conn,$row['payload'],1),1).'&deg;</em></span>
                                        </div>
                                </div>';
                        }
                echo '</div>
	</div>';
} elseif ($type == 24) {
        //-----------------------------------------------
        //update display last 5 System Controller status
        //-----------------------------------------------
	echo '<div id="sc_status">';
	        //query to get last system_controller operation time
        	$query = "SELECT * FROM system_controller LIMIT 1";
	        $result = $conn->query($query);
        	$row = mysqli_fetch_array($result);
	        $system_controller_id = $row['id'];
        	$system_controller_name = $row['name'];
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

        	if ($system_controller_fault == '1') {
                	$date_time = date('Y-m-d H:i:s');
                        $datetime1 = strtotime("$date_time");
                        $datetime2 = strtotime("$system_controller_seen");
                        $interval  = abs($datetime2 - $datetime1);
                        $ctr_minutes   = round($interval / 60);
                        echo '
                                <ul class="list-group list-group-flush">
                                        <li class="list-group-item">
                                                <div class="header">
                                                        <div class="d-flex justify-content-between">
                                                                <span>
                                                                        <strong class="primary-font red">System Controller Fault!!!</strong>
                                                                </span>
                                                                <span>
                                                                        <small class="text-muted">
                                                                                <i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
                                                                        </small>
                                                                </span>
                                                        </div>
                                                        <br>
                                                        <p>Node ID '.$system_controller_node_id.' last seen at '.$system_controller_seen.' </p>
                                                        <p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
                                                </div>
                                        </li>
                                </ul>';
                }
                $bquery = "SELECT DATE_FORMAT(start_datetime, '%H:%i') AS start_datetime, DATE_FORMAT(stop_datetime, '%H:%i') AS stop_datetime ,
				DATE_FORMAT(expected_end_date_time, '%H:%i') AS expected_end_date_time,
				IF(ISNULL(stop_datetime),TIMESTAMPDIFF(MINUTE, start_datetime, NOW()),TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) AS on_minuts
				FROM controller_zone_logs WHERE zone_id = ".$system_controller_id." ORDER BY id DESC LIMIT 5;";
                $bresults = $conn->query($bquery);
                if (mysqli_num_rows($bresults) == 0){
                        echo '<div class="list-group">
                                <a href="#" class="list-group-item"><i class="bi bi-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['system_controller_no_log'].'</a>
                        </div>';
                } else {
                        echo '<p class="text-muted">'. mysqli_num_rows($bresults) .' '.$lang['system_controller_last_records'].'</p>
                        <div class="list-group">' ;
                                echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
                                        <span>
                                                <i class="bi bi-fire red"></i> Start &nbsp; - &nbsp;End
                                        </span>
                                        <span class="text-muted small">
                                                <em>'.$lang['system_controller_on_minuts'].'&nbsp;</em>
                                        </span>
                                </a>';
                                while ($brow = mysqli_fetch_assoc($bresults)) {
                                        echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
                                                <span>
                                                        <i class="bi bi-fire red"></i> '. $brow['start_datetime'].' - ' .$brow['stop_datetime'].'
                                                </span>
                                                <span class="text-muted small">
                                                        <em>'.$brow['on_minuts'].'&nbsp;</em>
                                                </span>
                                        </a>';
                                }
                         echo '</div>';
                }
        echo '</div>';
} elseif ($type == 25) {
        //-----------------------------------------
        //return the current cpu tempeature status
        //----------------------------------------
	$max_cpu_temp = settings($conn, 'max_cpu_temp');
	$query = "select * from messages_in where node_id = '0' ORDER BY id DESC LIMIT 1";
	$result = $conn->query($query);
	$result = mysqli_fetch_array($result);
	$system_cc = $result['payload'];
	if ($system_cc < $max_cpu_temp - 10){$system_cc="#0bb71b";}elseif ($system_cc < $max_cpu_temp){$system_cc="#F0AD4E";}elseif ($system_cc > $max_cpu_temp){$system_cc="#ff0000";}
	echo '<div id="cpu_status">
        	<h3 class="status">
                <small class="statuscircle" style="color:'.$system_cc.'"><i class="bi bi-circle-fill" style="font-size: 0.55rem;"></i></small>
                <small class="statusdegree">'.number_format(DispTemp($conn,$result['payload']),0).'&deg;</small>';
                if ($result['payload'] > $max_cpu_temp){
                        echo '<small class="statuszoon"><i class="spinner-grow text-danger" role="status" style="width: 0.7rem; height: 0.7rem;"></i></small></h3>';
                }
	echo '</div>';
} elseif ($type == 26) {
        //-------------------------------
        //return the current frost status
        //-------------------------------
        $fcolor = "blue";

        $query = "SELECT sensor_id, sensor_child_id, frost_temp FROM sensors WHERE frost_temp <> 0;";
        $results = $conn->query($query);
        while ($row = mysqli_fetch_assoc($results)) {
                $query = "SELECT node_id FROM nodes WHERE id = ".$row['sensor_id']." LIMIT 1;";
                $result = $conn->query($query);
                $frost_sensor_node = mysqli_fetch_array($result);
                $frost_sensor_node_id = $frost_sensor_node['node_id'];
                //query to get temperature from messages_in_view_24h table view
                $query = "SELECT payload FROM messages_in_view_24h WHERE node_id = '".$frost_sensor_node_id."' AND child_id = ".$row['sensor_child_id']." LIMIT 1;";
                $result = $conn->query($query);
                $msg_in = mysqli_fetch_array($result);
                $frost_sensor_c = $msg_in['payload'];
                if ($frost_sensor_c <= $row["frost_temp"]) { $fcolor = "red"; }
        }
	echo '<small class="statuscircle" id="frost_status"><i class="bi bi-circle-fill '.$fcolor.'" style="font-size: 0.55rem;"></i></small>';
} elseif ($type == 27) {
        //-----------------------
        //update services status
        //-----------------------
        $rval=my_exec("/bin/systemctl status " . $id);
        if($rval['stdout']=='') {
        	$stat = 'Error: ' . $rval['stderr'];
        } else {
        	$stat='Status: Unknown';
        	$rval['stdout']=explode(PHP_EOL,$rval['stdout']);
                foreach($rval['stdout'] as $line) {
                	if(strstr($line,'Loaded:')) {
                        	if(strstr($line,'disabled;')) {
                                	$stat='Status: Disabled';
                                        break;
                            	}
                     	}
                        if(strstr($line,'Active:')) {
                        	if(strstr($line,'active (running)')) {
                                	$stat=trim($line);
                                        break;
                              	} else if(strstr($line,'(dead)')) {
                                	$stat='Status: Dead';
                                        break;
                                }
                    	}
           	}
       	}
        echo '<div id="service_'.$id.'">'.$stat.'</div>';
} elseif ($type == 28 || $type == 29 || $type == 30 || $type == 31) {
        //----------------------
        //set the service state
        //----------------------
		        if ($type == 28 || $type == 29 || $type == 30 || $type == 31) {
                		switch ($type) {
		                        case 28:
                		                $action = "start";
                                		break;
		                        case 29:
                		                $action = "stop";
                                		break;
		                        case 30:
                		                $action = "enable";
                                		 break;
		                        case 31:
                		                $action = "disable";
                                		 break;
		                }
                		if(substr($id,0,10)=='homebridge') {
		                        if($type != 27) {
                		                if($type == 28 || $type == 29) {
                                		        $rval=my_exec("sudo hb-service " . $action);
		                                } elseif ($type == 30) {
                		                        $rval=my_exec("sudo hb-service install --user homebridge");
                                		} else {
		                                        $rval=my_exec("sudo hb-service uninstall");
                		                }
		                        }
                		} else {
		                        $rval=my_exec("/usr/bin/sudo /bin/systemctl " . $action . " " . $id);
                		}
            			$per='';
            			similar_text($rval['stderr'],'We trust you have received the usual lecture from the local System Administrator. It usually boils down to these three things: #1) Respect the privacy of others. #2) Think before you type. #3) With great power comes great responsibility. sudo: no tty present and no askpass program specified',$per);
            			if($per>80) {
					if(substr($id,0,10)=='homebridge') {
                				$rval['stdout']=$web_user_name.' cannot issue  hb-service commands.<br/><br/>If you would like it to be able to, add<br/><code>'.$web_user_name.' ALL=/usr/bin/hb-service<br/>'.$web_user_name.' ALL=NOPASSWD: /usr/bin/hb-service</code><br/>to /etc/sudoers.d/010_pi-nopasswd.';
					} else {
						$rval['stdout']=$web_user_name.' cannot issue systemctl commands.<br/><br/>If you would like it to be able to, add<br/><code>'.$web_user_name.' ALL=/bin/systemctl<br/>'.$web_user_name.' ALL=NOPASSWD: /bin/systemctl</code><br/>to /etc/sudoers.d/010_pi-nopasswd.';
					}
                			$rval['stderr']='';
            			}
            			echo '<p class="text-muted">systemctl ' . $action . ' ' . $id . '<br/>stdout: ' . $rval['stdout'] . '<br/>stderr: ' . $rval['stderr'] . '</p>';
        		}
} elseif ($type == 32) {
        //-----------------------
        //update services status
        //-----------------------
        $rval=my_exec("/bin/systemctl status " . $id);
        if($rval['stdout']=='') {
                $stat = 'Error: ' . $rval['stderr'];
        } else {
                $stat='Status: Unknown';
                $rval['stdout']=explode(PHP_EOL,$rval['stdout']);
                foreach($rval['stdout'] as $line) {
                        if(strstr($line,'Loaded:')) {
                                if(strstr($line,'disabled;')) {
                                        $stat='Status: Disabled';
                                        break;
                                }
                        }
                        if(strstr($line,'Active:')) {
                                if(strstr($line,'active (running)')) {
                                        $stat=trim($line);
                                        break;
                                } else if(strstr($line,'(dead)')) {
                                        $stat='Status: Dead';
                                        break;
                                }
                        }
                }
        }
        echo '<div id="serv_status">'.$stat.'</div>';
} elseif ($type == 33) {
        //--------------------------
        //update zone current state
        //--------------------------

	//define arrays for modes
	$mode_main=array(0=>"idle",
                10=>"fault",
                20=>"frost",
                30=>"overtemperature",
                40=>" holiday",
                50=>"nightclimate",
                60=>"boost",
                70=>"override",
                80=>"sheduled",
                90=>"away",
                100=>"hysteresis",
                110=>"Add-On",
                120=>"HVAC",
                130=>"undertemperature",
                140=>"manual");

	$mode_sub=array(0=>"stopped (above cut out setpoint or not running in this mode)",
		1=>"heating running",
		2=>"stopped (within deadband)",
		3=>"stopped (coop start waiting for the system_controller)",
		4=>"manual operation ON",
		5=>"manual operation OFF",
		6=>"cooling running",
		7=>"HVAC Fan Only",
		8=>"Max Running Time Exceeded - Hysteresis active");

	$query = "SELECT name, type_id, mode, zone_current_state.status, schedule, temp_reading, temp_target, temp_cut_in, temp_cut_out, controler_fault, sensor_fault
		FROM zone_current_state,zone
		WHERE zone.id = zone_current_state.zone_id;";

	$query = "SELECT z.name, z.type_id, mode, zone_current_state.status, schedule, temp_reading, temp_target, temp_cut_in, temp_cut_out, controler_fault, sensor_fault, sdt.sch_name
		FROM zone_current_state
		JOIN zone z ON z.id = zone_current_state.zone_id
		JOIN schedule_daily_time sdt ON sdt.id = sch_time_id;";
	$results = $conn->query($query);
	while ($row = mysqli_fetch_assoc($results)) {
                if ($row['status'] == 1) { $za_color = "green"; } else { $za_color = "red"; }
		if ($row['schedule'] == 1) { $scolor = "green"; } else { $scolor = "red"; }
                if ($row['controler_fault'] == 0) { $cf_color = "green"; } else { $cf_color = "red"; }
                if ($row['sensor_fault'] == 0) { $sf_color = "green"; } else { $sf_color = "red"; }
                echo '<tr>
                        <td class="col-1">'.$row["name"].'</td>
                        <td class="col-3">'.$mode_main[floor($row['mode']/10)*10].'<br/>'.$mode_sub[floor($row['mode']%10)].'</td>
                        <td style="text-align:center; vertical-align:middle;"><class="statuscircle"><i class="bi bi-circle-fill '.$za_color.'" style="font-size: 0.8rem;"></i></td>';
                        if ($row['schedule'] == 1) {
                                echo '<td style="text-align:center; vertical-align:middle;"><class="col-1">'.$row["sch_name"].'</td>';
                        } else {
                                echo '<td style="text-align:center; vertical-align:middle;"><class="statuscircle"><i class="bi bi-circle-fill red" style="font-size: 0.8rem;"></i></td>';
                        }
                        if ($row['type_id'] == 5 || ($row['status'] == 0 && $row['schedule'] == 0)) {
                                $t1 = "";
                                $t2 = "";
                                $t3 = "";
                                $t4 = "";
                        } else {
                                $t1 = $row["temp_reading"];
                                $t2 = $row["temp_target"];
                                $t3 = $row["temp_cut_in"];
                                $t4 = $row["temp_cut_out"];
                        }
                        echo '<td style="text-align:center; vertical-align:middle;"><class="col-1">'.$t1.'</td>
                        <td style="text-align:center; vertical-align:middle;"><class="col-1">'.$t2.'</td>
                        <td style="text-align:center; vertical-align:middle;"><class="col-1">'.$t3.'</td>
                        <td style="text-align:center; vertical-align:middle;"><class="col-1">'.$t4.'</td>';
                        echo '<td style="text-align:center; vertical-align:middle;"><class="statuscircle"><i class="bi bi-circle-fill '.$cf_color.'" style="font-size: 0.8rem;"></i></td>
                        <td style="text-align:center; vertical-align:middle;"><class="statuscircle"><i class="bi bi-circle-fill '.$sf_color.'" style="font-size: 0.8rem;"></i></td>
                </tr>';

	}
} elseif ($type == 34) {
        //---------------------------------------
        //update  gateway and controller scripts
        //---------------------------------------
	$query = "SELECT * FROM gateway";
	$result = $conn->query($query);
        $grow = mysqli_fetch_array($result);
       	$query = "SELECT * FROM system_controller";
        $result = $conn->query($query);
        $scrow = mysqli_fetch_array($result);
        // Checking if Gateway script is running
	$gw_script_txt = 'python3 /var/www/cron/gateway.py';
        exec("ps -eo pid,etime,cmd | grep '$gw_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $pids);
        $nopids = count($pids);
	if ($nopids == 0) {
	        $gpid = "";
        	$gpid_grunning_since = "";
                $gw_color = "red";
	} else {
	        $gpid = $grow['pid'];
        	$gpid_running_since =$grow['pid_running_since'];
		$gw_color = "green";
	}
        // Checking if System Controller script is running
        $sc_script_txt = 'python3 /var/www/cron/controller.py';
        exec("ps -eo pid,etime,cmd | grep '$sc_script_txt' | grep -v grep | awk '{ print $2 }' | head -1", $pids);
        $nopids = count($pids);
        if ($nopids == 0) {
                $scpid = "";
                $scpid_grunning_since = "";
                $sc_color = "red";
        } else {
                $scpid = $scrow['pid'];
                $scpid_running_since = $scrow['pid_running_since'];
                $sc_color = "green";
        }

	echo '<br><h4 class="info"><i class="bi bi-activity '. $gw_color .'" style="font-size:2rem;"></i> '.$lang['smart_home_gateway_scr_info'].'</h4>
	<div class="list-group">
		<a href="#" class="list-group-item d-flex justify-content-between"><span>PID</span><span class="text-muted small"><em> '.$gpid.'</em></span></a>
		<a href="#" class="list-group-item d-flex justify-content-between"><span>'.$lang['smart_home_gateway_pid'].':</span><span class="text-muted small"><em>'.$gpid_running_since.'</em></span></a>';

		$query = "select * FROM gateway_logs WHERE pid_datetime >= NOW() - INTERVAL 5 MINUTE;";
		$result = $conn->query($query);
                $rowcount = mysqli_num_rows($result);
		if ($rowcount != 0){
			$gw_restarted = $rowcount;
		} else {
			$gw_restarted = '0';
                }
                if ($nopids != 0) {
			$query = "select * FROM gateway_logs ORDER BY id DESC LIMIT 1;";
			$result = $conn->query($query);
			$glrow = mysqli_fetch_array($result);
			echo '<div class="list-group-item d-flex justify-content-between"><span>'.$lang['smart_home_gateway_scr'].':</span><span class="text-muted small"><em>'.$gw_restarted.'</em></span></div>';
			if ($gw_restarted == '0') {
                	        $query = "SELECT *
                        	        FROM nodes
                                	LEFT JOIN sensors s ON nodes.id = s.sensor_id
	                                LEFT JOIN relays r ON nodes.id = r.relay_id
        	                        WHERE nodes.type LIKE 'MQTT%';";
                	        $mqttresult = $conn->query($query);
                        	$mqttrowcount = mysqli_num_rows($mqttresult);
				if ($mqttrowcount != 0){
        	        		echo '<div class="list-group-item d-flex justify-content-between"><span>'.$lang['mqtt_per_hour'].':</span><span class="text-muted small"><em>'.$glrow['mqtt_sent'].' - '.$glrow['mqtt_recv'].'</em></span></div>';
				}
                        	$query = "SELECT *
                                	FROM nodes
	                                LEFT JOIN sensors s ON nodes.id = s.sensor_id
        	                        LEFT JOIN relays r ON nodes.id = r.relay_id
                	                WHERE nodes.type LIKE 'MySensor%';";
                        	$msresult = $conn->query($query);
	                        $msrowcount = mysqli_num_rows($msresult);
				if ($msrowcount != 0){
                	        	echo '<div class="list-group-item d-flex justify-content-between"><span>'.$lang['mysensors_per_minute'].':</span><span class="text-muted small"><em>'.$glrow['mysensors_sent'].' - '.$glrow['mysensors_recv'].'</em></span></div>';
				}
				$query = "SELECT *
					FROM nodes
					LEFT JOIN sensors s ON nodes.id = s.sensor_id
					LEFT JOIN relays r ON nodes.id = r.relay_id
					WHERE nodes.type LIKE 'GPIO%' AND nodes.node_id != '0';";
				$nresult = $conn->query($query);
        	                $nrowcount = mysqli_num_rows($nresult);
                	        if ($nrowcount != 0){
                        		echo '<div class="list-group-item d-flex justify-content-between"><span>'.$lang['gpio_per_hour'].':</span><span class="text-muted small"><em>'.$glrow['gpio_sent'].' - '.$glrow['gpio_recv'].'</em></span></div>';
				}
			}
		}
	echo '</div>
        <!-- /.list-group -->

        <br><h4 class="info"><i class="bi bi-activity '. $sc_color .'" style="font-size:2rem;"></i> '.$lang['smart_home_controller_scr_info'].'</h4>
	<div class="list-group">
		<div class="list-group-item d-flex justify-content-between"><span>PID</span><span class="text-muted small"><em> '.$scpid.'</em></span></div>
		<div class="list-group-item d-flex justify-content-between"><span>'.$lang['smart_home_gateway_pid'].':</span><span class="text-muted small"><em>'.$scpid_running_since.'</em></span></div>';

		$query = "select * FROM controller_zone_logs WHERE zone_id = 0 AND start_datetime >= NOW() - INTERVAL 5 MINUTE;";
		$result = $conn->query($query);
		if (mysqli_num_rows($result) != 0){
			$sc_restarted = mysqli_num_rows($result);
		} else {
			$sc_restarted = '0';
		}
		echo '<div class="list-group-item d-flex justify-content-between"><span>'.$lang['smart_home_gateway_scr'].':</span><span class="text-muted small"><em>'.$sc_restarted.'</em></span></div>';
	echo '</div>';
} elseif ($type == 35) {
        //----------------------
        //update ethernet modal
        //-----------------------
	foreach (scandir("/sys/class/net") as $file) {
        	if (substr($file, 0, 1) == 'e') {
                	$eth_name = trim($file);
        	}
	}
	$rxdata = exec ("cat /sys/class/net/".$eth_name."/statistics/rx_bytes");
	$txdata = exec ("cat /sys/class/net/".$eth_name."/statistics/tx_bytes");
	$rxdata = $rxdata/1024; // convert to kb
	$rxdata = $rxdata/1024; // convert to mb
	$txdata = $txdata/1024; // convert to kb
	$txdata = $txdata/1024; // convert to mb
	$nicmac = exec ("cat /sys/class/net/".$eth_name."/address");
	$nicpeed = exec ("cat /sys/class/net/".$eth_name."/speed");
	$nicactive = exec ("cat /sys/class/net/".$eth_name."/operstate");
        echo '<div class="list-group-item">
		<div class="d-flex justify-content-between">
			<span>
				<i class="bi bi-diagram-2-fill green"></i>&nbsp'.$lang['status'].':
			</span>
			<span>'.$nicactive.'</span>
		</div>
	</div>
	<div class="list-group-item">
		<div class="d-flex justify-content-between">
			<span>
				<i class="bi bi-diagram-2-fill green"></i>&nbsp'.$lang['speed'].':
			</span>
			<span>'.$nicpeed.'Mb</span>
		</div>
	</div>
	<div class="list-group-item">
		<div class="d-flex justify-content-between">
			<span>
				<i class="bi bi-diagram-2-fill green"></i>&nbsp'.$lang['mac'].':
			</span>
			<span>'.$nicmac.'</span>
		</div>
	</div>
	<div class="list-group-item">
		<div class="d-flex justify-content-between">
			<span>
				<i class="bi bi-diagram-2-fill green"></i>&nbsp'.$lang['download'].':
			</span>
			<span>'.number_format($rxdata,0).' MB</span>
		</div>
	</div>
	<div class="list-group-item">
		<div class="d-flex justify-content-between">
			<span>
				<i class="bi bi-diagram-2-fill green"></i>&nbsp'.$lang['upload'].':
			</span>
			<span>'.number_format($txdata,0).' MB</span>
		</div>
	</div>';
} elseif ($type == 36) {
        //------------------
        //update wifi modal
        //------------------
	$rxwifidata = exec ("cat /sys/class/net/wlan0/statistics/rx_bytes");
	$txwifidata = exec ("cat /sys/class/net/wlan0/statistics/tx_bytes");
	$rxwifidata = $rxwifidata/1024; // convert to kb
	$rxwifidata = $rxwifidata/1024; // convert to mb

	$txwifidata = $txwifidata/1024; // convert to kb
	$txwifidata = $txwifidata/1024; // convert to mb
	$wifimac = exec ("cat /sys/class/net/wlan0/address");
	//$wifipeed = exec ("cat /sys/class/net/wlan0/speed");
	//$wifipeed = exec("iwconfig wlan0 | grep -i --color quality");
	$wifistatus = exec ("cat /sys/class/net/wlan0/operstate");
	echo '<div class="list-group-item">
		<div class="d-flex justify-content-between">
                	<span>
                        	<i class="bi bi-reception-4 green"></i> '.$lang['status'].':
                	</span>
                	<span>'.$wifistatus.'</span>
        	</div>
	</div>
	<div class="list-group-item">
        	<div class="d-flex justify-content-between">
                	<span>
                        	<i class="bi bi-reception-4 green"></i> '.$lang['mac'].':
                	</span>
                	<span>'.$wifimac.'</span>
        	</div>
	</div>
	<div class="list-group-item">
        	<div class="d-flex justify-content-between">
                	<span>
                        	<i class="bi bi-reception-4 green"></i> '.$lang['download'].':
                	</span>
                	<span>'.number_format($rxwifidata,0).' MB</span>
        	</div>
	</div>
	<div class="list-group-item">
        	<div class="d-flex justify-content-between">
                	<span>
                        	<i class="bi bi-reception-4 green"></i> '.$lang['upload'].':
                	</span>
                	<span>'.number_format($txwifidata,0).' MB</span>
        	</div>
	</div>';
} elseif ($type == 37) {
        //--------------------------
        //update relay states modal
        //--------------------------
        $query = "SELECT * FROM relays ORDER BY relay_id asc;";
        $results = $conn->query($query);
        while ($rrow = mysqli_fetch_assoc($results)) {
                $r_id = $rrow['id'];
                $r_name = $rrow['name'];
                $relay_id = $rrow['relay_id'];
                $relay_child_id = $rrow['relay_child_id'];
                $relay_type = $rrow['type'];
		$relay_lag_time = $rrow['lag_time'];
                $query = "SELECT payload FROM messages_out where n_id = {$relay_id} AND child_id = {$relay_child_id} LIMIT 1;";
                $mresult = $conn->query($query);
                $mrow = mysqli_fetch_array($mresult);
                $payload = $mrow['payload'];
		$query = "SELECT * FROM relay_logs WHERE relay_id = {$r_id} ORDER BY id DESC;";
		$lresult = $conn->query($query);
		$lrow = mysqli_fetch_assoc($lresult);
                $content_msg = $lrow['zone_mode'];
                if ($lrow['message'] == "OFF" and $payload == 1 and $relay_lag_time > 0) {
                        $r_color = "orange";
                } elseif ($lrow['message'] == "OFF" or $payload == 0) {
                        $r_color = "red";
                } else {
                        $r_color = "green";
                }
                echo '<tr>
                        <td class="col-6">'.$r_name.'</td>
                        <td class="col-2" style="text-align:center; vertical-align:middle;" data-bs-toggle="tooltip" title="'.$content_msg.'"><button class="btn-circle" style="background-color:'.$r_color.'" onclick="relay_log(`'.$r_id.'`, `'.$r_name.'`, `'.$relay_id.'`);"</button></td>
                </tr>';
	}
}
?>
