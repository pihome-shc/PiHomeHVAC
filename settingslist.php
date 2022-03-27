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

if ($settings_id <= 3) {
	echo "
	<script language='javascript' type='text/javascript'>
	$('#ajaxModal').on('show.bs.modal', function(e) {
	    //console.log($(e.relatedTarget).data('ajax'));
	    $(this).find('#ajaxModalLabel').html('...');
	    $(this).find('#ajaxModalBody').html('Waiting ...');
	    $(this).find('#ajaxModalFooter').html('...');
	    $(this).find('#ajaxModalContent').load($(e.relatedTarget).data('ajax'));
	});
	</script>";
}
?>

<div class="panel panel-primary">
	<div class="panel-heading">
		<i class="fa fa-cog fa-fw"></i>
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
		<div class="pull-right">
			<div class="btn-group"><?php echo date("H:i"); ?>
			</div>
		</div>
        </div>
        <!-- /.panel-heading -->
	<div class="panel-group">
        	<div class="panel-body">
			<div class="panel">
				<?php if ($settings_id == 1) { ?> 
	       	                	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#show_frost">
        	        	        <h3 class="buttontop"><small><?php echo $lang['frost']; ?> </small></h3>
                	        	<h3 class="degre" ><i class="ionicons ion-ios-snowy blue"></i></h3>
	                	        <h3 class="status">
        	                	<small class="statuscircle"><i class="fa fa-circle fa-fw <?php echo $fcolor; ?>"></i></small>
	                	        <small class="statuszoon"><i class="fa"></i></small></h3>
        	                	</button>

	        	                <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#wifi_setup">
        	        	        <h3 class="buttontop"><small><?php echo $lang['wifi']; ?></small></h3>
                	        	<h3 class="degre" ><i class="fa fa-signal green"></i></h3>
	                        	<h3 class="status"></small></h3>
		                        </button>

        		                <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#eth_setup">
                		        <h3 class="buttontop"><small><?php echo $lang['ethernet']; ?></small></h3>
                        		<h3 class="degre" ><i class="ionicons ion-network orange"></i></h3>
		                        <h3 class="status"></small></h3>
        		                </button>

                		        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#status_job">
                        		<h3 class="buttontop"><small><?php echo $lang['job_status']; ?></small></h3>
	                        	<h3 class="degre" ><i class="ionicons ion-ios-timer-outline blue"></i></h3>
	        	                <h3 class="status"></small></h3>
        	        	        </button>

                	                <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Sensors">
                        	        <h3 class="buttontop"><small><?php echo $lang['sensors']; ?></small></h3>
                                	<h3 class="degre" ><i class="ionicons ion-thermometer red"></i></h3>
	                                <h3 class="status"></small></h3>
        	                        </button>

        			        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Uptime">
	        		        <h3 class="buttontop"><small><?php echo $lang['update_etc']; ?></small></h3>
					<h3 class="degre" ><i class="ionicons ion-clock red"></i></h3>
	       				<h3 class="status"></small></h3>
        	       			</button>

                	               	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#os_version">
        	        	        <h3 class="buttontop"><small><?php echo $lang['os_version']; ?></small></h3>
       		        		<h3 class="degre" ><i class="fa fa-linux"></i></h3>
	        		        <h3 class="status"></small></h3>
		        		</button>

	        	      		<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#maxair_versions">
        	        	       	<h3 class="buttontop"><small><?php echo $lang['maxair_versions']; ?></small></h3>
			        	<h3 class="degre" ><i class="fa fa-code-fork fa-1x blueinfo"></i></h3>
	               			<h3 class="status"></small></h3>
        	       			</button>

	        	                <?php
                        	        $max_cpu_temp = settings($conn, 'max_cpu_temp');
			        	$query = "select * from messages_in where node_id = 0 limit 1";
	        			$result = $conn->query($query);
        	       			$result = mysqli_fetch_array($result);
                			$system_cc = $result['payload'];
	                	       	if ($system_cc < $max_cpu_temp - 10){$system_cc="#0bb71b"; $fan=" ";}elseif ($system_cc < $max_cpu_temp){$system_cc="#F0AD4E"; $fan="fa-pulse";}elseif ($system_cc > $max_cpu_temp){$system_cc="#ff0000"; $fan="fa-pulse";}
	       	                	?>
		                	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-remote="false"  data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_System">
        		               	<h3 class="buttontop"><small><?php echo $lang['system']; ?> &deg;</small></h3>
        			        <h3 class="degre" ><i class="fa fa-server fa-1x green"></i></h3>
					<h3 class="status">
        				<small class="statuscircle" style="color:<?php echo $system_cc;?>"><i class="fa fa-circle fa-fw"></i></small>
		                	<small class="statusdegree"><?php echo number_format(DispTemp($conn,$result['payload']),0);?>&deg;</small>
		                       	<small class="statuszoon"><i class="fa fa-asterisk <?php echo $fan;?>"></i></small></h3>
        		                </button>

                        	   	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#big_thanks">
        	                	<h3 class="buttontop"><small><?php echo $lang['big_thanks']; ?></small></h3>
	               		        <h3 class="degre" ><i class="ionicons ion-help-buoy blueinfo"></i></h3>
        	               		<h3 class="status"></small></h3>
	        	               	</button>

					<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#last_sw_install">
        	                        <h3 class="buttontop"><small><?php echo $lang['software_install']; ?></small></h3>
                	                <h3 class="degre" ><i class="fa fa-terminal"></i></h3>
                        	        <h3 class="status"></small></h3>
                                	</button>

	               			<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#documentation">
        	                        <h3 class="buttontop"><small><?php echo $lang['documentation']; ?></small></h3>
                	                <h3 class="degre" ><i class="fa fa-file"></i></h3>
                        	        <h3 class="status"></small></h3>
                                	</button>
				<?php } ?>
                                <?php if ($settings_id == 3) { ?>
                                	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#change_system_mode">
                                        <h3 class="buttontop"><small><?php echo $lang['system_mode']; ?></small></h3>
                                        <?php 
					if (settings($conn, 'mode') &0b1 == 1) {
						echo '<h3 class="degre" >'.$lang['hvac'].'</h3>'; 
					} else {
						echo '<h3 class="degre" ><i class="ionicons ion-flame fa-1x red"></i></h3>';
					}
					?>
                                        <h3 class="status"></small></h3>
                                        </button>

					<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#jobs_schedule">
                                        <h3 class="buttontop"><small><?php echo $lang['jobs']; ?></small></h3>
	        	        	<h3 class="degre" ><i class="ionicons ion-ios-timer-outline blue"></i></h3>
        	        	        <h3 class="status"></small></h3>
	                	        </button>

                                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#network_setting">
                                        <h3 class="buttontop"><small><?php echo $lang['network']; ?></small></h3>
                                        <h3 class="degre" ><i class="ionicons ion-network blue"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

				        <?php
				        $c_f = settings($conn, 'c_f');
	        			if($c_f==1 || $c_f=='1')
        	        			$TUnit='F';
                	     		else
	                        		$TUnit='C';
			                ?>
        			        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#change_units">
	                		<h3 class="buttontop"><small><?php echo $lang['units']; ?></small></h3>
		                	<h3 class="degre" ><?php echo $TUnit;?></h3>
        		                <h3 class="status"></small></h3>
	        		        </button>

	         	        	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#language">
		                	<h3 class="buttontop"><small><?php echo $lang['language']; ?></small></h3>
        		                <h3 class="degre" ><i class="fa fa-language fa-1x blueinfo"></i></h3>
                		        <h3 class="status"></small></h3>
		                	</button>

			                <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#time_zone">
        			        <h3 class="buttontop"><small><?php echo $lang['time_zone']; ?></small></h3>
		                	<h3 class="degre" ><i class="fa fa-globe green"></i></h3>
        		                <h3 class="status"></small></h3>
                		         </button>

		        	        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_OpenWeather">
        		        	<h3 class="buttontop"><small><?php echo $lang['openweather']; ?></small></h3>
	                		<h3 class="degre" ><i class="fa fa-sun-o"></i></h3>
		                	<h3 class="status"></small></h3>
	        		        </button>

                                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#display_graphs">
                                        <h3 class="buttontop"><small><?php echo $lang['enable_graphs']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-bar-chart fa-1x blueinfo"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

		                	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#zone_graph">
        		                <h3 class="buttontop"><small><?php echo $lang['select']; ?></small></h3>
	                		<h3 class="degre" ><i class="fa fa-bar-chart fa-1x"></i></h3>
		                	<h3 class="status"></small></h3>
        		                </button>

	                		<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#email_setting">
			                <h3 class="buttontop"><small><?php echo $lang['email']; ?></small></h3>
        			        <h3 class="degre" ><i class="fa fa-envelope blueinfo"></i></h3>
	        			<h3 class="status"></small></h3>
	        		        </button>

	        	        	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_MQTT">
		        	        <h3 class="buttontop"><small><?php echo $lang['mqtt']; ?></small></h3>
                	        	<h3 class="degre" ><?php echo $lang['mqtt']; ?></h3>
				        <h3 class="status"></small></h3>
			                </button>

					<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#set_buttons">
                                        <h3 class="buttontop"><small><?php echo $lang['set_buttons']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-th-large orange"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

					<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#sensor_limits">
                                        <h3 class="buttontop"><small><?php echo $lang['sensor_limits']; ?></small></h3>
                                        <h3 class="degre" ><i class="ionicons ion-thermometer green"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>
                                <?php } ?>
                                <?php if ($settings_id == 2) { ?>
		        		<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-remote="false" data-target="#ajaxModal" data-ajax="ajax.php?Ajax=GetModal_Services">
        		        	<h3 class="buttontop"><small><?php echo $lang['services']; ?></small></h3>
                		        <h3 class="degre" ><i class="ionicons ion-ios-cog-outline"></i></h3>
		                        <h3 class="status"></small></h3>
			                </button>

                                 	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#set_repository">
                                        <h3 class="buttontop"><small><?php echo $lang['repository']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-github"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

					<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#maxair_update">
                                        <h3 class="buttontop"><small><?php echo $lang['maxair_update']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-download fa-1x blueinfo"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

	                        	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#backup_image">
			                <h3 class="buttontop"><small><?php echo $lang['backup']; ?></small></h3>
				        <h3 class="degre" ><i class="fa fa-clone fa-1x blue"></i> </h3>
        				<h3 class="status"></small></h3>
	                		</button>

        	                	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#user_setup">
                	                <h3 class="buttontop"><small><?php echo $lang['user_accounts']; ?></small></h3>
			        	<h3 class="degre" ><i class="ionicons ion-person blue"></i></h3>
        			        <h3 class="status"></small></h3>
	        	        	</button>

					<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#sw_install">
                                        <h3 class="buttontop"><small><?php echo $lang['software_install']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-terminal"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

                                        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" udata-href="#" data-toggle="modal" data-target="#db_cleanup">
                                        <h3 class="buttontop"><small><?php echo $lang['db_cleanup']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-database orange"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

        				<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" udata-href="#" data-toggle="modal" data-target="#max_cpu_temp">
                                        <h3 class="buttontop"><small><?php echo $lang['max_cpu_temp']; ?></small></h3>
                                        <h3 class="degre" ><i class="ionicons ion-thermometer red"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>
                                <?php } ?>
                                <?php if ($settings_id == 4) { ?>
					<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_system_controller.php" data-toggle="modal" data-target="#system_controller">
                                        <h3 class="buttontop"><small><?php echo $lang['controller']; ?></small></h3>
                                        <h3 class="degre" ><?php echo "SC"; ?></h3>
		        		<h3 class="status"></small></h3>
	                		</button>

        	                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#boost_setup">
			               	<h3 class="buttontop"><small><?php echo $lang['boost']; ?></small></h3>
		                	<h3 class="degre" ><i class="fa fa-rocket fa-1x blueinfo"></i></h3>
        		                <h3 class="status"></small></h3>
				        </button>

	                		<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#override_setup">
        	                	<h3 class="buttontop"><small><?php echo $lang['override']; ?></small></h3>
			                <h3 class="degre" ><i class="fa fa-refresh fa-1x blue"></i></h3>
                			<h3 class="status"></small></h3>
		                        </button>
                                <?php } ?>
                                <?php if ($settings_id == 5) { ?>
		                	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_gpio.php" data-toggle="modal" data-target="#zone_setup">
        		                <h3 class="buttontop"><small><?php echo $lang['zone']; ?></small></h3>
				        <h3 class="degre" ><i class="glyphicon glyphicon-th-large orange"></i> </h3>
	                		<h3 class="status"></small></h3>
        	                        </button>

                                	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#zone_types">
                                        <h3 class="buttontop"><small><?php echo $lang['zone_type']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-list-ol orange"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

			        	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="edit_override.php" data-toggle="modal" data-target="#nodes">
        	        		<h3 class="buttontop"><small><?php echo $lang['nodes']; ?></small></h3>
                	                <h3 class="degre" ><i class="fa fa-sitemap fa-1x green"></i></h3>
				        <h3 class="status"></small></h3>
		                	</button>

                                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#node_alerts">
                                        <h3 class="buttontop"><small><?php echo $lang['node_alerts']; ?></small></h3>
                                        <h3 class="degre" ><i class="ion-android-notifications-none blueinfo"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

                			<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#sensor_gateway">
                                        <h3 class="buttontop"><small><?php echo $lang['gateway']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-heartbeat red"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>
                                <?php } ?>
				<?php if ($settings_id == 6) { ?>
                                     	<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#relay_setup">
                                        <h3 class="buttontop"><small><?php echo $lang['relays']; ?></small></h3>
                                        <h3 class="degre" ><i class="ionicons ion-shuffle"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

                                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#sensor_setup">
                                        <h3 class="buttontop"><small><?php echo $lang['sensors']; ?></small></h3>
                                        <h3 class="degre" ><i class="ionicons ion-thermometer red"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

                                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#sensor_types">
                                        <h3 class="buttontop"><small><?php echo $lang['sensor_type']; ?></small></h3>
                                        <h3 class="degre" ><i class="fa fa-list-ol red"></i></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

                                        <button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#add_on_http">
                                        <h3 class="buttontop"><small><?php echo $lang['add_on']; ?></small></h3>
                                        <h3 class="degre" ><?php echo $lang['add_on_http']; ?></h3>
                                        <h3 class="status"></small></h3>
                                        </button>

					<button class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-href="#" data-toggle="modal" data-target="#mqtt_devices">
                                        <h3 class="buttontop"><small><?php echo $lang['mqtt_device']; ?></small></h3>
					<h3 class="degre" ><?php echo $lang['mqtt']; ?></h3>
                                        <h3 class="status"></small></h3>
                                        </button>
                                <?php } ?>
			</div>
			<!-- /.panel -->

			<?php if ($settings_id <= 3) { ?>
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
			<?php } ?>
		<?php
		$model_num = $settings_id;
		include("model.php");
                ?>

	        </div>
		<!-- /.panel-body -->
		<div class="panel-body">
                       	<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#reboot_system">
	                <h3 class="buttontop"><small><?php echo $lang['reboot_pi']; ?></small></h3>
        	        <h3 class="degre" ><i class="ion-ios-refresh-outline orange"></i></h3>
                        <h3 class="status"></small></h3>
                       	</button>

	                <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn animated fadeIn" data-toggle="modal" data-target="#shutdown_system">
        	        <h3 class="buttontop"><small><?php echo $lang['shutdown_pi']; ?></small></h3>
                        <h3 class="degre" ><i class="fa fa-power-off fa-1x red"></i></h3>
                       	<h3 class="status"></small></h3>
	                </button>
		</div>
                <!-- /.panel-body -->
	</div>
	<!-- /.panel-group -->

        <div class="panel-footer">
        	<?php
            	ShowWeather($conn);
            	?>
            	<div class="pull-right">
                	<div class="btn-group">
                    		<?php
                    		echo '<i class="ionicons ion-ios-clock-outline"></i> All Schedule: ' . secondsToWords((array_sum($schedule_time) * 60));
                    		?>
                	</div>
            	</div>
        </div>
	<!-- /.panel-footer -->
</div>
<!-- /.panel-primary -->
<?php if (isset($conn)) {
    $conn->close();
} ?>
<script>
$(document).ready(function(){
    $('[data-toggle="tooltip"]').tooltip();   
});
</script>
