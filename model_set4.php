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

//System Controller settings
echo '
<div class="modal fade" id="system_controller" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['system_controller_settings'].'</h5>
                		<div class="dropdown pull-right">
                        		<a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                		<i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        		</a>
                        		<ul class="dropdown-menu">
                        		<li><a href="pdf_download.php?file=setup_guide_system_controller.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_system_controller'].'</a></li>
                        		</ul>
                		</div>
            		</div>
            		<div class="modal-body">';
				$query = "SELECT * FROM nodes where name = 'System Controller' OR name = 'GPIO Controller' OR name = 'I2C Controller';";
				$result = $conn->query($query);
				$ncount=mysqli_num_rows($result);
				if ($ncount > 0){
					$query = "SELECT * FROM system_controller;";
					$bresult = $conn->query($query);
					$bcount = $bresult->num_rows;
					if ($bcount > 0) { $brow = mysqli_fetch_array($bresult); }
					echo '<p class="text-muted">'.$lang['system_controller_info_text'].'</p>';

					echo '
					<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

					<div class="form-group" class="control-label">
						<div class="checkbox checkbox-default checkbox-circle">';
							if ($bcount > 0) {
								if ($bresult and $brow['status'] == '1'){
									echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" checked Disabled>';
								}else {
									echo '<input id="checkbox2" class="styled" type="checkbox" value="1" name="status" Disabled>';
								}
							} else {
								echo '<input id="checkbox2" class="styled" type="checkbox" value="0" name="status" Enabled>';
							}
							echo '<label for="checkbox2"> '.$lang['system_controller_enable'].'</label>
						</div>
					</div>
					<!-- /.form-group -->

					<div class="form-group" class="control-label"><label>'.$lang['system_controller_name'].'</label>
						<input class="form-control input-sm" type="text" id="name" name="name" value="'.$brow['name'].'" placeholder="System Controller Name to Display on Screen ">
						<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->

                        		<div class="form-group" class="control-label"><label>'.$lang['heat_relay_id'].'</label> <small class="text-muted">'.$lang['heat_relay_id_info'].'</small>
                                                <select class="form-control input-sm" type="text" id="heat_relay_id" name="heat_relay_id" >';
                                                //get list of heat relays to display
                                                if ((settings($conn, 'mode') & 0b1) == 0) { $heat_relay_type = 1; } else { $heat_relay_type = 2; }
                                                $query = "SELECT id, name FROM relays WHERE type = {$heat_relay_type};";
                                                $result = $conn->query($query);
                                                if ($result){
                                                        while ($nrow=mysqli_fetch_array($result)) {
								echo '<option value="'.$nrow['id'].'" ' . ($nrow['id']==$brow['heat_relay_id'] ? 'selected' : '') . '>'.$nrow['name'].'</option>';
                                                        }
                                                }
                                                echo '</select>
                                                <div class="help-block with-errors">
                                                </div>
                                        </div>
					<!-- /.form-group -->';

					if ((settings($conn, 'mode') & 0b1) == 1) {
                                        echo '<div class="form-group" class="control-label"><label>'.$lang['cool_relay_id'].'</label> <small class="text-muted">'.$lang['cool_relay_id_info'].'</small>
                                                <select class="form-control input-sm" type="text" id="cool_relay_id" name="cool_relay_id" >';
                                                //get list of heat relays to display
                                                $query = "SELECT id, name FROM relays WHERE type = 3;";
                                                $result = $conn->query($query);
                                                if ($result){
                                                        while ($nrow=mysqli_fetch_array($result)) {
                                                        	echo '<option value="'.$nrow['id'].'" ' . ($nrow['id']==$brow['cool_relay_id'] ? 'selected' : '') . '>'.$nrow['name'].'</option>';
                                                        }
                                                }
                                                echo '</select>
                                                <div class="help-block with-errors">
                                                </div>
                                        </div>
                                        <!-- /.form-group -->

                                        <div class="form-group" class="control-label"><label>'.$lang['fan_relay_id'].'</label> <small class="text-muted">'.$lang['fan_relay_id_info'].'</small>
                                                <select class="form-control input-sm" type="text" id="fan_relay_id" name="fan_relay_id" >';
                                                //get list of heat relays to display
                                                $query = "SELECT id, name FROM relays WHERE type = 4;";
                                                $result = $conn->query($query);
                                                if ($result){
                                                        while ($nrow=mysqli_fetch_array($result)) {
                                                        	echo '<option value="'.$nrow['id'].'" ' . ($nrow['id']==$brow['fan_relay_id'] ? 'selected' : '') . '>'.$nrow['name'].'</option>';
                                                        }
                                                }
                                                echo '</select>
                                                <div class="help-block with-errors">
                                                </div>
                                        </div>
                                        <!-- /.form-group -->';
					}

					echo '<div class="form-group" class="control-label"><label>'.$lang['system_controller_hysteresis_time'].'</label> <small class="text-muted">'.$lang['system_controller_hysteresis_time_info'].'</small>
						<select class="form-control input-sm" type="text" id="hysteresis_time" name="hysteresis_time">
						<option selected>'.$brow['hysteresis_time'].'</option>
						<option value="0">0</option>
						<option value="1">1</option>
						<option value="2">2</option>
						<option value="3">3</option>
						<option value="4">4</option>
						<option value="5">5</option>
						<option value="6">6</option>
						<option value="7">7</option>
						<option value="8">8</option>
						<option value="9">9</option>
						<option value="10">10</option>
						<option value="15">15</option>
						</select>
					    	<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->

					<div class="form-group" class="control-label"><label>'.$lang['max_operation_time'].'</label> <small class="text-muted">'.$lang['max_operation_time_info'].'</small>
						<select class="form-control input-sm" type="text" id="max_operation_time" name="max_operation_time">
						<option selected>'.$brow['max_operation_time'].'</option>
                                                <option value="0">0</option>
						<option value="30">30</option>
						<option value="40">40</option>
						<option value="45">45</option>
						<option value="50">50</option>
						<option value="55">55</option>
						<option value="60">60</option>
						<option value="65">65</option>
						<option value="70">70</option>
						<option value="80">80</option>
						<option value="85">85</option>
						<option value="90">90</option>
						<option value="95">95</option>
						<option value="100">100</option>
						<option value="110">110</option>
						<option value="120">120</option>
						<option value="180">180</option>
						</select>
	    					<div class="help-block with-errors">
						</div>
					</div>
                                        <!-- /.form-group -->
					';

                                        if ((settings($conn, 'mode') & 0b1) == 0) {
						echo '<div class="form-group" class="control-label"><label>'.$lang['system_controller_overrun'].'</label> <small class="text-muted">'.$lang['system_controller_overrun_info'].'</small>
							<select class="form-control input-sm" type="text" id="overrun" name="overrun">
							<option selected>'.$brow['overrun'].'</option>
							<option value="-1">Keep valve open until next System Controller start</option>
							<option value="0">Disable</option>
							<option value="1">1</option>
							<option value="2">2</option>
							<option value="3">3</option>
							<option value="4">4</option>
							<option value="5">5</option>
							<option value="6">6</option>
							<option value="7">7</option>
							<option value="8">8</option>
							<option value="9">9</option>
							</select>
	    						<div class="help-block with-errors">
							</div>
						</div>
						<!-- /.form-group -->
                        			<div class="form-group" class="control-label"><label>'.$lang['weather_factoring'].'</label> <small class="text-muted">'.$lang['weather_factoring_info'].'</small>
                                                        <select class="form-control input-sm" type="text" id="weather_factoring" name="weather_factoring">
                                        			<option value="0" ' . ($brow['weather_factoring'] == 0 || $brow['weather_factoring'] == '0' ? 'selected' : '') . '>'.$lang['disabled'].'</option>
                                        			<option value="1" ' . ($brow['weather_factoring'] == 1 || $brow['weather_factoring'] == '1' ? 'selected' : '') . '>'.$lang['enabled'].'</option>
                                                        </select>
                                                        <div class="help-block with-errors">
                                                        </div>
                                                </div>
                                                <!-- /.form-group -->
                                		<div class="form-group" class="control-label"><label>'.$lang['weather_sensor'].'</label> <small class="text-muted">'.$lang['weather_sensor_info'].'</small>
                                                	<select class="form-control input-sm" type="text" id="weather_sensor_id" name="weather_sensor_id" >
                                				<option value="0" ' . ($brow['weather_sensor_id'] == 0 || $brow['weather_sensor_id'] == '0' ? 'selected' : '') . '>'.$lang['openweather'].'</option>';
                                                		//get list of sensors to display
                                                		$query = "SELECT id, name FROM sensors WHERE sensor_type_id = 1;";
                                                		$result = $conn->query($query);
                                                		if ($result){
                                                        		while ($srow=mysqli_fetch_array($result)) {
                                                				echo '<option value="'.$srow['id'].'" ' . ($srow['id']==$brow['weather_sensor_id'] ? 'selected' : '') . '>'.$srow['name'].'</option>';
                                                        		}
                                                		}
                                                		echo '</select>
                                                		<div class="help-block with-errors">
                                                		</div>
                                        		</div>
                                        		<!-- /.form-group -->
						';
					}
				} else {
					echo '<p class="text-muted">'.$lang['system_controller_no_nodes'].'</p>';
				}
			echo '</div>
			<!-- /.modal-body -->
        	   	<div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>';
				if ($ncount > 0) { echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="system_controller_settings('.(settings($conn, 'mode') & 0b1).')">'; }

            		echo '</div>
			<!-- /.modal-footer -->
        	</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal fade -->
';

//boost model
echo '
<div class="modal fade" id="boost_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['boost_settings'].'</h5>
            </div>
            <div class="modal-body">';
if ((settings($conn, 'mode') & 0b1) == 0) {
	echo '<p class="text-muted"> '.$lang['boost_settings_text'].' </p>';
} else {
        echo '<p class="text-muted"> '.$lang['hvac_boost_settings_text'].' </p>';
}

if ((settings($conn, 'mode') & 0b1) == 0) {
	$query = "SELECT boost.id, boost.`status`, boost.sync, boost.zone_id, zone_idx.index_id, zone_type.category, zone.name, 
        boost.temperature, boost.minute, boost_button_id, boost_button_child_id, hvac_mode, ts.sensor_type_id
        FROM boost
        JOIN zone ON boost.zone_id = zone.id
        JOIN zone zone_idx ON boost.zone_id = zone_idx.id
        JOIN zone_type ON zone_type.id = zone.type_id
        JOIN zone_sensors zs ON zone.id = zs.zone_id
        JOIN sensors ts ON zs.zone_sensor_id = ts.id
	ORDER BY index_id ASC, minute ASC;";
	$results = $conn->query($query);
	echo '<table class="table table-bordered">
	    <tr>
        	<th class="col-xs-4"><small>'.$lang['zone'].'</small></th>
        	<th class="col-xs-2"><small>'.$lang['boost_time'].'</small></th>
        	<th class="col-xs-2"><small>'.$lang['boost_temp'].'</small></th>
        	<th class="col-xs-2"><small>'.$lang['boost_console_id'].'</small></th>
        	<th class="col-xs-1"><small>'.$lang['boost_button_child_id'].'</small></th>
        	<th class="col-xs-1"></th>
    	</tr>';
} else {
        $query = "SELECT boost.*, ts.sensor_type_id 
	FROM boost
	JOIN zone_sensors zs ON boost.zone_id = zs.zone_id
        JOIN sensors ts ON zs.zone_sensor_id = ts.id
	ORDER BY hvac_mode ASC;";
        $results = $conn->query($query);
        echo '<table class="table table-bordered">
            <tr>
                <th class="col-xs-2"><small>'.$lang['hvac_function'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['boost_time'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['boost_temp'].'</small></th>
                <th class="col-xs-1"></th>
        </tr>';
}

while ($row = mysqli_fetch_assoc($results)) {
    $query = "SELECT * FROM boost WHERE zone_id = {$row['zone_id']};";
    $result = $conn->query($query);
    $b_count=mysqli_num_rows($result);
    if ($b_count > 1) { $disabled = ""; } else { $disabled = "disabled"; }
    $minute = $row["minute"];
    if ((settings($conn, 'mode') & 0b1) == 0) {
    	$boost_button_id = $row["boost_button_id"];
    	$boost_button_child_id = $row["boost_button_child_id"];
    	echo '
            <tr>
            	<th scope="row"><small>'.$row['name'].'</small></th>
            	<td><input id="minute'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="minute" size="3" value="'.$minute.'" placeholder="Minutes" required></td>';
	    	if($row["category"] < 2) {
            		echo '<td><input id="temperature'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>
            		<td><input id="boost_button_id'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="button_id"  size="3" value="'.$boost_button_id.'" placeholder="Button ID" required></td>
            		<td><input id="boost_button_child_id'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="button_child_id" size="3" value="'.$boost_button_child_id.'" placeholder="Child ID" required></td>';
	    	} else {
            		echo '<td><input id="temperature'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>
            		<td><input id="boost_button_id'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="button_id"  size="3" value="'.$boost_button_id.'" placeholder="Button ID" required></td>
            		<td><input id="boost_button_child_id'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="button_child_id" size="3" value="'.$boost_button_child_id.'" placeholder="Child ID" required></td>';
	    	}
	     	echo '<input type="hidden" id="zone_id'.$row["id"].'" name="zone_id" value="'.$row["zone_id"].'">
		<input type="hidden" id="hvac_mode'.$row["id"].'" name="hvac_mode" value="'.$row["hvac_mode"].'">
                <input type="hidden" id="sensor_type'.$row["id"].'" name="sensor_type" value="'.$row["sensor_type_id"].'">
                <td>';
                if ($b_count > 1) { echo '<a href="javascript:delete_boost('.$row["id"].');">'; }
                echo '<button class="btn btn-danger btn-xs '.$disabled.'" ';
                if ($b_count > 1) { echo 'data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="You are about to DELETE this BOOST Setting"'; }
                echo '><span class="glyphicon glyphicon-trash"></span></button> </a></td>
            </tr>';
    } else {
	$hvac_mode = $row['hvac_mode'];
	if ($hvac_mode == 3) {
		$mode = "FAN";
	} elseif ($hvac_mode ==4) {
                $mode = "HEAT";
	} else {
                $mode = "COOL";
	}
    	echo '
            <tr>
            	<th scope="row"><small>'.$mode.'</small></th>
            	<td><input id="minute'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="minute" size="3" value="'.$minute.'" placeholder="Minutes" required></td>';
	    	if($hvac_mode > 3) {
            		echo '<td><input id="temperature'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>';
	    	} else {
            		echo '<td><input id="temperature'.$row["id"].'" type="hidden" class="pull-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>';
	    	}
	     	echo '<input type="hidden" id="zone_id'.$row["id"].'" name="zone_id" value="'.$row["zone_id"].'">
		<input type="hidden" id="hvac_mode'.$row["id"].'" name="hvac_mode" value="'.$row["hvac_mode"].'">
                <input type="hidden" id="sensor_type'.$row["id"].'" name="sensor_type" value="'.$row["sensor_type_id"].'">
                <td>';
                if ($b_count > 1) { echo '<a href="javascript:delete_boost('.$row["id"].');">'; }
                echo '<button class="btn btn-danger btn-xs '.$disabled.'" ';
                if ($b_count > 1) { echo 'data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="You are about to DELETE this BOOST Setting"'; }
                echo '><span class="glyphicon glyphicon-trash"></span></button> </a></td>
            </tr>';
    }
}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_boost()">
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_boost">'.$lang['add_boost'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Boost
if ((settings($conn, 'mode') & 0b1) == 0) { $info_text = $lang['boost_info_text']; $zone_hvac = $lang['zone']; } else { $info_text = $lang['hvac_boost_info_text']; $zone_hvac = $lang['hvac_function']; }
echo '
<div class="modal fade" id="add_boost" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_boost'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$info_text.'</p>
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

	<div class="form-group" class="control-label"><label>'.$zone_hvac.'</label> 
	<select class="form-control input-sm" type="text" id="zone_id" name="zone_id">';
	if ((settings($conn, 'mode') & 0b1) == 0) {
		//Get Zone List
		$query = "SELECT * FROM zone where status = 1;";
		$result = $conn->query($query);
		if ($result){
			while ($zrow=mysqli_fetch_array($result)) {
				echo '<option value="'.$zrow['id'].'">'.$zrow['name'].'</option>';
			}
		}
	} else {
	        echo '<option value=3>FAN</option>
        	<option value=4>HEAT</option>
        	<option value=5>COOL</option>';
	}
	echo '
	</select>
    <div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['boost_temperature'].'</label> <small class="text-muted">'.$lang['boost_temperature_info'].'</small>
	<select class="form-control input-sm" type="text" id="boost_temperature" name="boost_temperature">
	<option value="20">20</option>
	<option value="21">22</option>
	<option value="23">23</option>
	<option value="24">24</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="35">35</option>
	<option value="40">40</option>
	<option value="45">45</option>
	<option value="50">50</option>
	<option value="55">55</option>
	<option value="60">60</option>
	<option value="65">65</option>
	<option value="70">70</option>
	<option value="75">75</option>
	<option value="80">80</option>
	<option value="85">85</option>
	<option value="90">90</option>
	<option value="95">95</option>
	</select>
    <div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['boost_time'].'</label> <small class="text-muted">'.$lang['boost_time_info'].'</small>
	<select class="form-control input-sm" type="text" id="boost_time" name="boost_time">
	<option value="20">20</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="35">35</option>
	<option value="40">40</option>
	<option value="45">45</option>
	<option value="50">50</option>
	<option value="55">55</option>
	<option value="60">60</option>
	<option value="65">65</option>
	<option value="70">70</option>
	<option value="80">80</option>
	<option value="85">85</option>
	<option value="90">90</option>
	<option value="95">95</option>
	<option value="100">100</option>
	<option value="110">110</option>
	<option value="120">120</option>
	</select>
    <div class="help-block with-errors"></div></div>';
	if ((settings($conn, 'mode') & 0b1) == 0) {
		echo '<div class="form-group" class="control-label"><label>'.$lang['boost_console_id'].'</label> <small class="text-muted">'.$lang['boost_console_id_info'].'</small>
		<select class="form-control input-sm" type="text" id="boost_console_id" name="boost_console_id">';
		//get list from nodes table to display 
		$query = "SELECT * FROM nodes where name = 'Button Console' OR name = 'Boost Controller' AND node_id!=0;";
		$result = $conn->query($query);
		if ($result){
			while ($nrow=mysqli_fetch_array($result)) {
				echo '<option value="'.$nrow['node_id'].'">'.$nrow['node_id'].'</option>';
			}
		}
		echo '<option value="0">N/A</option>
		</select>
	    <div class="help-block with-errors"></div></div>

		<div class="form-group" class="control-label"><label>'.$lang['boost_button_child_id'].'</label> <small class="text-muted">'.$lang['boost_button_child_id_info'].'</small>
		<select class="form-control input-sm" type="text" id="boost_button_child_id" name="boost_button_child_id">
		<option value="0">N/A</option>
		<option value="1">1</option>
		<option value="2">2</option>
		<option value="3">3</option>
		<option value="4">4</option>
		<option value="5">5</option>
		<option value="6">6</option>
		<option value="7">7</option>
		<option value="8">8</option>
		</select>
    	  <div class="help-block with-errors"></div></div>	';
	}
echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>';
				if ((settings($conn, 'mode') & 0b1) == 0) {
					echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_boost(0)">';
				} else {
                                        echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_boost(1)">';
				}

            echo '</div>
        </div>
    </div>
</div>';

//override model
echo '
<div class="modal fade" id="override_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['override_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_override.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_override'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['override_settings_text'].'</p>';
if ((settings($conn, 'mode') & 0b1) == 0) { //boiler mode
	$query = "SELECT override.`status`, override.sync, override.purge, override.zone_id, zone_idx.index_id, zone_type.category, zone.name,
	override.time, override.temperature, override.hvac_mode, ts.sensor_type_id
	FROM override
	JOIN zone ON override.zone_id = zone.id
	JOIN zone zone_idx ON override.zone_id = zone_idx.id
	JOIN zone_type ON zone_type.id = zone.type_id
        JOIN zone_sensors zs ON zone.id = zs.zone_id
        JOIN sensors ts ON zs.zone_sensor_id = ts.id
	WHERE category < 2
        ORDER BY index_id ASC;";
} else {
        $query = "SELECT override.*, ts.sensor_type_id
        FROM override
        JOIN zone_sensors zs ON override.zone_id = zs.zone_id
        JOIN sensors ts ON zs.zone_sensor_id = ts.id
        ORDER BY hvac_mode ASC;";
}
$results = $conn->query($query);
echo '	<div class=\"list-group\">';
while ($row = mysqli_fetch_assoc($results)) {
	if ((settings($conn, 'mode') & 0b1) == 0) {
		$name = $row['name'];
	} else {
		if ($row['hvac_mode']  == 4) { $name = 'HEAT'; } else { $name = 'COOL'; };
	}
	echo "<a href=\"#\" class=\"list-group-item\">
	<i class=\"fa fa-refresh fa-1x blue\"></i> ".$name." 
    <span class=\"pull-right text-muted small\"><em>".DispSensor($conn,$row["temperature"],$row["sensor_type_id"])."&deg; </em></span></a>";
}
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';
?>

<script>
$(document).ready(function(){
$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>
