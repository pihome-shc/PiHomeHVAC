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

$theme = settings($conn, 'theme');
$tile_size = theme($conn, $theme, 'tile_size');

if($tile_size == 1 || settings($conn, 'language') == "sk" || settings($conn, 'language') == "de") { $button_style = "btn-xxl-wide"; } else { $button_style = "btn-xxl"; }
$page_refresh = page_refresh($conn);

// set the display mask for standalone sensors and add-on controllers
$user_id = $_SESSION['user_id'];
if (strpos($_SESSION['username'], "admin") !== false) { //admin account, display everything so mask = 0
	$user_display_mask = 0;
} else {
	//not the admin user, so set the mask dependant on the user's position in the 'user' table
	$query = "select count(*) as pos from user where id<='{$user_id}' AND `username` NOT LIKE 'admin' LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$user_display_mask = pow(2,($row['pos'] - 1));
}
?>
<script language='javascript' type='text/javascript'>
	$('#ajaxModal').on('show.bs.modal', function(e) {
        	//console.log($(e.relatedTarget).data('ajax'));
            	$(this).find('#ajaxModalLabel').html('...');
            	$(this).find('#ajaxModalBody').html('Waiting ...');
            	$(this).find('#ajaxModalFooter').html('...');
            	$(this).find('#ajaxModalContent').load($(e.relatedTarget).data('ajax'));
        });
</script>

<div class="container-fluid ps-0 pe-0">
	<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
		<div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
				<div class="Light"><i class="bi bi-house-fill"></i> <?php echo $lang['home']; ?></div>
			        <div class="btn-group" id="homelist_date"><?php echo date("H:i"); ?></div>
			</div>
		</div>
		<!-- /card-header -->
		<div class="card-body">
        		<div class="row <?php echo theme($conn, $theme, 'row_justification'); ?>">
					<?php
					if ($user_display_mask == 0) {
						echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle black-background no-shadow '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`home.php?page_name=onetouch`)">
						<h3 class="text-nowrap buttontop"><small>'.$lang['one_touch'].'</small></h3>
				                <h3 class="degre" style="margin-top:0px;"><i class="bi bi-bullseye" style="font-size: 2rem;"></i></h3>
				                <h3 class="status"></h3>
			        	        </button>';
					}

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
	                		}else {
			                        $holidays_status = 0;
                			}

					//GET BOILER DATA AND FAIL ZONES IF SYSTEM CONTROLLER COMMS TIMEOUT
					//query to get last system_controller operation time and hysteresis time
                                        $query = "SELECT * FROM system_controller LIMIT 1";
                                        $result = $conn->query($query);
                                        $row = mysqli_fetch_array($result);
                                        $sc_count=$result->num_rows;
                                        $system_controller_id = $row['id'];
                                        $system_controller_node_id = $row['node_id'];
                                        $system_controller_name = $row['name'];
                                        $system_controller_max_operation_time = $row['max_operation_time'];
                                        $system_controller_hysteresis_time = $row['hysteresis_time'];
                                        $sc_mode  = $row['sc_mode'];
                                        $sc_active_status  = $row['active_status'];
                                        $hvac_relays_state = $row['hvac_relays_state'];

                                        if (!empty($system_controller_node_id)) {
                                                //Get data from nodes table
                                                $query = "SELECT * FROM nodes WHERE id = {$system_controller_node_id} AND status IS NOT NULL LIMIT 1";
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
                                        }

			                //if in HVAC mode display the mode selector
                			if ($system_controller_mode == 1) {
		        	                switch ($sc_mode) {
                			                case 0:
                                			        $current_sc_mode = $lang['mode_off'];
		                                	        break;
	                		                case 1:
        	                        		        $current_sc_mode = $lang['mode_timer'];
			                                        break;
                			                case 2:
                                			        $current_sc_mode = $lang['mode_timer'];
		                        	                break;
                		                	case 3:
                                		        	$current_sc_mode = $lang['mode_timer'];
			                                        break;
        	        		                case 4:
                	                		        $current_sc_mode = $lang['mode_auto'];
		        	                                break;
                			                case 5:
                                			        $current_sc_mode = $lang['mode_fan'];
		                                	        break;
	                		                case 6:
        	                        		        $current_sc_mode = $lang['mode_heat'];
			                                        break;
                			                case 7:
                                			        $current_sc_mode = $lang['mode_cool'];
		                        	                break;
                		                	default:
                                		        	$current_sc_mode = $lang['mode_off'];
						}
        	   			} else {
			                        switch ($sc_mode) {
                			                case 0:
                                			        $current_sc_mode = $lang['mode_off'];
		                        	                break;
                		                	case 1:
                                		        	$current_sc_mode = $lang['mode_timer'];
			                                        break;
        	        		                case 2:
                	                		        $current_sc_mode = $lang['mode_ce'];
		        	                                break;
                			                case 3:
                                			        $current_sc_mode = $lang['mode_hw'];
		                                	        break;
	                		                case 4:
        	                        		        $current_sc_mode = $lang['mode_both'];
			                                        break;
                			                default:
                                			        $current_sc_mode = $lang['mode_off'];
						}

	                		}

					if ($mode_select == 0 && $user_display_mask == 0) {
                                                echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="active_sc_mode()">
		        	        	<h3 class="text-nowrap buttontop"><small>'.$lang['mode'].'</small></h3>
	        			        <h3 class="degre" >'.$current_sc_mode.'</h3>';
		                        	if ($system_controller_mode == 1) {
                		                	switch ($sc_mode) {
                                		        	case 1:
                                                			echo '<h3 class="statuszoon float-left text-dark" style="margin-left:5px"><small>'.$lang['mode_heat'].'</small></h3>';
			                                                break;
        	        		                        case 2:
                	                		                echo '<h3 class="statuszoon float-left text-dark" style="margin-left:5px"><small>'.$lang['mode_cool'].'</small></h3>';
                        	                        		break;
		                	                        case 3:
                		        	                        echo '<h3 class="statuszoon float-left text-dark" style="margin-left:5px"><small>'.$lang['mode_auto'].'</small></h3>';
                                			                break;
		                                        	default:
                		                                	echo '<h3 class="statuszoon float-left text-dark"><small>&nbsp</small></h3>';
	                                		}
			                        } else {
                			                echo '<h3 class="statuszoon float-left text-dark"><small>&nbsp</small></h3>';
		        	                }
                			        echo '</button>';
			                } elseif ($user_display_mask == 0) {
						echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`home.php?page_name=mode`)">
                			        <h3 class="text-nowrap buttontop"><small>'.$current_sc_mode.'</small></h3>
		                	        <h3 class="degre" >'.$lang['mode'].'</h3>';
                		        	if ($system_controller_mode == 1) {
                                			switch ($sc_mode) {
		                                        	case 1:
                		                                	echo '<h3 class="status"><small class="statuszoon float-left text-dark" style="margin-left:5px">'.$lang['mode_heat'].'</small></h3>';
	                                		                break;
			                                        case 2:
                			                                echo '<h3 class="status"><small class="statuszoon float-left text-dark" style="margin-left:5px">'.$lang['mode_cool'].'</small></h3>';
                        	        		                break;
		                	                        case 3:
                		        	                        echo '<h3 class="status"><small class="statuszoon float-left text-dark" style="margin-left:5px">'.$lang['mode_auto'].'</small></h3>';
                                			                break;
		                                        	default:
                		                                	echo '<h3 class="statuszoon float-left text-dark"><small>&nbsp</small></h3>';
	                                		}
			                        } else {
                			                echo '<h3 class="statuszoon float-left text-dark"><small>&nbsp</small></h3>';
		        	                }
                			        echo '</button>';
			                }

					//loop through zones
					$active_schedule = 0;
					$zone_params = [];
					$query = "SELECT `zone`.`id`, `zone`.`name`, `zone_type`.`type`, `zone_type`.`category` FROM `zone`, `zone_type` WHERE (`zone`.`type_id` = `zone_type`.`id`) AND (`zone_type`.`category` = 0 OR `zone_type`.`category` = 3 OR `zone_type`.`category` = 4) ORDER BY `zone`.`index_id` ASC;";
					$results = $conn->query($query);
					while ($row = mysqli_fetch_assoc($results)) {
						$zone_id=$row['id'];
						$zone_name=$row['name'];
						$zone_type=$row['type'];
		                        	$zone_category=$row['category'];

	                		        //query to get the zone controller info
						if ($zone_category <> 3) {
	        			                $query = "SELECT relays.relay_id, relays.relay_child_id FROM zone_relays, relays WHERE (zone_relays.zone_relay_id = relays.id) AND zone_id = '{$zone_id}' LIMIT 1;";
        	        	        		$result = $conn->query($query);
		                		        $zone_relays = mysqli_fetch_array($result);
                		        		$zone_relay_id=$zone_relays['relay_id'];
	                        			$zone_relay_child_id=$zone_relays['relay_child_id'];
						}

						//query to get zone current state
						$query = "SELECT * FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
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
						$temp_reading_time = $zone_current_state['sensor_reading_time'];
						$overrun = $zone_current_state['overrun'];
						$schedule = $zone_current_state['schedule'];

		                	        //get the current zone schedule status
			                        $sch_status = $schedule & 0b1;
        	        		        $away_sch = ($schedule >> 1) & 0b1;
						if ($sch_status == 1) { $active_schedule = 1; }

						//get the sensor id
//			        	        $query = "SELECT * FROM sensors WHERE zone_id = '{$zone_id}';";
        					$query = "SELECT * FROM `sensors` WHERE (now() < DATE_ADD(`last_seen`, INTERVAL `fail_timeout` MINUTE) OR `fail_timeout` = 0) AND zone_id = '{$zone_id}';";
        			        	$sresults = $conn->query($query);
                                                $sensor_count = mysqli_num_rows($sresults);
						// multiple rows if a zone using multiple sensors
						$zone_c = 0;
                                                while ($srow = mysqli_fetch_assoc($sresults)) {
                                                        $s_id = $srow['id'];
                                                        $sensor_id = $srow['sensor_id'];
                                                        $sensor_child_id = $srow['sensor_child_id'];
                                                        $sensor_type_id = $srow['sensor_type_id'];
							$zone_c = $zone_c + $srow['current_val_1'];
                                                }
						$zone_c = $zone_c / $sensor_count;
						// if average zone temperature, then set chilf id to zero, for getting readings from the messages_in table
						if ($sensor_count > 1) {
							$zone_node_id = "zavg_".$zone_id; 
                                                        $sensor_child_id = 0;
						} else {
							//get the node id
        		        			$query = "SELECT node_id FROM nodes WHERE id = '{$sensor_id}' LIMIT 1;";
				                	$result = $conn->query($query);
                					$nodes = mysqli_fetch_array($result);
		                			$zone_node_id = $nodes['node_id'];
						}
                                              	$ajax_modal_24h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$s_id.",0";
                                              	$ajax_modal_1h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$s_id.",1";

						//query to get temperature from messages_in_view_24h table view
//			                        $query = "SELECT * FROM messages_in WHERE node_id = '{$zone_node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id desc LIMIT 1;";
//						$mresult = $conn->query($query);
//						$m_in = mysqli_fetch_array($mresult);
//						$zone_c = $m_in['payload'];
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
                        			7 - HVAC Fan Only
                        			8 - Max Running Time Exceeded - Hysteresis active*/

 						echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-href="#" data-bs-toggle="modal" data-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Schedule_List,'.$zone_id.'">
						<h3 class="text-nowrap buttontop"><small>'.$zone_name.'</small></h3>';
						if ($sensor_type_id == 3) {
							if ($zone_c == 0) { echo '<h3 class="degre" id="zd_'.$zone_id.'">OFF</h3>'; } else { echo '<h3 class="degre" id="zd_'.$zone_id.'">ON</h3>'; }
						} else {
							$unit = SensorUnits($conn,$sensor_type_id);
                                                        if ($sensor_count > 1) { // add symbol to indicate that this is an average reading
                                                                $unit = $unit .$lang['mean'];
                                                        }
        		                		echo '<h3 class="degre" id="zd_'.$zone_id.'">'.number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit.'</h3>';
						}
						echo '<h3 class="status">';

		                        	if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = $zone_mode + 10; }
	                		        $rval=getIndicators($conn, $zone_mode, $zone_temp_target);
			                        //Left small circular icon/color status
						echo '<small class="statuscircle" id="zs1_'.$zone_id.'"><i class="bi bi-circle-fill '.$rval['status'].'" style="font-size: 0.55rem;"></i></small>';
		        	                //Middle target temp
                			        if ($sensor_type_id != 3) { echo '<small class="statusdegree" id="zs2_'.$zone_id.'">' . $rval['target'] .'</small>'; }
		                        	//Right icon for what/why
	                		     	echo '<small class="statuszoon" id="zs3_'.$zone_id.'"><i class="bi ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' icon-fw"></i></small>';
			                        //Overrun Icon
                			        if($overrun == 1) {
		        	                    echo '<small class="statuszoon" id="zs4_'.$zone_id.'"><i class="bi bi-play-fill orange-red"></i></small>';
                			        }
		                        	echo '</h3></button>';      //close out status and button
						$zone_params[] = array('zone_id' =>$row['id'], 'zone_name' =>$row['name'], 'zone_category' =>$row['category']);
					} // end of zones while loop

                			// Temperature Sensors Pre System Controller
					$sensor_params = [];
                			$query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id, nodes.node_id, nodes.last_seen, nodes.notice_interval
						FROM sensors, nodes
						WHERE (nodes.id = sensors.sensor_id) AND sensors.zone_id = 0 AND sensors.show_it = 1 AND sensors.pre_post = 1
						AND (sensors.user_display & {$user_display_mask}) = 0
						order by index_id asc;";
			                $results = $conn->query($query);
        	        		while ($row = mysqli_fetch_assoc($results)) {
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
                			        //query to get sensor from messages_in table
				                if ($sensor_type_id == 4) {
                        				$query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' AND sub_type = 0 ORDER BY id DESC LIMIT 1;";
                				} else {
                        				$query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id DESC LIMIT 1;";
                				}
                			        $result = $conn->query($query);
		                        	$sensor = mysqli_fetch_array($result);
	                		        $sensor_r = $sensor['payload'];
			                        $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor_id.",0";
                			        echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-toggle="modal" data-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal.'">
		        	                <h3 class="text-nowrap buttontop"><small>'.$sensor_name.'</small></h3>';
                                                if ($sensor_type_id == 3) {
                                                        $deg_msg = floor($sensor_r);
                                                        $query = "SELECT message FROM sensor_messages WHERE message_id = {$deg_msg} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                                                        $result = $conn->query($query);
                                                        $rowcount=mysqli_num_rows($result);
                                                        if ($rowcount == 0) {
                                                                if ($sensor_r == 0) {
                                                                        echo '<h3 class="degre" id="sd_'.$sensor_id.'">OFF</h3>';
                                                                } else {
                                                                        echo '<h3 class="degre" id="sd_'.$sensor_id.'">ON</h3>';
                                                                }
                                                        } else {
                                                                $sensor_message = mysqli_fetch_array($result);
                                                                echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.$sensor_message['message'].'</h3>';
                                                        }
                                                } elseif ($sensor_type_id == 4) {
                                                        $deg_msg = floor($sensor_r);
                                                        $query = "SELECT message FROM sensor_messages WHERE message_id = {$deg_msg} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                                                        $result = $conn->query($query);
                                                        $sensor_message = mysqli_fetch_array($result);
                                                        echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.$sensor_message['message'].'</h3>';
						} else {
							$unit = SensorUnits($conn,$sensor_type_id);
        		                		echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.number_format(DispSensor($conn,$sensor_r,$sensor_type_id),1).$unit.'</h3>';
						}
			                        if ($sensor_type_id == 4) {
                        			        $s_color = floor($sensor_r);
			                                $query = "SELECT status_color FROM sensor_messages WHERE message_id = {$s_color} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                        			        $result = $conn->query($query);
			                                $sensor_message = mysqli_fetch_array($result);
                        			        $shcolor = $sensor_message['status_color'];
			                        }
                			        echo '<h3 class="status">
		                        	<small class="statuscircle" id="ss1_'.$sensor_id.'"><i class="bi bi-circle-fill '.$shcolor.'" style="font-size: 0.55rem;"></i></small>';
                                                //Right Lower Message
                                                if ($sensor_type_id != 3) {
                                                        if ($sensor_type_id == 4) {
                                                                $s_msg = floor($sensor_r);
                                                        	$query = "SELECT message FROM sensor_messages WHERE message_id = {$s_msg} AND sub_type = 1 AND sensor_id = {$sensor_id} LIMIT 1;";
                                                                $result = $conn->query($query);
                                                                $right_message = mysqli_fetch_array($result);
                                                                $msg = $right_message['message'];
                                                        } else {
                                                                $msg = $rval['target'];
                                                        }
                                                        echo '<small class="statuszoon" id="ss2_'.$sensor_id.'">' . $rmsg .'</small>';
                                                }
	                		        echo '</h3></button>';      //close out status and button
						$sensor_params[] = array('sensor_id' =>$row['id'], 'sensor_name' =>$row['name']);
                			}

					//SYSTEM CONTROLLER BUTTON
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

						echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-toggle="modal" data-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_SystemController">
						<h3 class="text-nowrap buttontop"><small>'.$system_controller_name.'</small></h3>';
						if ($system_controller_mode == 1) {
                                			switch ($sc_mode) {
		                        	                case 0:
                		                	                echo '<h3 class="degre" id="scd" ><i class="bi bi-power"></i></h3>';
                                		        	        break;
			                                        case 1:
									if ($active_schedule) {
                	                		                	if ($hvac_relays_state & 0b100) { $system_controller_colour="colorize-red"; } else { $system_controller_colour="colorize-blue"; }
									} else {
										$system_controller_colour="";
									}
                                			                echo '<h3 class="degre" id="scd" ><i class="bi bi-snow icon-1x '.$system_controller_colour.'"></i></h3>';
									break;
								case 2:
        	        		                                if ($active_schedule) {
                	                		                	if ($hvac_relays_state & 0b010) { $system_controller_colour="blueinfo"; } else { $system_controller_colour="orange-red"; }
                        	                        		} else {
		                	                                        $system_controller_colour="";
                		        	                        }
                                			                echo '<h3 class="degre" id="scd" ><i class="bi bi-snow icon-1x '.$system_controller_colour.'"></i></h3>';
                                                			break;
			                                        case 3:
									if ($hvac_relays_state == 0b000) {
                	                					if ($sc_active_status==1) {
                        	                					$system_controller_colour="green";
		                	                			} elseif ($sc_active_status==0) {
                		        	                			$system_controller_colour="";
                                						}
										echo '<h3 class="degre" id="scd" ><i class="bi bi-power '.$system_controller_colour.'" style="font-size: 1.2rem;"></i></h3>';
									} elseif ($hvac_relays_state & 0b100) {
										echo '<h3 class="degre" id="scd" ><i class="bi bi-fire red" style="font-size: 1.2rem;"></i></h3>';
									} elseif ($hvac_relays_state & 0b010) {
										echo '<h3 class="degre" id="scd" ><i class="bi bi-snow blueinfo" style="font-size: 1.2rem;"></i></h3>';
									}
									break;
                                			        case 4:
                                        	        		if ($hvac_relays_state == 0b000) {
		                                	                       	$system_controller_colour="green";
                		                        	                echo '<h3 class="degre" id="scd" ><i class="bi bi-power '.$system_controller_colour.'" style="font-size: 1.2rem;"></i></h3>';
                                		                	} elseif ($hvac_relays_state & 0b100) {
	                                                		        echo '<h3 class="degre" id="scd" ><i class="bi bi-fire red" style="font-size: 1.2rem;"></i></h3>';
			                                                } elseif ($hvac_relays_state & 0b010) {
                			                                        echo '<h3 class="degre" id="scd" ><i class="bi bi-snow blue" style="font-size: 1.2rem;"></i></h3>';
                        	        		                }
                                	                		break;
		                        	                case 5:
                		                                echo '<h3 class="degre" id="scd" ><img src="images/hvac_fan_30.png" border="0"></h3>';
                                			                break;
		                                        	case 6:
									if ($hvac_relays_state & 0b100) { $system_controller_colour = "red"; } else { $system_controller_colour = "blueinfo"; }
										echo '<h3 class="degre" id="scd" ><i class="bi bi-fire '.$system_controller_colour.'" style="font-size: 1.2rem;"></i></h3>';
        	                                        		break;
			                                        case 7:
                			                                if ($hvac_relays_state & 0b010) { $system_controller_colour = "blueinfo"; } else { $system_controller_colour = ""; }
                                			                echo '<h3 class="degre" id="scd" ><i class="bi bi-snow '.$system_controller_colour.'" style="font-size: 1.2rem;">></i></h3>';
                                        	        		break;
								default:
                		                        	        echo '<h3 class="degre" id="scd" ><i class="bi bi-power"></i></h3>';
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
							echo '<h3 class="degre" id="scd" ><i class="bi bi-fire '.$system_controller_colour.'" style="font-size: 1.4rem;"></i></h3>';
						}

						if($system_controller_fault=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="statuszoon" id="scs"><i class="bi bi-x-circle-fill red"></i> </small>';}
						elseif($hysteresis=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="statuszoon" id="scs"><i class="bi bi-hourglass-split orange-red"></i> </small>';}
						else { echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 48px;" class="statuszoon" id="scs"></small>';}
						echo '</h3></button>';
					}
					// end if system controller button

					// Temperature Sensors Post System Controller
					$query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id, sensors.user_display, nodes.node_id, nodes.last_seen,
						nodes.notice_interval FROM sensors, nodes WHERE (nodes.id = sensors.sensor_id) AND sensors.zone_id = 0 AND sensors.show_it = 1
						AND sensors.pre_post = 0 AND (sensors.user_display & {$user_display_mask}) = 0
						order by index_id asc;";
			                $results = $conn->query($query);
                			while ($row = mysqli_fetch_assoc($results)) {
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
		                        	//query to get sensor reading from messages_in  table
                                                if ($sensor_type_id == 4) {
		                                        $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' AND sub_type = 0 ORDER BY id DESC LIMIT 1;";
                                                } else {
                                                        $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id DESC LIMIT 1;";
                                                }
			                        $result = $conn->query($query);
                			        $sensor = mysqli_fetch_array($result);
		        	                $sensor_r = $sensor['payload'];
                			        $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor_id.",0";
		   				echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-toggle="modal" data-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal.'">
	                		        <h3 class="text-nowrap buttontop"><small>'.$sensor_name.'</small></h3>';
			                        if ($sensor_type_id == 3) {
                			                if ($sensor_r == 0) {
								echo '<h3 class="degre" id="sd_'.$sensor_id.'">OFF</h3>';
							} else {
								echo '<h3 class="degre" id="sd_'.$sensor_id.'">ON</h3>';
							}
						} elseif ($sensor_type_id == 4) {
                                			$deg_msg = floor($sensor_r);
                                			$query = "SELECT message FROM sensor_messages WHERE message_id = {$deg_msg} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                                			$result = $conn->query($query);
                                			$sensor_message = mysqli_fetch_array($result);
							echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.$sensor_message['message'].'</h3>';
						} else {
							$unit = SensorUnits($conn,$sensor_type_id);
        	                			echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.number_format(DispSensor($conn,$sensor_r,$sensor_type_id),1).$unit.'</h3>';
						}
			                        if ($sensor_type_id == 4) {
                        			        $s_color = floor($sensor_r);
			                                $query = "SELECT status_color FROM sensor_messages WHERE message_id = {$s_color} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                        			        $result = $conn->query($query);
			                                $sensor_message = mysqli_fetch_array($result);
                        			        $shcolor = $sensor_message['status_color'];
			                        }
        	        		        echo '<h3 class="status">
			                        <small class="statuscircle" id="ss1_'.$sensor_id.'"><i class="bi bi-circle-fill '.$shcolor.'" style="font-size: 0.55rem;"></i></small>';
                                                //Right Lower Message
                                                if ($sensor_type_id != 3) {
				                        if ($sensor_type_id == 4) {
                                				$s_msg = floor($sensor_r);
				                        	$query = "SELECT message FROM sensor_messages WHERE message_id = {$s_msg} AND sub_type = 1 AND sensor_id = {$sensor_id} LIMIT 1;";
                                				$result = $conn->query($query);
				                                $right_message = mysqli_fetch_array($result);
                                				$msg = $right_message['message'];
                        				} else {
								$msg = $rval['target'];
							}
							echo '<small class="statuszoon" id="ss2_'.$sensor_id.'">' . $rmsg .'</small>';
						}
                			        echo '</h3></button>';      //close out status and button
						$sensor_params[] = array('sensor_id' =>$row['id'], 'sensor_name' =>$row['name']);
	 				}
//					$js_sensor_params = json_encode($sensor_params);

                			// Add-On buttons
/*		        	        $query = "SELECT `zone`.`id`, `zone`.`name`, `zone_type`.`type`, `zone_type`.`category`
						FROM `zone`, `zone_type`
						WHERE (`zone`.`type_id` = `zone_type`.`id`) AND (`zone_type`.`category` = 1 OR `zone_type`.`category` = 2 OR `zone_type`.`category` = 5)
						ORDER BY `zone`.`index_id` ASC;"; */

					$query = "SELECT DISTINCT `zone`.`id`, `zone`.`name`, `zt`.`type`, `zt`.`category`, `r`.`user_display`
						FROM `zone`
						LEFT JOIN `zone_type` zt ON `zone`.`type_id` = zt.`id`
						LEFT JOIN `zone_relays` zr ON `zone`.`id` = zr.`zone_id`
						LEFT JOIN `relays` r on `zr`.`zone_relay_id` = r.`id`
						WHERE (`zt`.`category` = 1 OR `zt`.`category` = 2 OR `zt`.`category` = 5) AND (`r`.`user_display` & {$user_display_mask}) = 0
						ORDER BY `zone`.`index_id` ASC;";
	                		$z_results = $conn->query($query);
			                while ($zone_row = mysqli_fetch_assoc($z_results)) {
                			        //get the schedule status for this zone
						$zone_id = $zone_row['id'];
                			        $zone_name = $zone_row['name'];
		                        	$zone_type = $zone_row['type'];
	                		        $zone_category = $zone_row['category'];
						if ($zone_category != 2) {	// switch type zones do not have a sensor allocated
				                        //get the sensor id
	                			        $query = "SELECT * FROM sensors WHERE zone_id = '{$zone_id}' LIMIT 1;";
			        	                $result = $conn->query($query);
                				        $sensor = mysqli_fetch_array($result);
		        	                	$temperature_sensor_id=$sensor['sensor_id'];
	                			        $temperature_sensor_child_id=$sensor['sensor_child_id'];
			                	        $sensor_type_id=$sensor['sensor_type_id'];
                                                	$ajax_modal_24h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor['id'].",0";
	                                                $ajax_modal_1h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor['id'].",1";

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
						}

						//get the current zone schedule status
		                        	$query = "SELECT * FROM zone_current_state WHERE zone_id =  '{$zone_id}' LIMIT 1;";
	                		        $result = $conn->query($query);
			                        $zone_current_state = mysqli_fetch_array($result);
                                                $zone_temp_target = $zone_current_state['temp_target'];
                                                $schedule = $zone_current_state['schedule'];

                                                //get the current zone schedule status
                                                $sch_status = $schedule & 0b1;
                                                $away_sch = ($schedule >> 1) & 0b1;
                			        if ($zone_current_state['mode'] == 0) { $add_on_active = 0; } else { $add_on_active = 1; }

			                        if ($add_on_active == 1 && $away_status == 0) { $add_on_colour = "green"; } elseif ($add_on_active == 0) { $add_on_colour = "black"; }
                                                if ($zone_category == 5) {
							$add_on_colour = "black";
	 						echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-href="#" data-bs-toggle="modal" data-remote="false" data-bs-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Schedule_List,'.$zone_id.'">';
        	        		        } elseif ($zone_category == 2) {
							$link = 'toggle_add_on('.$zone_id.')';
		        	                	echo '<button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn" onclick="'.$link.'">';
						} else {
	   						echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#'.$zone_type.''.$zone_id.'" data-bs-backdrop="static" data-bs-keyboard="false">';
						}
        	        		        echo '<h3 class="text-nowrap buttontop"><small>'.$zone_name.'</small></h3>';
			                        if ($zone_category == 1 || $zone_category == 5) {
                			                $unit = SensorUnits($conn,$sensor_type_id);
                                			echo '<h3 class="degre" id="zd_'.$zone_id.'">'.number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit.'</h3>';
		                        	} elseif ($zone_category == 6) {
							if ($add_on_active == 0) { echo '<h3 class="degre" id="zd_'.$zone_id.'">OFF</h3>'; } else { echo '<h3 class="degre" id="zd_'.$zone_id.'">ON</h3>'; }
						} else {
        	        		        	echo '<h3 class="degre" id="zd_'.$zone_id.'"><i class="bi bi-power '.$add_on_colour.'" style="font-size: 1.4rem;"></i></h3>';
						}
                			        echo '<h3 class="status">';

			                        if ($sch_status =='1') {
        	        		                $add_on_mode = $zone_current_state['mode'];
			                        } else {
                			                if ($add_on_active == 0) {
								$add_on_mode = 0;
							} elseif ($zone_category == 1 || $zone_category == 2) {
								$add_on_mode = $zone_current_state['mode'];
							} else {
								$add_on_mode = 114;
							}
		                	        }

						if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = 90; }
			                        $rval=getIndicators($conn, $add_on_mode, $zone_temp_target);
        	        		        //Left small circular icon/color status
						echo '<small class="statuscircle" id="zs1_'.$zone_id.'"><i class="bi bi-circle-fill '.$rval['status'].'" style="font-size: 0.55rem;"></i></small>';
                			        //Middle target temp
		                	        echo '<small class="statusdegree" id="zs2_'.$zone_id.'">' . $rval['target'] .'</small>';
                		        	//Right icon for what/why
			                        echo '<small class="statuszoon" id="zs3_'.$zone_id.'"><i class="bi ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' icon-fw"></i></small>';
        	        		        echo '</h3></button>';      //close out status and button
			                        $zone_params[] = array('zone_id' =>$zone_id, 'zone_name' =>$zone_name, 'zone_category' =>$zone_category);

                                                if ($zone_category == 2) {      // switch type zones may sensors associated
							// display any sensors associated with the current zone
							$query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id, sensors.user_display, nodes.node_id,
								nodes.last_seen, nodes.notice_interval
								FROM sensors, nodes
								WHERE (nodes.id = sensors.sensor_id) AND sensors.zone_id = {$zone_id} AND sensors.show_it = 1
								AND (sensors.user_display & {$user_display_mask}) = 0
								order by index_id asc;";
			        		        $s_results = $conn->query($query);
                					while ($s_row = mysqli_fetch_assoc($s_results)) {
			                	        	$sensor_id = $s_row['id'];
								$sensor_name = $s_row['name'];
					                        $sensor_child_id = $s_row['sensor_child_id'];
								$node_id = $s_row['node_id'];
					                        $node_seen = $s_row['last_seen'];
                					        $node_notice = $s_row['notice_interval'];
								$sensor_type_id = $s_row['sensor_type_id'];
								$shcolor = "green";
						                if($node_notice > 0){
        						                $now=strtotime(date('Y-m-d H:i:s'));
                				        		$node_seen_time = strtotime($node_seen);
		        			                	if ($node_seen_time  < ($now - ($node_notice*60))) { $shcolor = "red"; }
        						        }
		                        			//query to get sensor reading from messages_in  table
                                                		if ($sensor_type_id == 4) {
			                                	        $query = "SELECT *
										FROM messages_in 
										WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' AND sub_type = 0
										ORDER BY id DESC LIMIT 1;";
	        	                                        } else {
        	        	                                        $query = "SELECT *
										FROM messages_in
										WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}'
										ORDER BY id DESC LIMIT 1;";
                	        	                        }
				        	                $result = $conn->query($query);
                					        $sensor = mysqli_fetch_array($result);
		        	        	        	$sensor_r = $sensor['payload'];
	                				        $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor_id.",0";
			   					echo '<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-toggle="modal" data-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal.'">
		        	        		        <h3 class="text-nowrap buttontop"><small>'.$sensor_name.'</small></h3>';
					                        if ($sensor_type_id == 3) {
                					                if ($sensor_r == 0) {
										echo '<h3 class="degre" id="sd_'.$sensor_id.'">OFF</h3>';
									} else {
										echo '<h3 class="degre" id="sd_'.$sensor_id.'">ON</h3>';
									}
								} elseif ($sensor_type_id == 4) {
                	                				$deg_msg = floor($sensor_r);
	                        	        			$query = "SELECT message
										FROM sensor_messages
										WHERE message_id = {$deg_msg} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
        	                        				$result = $conn->query($query);
                	                				$sensor_message = mysqli_fetch_array($result);
									echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.$sensor_message['message'].'</h3>';
								} else {
									$unit = SensorUnits($conn,$sensor_type_id);
        		                				echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.number_format(DispSensor($conn,$sensor_r,$sensor_type_id),1).$unit.'</h3>';
								}
				        	                if ($sensor_type_id == 4) {
        	                				        $s_color = floor($sensor_r);
				                        	        $query = "SELECT status_color
										FROM sensor_messages
										WHERE message_id = {$s_color} AND sub_type = 0 AND sensor_id = {$sensor_id} LIMIT 1;";
                        				        	$result = $conn->query($query);
					                                $sensor_message = mysqli_fetch_array($result);
        	                				        $shcolor = $sensor_message['status_color'];
				                	        }
        	        			        	echo '<h3 class="status">
				        	                <small class="statuscircle" id="ss1_'.$sensor_id.'"><i class="bi bi-circle-fill '.$shcolor.'" style="font-size: 0.55rem;"></i></small>';
        	                                	        //Right Lower Message
                	                                	if ($sensor_type_id != 3) {
					                        	if ($sensor_type_id == 4) {
                                						$s_msg = floor($sensor_r);
						                        	$query = "SELECT message 
											FROM sensor_messages
											WHERE message_id = {$s_msg} AND sub_type = 1 AND sensor_id = {$sensor_id} LIMIT 1;";
        	                        					$result = $conn->query($query);
					                	                $right_message = mysqli_fetch_array($result);
                        	        					$msg = $right_message['message'];
	                        					} else {
										$msg = $rval['target'];
									}
									echo '<small class="statuszoon" id="ss2_'.$sensor_id.'">' . $rmsg .'</small>';
								}
        	        				        echo '</h3></button>';      //close out status and button
								$sensor_params[] = array('sensor_id' =>$sensor_id, 'sensor_name' =>$sensor_name);
							}
	 					}
                			} // end of zones while loop
                                        $js_sensor_params = json_encode($sensor_params);
		                	$js_zone_params = json_encode($zone_params);

					//select addional onetouch buttons
					$button_params = [];
		        	        $query = "SELECT * FROM button_page WHERE page = 1 ORDER BY index_id ASC";
                			$results = $conn->query($query);
			                if (mysqli_num_rows($results) > 0) {
        	        		        while ($row = mysqli_fetch_assoc($results)) {
                	                		$var = $row['function'];
		        	                        $var($conn, $lang[$var]);
							if ($row['page'] == 1) { $button_params[] = array('button_id' =>$row['id'], 'button_name' =>$row['name'], 'button_function' =>$row['function']); }
		                        	}
						$js_button_params = json_encode($button_params);
        	        		}
					?>
        		</div>
        		<!-- /.row -->
		</div>
                <!-- /.card-body -->
		<!-- Generic Ajax Modal -->
        	<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		           <div class="modal-dialog">
                		<div class="modal-content" id="ajaxModalContent">
                                	<div class="modal-header <?php echo theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color'); ?>">
                                        	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		                                 <h5 class="modal-title" id="ajaxModalLabel">...</h5>
                		        </div>
                                	<div class="modal-body" id="ajaxModalBody">
                                        	<?php echo $lang['waiting']; ?>
		                        </div>
                		        	<div class="modal-footer" id="ajaxModalFooter">
                                			...
		                        </div>
                		</div>
			</div>
                </div>

		<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
	          		<div class="btn-group" id="footer_weather">
			        	<?php
                			ShowWeather($conn);
                        	?>
			        </div>

                	        <div class="btn-group" id="footer_running_time">
					<?php
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
					if($system_controller_time_on >0){	echo ' <i class="bi bi-clock"></i>&nbsp'.secondsToWords(($system_controller_time_on)*60);}
					?>
				</div>
			</div>
		</div>
		<!-- /.card-footer -->
	</div>
	<!-- /.card -->
</div>
<!-- /.container -->

<?php if(isset($conn)) { $conn->close();} ?>
<script language="javascript" type="text/javascript">

// update the screen data every x seconds
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';
  var live_temp_zone_id = document.getElementById("zone_id").value;

  (function loop() {
    var data = '<?php echo $js_zone_params ?>';
    if (data.length > 0) {
            var obj = JSON.parse(data)
            //console.log(obj.length);

            for (var i = 0; i < obj.length; i++) {
              $('#zd_' + obj[i].zone_id).load("ajax_fetch_data.php?id=" + obj[i].zone_id + "&type=1").fadeIn("slow");
              $('#zs1_' + obj[i].zone_id).load("ajax_fetch_data.php?id=" + obj[i].zone_id + "&type=2").fadeIn("slow");
              $('#zs2_' + obj[i].zone_id).load("ajax_fetch_data.php?id=" + obj[i].zone_id + "&type=3").fadeIn("slow");
              $('#zs3_' + obj[i].zone_id).load("ajax_fetch_data.php?id=" + obj[i].zone_id + "&type=4").fadeIn("slow");
              $('#zs4_' + obj[i].zone_id).load("ajax_fetch_data.php?id=" + obj[i].zone_id + "&type=5").fadeIn("slow");
              // console.log(obj[i].zone_id + ", " + obj[i].zone_category);
            }
    }

    var data1 = '<?php echo $js_sensor_params ?>';
    if (data1.length > 0) {
            var obj1 = JSON.parse(data1)
            //console.log(obj1.length);

            for (var x = 0; x < obj1.length; x++) {
              $('#sd_' + obj1[x].sensor_id).load("ajax_fetch_data.php?id=" + obj1[x].sensor_id + "&type=6").fadeIn("slow");
              $('#ss1_' + obj1[x].sensor_id).load("ajax_fetch_data.php?id=" + obj1[x].sensor_id + "&type=7").fadeIn("slow");
              $('#ss2_' + obj1[x].sensor_id).load("ajax_fetch_data.php?id=" + obj1[x].sensor_id + "&type=8").fadeIn("slow");
              // console.log(obj1[i].sensor_id);
            }
            $('#scd').load("ajax_fetch_data.php?id=0&type=9").fadeIn("slow");
            $('#scs').load("ajax_fetch_data.php?id=0&type=10").fadeIn("slow");
    }

    var data2 = '<?php echo $js_button_params ?>';
    if (data2.length > 0) {
            var obj2 = JSON.parse(data2)
            // console.log(obj2.length);

            for (var y = 0; y < obj2.length; y++) {
              if (obj2[y].button_function == "live_temp") {
                $('#load_temp').load("ajax_fetch_data.php?id=" + live_temp_zone_id + "&type=1").fadeIn("slow");
              }
              $('#bs1_' + obj2[y].button_id).load("ajax_fetch_data.php?id=" + obj2[y].button_id + "&type=11").fadeIn("slow");
              $('#bs2_' + obj2[y].button_id).load("ajax_fetch_data.php?id=" + obj2[y].button_id + "&type=12").fadeIn("slow");
//              console.log(obj2[y].button_id);
//              console.log(obj2[y].button_function);
            }
    }

    $('#sc_status').load("ajax_fetch_data.php?id=0&type=24").fadeIn("slow");
    $('#homelist_date').load("ajax_fetch_data.php?id=0&type=13").fadeIn("slow");
    $('#footer_weather').load("ajax_fetch_data.php?id=0&type=14").fadeIn("slow");
    $('#footer_running_time').load("ajax_fetch_data.php?id=0&type=15").fadeIn("slow");
    setTimeout(loop, delay);
  })();
});
</script>
