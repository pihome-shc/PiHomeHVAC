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

//Relay model
echo '<div class="modal fade" id="relay_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['relay_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=configure_relay_devices.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['configure_relay_devices'].'</a></li>
                                <li class="divider"></li>
				<li><a href="pdf_download.php?file=setup_pump_type_relays.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_pump_type_relays'].'</a></li>
                                <li class="divider"></li>
				<li><a href="pdf_download.php?file=delete_zones_relays_sensors_nodes.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['delete_zones_relays_sensors_nodes'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['relay_settings_text'].'</p>';
		$query = "SELECT DISTINCT relays.id, relays.relay_id, relays.relay_child_id, relays.on_trigger, relays.name, relays.type, IF(zr.zone_id IS NULL, 0, 1) AS attached, nd.node_id, nd.last_seen
                FROM relays
                LEFT join zone_relays zr ON relays.id = zr.zone_relay_id
                LEFT JOIN zone z ON zr.zone_id = z.id
                JOIN nodes nd ON relays.relay_id = nd.id
                ORDER BY relay_id asc, relay_child_id ASC;";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
        			<th class="col-sm-3"><small>'.$lang['relay_name'].'</small></th>
        			<th class="col-sm-1"><small>'.$lang['type'].'</small></th>
        			<th class="col-sm-2"><small>'.$lang['node_id'].'</small></th>
        			<th class="col-sm-2"><small>'.$lang['relay_child_id'].'</small></th>
        			<th class="col-sm-2">'.$lang['relay_trigger'].'</th>
                                <th class="col-sm-2"></th>
    			</tr>';

			while ($row = mysqli_fetch_assoc($results)) {
    				switch ($row['type']) {
        				case 0:
                				$relay_type ="Zone";
                                                $attached_to = $row["zone_name"]." Zone";
                				break;
        				case 1:
                				$relay_type ="Boiler";
                                                $attached_to = "System Controller Relay";
                				break;
        				case 2:
                				$relay_type ="HVAC - Heat";
                                                $attached_to = "HVAC Heat Relay";
                				break;
        				case 3:
                				$relay_type ="HVAC - Cool";
                                                $attached_to = "HVAC Cool Relay";
                				break;
        				case 4:
                				$relay_type ="HVAC - Fan";
                                                $attached_to = "HVAC Fan Relay";
                				break;
                                        case 5:
                                                $relay_type ="Pump";
                                                $attached_to = $row["zone_name"]." Zone";
                                                break;
    				}
				if ($row["on_trigger"] == 0) { $trigger = "LOW"; } else { $trigger = "HIGH"; }
    				echo '<tr>
            				<td>'.$row["name"].'<br> <small>('.$row["last_seen"].')</small></td>
            				<td>'.$relay_type.'</td>
            				<td>'.$row["node_id"].'</td>
            				<td>'.$row["relay_child_id"].'</td>
                                        <td>'.$trigger.'</td>
            				<td><a href="relay.php?id='.$row["id"].'"><button class="btn btn-primary btn-xs"><span class="ionicons ion-edit"></span></button> </a>&nbsp;&nbsp';
            				if($row['attached'] == 1 || $row['type'] == 1) {
		 				echo '<button class="btn btn-danger btn-xs disabled" data-toggle="tooltip" title="'.$lang['confirm_del_relay_2'].$attached_to.'"><span class="glyphicon glyphicon-trash"></span></button></td>';
	    				} else {
                				echo '<a href="javascript:delete_relay('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$lang['confirm_del_relay_1'].'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>';
            				}
        			echo '</tr>';
			}
		echo '</table>
	    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <a class="btn btn-default login btn-sm" href="relay.php">'.$lang['relay_add'].'</a>
            </div>
        </div>
    </div>
</div>';

//Sensor model
echo '<div class="modal fade" id="sensor_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=sensor_types.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['sensor_types'].'</a></li>
                                <li class="divider"></li>
                                <li><a href="pdf_download.php?file=humidity_sensors.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['humidity_sensors'].'</a></li>
				<li class="divider"></li>
                                <li><a href="pdf_download.php?file=ds18b20_temperature_sensor.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['ds18b20_temperature_sensor'].'</a></li>
                                <li class="divider"></li>
                                <li><a href="pdf_download.php?file=import_sensor_readings.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['import_sensor_readings'].'</a></li>
                                <li class="divider"></li>
				<li><a href="pdf_download.php?file=delete_zones_relays_sensors_nodes.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['delete_zones_relays_sensors_nodes'].'</a></li>
                                <li class="divider"></li>
				<li><a href="pdf_download.php?file=setup_guide_sensors.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_sensors'].'</a></li>
                                <li class="divider"></li>
				<li><a href="pdf_download.php?file=setup_sensor_notifications.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_sensor_notifications'].'</a></li>
                    	</ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['sensor_settings_text'].'</p>';
		$query = "select sensors.id, sensors.name, sensors.sensor_child_id, sensors.correction_factor, sensors.zone_id, sensors.show_it, nodes.node_id, nodes.last_seen from sensors, nodes WHERE sensors.sensor_id = nodes.id order by name asc";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-md-2"><small>'.$lang['sensor_name'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['node_id'].'</small></th>
                                <th class="col-md-1"><small>'.$lang['sensor_child_id'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['correction_factor'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['zone_name'].'</small></th>
                                <th class="col-md-1"><small>'.$lang['show'].'</small></th>
                                <th class="col-md-2"></th>
    			</tr>';
			while ($row = mysqli_fetch_assoc($results)) {
    				if (!empty($row['zone_id'])) {
					$query = "SELECT  name FROM zone WHERE id = '{$row['zone_id']}' LIMIT 1;";
        				$z_results = $conn->query($query);
        				$rowcount=mysqli_num_rows($z_results);
        				if($rowcount > 0) {
						$z_row = mysqli_fetch_assoc($z_results);
						$zone_name = $z_row['name'];
					} else {
        					$zone_name = "Not Allocated";
    					}
    				} else {
					$zone_name = "Not Allocated";
    				}
    				$check = ($row['show_it'] == 1) ? 'checked' : '';
                                $cf = ($row['correction_factor'] == 0) ? '0' : $row['correction_factor'];
                                echo '<tr>
                                        <td>'.$row["name"].'<br> <small>('.$row["last_seen"].')</small></td>
                                        <td>'.$row["node_id"].'</td>
                                        <td>'.$row["sensor_child_id"].'</td>
                                        <td>'.$cf.'</td>
            				<td>'.$zone_name.'</td>';
            				if (empty($row['zone_id'])) { 
						echo '<td style="text-align:center; vertical-align:middle;">
                				<input type="checkbox" id="checkbox'.$row["id"].'" name="checkbox'.$row["id"].'" value="1" '.$check.'>
            					</td>';
	    				} else {
                				echo '<td style="text-align:center; vertical-align:middle;">
                				<input type="checkbox" id="checkbox'.$row["id"].'" name="checkbox'.$row["id"].'" value="1" '.$check.' disabled>
                				</td>';
	    				}
	    				echo '<td><a href="sensor.php?id='.$row["id"].'"><button class="btn btn-primary btn-xs"><span class="ionicons ion-edit"></span></button> </a>&nbsp;&nbsp';
	    				if (empty($row['zone_id'])) { 
						echo '<a href="javascript:delete_sensor('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$lang['confirm_del_sensor_4'].'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>'; 
	    				} else {
						echo '<button class="btn btn-danger btn-xs disabled" data-toggle="tooltip" title="'.$lang['confirm_del_sensor_5'].$zone_name.'"><span class="glyphicon glyphicon-trash"></span></button></td>';
	    				}
        			echo '</tr>';
			}
		echo '</table>
	    </div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                	<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="show_sensors()">
                	<a class="btn btn-default login btn-sm" href="sensor.php">'.$lang['sensor_add'].'</a>
            </div>
        </div>
    </div>
</div>';

//Sensor Type
echo '
<div class="modal fade" id="sensor_types" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_type'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=sensor_types.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['sensor_types'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['sensor_type_info'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-xs-8"><small>'.$lang['type'].'</small></th>
         <th class="col-xs-3"><small>'.$lang['units_character'].'</small></th>
       <th class="col-xs-1"></th>
    </tr>';

$query = "SELECT * FROM sensor_type where `purge`=0;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
    $query = "SELECT * FROM `sensors` WHERE `sensor_type_id` = '".$row['id']."' LIMIT 1;";
    $t_results = $conn->query($query);
    $rowcount=mysqli_num_rows($t_results);
    if($rowcount > 0) {
        $content_msg=$lang['confirm_del_active_sensor_type'];
    } else {
        $content_msg=$lang['confirm_del_de_active_sensor_type'];
    }

    echo '
        <tr>
            <td>'.$row["type"].'</td>
            <td>'.$row["units"].'</td>
            <td><a href="javascript:delete_sensor_type('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_sensor_type">'.$lang['sensor_type_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Sensor Type
echo '
<div class="modal fade" id="add_sensor_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_type_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['sensor_type_add_info_text'].'</p>
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['sensor_type'].'</label> <small class="text-muted">'.$lang['sensor_type_info'].'</small>
        <input class="form-control input-sm" type="text" id="sensor_type" name="sensor_type" value="" placeholder="'.$lang['sensor_type'].'">
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['units_character'].'</label> <small class="text-muted">'.$lang['units_character_info'].'</small>
        <input class="form-control input-sm" type="text" id="sensor_units" name="sensor_units" value="" placeholder="'.$lang['sensor_units'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_sensor_type()">
            </div>
        </div>
    </div>
</div>';

//Add HTTP Message model
echo '
<div class="modal fade" id="add_on_http" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>';
                $query = "SELECT `name` FROM `zone_view` WHERE `relay_type` = 'Tasmota' ORDER BY `name` ASC;";
                $zresult = $conn->query($query);
                $zcount = $zresult->num_rows;
                $query = "SELECT `node_id` FROM `nodes` WHERE `type` = 'Tasmota' ORDER BY `node_id` ASC;";
                $nresult = $conn->query($query);
                $ncount = $nresult->num_rows;
                if ($zcount + $ncount == 0) {
                        echo '<h5 class="modal-title">'.$lang['no_tasmota'].'</h5>';
                } else {
                        echo '<h5 class="modal-title">'.$lang['add_on_settings'].'</h5>';
                }
                echo '<div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_guide_tasmota_lamp_zone.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_add_on_device'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">';

if ($zcount + $ncount > 0) {
        echo '<p class="text-muted"> '.$lang['add_on_settings_text'].' </p>';

        $query = "SELECT http_messages.*, nodes.type FROM http_messages, nodes WHERE http_messages.node_id = nodes.node_id;";
        $results = $conn->query($query);
        echo '<table class="table table-bordered">
        <tr>
                <th class="col-xs-2"><small>'.$lang['type'].'</small></th>
                <th class="col-xs-3"><small>'.$lang['zone_name'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['message_type'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['command'].'</small></th>
                <th class="col-xs-2"><small>'.$lang['parameter'].'</small></th>
                <th class="col-xs-1"></th>
        </tr>';
        while ($row = mysqli_fetch_assoc($results)) {
                echo '
                        <tr>
                        <td>'.$row["type"].'</td>
                        <td>'.$row["zone_name"].'</td>
                        <td>'.$row["message_type"].'</td>
                        <td>'.$row["command"].'</td>
                        <td>'.$row["parameter"].'</td>
                        <td><a href="javascript:delete_http_msg('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
                        </tr>';
        }
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>';
                if ($zcount > 0) {
			echo '<button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#zone_add_http_msg">'.$lang['zone_add_http_msg'].'</button>';
                }
                if ($ncount > 0) {
			echo '<button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#node_add_http_msg">'.$lang['node_add_http_msg'].'</button>';
                }
            echo '</div>
        </div>
    </div>
</div>';

//Add New HTTP Message based on Zone Name
echo '
<div class="modal fade" id="zone_add_http_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_on_messages'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_on_add_info_text'].'</p>
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
       	<div class="form-group" class="control-label"><label>'.$lang['zone_name'].'</label> <small class="text-muted">'.$lang['add_zone_name_info'].'</small>
        <select class="form-control input-sm" type="text" id="zone_http_id" name="zone_http_id">';
        while ($zrow=mysqli_fetch_array($zresult)) {
        	echo '<option value="'.$zrow['name'].'">'.$zrow['name'].'</option>';
        }
        echo '</select>
    	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['message_type'].'</label> <small class="text-muted">'.$lang['message_type_info'].'</small>
	<select <input class="form-control input-sm" type="text" id="zone_add_msg_type" name="zone_add_msg_type" value="" placeholder="'.$lang['message_type'].'">
	<option selected value="0">0 </option>
        <option value="1">1 </option>
	</select>
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['http_command'].'</label> <small class="text-muted">'.$lang['http_command_info'].'</small>
	<input class="form-control input-sm" type="text" id="zone_http_command" name="zone_http_command" value="" placeholder="'.$lang['http_command'].'">
	<div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['http_parameter'].'</label> <small class="text-muted">'.$lang['http_parameter_info'].'</small>
        <input class="form-control input-sm" type="text" id="zone_http_parameter" name="zone_http_parameter" value="" placeholder="'.$lang['http_parameter'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_zone_http_msg()">
            </div>
        </div>
    </div>
</div>';

//Add New HTTP Message based on Node ID
echo '
<div class="modal fade" id="node_add_http_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_on_messages'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_on_add_info_text'].'</p>
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['node_id'].'</label> <small class="text-muted">'.$lang['add_node_id_info'].'</small>
        <select class="form-control input-sm" type="text" id="node_http_id" name="node_http_id">';
        while ($nrow=mysqli_fetch_array($nresult)) {
                echo '<option value="'.$nrow['node_id'].'">'.$nrow['node_id'].'</option>';
        }
        echo '</select>
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['message_type'].'</label> <small class="text-muted">'.$lang['message_type_info'].'</small>
        <select <input class="form-control input-sm" type="text" id="node_add_msg_type" name="node_add_msg_type" value="" placeholder="'.$lang['message_type'].'">
        <option selected value="0">0 </option>
        <option value="1">1 </option>
        </select>
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['http_command'].'</label> <small class="text-muted">'.$lang['http_command_info'].'</small>
        <input class="form-control input-sm" type="text" id="node_http_command" name="node_http_command" value="" placeholder="'.$lang['http_command'].'">
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['http_parameter'].'</label> <small class="text-muted">'.$lang['http_parameter_info'].'</small>
        <input class="form-control input-sm" type="text" id="node_http_parameter" name="node_http_parameter" value="" placeholder="'.$lang['http_parameter'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_node_http_msg()">
            </div>
        </div>
    </div>
</div>';

//MQTT Devices
echo '<div class="modal fade" id="mqtt_devices" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['mqtt_device'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_guide_mqtt.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_mqtt'].'</a></li>
                                <li class="divider"></li>
                                <li><a href="pdf_download.php?file=setup_zigbee2mqtt.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_zigbee2mqtt'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['mqtt_device_text'].'</p>';
		$query = "SELECT `mqtt_devices`.`id`, `nodes`.`name` AS `node`, `nodes`.`node_id` AS `node_id`, `mqtt_devices`.`child_id` AS `child`, `mqtt_devices`.`name` AS `name`, `mqtt_devices`.`mqtt_topic`, `mqtt_devices`.`on_payload`, `mqtt_devices`.`off_payload`, `mqtt_devices`.`attribute` FROM `mqtt_devices`, `nodes` WHERE `mqtt_devices`.`nodes_id` = `nodes`.`id` ORDER BY `mqtt_devices`.`nodes_id`, `mqtt_devices`.`child_id`;";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-md-2"><small>'.$lang['node'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['mqtt_child_id'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['mqtt_child_name'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['mqtt_topic'].'</small></th>
                                <th class="col-md-1"><small>'.$lang['mqtt_on_payload'].'</small></th>
                                <th class="col-md-1"><small>'.$lang['mqtt_off_payload'].'</small></th>
                                <th class="col-md-2"><small>'.$lang['mqtt_attribute'].'</small></th>
                                <th class="col-md-1"></th>
    			</tr>';
			while ($row = mysqli_fetch_assoc($results)) {
                                echo '<tr>
                                        <td><small>'.$row["node_id"].' - '.$row["node"].'</small></td>
                                        <td><small>'.$row["child"].'</small></td>
                                        <td><small>'.$row["name"].'</small></td>
                                        <td><small>'.$row["mqtt_topic"].'</small></td>
                                        <td><small>'.$row["on_payload"].'</small></td>
            				            <td><small>'.$row["off_payload"].'</small></td>
                                        <td><small>'.$row["attribute"].'</small></td>
	    				<td><a href="mqtt_device.php?id='.$row["id"].'"><button class="btn btn-primary btn-xs"><span class="ionicons ion-edit"></span></button> </a>&nbsp
					<a href="javascript:delete_mqtt_device('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$lang['confirm_del_mqtt_child'].'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        			</tr>';
			}
		echo '</table>
	    </div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                	<a class="btn btn-default login btn-sm" href="mqtt_device.php">'.$lang['mqtt_add_device'].'</a>
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
