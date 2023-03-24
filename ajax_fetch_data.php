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

if(isset($_GET['id'])) { $id = $_GET['id']; }
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

if ($type <= 5) {
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
        	if ($zone_current_state['mode'] == 0) { $add_on_active = 0; } else { $add_on_active = 1; }
                if ($add_on_active == 1 && $zone_category != 5) { $add_on_colour = "green"; } elseif ($add_on_active == 0 || ($add_on_active == 1 && $zone_category == 5)) {$add_on_colour = "black"; }
	}

	//get the sensor id
	$query = "SELECT * FROM sensors WHERE zone_id = '{$id}' LIMIT 1;";
	$result = $conn->query($query);
	$sensor = mysqli_fetch_array($result);
	$temperature_sensor_id=$sensor['sensor_id'];
	$temperature_sensor_child_id=$sensor['sensor_child_id'];
	$sensor_type_id=$sensor['sensor_type_id'];
	$zone_c = $sensor['current_val_1'];
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
                        } elseif ($zone_category == 1) {
                        	$add_on_mode = $zone_current_state['mode'];
                      	} else {
                        	$add_on_mode = 114;
                        }
                }
                if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = 90; }
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
                                	$unit = SensorUnits($conn,$sensor_type_id);
                               		 echo number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit;
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
			if ($zone_mode_main == 60) {
				echo '<img src="images/'.$rval['shactive'].'" width="10" height="10" alt="">';
			} else {
	        	        echo '<i class="bi ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' bi-fw">';
			}
                	break;
	        case 5:
        	        if($overrun == 1) { echo '<i class="bi bi-play-fill orange-red">'; }
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
                  WHERE sensors.id = {$id} AND (nodes.id = sensors.sensor_id) AND sensors.zone_id = 0 AND sensors.show_it = 1 LIMIT 1;";
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
                                if ($sensor_r == 0) { echo 'OFF'; } else { echo 'ON'; }
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
							echo '<img src="images/flame.svg" class="'.$system_controller_colour.'" style="margin-top: -5px" style="margin-top: -5px" width="25" height="25" alt="">';
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
								echo '<img src="images/flame.svg" class="colorize-red" style="margin-top: -5px" width="25" height="25" alt="">';
							} elseif ($hvac_relays_state & 0b010) {
								echo '<i class="bi bi-snow blueinfo" style="font-size: 1.4rem;">';
							}
							break;
        	                        	case 4:
                	                        	if ($hvac_relays_state == 0b000) {
                        	                      		$system_controller_colour="#00C853";
	                        	                        echo '<i class="bi bi-power '.$system_controller_colour.'" style="font-size: 1.4rem;">';
        	                        	        } elseif ($hvac_relays_state & 0b100) {
                	                        	        echo '<img src="images/flame.svg" class="colorize-red" style="margin-top: -5px" width="25" height="25" alt="">';
	                        	                } elseif ($hvac_relays_state & 0b010) {
        	                        	                echo '<i class="bi bi-snow blueinfo" style="font-size: 1.4rem;">';
                	                        	}
	                	                        break;
        	                	        case 5:
                	                	        echo '<img src="images/hvac_fan_30.png" border="0"></h3>';
                        	                	break;
	                                	case 6:
							if ($hvac_relays_state & 0b100) { $system_controller_colour = "colorize-red"; } else { $system_controller_colour = "colorize-blue"; }
							echo '<img src="images/flame.svg" class="'.$system_controller_colour.'" style="margin-top: -5px" width="25" height="25" alt="">';
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
						$system_controller_colour="colorize-red";
					} elseif ($sc_active_status==0) {
						$system_controller_colour="colorize-blue";
					}
					if ($sc_mode==0) {
                		               	$system_controller_colour="";
                        		}
	                        	echo '<img src="images/flame.svg" class="'.$system_controller_colour.'" style="margin-top: -5px" width="25" height="25" alt="">';
				}
			} elseif ($type == 10) {
				if($system_controller_fault=='1') {echo'<i class="bi bi-x-circle-fill red">';}
				elseif($hysteresis=='1') {echo'<i class="bi bi-hourglass-split orange-red">';}
				else { echo'';}
			}
		}
	} else {
		if ($type == 9) { echo '<img src="images/flame.svg" style="margin-top: -5px" width="25" height="25" alt="">'; } else { echo''; }
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
		        $query = "SELECT status FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
		        $result = $conn->query($query);
		        $holidays_status=mysqli_num_rows($result);
		        if ($holidays_status=='1'){$holidaystatus="red";}elseif ($holidays_status=='0'){$holidaystatus="blueinfo";}
        		echo '<i class="bi bi-circle-fill '.$holidaystatus.'" style="font-size: 0.55rem;">';
                        break;
	}
} elseif ($type == 13) {
	//------------
	//return time
	//------------
        $username = isset($_SESSION['username']) ? $_SESSION['username'] : 'Session Timed Out';
        if ($id == 0) { echo $username.'&nbsp;&nbsp; - '.date("H:i"); } else { echo '&nbsp;&nbsp;'.$username.'&nbsp;&nbsp; - '.date("H:i"); }
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
	$query="SELECT sensor_id, sensor_child_id, sensor_type_id FROM sensors WHERE id = {$id} LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$id = $row['sensor_id'];
	$child_id = $row['sensor_child_id'];
	$sensor_type_id = $row['sensor_type_id'];
	$query="SELECT node_id FROM nodes WHERE id  = {$id} LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$node_id = $row['node_id'];
	// get the latest sensor temperature reading
	$query="SELECT payload FROM messages_in where node_id = '".$node_id."' AND child_id = ".$child_id." ORDER BY id DESC LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$sensor_c = $row['payload'];
	$unit = SensorUnits($conn,$sensor_type_id);
	//echo number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit;
	echo '&nbsp&nbsp<i class="bi bi-thermometer-half red"></i> - '.number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit;
} elseif ($type == 17) {
        //---------------------------------------
        //process running time for All Schedules
        //---------------------------------------
	//following variable set to 0 on start for array index.
	$sch_time_index = '0';
	$query = "SELECT time_id, time_status, `start`, `end`, WeekDays,tz_id, tz_status, zone_id, index_id, zone_name, type, `category`, temperature,
        	FORMAT(max(temperature),2) as max_c, sch_name, sch_type, start_sr, start_ss, start_offset, end_sr, end_ss, end_offset, sensor_type_id, stype
	        FROM schedule_daily_time_zone_view
        	WHERE holidays_id = 0 AND tz_status = 1
	        GROUP BY time_id ORDER BY start, sch_name asc";
	$results = $conn->query($query);
	while ($row = mysqli_fetch_assoc($results)) {
        	$start_time = strtotime($row['start']);
	        $end_time = strtotime($row['end']);
        	$start_sr = $row['start_sr'];
	        $start_ss = $row['start_ss'];
        	$start_offset = $row['start_offset'];
	        $end_sr = $row['end_sr'];
        	$end_ss = $row['end_ss'];
	        $end_offset = $row['end_offset'];
        	if ($start_sr == 1 || $start_ss == 1 || $end_sr == 1 || $end_ss == 1) {
                	$query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
	                $result = $conn->query($query);
        	        $rowcount=mysqli_num_rows($result);
                	if ($rowcount > 0) {
                        	$wrow = mysqli_fetch_array($result);
	                        $sunrise_time = date('H:i:s', $wrow['sunrise']);
        	                $sunset_time = date('H:i:s', $wrow['sunset']);
                	        if ($start_sr == 1 || $start_ss == 1) {
                        	        if ($start_sr == 1) { $start_time = strtotime($sunrise_time); } else { $start_time = strtotime($sunset_time); }
                                	$start_time = $start_time + ($start_offset * 60);
	                        }
        	                if ($end_sr == 1 || $end_ss == 1) {
                	                if ($end_sr == 1) { $end_time = strtotime($sunrise_time); } else { $end_time = strtotime($sunset_time); }
                        	        $end_time = $end_time + ($end_offset * 60);
	                        }
        	        }
	        }

        	//calculate total time of day schedule using array schedule_time with index as sch_time_index variable
	        if ($row["time_status"] == "1") {
        	        $total_time = $end_time - $start_time;
                	$total_time = $total_time / 60;
	                //save all total_time variable value to schedule_time array and incriment array index (sch_time_index)
        	        $schedule_time[$sch_time_index] = $total_time;
                	$sch_time_index = $sch_time_index + 1;
	        }
	} //end of schedule time while loop
	echo ' <i class="bi bi-clock"></i>&nbspAll Schedule:&nbsp' .secondsToWords((array_sum($schedule_time) * 60));
} elseif ($type == 18 || $type == 19) {
        //------------------------------------------------------
        //return the schedule status and temp for schedule by id
        //------------------------------------------------------
        $prev_dow = $dow - 1;
	if ($type == 18) { $holiday_id = 0; } else { $holiday_id = 1; }
        $query = "SELECT time_id, time_status, `start`, `end`, WeekDays,tz_id, tz_status, zone_id, index_id, zone_name, type, `category`, temperature,
                FORMAT(max(temperature),2) as max_c, sch_name, sch_type, start_sr, start_ss, start_offset, end_sr, end_ss, end_offset, sensor_type_id, stype
                FROM schedule_daily_time_zone_view
                WHERE time_id = {$id} AND holidays_id = {$holiday_id} AND (tz_status = 1 OR (tz_status = 0 AND disabled = 1));";
        $result = $conn->query($query);
	$row = mysqli_fetch_array($result);
        $sch_type = $row['sch_type'];
        $tz = timezone_open(settings($conn, 'timezone'));
        $time_offset = timezone_offset_get($tz, date_create("now"));
        $time_offset = $time_offset - (3600 * Date('I'));
        $time_offset = 0;
        $time = strtotime(date("G:i:s"));
        $start_time = strtotime($row['start']) + $time_offset;
        $end_time = strtotime($row['end']) + $time_offset;
        $start_sr = $row['start_sr'];
        $start_ss = $row['start_ss'];
        $start_offset = $row['start_offset'];
        $end_sr = $row['end_sr'];
        $end_ss = $row['end_ss'];
        $end_offset = $row['end_offset'];
        if ($start_sr == 1 || $start_ss == 1 || $end_sr == 1 || $end_ss == 1) {
        	$query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
                $result = $conn->query($query);
                $rowcount=mysqli_num_rows($result);
                if ($rowcount > 0) {
                	$wrow = mysqli_fetch_array($result);
                        $sunrise_time = date('H:i:s', $wrow['sunrise']);
                        $sunset_time = date('H:i:s', $wrow['sunset']);
                        if ($start_sr == 1 || $start_ss == 1) {
                        	if ($start_sr == 1) { $start_time = strtotime($sunrise_time); } else { $start_time = strtotime($sunset_time); }
                                $start_time = $start_time + ($start_offset * 60);
                        }
                        if ($end_sr == 1 || $end_ss == 1) {
                                if ($end_sr == 1) { $end_time = strtotime($sunrise_time); } else { $end_time = strtotime($sunset_time); }
                                $end_time = $end_time + ($end_offset * 60);
                        }
                }
        }
        $query = "SELECT  * FROM `schedule_daily_time_zone` WHERE `schedule_daily_time_id` = {$id};";
        $results = $conn->query($query);
        $count = mysqli_num_rows($results);
	$query = "SELECT COUNT(*) AS dis_cont FROM `schedule_daily_time_zone` WHERE ((`status` = 0 AND `disabled` = 1) OR (`status` = 0 AND `disabled` = 0)) AND `schedule_daily_time_id` = {$id};";
        $result = $conn->query($query);
        $sdrow = mysqli_fetch_array($result);
	if($row["time_status"]=="0"){ $shactive="bluesch"; }else{ $shactive="orangesch"; }
        if ((($end_time > $start_time && $time > $start_time && $time < $end_time && ($row["WeekDays"]  & (1 << $dow)) > 0) || ($end_time < $start_time && $time < $end_time && ($row["WeekDays"]  & (1 << $prev_dow)) > 0) || ($end_time < $start_time && $time > $start_time && ($row["WeekDays"]  & (1 << $dow)) > 0)) && $row["time_status"]=="1") {
        	if (($sch_type == 1 && $away_status == 1) || ($sch_type == 0 && $away_status == 0)) { $shactive="redsch"; }
	}
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
	$squery = "SELECT schedule_daily_time.sch_name, schedule_daily_time.start, schedule_daily_time.end,
        schedule_daily_time_zone.zone_id, schedule_daily_time_zone.temperature, schedule_daily_time_zone.id AS tz_id, schedule_daily_time_zone.coop, schedule_daily_time_zone.disabled
        FROM `schedule_daily_time`, `schedule_daily_time_zone`
        WHERE (schedule_daily_time.id = schedule_daily_time_zone.schedule_daily_time_id) AND schedule_daily_time.status = 1
        AND (schedule_daily_time_zone.status = 1 OR schedule_daily_time_zone.disabled = 1) AND schedule_daily_time.type = 0 AND schedule_daily_time_zone.id ='{$id}'
        AND (schedule_daily_time.WeekDays & (1 << {$dow})) > 0
        ORDER BY schedule_daily_time.start asc;";
        $sresults = $conn->query($squery);
	$srow = mysqli_fetch_assoc($sresults);

        $shactive="orangesch_list";
        $tz = timezone_open(settings($conn, 'timezone'));
        $time_offset = timezone_offset_get($tz, date_create("now"));
        $time_offset = $time_offset - (3600 * Date('I'));
        $time = strtotime(date("G:i:s"));
        $start_time = strtotime($srow['start']) + $time_offset;
        $end_time = strtotime($srow['end']) + $time_offset;
        if ($time >$start_time && $time <$end_time){$shactive="redsch_list";}

        $query = "SELECT sensor_type_id FROM sensors WHERE zone_id = '{$srow['$zone_id']}' LIMIT 1;";
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
}
?>
