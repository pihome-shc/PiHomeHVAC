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

if(settings($conn, 'language') == "sk" || settings($conn, 'language') == "de") { $button_style = "btn-xxl-wide"; } else { $button_style = "btn-xxl"; }
$page_refresh = settings($conn, 'page_refresh') * 1000;
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


<div class="panel panel-primary">
        <div class="panel-heading">
                <div class="Light"><i class="fa fa-home fa-fw"></i> <?php echo $lang['home']; ?>
                        <div class="pull-right">
                                <div class="btn-group" id="homelist_date"><?php echo date("H:i"); ?>
                                </div>
                        </div>
                </div>
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">
                <a style="color: #777; cursor: pointer; text-decoration: none;" href="home.php?page_name=onetouch">
                <button class="btn btn-default btn-circle black-background <?php echo $button_style; ?> mainbtn animated fadeIn">
                <h3><small><?php echo $lang['one_touch']; ?></small></h3>
                <h3 class="degre" style="margin-top:0px;"><i class="fa fa-bullseye fa-2x"></i></h3>
                <h3 class="status"></h3>
                </button></a>
                <?php

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

		if ($mode_select == 0 ) {
		        echo '<a href="javascript:active_sc_mode();">
        	        <button type="button" class="btn btn-default btn-circle '.$button_style.' mainbtn">
                	<h3 class="buttontop"><small>'.$lang['mode'].'</small></h3>
	                <h3 class="degre" >'.$current_sc_mode.'</h3>';
                        if ($system_controller_mode == 1) {
                                switch ($sc_mode) {
                                        case 1:
                                                echo '<h3 class="statuszoon pull-left text-dark" style="margin-left:5px"><small>'.$lang['mode_heat'].'</small></h3>';
                                                break;
                                        case 2:
                                                echo '<h3 class="statuszoon pull-left text-dark" style="margin-left:5px"><small>'.$lang['mode_cool'].'</small></h3>';
                                                break;
                                        case 3:
                                                echo '<h3 class="statuszoon pull-left text-dark" style="margin-left:5px"><small>'.$lang['mode_auto'].'</small></h3>';
                                                break;
                                        default:
                                                echo '<h3 class="statuszoon pull-left text-dark"><small>&nbsp</small></h3>';
                                }
                        } else {
                                echo '<h3 class="statuszoon pull-left text-dark"><small>&nbsp</small></h3>';
                        }
                        echo '</button></a>';
                } else {
                        echo '<a style="color: #777; cursor: pointer; <small>text-decoration: none;" href="home.php?page_name=mode">
                        <button class="btn btn-default btn-circle black-background '.$button_style.' mainbtn animated fadeIn">
                        <h3><small>'.$current_sc_mode.'</small></h3>
                        <h3 class="degre" >'.$lang['mode'].'</h3>';
                        if ($system_controller_mode == 1) {
                                switch ($sc_mode) {
                                        case 1:
                                                echo '<h3 class="statuszoon pull-left text-dark" style="margin-left:5px"><small>'.$lang['mode_heat'].'</small></h3>';
                                                break;
                                        case 2:
                                                echo '<h3 class="statuszoon pull-left text-dark" style="margin-left:5px"><small>'.$lang['mode_cool'].'</small></h3>';
                                                break;
                                        case 3:
                                                echo '<h3 class="statuszoon pull-left text-dark" style="margin-left:5px"><small>'.$lang['mode_auto'].'</small></h3>';
                                                break;
                                        default:
                                                echo '<h3 class="statuszoon pull-left text-dark"><small>&nbsp</small></h3>';
                                }
                        } else {
                                echo '<h3 class="statuszoon pull-left text-dark"><small>&nbsp</small></h3>';
                        }
                        echo '</button></a>';
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
			$temp_reading_time= $zone_current_state['sensor_reading_time'];
			$overrun= $zone_current_state['overrun'];

                        //get the current zone schedule status
                        $rval=get_schedule_status($conn, $zone_id,$holidays_status,$away_status);
                        $sch_status = $rval['sch_status'];
                        $away_sch = $rval['away_sch'];
			if ($sch_status == 1) { $active_schedule = 1; }

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

 			echo '<button class="btn btn-default btn-circle '.$button_style.' mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Schedule_List,'.$zone_id.'">
			<h3><small>'.$zone_name.'</small></h3>';
			if ($sensor_type_id == 3) {
				if ($zone_c == 0) { echo '<h3 class="degre" id="zd_'.$zone_id.'">OFF</h3>'; } else { echo '<h3 class="degre" id="zd_'.$zone_id.'">ON</h3>'; }
			} else {
				$unit = SensorUnits($conn,$sensor_type_id);
        	                echo '<h3 class="degre" id="zd_'.$zone_id.'">'.number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit.'</h3>';
			}
			echo '<h3 class="status">';

                        if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = $zone_mode + 10; }
                        $rval=getIndicators($conn, $zone_mode, $zone_temp_target);
                        //Left small circular icon/color status
                        echo '<small class="statuscircle" id="zs1_'.$zone_id.'"><i class="fa fa-circle fa-fw ' . $rval['status'] . '"></i></small>';
                        //Middle target temp
                        if ($sensor_type_id != 3) { echo '<small class="statusdegree" id="zs2_'.$zone_id.'">' . $rval['target'] .'</small>'; }
                        //Right icon for what/why
                        echo '<small class="statuszoon" id="zs3_'.$zone_id.'"><i class="fa ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' fa-fw"></i></small>';
                        //Overrun Icon
                        if($overrun == 1) {
                            echo '<small class="statuszoon" id="zs4_'.$zone_id.'"><i class="fa ion-ios-play-outline orange fa-fw"></i></small>';
                        }
                        echo '</h3></button>';      //close out status and button
			$zone_params[] = array('zone_id' =>$row['id'], 'zone_name' =>$row['name'], 'zone_category' =>$row['category']);
		} // end of zones while loop

                // Temperature Sensors Pre System Controller
		$sensor_params = [];
                $query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id, nodes.node_id, nodes.last_seen, nodes.notice_interval FROM sensors, nodes WHERE (nodes.id = sensors.sensor_id) AND sensors.zone_id = 0 AND sensors.show_it = 1 AND sensors.pre_post = 1 order by index_id asc;";
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
                        //query to get temperature from messages_in_view_24h table view
                        $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id desc LIMIT 1;";
                        $result = $conn->query($query);
                        $sensor = mysqli_fetch_array($result);
                        $sensor_c = $sensor['payload'];
                        $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor_id.",0";
                        echo '<button class="btn btn-default btn-circle '.$button_style.' mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="'.$ajax_modal.'">
                        <h3><small>'.$sensor_name.'</small></h3>';
                        if ($sensor_type_id == 3) {
                                if ($sensor_c == 0) { echo '<h3 class="degre" id="sd_'.$sensor_id.'">OFF</h3>'; } else { echo '<h3 class="degre" id="sd_'.$sensor_id.'">ON</h3>'; }
			} else {
				$unit = SensorUnits($conn,$sensor_type_id);
        	                echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit.'</h3>';
			}
                        echo '<h3 class="status">
                        <small class="statuscircle" id="ss1_'.$sensor_id.'"><i class="fa fa-circle fa-fw '.$shcolor.'"></i></small>
                        </h3></button>';      //close out status and button
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

			echo '<button class="btn btn-default btn-circle '.$button_style.' mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_SystemController">
			<h3 class="text-info"><small>'.$system_controller_name.'</small></h3>';
			if ($system_controller_mode == 1) {
                                switch ($sc_mode) {
                                        case 0:
                                                echo '<h3 class="degre" id="scd" ><i class="fa fa-circle-o-notch"></i></h3>';
                                                break;
                                        case 1:
						if ($active_schedule) {
                                                	if ($hvac_relays_state & 0b100) { $system_controller_colour="red"; } else { $system_controller_colour="blue"; }
						} else {
							$system_controller_colour="";
						}
                                                echo '<h3 class="degre" id="scd" ><i class="ionicons ion-flame fa-1x '.$system_controller_colour.'"></i></h3>';
						break;
					case 2:
                                                if ($active_schedule) {
                                                	if ($hvac_relays_state & 0b010) { $system_controller_colour="blueinfo"; } else { $system_controller_colour="orange"; }
                                                } else {
                                                        $system_controller_colour="";
                                                }
                                                echo '<h3 class="degre" id="scd" ><i class="fa fa-snowflake-o fa-1x '.$system_controller_colour.'"></i></h3>';
                                                break;
                                        case 3:
						if ($hvac_relays_state == 0b000) {
                                			if ($sc_active_status==1) {
                                        			$system_controller_colour="green";
                                			} elseif ($sc_active_status==0) {
                                        			$system_controller_colour="";
                                			}
							echo '<h3 class="degre" id="scd" ><i class="fa fa-circle-o-notch fa-1x '.$system_controller_colour.'"></i></h3>';
						} elseif ($hvac_relays_state & 0b100) {
							echo '<h3 class="degre" id="scd" ><i class="ionicons ion-flame fa-1x red"></i></h3>';
						} elseif ($hvac_relays_state & 0b010) {
							echo '<h3 class="degre" id="scd" ><i class="fa fa-snowflake-o fa-1x blueinfo"></i></h3>';
						}
						break;
                                        case 4:
                                                if ($hvac_relays_state == 0b000) {
                                                       	$system_controller_colour="green";
                                                        echo '<h3 class="degre" id="scd" ><i class="fa fa-circle-o-notch fa-1x '.$system_controller_colour.'"></i></h3>';
                                                } elseif ($hvac_relays_state & 0b100) {
                                                        echo '<h3 class="degre" id="scd" ><i class="ionicons ion-flame fa-1x red"></i></h3>';
                                                } elseif ($hvac_relays_state & 0b010) {
                                                        echo '<h3 class="degre" id="scd" ><i class="fa fa-snowflake-o fa-1x blueinfo"></i></h3>';
                                                }
                                                break;
                                        case 5:
                                                echo '<h3 class="degre" id="scd" ><img src="images/hvac_fan_30.png" border="0"></h3>';
                                                break;
                                        case 6:
						if ($hvac_relays_state & 0b100) { $system_controller_colour = "red"; } else { $system_controller_colour = "blue"; }
                                                echo '<h3 class="degre" id="scd" ><i class="ionicons ion-flame fa-1x '.$system_controller_colour.'"></i></h3>';
                                                break;
                                        case 7:
                                                if ($hvac_relays_state & 0b010) { $system_controller_colour = "blueinfo"; } else { $system_controller_colour = ""; }
                                                echo '<h3 class="degre" id="scd" ><i class="fa fa-snowflake-o fa-1x '.$system_controller_colour.'"></i></h3>';
                                                break;
					default:
                                                echo '<h3 class="degre" id="scd" ><i class="fa fa-circle-o-notch"></i></h3>';
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
                                echo '<h3 class="degre" id="scd" ><i class="ionicons ion-flame fa-1x '.$system_controller_colour.'"></i></h3>';
			}

			if($system_controller_fault=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="statuszoon" id="scs"><i class="fa ion-android-cancel fa-1x red"></i> </small>';}
			elseif($hysteresis=='1') {echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 70px;" class="statuszoon" id="scs"><i class="fa fa-hourglass fa-1x orange"></i> </small>';}
			else { echo'<h3 class="status"><small class="statusdegree"></small><small style="margin-left: 48px;" class="statuszoon" id="scs"></small>';}
			echo '</h3></button>';
		}
		// end if system controller button

		// Temperature Sensors Post System Controller
		$query = "SELECT sensors.id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id,nodes.node_id, nodes.last_seen, nodes.notice_interval FROM sensors, nodes WHERE (nodes.id = sensors.sensor_id) AND sensors.zone_id = 0 AND sensors.show_it = 1 AND sensors.pre_post = 0 order by index_id asc;";
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
                        //query to get temperature from messages_in_view_24h table view
                        $query = "SELECT * FROM messages_in WHERE node_id = '{$node_id}' AND child_id = '{$sensor_child_id}' ORDER BY id desc LIMIT 1;";
                        $result = $conn->query($query);
                        $sensor = mysqli_fetch_array($result);
                        $sensor_c = $sensor['payload'];
                        $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor_id.",0";
   			echo '<button class="btn btn-default btn-circle '.$button_style.' mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="'.$ajax_modal.'">
                        <h3><small>'.$sensor_name.'</small></h3>';
                        if ($sensor_type_id == 3) {
                                if ($sensor_c == 0) { echo '<h3 class="degre" id="sd_'.$sensor_id.'">OFF</h3>'; } else { echo '<h3 class="degre" id="sd_'.$sensor_id.'">ON</h3>'; }
			} else {
				$unit = SensorUnits($conn,$sensor_type_id);
        	                echo '<h3 class="degre" id="sd_'.$sensor_id.'">'.number_format(DispSensor($conn,$sensor_c,$sensor_type_id),1).$unit.'</h3>';
			}
                        echo '<h3 class="status">
                        <small class="statuscircle" id="ss1_'.$sensor_id.'"><i class="fa fa-circle fa-fw '.$shcolor.'"></i></small>
                        </h3></button>';      //close out status and button
			$sensor_params[] = array('sensor_id' =>$row['id'], 'sensor_name' =>$row['name']);
 		}
		$js_sensor_params = json_encode($sensor_params);

                // Add-On buttons
                $query = "SELECT `zone`.`id`, `zone`.`name`, `zone_type`.`type`, `zone_type`.`category` FROM `zone`, `zone_type` WHERE (`zone`.`type_id` = `zone_type`.`id`) AND (`zone_type`.`category` = 1 OR `zone_type`.`category` = 2) ORDER BY `zone`.`index_id` ASC;";

                $results = $conn->query($query);
                while ($row = mysqli_fetch_assoc($results)) {
                        //get the schedule status for this zone
			$zone_id = $row['id'];
                        $zone_name=$row['name'];
                        $zone_type=$row['type'];
                        $zone_category=$row['category'];

                        //get the sensor id
                        $query = "SELECT * FROM sensors WHERE zone_id = '{$zone_id}' LIMIT 1;";
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

			//get the current zone schedule status
			$rval=get_schedule_status($conn, $zone_id,$holidays_status,$away_status);
                        $sch_status = $rval['sch_status'];
                        $away_sch = $rval['away_sch'];

                        //query to get zone current state
                        $query = "SELECT * FROM zone_current_state WHERE zone_id =  '{$row['id']}' LIMIT 1;";
                        $result = $conn->query($query);
                        $zone_current_state = mysqli_fetch_array($result);
                        if ($zone_current_state['mode'] == 0) { $add_on_active = 0; } else { $add_on_active = 1; }

                        if ($add_on_active == 1){$add_on_colour = "green";} elseif ($add_on_active == 0){$add_on_colour = "black";}
                        if ($zone_category == 2) {
				echo '<a href="javascript:update_add_on('.$row['id'].');">
                        	<button type="button" class="btn btn-default btn-circle '.$button_style.' mainbtn">';
			} else {
	   			echo '<button class="btn btn-default btn-circle '.$button_style.' mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#'.$zone_type.''.$zone_id.'" data-backdrop="static" data-keyboard="false">';
			}
                        echo '<h3 class="buttontop"><small>'.$row['name'].'</small></h3>';
                        if (($zone_category == 1 && $sensor_type_id != 3)) {
                                $unit = SensorUnits($conn,$sensor_type_id);
                                echo '<h3 class="degre" id="zd_'.$zone_id.'">'.number_format(DispSensor($conn,$zone_c,$sensor_type_id),1).$unit.'</h3>';
                        } elseif ($zone_category == 1 && $sensor_type_id == 3) {
				if ($add_on_active == 0) { echo '<h3 class="degre" id="zd_'.$zone_id.'">OFF</h3>'; } else { echo '<h3 class="degre" id="zd_'.$zone_id.'">ON</h3>'; }
			} else {
                        	echo '<h3 class="degre" id="zd_'.$zone_id.'"><i class="fa fa-power-off fa-1x '.$add_on_colour.'"></i></h3>';
			}
                        echo '<h3 class="status">';

                        if ($sch_status =='1') {
                                $add_on_mode = $zone_current_state['mode'];
                        } else {
                                if ($add_on_active == 0) { $add_on_mode = 0; } else { $add_on_mode = 114; }
                        }

			if ($away_status == 1 && $away_sch == 1 ) { $zone_mode = 90; }
                        $rval=getIndicators($conn, $add_on_mode, $zone_temp_target);
                        //Left small circular icon/color status
                        echo '<small class="statuscircle" id="zs1_'.$zone_id.'"><i class="fa fa-circle fa-fw ' . $rval['status'] . '"></i></small>';
                        //Middle target temp
                        echo '<small class="statusdegree" id="zs2_'.$zone_id.'">' . $rval['target'] .'</small>';
                        //Right icon for what/why
                        echo '<small class="statuszoon" id="zs3_'.$zone_id.'"><i class="fa ' . $rval['shactive'] . ' ' . $rval['shcolor'] . ' fa-fw"></i></small>';
                        echo '</h3></button></a>';      //close out status and button
                        $zone_params[] = array('zone_id' =>$row['id'], 'zone_name' =>$row['name'], 'zone_category' =>$row['category']);
                } // end of zones while loop
                $js_zone_params = json_encode($zone_params);

                echo '<input type="hidden" id="sch_active" name="sch_active" value="'.$sch_status.'"/>';

		//select addional onetouch buttons
		$button_params = [];
                $query = "SELECT * FROM button_page WHERE page = 1 ORDER BY index_id ASC";
                $results = $conn->query($query);
                if (mysqli_num_rows($results) > 0) {
                        while ($row = mysqli_fetch_assoc($results)) {
                                $var = $row['function'];
                                $var($conn, $lang[$var]);
				if ($row['page'] == 1) { $button_params[] = array('button_id' =>$row['id'], 'button_name' =>$row['name']); }
                        }
			$js_button_params = json_encode($button_params);
                }
		?>
		</div>
                <!-- /.panel-body -->
		<!-- Generic Ajax Modal -->
        	<div class="modal fade" id="ajaxModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                	<div class="modal-dialog">
                        	<div class="modal-content" id="ajaxModalContent">
                                	<div class="modal-header">
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
                <div class="panel-footer">
                        <div class="btn-group" id="footer_weather">
                                <?php
                                ShowWeather($conn);
                                ?>
                        </div>

                       	<div class="pull-right">
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
					if($system_controller_time_on >0){	echo ' <i class="ionicons ion-ios-clock-outline"></i> '.secondsToWords(($system_controller_time_on)*60);}
					?>
                        	</div>
                 	</div>
		</div>
		<!-- /.panel-footer -->
	</div>
	<!-- /.panel-primary -->
<?php if(isset($conn)) { $conn->close();} ?>
<script language="javascript" type="text/javascript">

// update the heating zone temperature every 1 second
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';

  (function loop() {
    var data = '<?php echo $js_zone_params ?>';
    var obj = JSON.parse(data)
    //console.log(obj.length);

    for (var i = 0; i < obj.length; i++) {
      if (obj[i].zone_category == 0 || obj[i].zone_category == 3 || obj[i].zone_category == 4) {
      	$('#zd_' + obj[i].zone_id).load("fetch_homelist.php?zone_id=" + obj[i].zone_id + "&type=1").fadeIn("slow");
      } else {
        $('#zd_' + obj[i].zone_id).load("fetch_homelist.php?zone_id=" + obj[i].zone_id + "&type=8").fadeIn("slow");
      }
      $('#zs1_' + obj[i].zone_id).load("fetch_homelist.php?zone_id=" + obj[i].zone_id + "&type=2").fadeIn("slow");
      $('#zs2_' + obj[i].zone_id).load("fetch_homelist.php?zone_id=" + obj[i].zone_id + "&type=3").fadeIn("slow");
      $('#zs3_' + obj[i].zone_id).load("fetch_homelist.php?zone_id=" + obj[i].zone_id + "&type=4").fadeIn("slow");
      $('#zs4_' + obj[i].zone_id).load("fetch_homelist.php?zone_id=" + obj[i].zone_id + "&type=5").fadeIn("slow");
//     console.log(obj[i].zone_id + ", " + obj[i].zone_category);
    }
    var data1 = '<?php echo $js_sensor_params ?>';
    var obj1 = JSON.parse(data1)
    //console.log(obj.length);

    for (var x = 0; x < obj1.length; x++) {
      $('#sd_' + obj1[x].sensor_id).load("fetch_homelist.php?sensor_id=" + obj1[x].sensor_id + "&type=6").fadeIn("slow");
      $('#ss1_' + obj1[x].sensor_id).load("fetch_homelist.php?sensor_id=" + obj1[x].sensor_id + "&type=7").fadeIn("slow");
//     console.log(obj1[i].sensor_id);
      //load() method fetch data from fetch.php page
    }
    $('#scd').load("fetch_homelist.php?zone_id=0&type=9").fadeIn("slow");
    $('#scs').load("fetch_homelist.php?zone_id=0&type=10").fadeIn("slow");

    var data2 = '<?php echo $js_button_params ?>';
    var obj2 = JSON.parse(data2)
//  console.log(obj.length);

    for (var y = 0; y < obj2.length; y++) {
      $('#bs1_' + obj2[y].button_id).load("fetch_homelist.php?button_id=" + obj2[y].button_id + "&type=11").fadeIn("slow");
      $('#bs2_' + obj2[y].button_id).load("fetch_homelist.php?button_id=" + obj2[y].button_id + "&type=12").fadeIn("slow");
//     console.log(obj2[y].button_id);
      //load() method fetch data from fetch.php page
    }

    $('#homelist_date').load("fetch_homelist.php?zone_id=0&type=13").fadeIn("slow");
    $('#footer_weather').load("fetch_homelist.php?zone_id=0&type=14").fadeIn("slow");
    $('#footer_running_time').load("fetch_homelist.php?zone_id=0&type=15").fadeIn("slow");
    setTimeout(loop, delay);
  })();
});
</script>
