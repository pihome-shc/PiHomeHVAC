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
require_once(__DIR__ . '/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__ . '/st_inc/connection.php');
require_once(__DIR__ . '/st_inc/functions.php');

if(isset($_GET['id'])) {
        $settings_id = $_GET['id'];
}

$theme = settings($conn, 'theme');

if ($settings_id == 1) {
	//query to frost protection temperature
	$fcolor = "blue";
	if(settings($conn, 'language') == "sk" || settings($conn, 'language') == "de") { $button_style = "btn-xxl-wide"; } else { $button_style = "btn-xxl"; }

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
}
?>

<div class="container-fluid ps-0 pe-0">
	<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
		<div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
				<div class="btn-group">
					<i class="bi bi-gear-fill"></i>&nbsp;
					<?php
					switch ($settings_id) {
  						case 1:
							echo $lang['system_status'];
    							break;
					        case 2:
        		                	        echo $lang['system_maintenance'];
					                break;
                        			case 3:
			        		        echo $lang['system_configuration'];
                        	        		break;
					        case 4:
        		                	        echo $lang['system_controller_configuration'];
					                break;
                        			case 5:
			        		        echo $lang['node_zone_configuration'];
                        	        		break;
					        case 6:
        		                	        echo $lang['device_configuration'];
					                break;
  						default:
			    				echo "";
					}
					?>
				</div>
				<div class="btn-group" id="settings_date"><?php echo date("H:i"); ?></div>
			</div>
		</div>
		<!-- /.card-header -->
		<div class="card-body">
        		<div class="row <?php echo theme($conn, settings($conn, 'theme'), 'row_justification'); ?>">
					<?php if ($settings_id == 1) { ?> 
	       		        	       	<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#show_frost">
				                <h3 class="buttontop"><small><?php echo $lang['frost']; ?> </small></h3>
                			       	<h3 class="degre" ><i class="bi bi-snow blue"></i></h3>
				                <h3 class="status">
        	        		       	<small class="statuscircle" id="frost_status"><i class="bi bi-circle-fill <?php echo $fcolor; ?>" style="font-size: 0.55rem;"></i></small>
				                <small class="statuszoon"><i class="fa"></i></small></h3>
        	        			</button>

					        <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#wifi_setup">
        		        	        <h3 class="buttontop"><small><?php echo $lang['wifi']; ?></small></h3>
				               	<h3 class="degre" ><i class="bi bi-reception-4 green" style"font-size:1.5rem;"></i></h3>
	                		       	<h3 class="status"></small></h3>
					        </button>

				                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#eth_setup">
        	        		        <h3 class="buttontop"><small><?php echo $lang['ethernet']; ?></small></h3>
				        	<h3 class="degre" ><i class="bi bi-ethernet" style="font-size: 1.5rem;"></i></h3>
		        		        <h3 class="status"></small></h3>
			        	        </button>

			                        <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#status_job">
                        			<h3 class="buttontop"><small><?php echo $lang['job_status']; ?></small></h3>
				        	<h3 class="degre" ><i class="bi bi-clock-history blue" style="font-size: 1.5rem;"></i></h3>
	        			       	<h3 class="status"></small></h3>
				                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#sc_z_logs">
                                                <h3 class="buttontop"><small><?php echo $lang['sc_zone_logs']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-table" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#zones_states">
                                                <h3 class="buttontop"><small><?php echo $lang['zone_state']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi  bi-columns-gap orange" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style;?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#status_scripts">
                                                <h3 class="buttontop"><small><?php echo $lang['scripts_status']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-activity red" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style;?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#status_sensors">
				                <h3 class="buttontop"><small><?php echo $lang['sensors']; ?></small></h3>
                        		       	<h3 class="degre" ><i class="bi bi-thermometer-half blue" style="font-size: 1.5rem;"></i></h3>
					        <h3 class="status"></small></h3>
        	        		        </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#status_relays">
                                                <h3 class="buttontop"><small><?php echo $lang['relays']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-shuffle" style="font-size:1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style;?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#s_uptime">
	        				<h3 class="buttontop"><small><?php echo $lang['update_etc']; ?></small></h3>
						<h3 class="degre" ><i class="bi bi-clock red" style="font-size: 1.5rem;"></i></h3>
	       					<h3 class="status"></small></h3>
			        	       	</button>

                				<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#os_version">
        	        	        	<h3 class="buttontop"><small><?php echo $lang['os_version']; ?></small></h3>
	       					<h3 class="degre" ><h3 class="degre" ><img src="images/linux.svg" style="margin-top: -5px;" width="25" height="25" alt=""></h3></h3>
		        		        <h3 class="status"></small></h3>
	       					</button>

		        	      		<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#maxair_versions">
        				       	<h3 class="buttontop"><small><?php echo $lang['maxair_versions']; ?></small></h3>
				        	<h3 class="degre" ><i class="bi bi-collection blueinfo" style="font-size: 1.5rem;"></i></h3>
	        				<h3 class="status"></small></h3>
		       				</button>

	        	                	<?php
		                       	        $max_cpu_temp = settings($conn, 'max_cpu_temp');
				        	$query = "select * from messages_in where node_id = 0 ORDER BY id DESC LIMIT 1";
		        			$result = $conn->query($query);
        					$result = mysqli_fetch_array($result);
		        			$system_cc = $result['payload'];
	        			       	if ($system_cc < $max_cpu_temp - 10){$system_cc="#0bb71b";}elseif ($system_cc < $max_cpu_temp){$system_cc="#F0AD4E";}elseif ($system_cc > $max_cpu_temp){$system_cc="#ff0000";}
		        	        	?>
                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#cpu_temp_history">
			               		<h3 class="buttontop"><small><?php echo $lang['system']; ?> &deg;</small></h3>
	        			        <h3 class="degre" style="margin-top:12px;"><i class="bi bi-cpu-fill" style="font-size: 1.5rem;"></i></h3>
						<div id="cpu_status">
							<h3 class="status">
        						<small class="statuscircle" style="color:<?php echo $system_cc;?>"><i class="bi bi-circle-fill" style="font-size: 0.55rem;"></i></small>
				                	<small class="statusdegree"><?php echo number_format(DispTemp($conn,$result['payload']),0);?>&deg;</small>
							<?php if ($result['payload'] > $max_cpu_temp){
						               	echo '<small class="statuszoon"><i class="spinner-grow text-danger" role="status" style="width: 0.7rem; height: 0.7rem;"></i></small></h3>';
							} ?>
						</div>
				                </button>

                			   	<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#big_thanks">
		                		<h3 class="buttontop"><small><?php echo $lang['big_thanks']; ?></small></h3>
	        		        	<h3 class="degre" ><i class="bi bi-life-preserver blueinfo" style="font-size:1.5rem;"></i></h3>
		       	               		<h3 class="status"></small></h3>
				               	</button>

						<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#last_sw_install">
        			                <h3 class="buttontop"><small><?php echo $lang['software_install']; ?></small></h3>
		        	                <h3 class="degre" ><i class="bi bi-terminal-fill green" style="font-size: 1.5rem;"></i></h3>
                			        <h3 class="status"></small></h3>
		              	        	</button>

	        				<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#documentation">
		                        	<h3 class="buttontop"><small><?php echo $lang['documentation']; ?></small></h3>
	        		                <h3 class="degre" ><i class="bi bi-file-earmark-pdf" style="font-size=1.5rem;"></i></h3>
		                       	        <h3 class="status"></small></h3>
                		        	</button>
					<?php } ?>
	        		        <?php if ($settings_id == 3) { ?>
		                               	<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#change_system_mode">
                		                <h3 class="buttontop"><small><?php echo $lang['system_mode']; ?></small></h3>
		                                <?php 
						if (settings($conn, 'mode') &0b1 == 1) {
							echo '<h3 class="degre" >'.$lang['hvac'].'</h3>'; 
						} else {
                                                	echo '<h3 class="degre" ><i class="bi bi-fire red" style="font-size=1.5rem;"></i></h3>';
						}
						?>
		                                <h3 class="status"></small></h3>
                		                </button>

						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-bs-toggle="modal" data-bs-target="#jobs_schedule">
                		                <h3 class="buttontop"><small><?php echo $lang['jobs']; ?></small></h3>
		       		        	<h3 class="degre" ><i class="bi bi-clock-history blue" style="font-size=1.5rem;"></i></h3>
        				        <h3 class="status"></small></h3>
		               		        </button>

						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#add_theme">
		                                <h3 class="buttontop"><small><?php echo $lang['theme']; ?></small></h3>
        			                <h3 class="degre" style="margin-top: 12px;"><i class="bi bi-rainbow orange" style="font-size: 2rem;"></i></h3>
		                                <h3 class="status"></small></h3>
                		                </button>

						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#network_setting">
                                                <h3 class="buttontop"><small><?php echo $lang['network']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-diagram-3 blue" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

					        <?php
					        $c_f = settings($conn, 'c_f');
	        				if($c_f==1 || $c_f=='1')
        						$TUnit='F';
                	     			else
       			        			$TUnit='C';
				                ?>
        				        <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#change_units">
		                		<h3 class="buttontop"><small><?php echo $lang['units']; ?></small></h3>
				        	<h3 class="degre" ><?php echo $TUnit;?></h3>
	       			                <h3 class="status"></small></h3>
					        </button>

	         		        	<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#language">
			        		<h3 class="buttontop"><small><?php echo $lang['language']; ?></small></h3>
	       		                	<h3 class="degre" ><i class="bi bi-translate blueinfo" style="font-size: 1.5rem;"></i></h3>
					        <h3 class="status"></small></h3>
			                	</button>

				                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-bs-toggle="modal" data-bs-target="#time_zone">
	        			        <h3 class="buttontop"><small><?php echo $lang['time_zone']; ?></small></h3>
				        	<h3 class="degre" ><i class="bi bi-globe green" style="font-size: 1.5rem;"></i></h3>
	       			                <h3 class="status"></small></h3>
        			         	</button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-bs-toggle="modal" data-bs-target="#schedule_test">
                                                <h3 class="buttontop"><small><?php echo $lang['schedule_test']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-calendar-date-fill" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-bs-toggle="modal" data-bs-target="#modal_openweather">
        					<h3 class="buttontop"><small><?php echo $lang['openweather']; ?></small></h3>
					        <h3 class="degre" ><i class="bi bi-cloud-sun" style="font-size: 1.5rem;"></i></h3>
			        		<h3 class="status"></small></h3>
					        </button>

                        			<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#display_graphs">
			        	        <h3 class="buttontop"><small><?php echo $lang['enable_graphs']; ?></small></h3>
                        			<h3 class="degre" ><i class="bi bi-graph-up blueinfo" style="font-size: 1.5rem;"></i></h3>
			                        <h3 class="status"></small></h3>
                        			</button>

					        <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#zone_graph">
	        				<h3 class="buttontop"><small><?php echo $lang['select']; ?></small></h3>
					        <h3 class="degre" ><i class="bi bi-graph-up-arrow" style="font-size: 1.5rem;"></i></h3>
			        		<h3 class="status"></small></h3>
				        	</button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#archive_graphs">
                                                <h3 class="buttontop"><small><?php echo $lang['archive_graphs']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-graph-down red" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

	                			<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#email_setting">
						<h3 class="buttontop"><small><?php echo $lang['email']; ?></small></h3>
        					<h3 class="degre" ><i class="bi bi-envelope blueinfo" style="font-size: 1.5rem;"></i></h3>
				        	<h3 class="status"></small></h3>
	        				</button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#mqtt_connection">
			        		<h3 class="buttontop"><small><?php echo $lang['mqtt']; ?></small></h3>
                                                <h3 class="degre" ><img src="images/mqtt_32.png" class="colorize-purple" style="margin-top: -5px" width="25" height="25" alt=""></h3>
						<h3 class="status"></small></h3>
						</button>

						<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#set_buttons">
                                        	<h3 class="buttontop"><small><?php echo $lang['set_buttons']; ?></small></h3>
			                        <h3 class="degre" ><i class="bi bi-grid-fill orange" style="font-size: 1.5rem;"></i></h3>
                        			<h3 class="status"></small></h3>
				                </button>

						<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#sensor_limits">
                	        		<h3 class="buttontop"><small><?php echo $lang['sensor_limits']; ?></small></h3>
				                <h3 class="degre" ><i class="bi bi-thermometer-half green" style="font-size: 1.5rem;"></i></h3>
                        			<h3 class="status"></small></h3>
			                	</button>

						<?php if ($c_f == 0) { $icon = 'thermostat_30_C.png'; } else { $icon = 'thermostat_30_F.png'; } ?>
                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#livetemp_zone">
                                		<h3 class="buttontop"><small><?php echo $lang['live_temp']; ?></small></h3>
                                		<h3 class="degre" style="margin-top:5px;"><img src="images/<?php echo $icon; ?>" border="0"></h3>
                                		<h3 class="status"><small class="statuscircle"><i class="bi bi-circle-fill '.$lt_status.'" style="font-size: 0.55rem;"></i></small></h3>
                                                </button>
                        		<?php } ?>
			                <?php if ($settings_id == 2) { ?>
                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#theme">
                                                <h3 class="buttontop"><small><?php echo $lang['theme']; ?></small></h3>
                                                <h3 class="degre" style="margin-top: 12px;"><i class="bi bi-rainbow orange" style="font-size: 2rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#show_services">
			        		<h3 class="buttontop"><small><?php echo $lang['services']; ?></small></h3>
	                		        <h3 class="degre" ><i class="bi bi-gear-wide-connected" style="font-size: 1.5rem;"></i></h3>
					        <h3 class="status"></small></h3>
					        </button>

				               	<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#set_repository">
                        		        <h3 class="buttontop"><small><?php echo $lang['repository']; ?></small></h3>
			                        <h3 class="degre" ><i class="bi bi-github" style="font-size: 1.5rem;"></i></h3>
                        		        <h3 class="status"></small></h3>
			                        </button>

						<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#maxair_update">
				                <h3 class="buttontop"><small><?php echo $lang['maxair_update']; ?></small></h3>
        	                	        <h3 class="degre" ><i class="bi bi-cloud-download blueinfo" style="font-size: 1.5rem;"></i></h3>
				                <h3 class="status"></small></h3>
                        		        </button>

					       	<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#backup_image">
					        <h3 class="buttontop"><small><?php echo $lang['backup']; ?></small></h3>
						<h3 class="degre" ><h3 class="degre" ><img src="images/backup_database.svg" class="colorize-blue" style="margin-top: -5px;" width="25" height="25" alt=""></h3></h3>
        					<h3 class="status"></small></h3>
				                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#auto_backup">
                                                <h3 class="buttontop"><small><?php echo $lang['auto_backup']; ?></small></h3>
						<h3 class="degre" ><h3 class="degre" ><img src="images/backup_auto.svg" style="margin-top: -5px;" width="35" height="35" alt=""></h3></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#backup_restore">
                                                <h3 class="buttontop"><small><?php echo $lang['restore_db']; ?></small></h3>
						<h3 class="degre" ><h3 class="degre" ><img src="images/backup_database.svg" class="colorize-green" style="margin-top: -5px;" width="25" height="25" alt=""></h3></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

                                                <?php $query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'maxair' AND table_name = 'auto_image';";
                                                        $result = $conn->query($query);
                                                        if (mysqli_num_rows($result) != 0) { ?>
								<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#auto_image">
        	                                        	<h3 class="buttontop"><small><?php echo $lang['auto_image']; ?></small></h3>
                	                                	<h3 class="degre" ><i class="bi bi-image red" style="font-size: 1.5rem;"></i></h3>
                        	                        	<h3 class="status"></small></h3>
                                	                	</button>
							<?php } ?>

	        	        		<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#user_setup">
				                <h3 class="buttontop"><small><?php echo $lang['user_accounts']; ?></small></h3>
						<h3 class="degre" ><i class="bi bi-person-fill blue" style="font-size: 1.5rem;"></i></h3>
				        	<h3 class="status"></small></h3>
	        				</button>

						<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#sw_install">
                        			<h3 class="buttontop"><small><?php echo $lang['software_install']; ?></small></h3>
			                        <h3 class="degre" ><i class="bi bi-terminal-fill" style="font-size: 1.5rem;"></i></h3>
                        			<h3 class="status"></small></h3>
				                </button>

        	                		<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" udata-href="#" data-bs-toggle="modal" data-bs-target="#db_cleanup">
				                <h3 class="buttontop"><small><?php echo $lang['db_cleanup']; ?></small></h3>
                        			<h3 class="degre" ><i class="bi bi-server orange" style="font-size: 1.5rem;"></i></h3>
			        	        <h3 class="status"></small></h3>
                        			</button>

        					<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" udata-href="#" data-bs-toggle="modal" data-bs-target="#max_cpu_temp">
       			        	        <h3 class="buttontop"><small><?php echo $lang['max_cpu_temp']; ?></small></h3>
                                        	<h3 class="degre" ><i class="bi bi-thermometer-half red" style="font-size: 1.5rem;"></i></h3>
               			                <h3 class="status"></small></h3>
	                                        </button>

						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#change_refresh">
	                                        <h3 class="buttontop"><small><?php echo $lang['page_refresh']; ?></small></h3>
       				                <h3 class="degre" ><i class="bi bi-arrow-repeat" style="font-size: 1.5rem;"></i></h3>
                	                        <h3 class="status"></small></h3>
       				                </button>
                                	<?php } ?>
               			        <?php if ($settings_id == 4) { ?>
						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_system_controller.php" data-bs-toggle="modal" data-bs-target="#system_controller">
        			                <h3 class="buttontop"><small><?php echo $lang['controller']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-motherboard red" style="font-size: 1.5rem;"></i></h3>
       						<h3 class="status"></small></h3>
	        	        		</button>

       				                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-bs-toggle="modal" data-bs-target="#boost_setup">
			        	       	<h3 class="buttontop"><small><?php echo $lang['boost']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-rocket-takeoff" style="font-size: 1.5rem;"></i></h3>
	        		                <h3 class="status"></small></h3>
					        </button>

		                		<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_override.php" data-bs-toggle="modal" data-bs-target="#override_setup">
        			        	<h3 class="buttontop"><small><?php echo $lang['override']; ?></small></h3>
				                <h3 class="degre" ><i class="bi bi-arrow-repeat blue" style="font-size: 1.5rem;"></i></h3>
						<h3 class="status"></small></h3>
			                        </button>
       				        <?php } ?>
                	                <?php if ($settings_id == 5) { ?>
       				        	<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-bs-toggle="modal" data-bs-target="#zone_setup">
        		        	        <h3 class="buttontop"><small><?php echo $lang['zone']; ?></small></h3>
				        	<h3 class="degre" style="margin-top:12px;"><i class="bi bi-columns-gap orange" style="font-size: 1.5rem;"></i> </h3>
		                		<h3 class="status"></small></h3>
        			                </button>

	                                	<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_override.php" data-bs-toggle="modal" data-bs-target="#zone_types">
       				                <h3 class="buttontop"><small><?php echo $lang['zone_type']; ?></small></h3>
        	                                <h3 class="degre" style="margin-top:12px;"><i class="bi bi-list-ol orange" style="font-size: 2rem;"></i></h3>
       				                <h3 class="status"></small></h3>
                        	                </button>

			        		<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_override.php" data-bs-toggle="modal" data-bs-target="#nodes">
        	        			<h3 class="buttontop"><small><?php echo $lang['nodes']; ?></small></h3>
       				                <h3 class="degre" style="margin-top:12px;"><i class="bi bi-diagram-3-fill green" style="font-size: 2rem;"></i></h3>
					        <h3 class="status"></small></h3>
        			        	</button>

	                                        <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#node_alerts">
       				                <h3 class="buttontop"><small><?php echo $lang['node_alerts']; ?></small></h3>
                	                        <h3 class="degre" ><i class="bi bi-bell blueinfo" style="font-size:1.5rem;"></i></h3>
       				                <h3 class="status"></small></h3>
                                	        </button>

						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#sensor_gateway">
	                                        <h3 class="buttontop"><small><?php echo $lang['gateway']; ?></small></h3>
               			                <h3 class="degre" ><i class="bi bi-share-fill red" style="font-size:1.5rem;"></i></h3>
	                                        <h3 class="status"></small></h3>
       				                </button>
        	                        <?php } ?>
					<?php if ($settings_id == 6) { ?>
	                                     	<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#relay_setup">
        			                <h3 class="buttontop"><small><?php echo $lang['relays']; ?></small></h3>
	                                        <h3 class="degre" ><i class="bi bi-shuffle" style="font-size:1.5rem;"></i></h3>
       				                <h3 class="status"></small></h3>
                	                        </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#test_relays">
                                                <h3 class="buttontop"><small><?php echo $lang['test_relays']; ?></small></h3>
                                                <h3 class="degre" ><i class="bi bi-toggles green" style="font-size:1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

       				                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#sensor_setup">
                                	        <h3 class="buttontop"><small><?php echo $lang['sensors']; ?></small></h3>
       			                	<h3 class="degre" ><i class="bi bi-thermometer-half red" style="font-size: 1.5rem;"></i></h3>
	                                        <h3 class="status"></small></h3>
               			                </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#sensor_messages">
                                                <h3 class="buttontop"><small><?php echo $lang['sensor_message']; ?></small></h3>
                                                <h3 class="degre" ><i class="b1 bi-card-text red" style="font-size: 1.5rem;"></i></h3>
                                                <h3 class="status"></small></h3>
                                                </button>

	                                        <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#sensor_types">
       				                <h3 class="buttontop"><small><?php echo $lang['sensor_type']; ?></small></h3>
        	                                <h3 class="degre" ><i class="b1 bi-list-ol red" style="font-size: 2rem;"></i></h3>
       				                <h3 class="status"></small></h3>
                        	                </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#hide_sensor_relay">
                                                <h3 class="buttontop"><small><?php echo $lang['hide']; ?></small></h3>
                                                <h3 class="degre" ><i class="b1 bi-window-dash" style="font-size: 1.5rem;"></i></h3>
                                                 <h3 class="status"></small></h3>
                                                </button>

       			        	        <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#add_on_http">
                                        	<h3 class="buttontop"><small><?php echo $lang['add_on']; ?></small></h3>
               			                <h3 class="degre" ><?php echo $lang['add_on_http']; ?></h3>
	                                        <h3 class="status"></small></h3>
        			                </button>

						<button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#mqtt_devices">
       				                <h3 class="buttontop"><small><?php echo $lang['mqtt_device']; ?></small></h3>
                                                <h3 class="degre" ><img src="images/mqtt_32.png" class="colorize-purple" style="margin-top: -5px" width="25" height="25" alt=""></h3>
       				                <h3 class="status"></small></h3>
                                	        </button>

                                                <button class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-bs-toggle="modal" data-bs-target="#ebus_commands">
                                                <h3 class="buttontop"><small><?php echo $lang['ebus_commands']; ?></small></h3>
                                                <h3 class="degre" ><?php echo $lang['ebus']; ?></h3>
                                                <h3 class="status"></small></h3>
                                                </button>
               			        <?php } ?>
        		</div>
         		<!-- /.row -->

			<?php
			$model_num = $settings_id;
			include("model.php");
			?>

			<div class="row <?php echo theme($conn, settings($conn, 'theme'), 'row_justification'); ?>">
					<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#reboot_system">
			        	<h3 class="buttontop"><small><?php echo $lang['reboot_pi']; ?></small></h3>
				        <h3 class="degre" ><i class="bi bi-bootstrap-reboot orange" style="font-size: 1.5rem;"></i></h3>
                			<h3 class="status"></small></h3>
			       		</button>

		                	<button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-bs-toggle="modal" data-bs-target="#shutdown_system">
				        <h3 class="buttontop"><small><?php echo $lang['shutdown_pi']; ?></small></h3>
                	        	<h3 class="degre" ><i class="bi bi-toggle2-off red" style="font-size: 1.5rem;"></i></h3>
				       	<h3 class="status"></small></h3>
		        		</button>
			</div>
			<!-- /.row -->
		</div>
		<!-- /.card-body -->

		<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
			       	<div class="btn-group" id="footer_weather">
        	        		<?php ShowWeather($conn); ?>
			        </div>
	                        <div class="btn-group" id="footer_all_running_time">
		       			<?php
                			echo '<i class="bi bi-clock"></i>&nbspAll Schedule:&nbsp' . secondsToWords((array_sum($schedule_time) * 60));
		       			?>
                		</div>
		         </div>
		</div>
		<!-- /.card-footer -->
	</div>
	<!-- /.card -->
		</div>
		<!-- /.col-lg-12 -->
	</div>
	 <!-- /.row -->
</div>
 <!-- /.container -->

<?php if (isset($conn)) {
    $conn->close();
} ?>
