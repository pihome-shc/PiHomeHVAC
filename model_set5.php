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

//nodes modal
echo '
<div class="modal fade" id="nodes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_setting'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['node_settings_text'].' </p>';

$query = "SELECT * FROM nodes;";
$results = $conn->query($query);
echo '<table class="table table-bordered">
    <tr>
        <th class="col-xs-2"><small>'.$lang['type'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['node_id'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['max_child'].'</small></th>
        <th class="col-xs-4"><small>'.$lang['name'].'</small></th>
        <th class="col-xs-1"></th>
    </tr>';
while ($row = mysqli_fetch_assoc($results)) {
    if(strpos($row["name"], 'Sensor') !== false) {
        $query = "SELECT name, zone_id FROM sensors WHERE sensor_id = {$row['id']};";
        $s_results = $conn->query($query);
        $scount=mysqli_num_rows($s_results);
        if($scount > 0) {
                $count = 0;
                $zcount = 0;
                while ($s_row = mysqli_fetch_assoc($s_results)) {
                        if ($s_row["zone_id"] != 0) {
                                $query = "SELECT name FROM zone WHERE id = {$s_row['zone_id']} LIMIT 1;";
                                $z_results = $conn->query($query);
                                $z_row = mysqli_fetch_assoc($z_results);
                                if($zcount == 0) {
                                        $content_msg_z=$lang['confirm_del_sensor_3'].$z_row["name"];
                                } else {
                                        $content_msg_z=$content_msg_z." and - ".$z_row["name"];
                                }
                                $zcount = $zcount + 1;
                        } else {
                                if($count == 0) {
                                        $content_msg=$lang['confirm_del_sensor_2'].$s_row["name"];
                                } else {
                                        $content_msg=$content_msg." and - ".$s_row["name"];
                                }
                                $count = $count + 1;
                        }
                }
        } else {
                $content_msg=$lang['confirm_del_sensor_1'];
        }
    } elseif(strpos($row["name"], 'Controller') !== false || strpos($row["name"], 'Relay') !== false) {
        $query = "SELECT id, name, type FROM relays where relay_id = {$row['id']};";
        $r_results = $conn->query($query);
        $rcount=mysqli_num_rows($r_results);
        if($rcount > 0) {
                $count = 0;
		$zcount = 0;
                while ($r_row = mysqli_fetch_assoc($r_results)) {
			switch ($r_row["type"]) {
				case 0:
					$query = "SELECT zone.name FROM zone_controllers, zone where (zone.id = zone_controllers.zone_id) AND zone_relays.zone_relay_id = {$r_row['id']} LIMIT 1;";
					break;
                                case 1:
                                case 2:
                                        $query = "SELECT name FROM system_controller where heat_relay_id = {$r_row['id']} LIMIT 1;";
                                        break;
                                case 3:
                                        $query = "SELECT name FROM system_controller where cool_relay_id = {$r_row['id']} LIMIT 1;";
                                        break;
                                case 4:
                                        $query = "SELECT name FROM system_controller where fan_relay_id = {$r_row['id']} LIMIT 1;";
                                        break;
			}
                        $zc_results = $conn->query($query);
			$zccount=mysqli_num_rows($zc_results);
			if($zccount > 0) {
                        	$zc_row = mysqli_fetch_assoc($zc_results);
                                if($zcount == 0) {
                                        $content_msg_z=$lang['confirm_del_controller_3']." ".$zc_row["name"];
                                } else {
                                        $content_msg_z=$content_msg_z." and - ".$zc_row["name"];
                                }
                                $zcount = $zcount + 1;
			} else {
                        	if($count == 0) {
                                	$content_msg=$lang['confirm_del_controller_2']." ".$r_row["name"];
                        	} else {
                                	$content_msg=$content_msg." and - ".$r_row["name"];
                        	}
                        	$count = $count + 1;
			}
                }
        } else {
                $content_msg=$lang['confirm_del_controller_1'];
        }
    }
    echo '
        <tr>
            <td>'.$row["type"].'</td>
            <td>'.$row["node_id"].'</td>
            <td>'.$row["max_child_id"].'</td>
            <td>'.$row["name"].'</td>';
	    if($zcount != 0) {
		echo '<td><a href="javascript:delete_node('.$row["id"].');"><button class="btn btn-danger btn-xs disabled" data-toggle="tooltip" title="'.$content_msg_z.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>';
	    } else {
		echo '<td><a href="javascript:delete_node('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>';
	    }
        echo '</tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_node">'.$lang['node_add'].'</button>
            </div>
        </div>
    </div>
</div>';


//Add Node
echo '
<div class="modal fade" id="add_node" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['node_add_info_text'].'</p>

	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

	<div class="form-group" class="control-label"><label>'.$lang['node_type'].'</label> <small class="text-muted">'.$lang['node_type_info'].'</small>
	<select class="form-control input-sm" type="text" id="node_type" onchange=show_hide_devices() name="node_type">
	<option value="I2C" selected="selected">I2C</option>
	<option value="GPIO">GPIO</option>
        <option value="Tasmota">Tasmota</option>
        <option value="Dummy">Dummy</option>
        <option value="Switch">Switch</option>
        <option value="MQTT">MQTT</option>
	</select>
    <div class="help-block with-errors"></div></div>


	<div class="form-group" class="control-label"><label>'.$lang['node_id'].'</label> <small class="text-muted">'.$lang['node_id_info'].'</small>
		<input class="form-control input-sm" type="text" id="add_node_id" name="add_node_id" value="" placeholder="'.$lang['node_id'].'">
	<div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label" id="dummy_type_label" style="display:none"><label>'.$lang['node_name'].'</label> <smallclass="text-muted">'.$lang['dummy_name_info'].'</small>
                <select class="form-control input-sm" type="text" id="dummy_type" name="dummy_type">
                        <option value="Sensor" selected="selected">Dummy Sensor</option>
                        <option value="Controller">Dummy Controller</option>
                </select>
                <div class="help-block with-errors"></div>
        </div>

        <div class="form-group" class="control-label" id="mqtt_type_label" style="display:none"><label>'.$lang['node_name'].'</label> <smallclass="text-muted">'.$lang['mqtt_name_info'].'</small>
                <select class="form-control input-sm" type="text" id="mqtt_type" name="mqtt_type">
                        <option value="Sensor" selected="selected">MQTT Sensor</option>
                        <option value="Controller">MQTT Controller</option>
                </select>
                <div class="help-block with-errors"></div>
        </div>

	<div class="form-group" class="control-label" id="add_devices_label" style="display:block"><label>'.$lang['node_child_id'].'</label> <small class="text-muted">'.$lang['node_child_id_info'].'</small>
		<input class="form-control input-sm" type="text" id="nodes_max_child_id" name="nodes_max_child_id" value="0" placeholder="'.$lang['node_max_child_id'].'">
		<div class="help-block with-errors"></div>
	</div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_node()">

            </div>
        </div>
    </div>
</div>';

//Zone Type
echo '
<div class="modal fade" id="zone_types" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_type'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=zone_types.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['zone_types'].'</a></li>
                                <li class="divider"></li>
                                <li><a href="pdf_download.php?file=switch_zones.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['switch_zones'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['zone_type_text'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-xs-4"><small>'.$lang['type'].'</small></th>
        <th class="col-xs-7"><small>'.$lang['category'].'</small></th>
        <th class="col-xs-1"></th>
    </tr>';

$query = "SELECT * FROM zone_type where `purge`=0;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
    $query = "SELECT * FROM `zone` WHERE `type_id` = '".$row['id']."' LIMIT 1;";
    $t_results = $conn->query($query);
    $rowcount=mysqli_num_rows($t_results);
    if($rowcount > 0) {
        $content_msg=$lang['confirm_dell_active_zone_type'];
    } else {
        $content_msg=$lang['confirm_dell_de_active_zone_type'];
    }

    echo '
        <tr>
            <td>'.$row["type"].'</td>
            <td>'.$lang['zone_category'.$row["category"]].'</td>
            <td><a href="javascript:delete_zone_type('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_zone_type">'.$lang['zone_type_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Zone Type
echo '
<div class="modal fade" id="add_zone_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_type_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['zone_type_add_info_text'].'</p>

        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

        <div class="form-group" class="control-label"><label>'.$lang['zone_type'].'</label> <small class="text-muted">'.$lang['zone_type_info'].'</small>
        <input class="form-control input-sm" type="text" id="zone_type" name="zone_type" value="" placeholder="'.$lang['zone_type'].'">
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['category'].'</label> <small class="text-muted">'.$lang['zone_category_info'].'</small>

        <select class="form-control input-sm" type="text" id="category" name="category">
        <option value=0 selected>'.$lang['zone_category0'].'</option>
        <option value=1>'.$lang['zone_category1'].'</option>
        <option value=2>'.$lang['zone_category2'].'</option>
        <option value=3>'.$lang['zone_category3'].'</option>
        <option value=4>'.$lang['zone_category4'].'</option>
        </select>
    <div class="help-block with-errors"></div></div>

</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_zone_type()">

            </div>
        </div>
    </div>
</div>';

//Zone model
echo '
<div class="modal fade" id="zone_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_guide_zones.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_zones'].'</a></li>
                                <li class="divider"></li>
	                        <li><a href="pdf_download.php?file=setup_pump_type_relays.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_pump_type_relays'].'</a></li>
                                <li class="divider"></li>
                                <li><a href="pdf_download.php?file=switch_zones.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['switch_zones'].'</a></li>
                                <li class="divider"></li>
                        	<li><a href="pdf_download.php?file=switch_zone_state_control.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['switch_zone_state_control'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['zone_settings_text'].'</p>';
		$query = "select * from zone order by index_id asc";
		$results = $conn->query($query);
		echo '<div class=\"list-group\">';
			while ($row = mysqli_fetch_assoc($results)) {
        			if($row['status'] == 1) {
                			$content_msg=$lang['confirm_dell_active_zone'];
        			} else {
                			$content_msg=$lang['confirm_dell_de_active_zone'];
        			}
				echo "<div class=\"list-group-item\">
        				<i class=\"glyphicon glyphicon-th-large orange\"></i> ".$row['name']."";
        				$query = "select * from zone_view WHERE id = '{$row['id']}'order by index_id asc";
        				$vresult = $conn->query($query);
        				while ($vrow = mysqli_fetch_assoc($vresult)) {
						$unit = SensorUnits($conn,$vrow['sensor_type_id']);
                                                if ($vrow['r_type'] == 5) {
							echo "<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['pump_relay'].": ".$vrow['relay_type'].": ".$vrow['relay_id']."-".$vrow['relay_child_id']."</small></span><br>";
                				} elseif ($vrow['category'] == 2) {
							echo "<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['controller'].": ".$vrow['relay_type'].": ".$vrow['relay_id']."-".$vrow['relay_child_id']."</small></span><br>";
						} elseif ($vrow['category'] == 3) {
							echo "<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['min']." ".DispSensor($conn,$vrow['min_c'],$vrow['sensor_type_id']).$unit." </em>, ".$lang['max']." ".$vrow['max_c'].$unit." </em> - ".$lang['sensor'].": ".$vrow['sensors_id']."</small></span><br>";
                				} else {
                        				echo "<span class=\"pull-right \"><em>&nbsp;&nbsp;<small> ".$lang['max']." ".DispSensor($conn,$vrow['max_c'],$vrow['sensor_type_id']).$unit." </em> - ".$lang['sensor'].": ".$vrow['sensors_id']." - ".$vrow['relay_type'].": ".$vrow['relay_id']."-".$vrow['relay_child_id']."</small></span><br>";
                				}
        				}
        				echo "<span class=\"pull-right \"><small>
        					<a href=\"zone.php?id=".$row['id']."\" class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></a>&nbsp;&nbsp;
        					<a href=\"javascript:delete_zone(".$row['id'].");\"><button class=\"btn btn-danger btn-xs\" data-toggle=\"confirmation\" data-title=".$lang['confirmation']." data-content=\"$content_msg\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>
        				</small></span>
        				<br>
        			</div>";
			}
		echo '</div>
	    </div>
	    <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <a class="btn btn-default login btn-sm" href="zone.php">'.$lang['zone_add'].'</a>
            </div>
        </div>
    </div>
</div>';

//gateway model
echo '
<div class="modal fade" id="sensor_gateway" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
               			<h5 class="modal-title">'.$lang['smart_home_gateway'].'</h5>
                                <div class="dropdown pull-right">
                                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                                        </a>
                                        <ul class="dropdown-menu">
                                		<li><a href="pdf_download.php?file=setup_guide_gateway.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_gateway'].'</a></li>
                                        </ul>
                                </div>
            		</div>
            		<div class="modal-body">';
				$gquery = "SELECT * FROM gateway";
				$gresult = $conn->query($gquery);
                                $rowcount=mysqli_num_rows($gresult);
                                if($rowcount == 0) {
                                        echo $lang['smart_home_gateway_text_serial'];
                                        $display_wifi = "display:none";
                                        $display_serial = "display:block";
                                        $gateway_type = 'serial';
                                } else {
                                        $grow = mysqli_fetch_array($gresult);
                                        $gateway_type = $grow['type'];
                                        echo '<p class="text-muted">';
                                        if ($gateway_type=='wifi'){
                                                echo $lang['smart_home_gateway_text_wifi'];
                                                $display_wifi = "display:block";
                                                $display_serial = "display:none";
                                                $display_timeout = "display:block";
                                        } elseif ($gateway_type=='serial') {
                                                echo $lang['smart_home_gateway_text_serial'];
                                                $display_wifi = "display:none";
                                                $display_serial = "display:block";
                                                $display_timeout = "display:block";
                                        } elseif ($gateway_type=='virtual') {
                                                echo $lang['smart_home_gateway_text_virtual'];
                                                $display_wifi = "display:none";
                                                $display_serial = "display:none";
                                                $display_timeout = "display:none";
                                        }
                                }
				echo '</p>';
				echo '
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label">
					<div class="checkbox checkbox-default checkbox-circle">';
						if ($grow['status'] == '1'){
							echo '<input id="checkbox1" class="styled" type="checkbox" value="1" name="status" checked>';
						}else {
							echo '<input id="checkbox1" class="styled" type="checkbox" value="1" name="status">';
						}
						echo '
						<label for="checkbox1"> '.$lang['smart_home_gateway_enable'].'</label>
					</div>
				</div>
                               	<!-- /.form-group -->

                                <div class="form-group" class="control-label">
                                        <div class="checkbox checkbox-default checkbox-circle">';
                                                if ($grow['enable_outgoing'] == '1'){
                                                        echo '<input id="checkbox4" class="styled" type="checkbox" value="1" name="enable_outgoing" checked>';
                                                }else {
                                                        echo '<input id="checkbox4" class="styled" type="checkbox" value="1" name="enable_outgoing">';
                                                }
                                                echo '
                                                <label for="checkbox4"> '.$lang['enable_outgoing'].'</label>
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_type'].'</label>
                                        <select class="form-control input-sm" type="text" id="gw_type" name="gw_type" onchange=gw_location()>
                                        <option value="wifi" ' . ($gateway_type=='wifi' ? 'selected' : '') . '>'.$lang['wifi'].'</option>
                                        <option value="serial" ' . ($gateway_type=='serial' ? 'selected' : '') . '>'.$lang['serial'].'</option>
                                        <option value="virtual" ' . ($gateway_type=='virtual' ? 'selected' : '') . '>'.$lang['virtual'].'</option>
                                        </select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="wifi_gw" style="'.$display_wifi.'"><label>'.$lang['wifi_gateway_location'].'</label>
                                	<input class="form-control input-sm" type="text" id="wifi_location" name="wifi_location" value="'.$grow['location'].'" placeholder="Gateway Location">
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="serial_gw" style="'.$display_serial.'"><label>'.$lang['serial_gateway_location'].'</label>
                                        <select class="form-control input-sm" type="text" id="serial_location" name="serial_location">
                                        <option selected>'.$grow['location'].'</option>';
                                        $dev_tty = glob("/dev/tty*");
                                        for ($x = 0; $x <=  count($dev_tty) - 1; $x++) {
                                                echo '<option value="'.$dev_tty[$x].'" ' . '>'.$dev_tty[$x].'</option>';
                                        }
                                        echo '</select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="wifi_port" style="'.$display_wifi.'"><label>'.$lang['wifi_gateway_port'].' </label>
                                        <input class="form-control input-sm" type="text" id="wifi_port_num" name="wifi_port_num" value="'.$grow['port'].'" placeholder="Gateway Port">
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

                                <div class="form-group" class="control-label" id="serial_port" style="'.$display_serial.'"><label>'.$lang['serial_gateway_port'].' </label>
                                        <select class="form-control input-sm" type="text" id="serial_port_speed" name="serial_port_speed">
                                                <option selected>'.$grow['port'].'</option>
                                                <option value="9600">9600</option>
                                                <option value="19200">19200</option>
                                                <option value="38400">38400</option>
                                                <option value="57600">57600</option>
                                                <option value="74880">74880</option>
                                                <option value="115200">115200</option>
                                                <option value="230400">233400</option>
                                                <option value="250000">250000</option>
                                                <option value="500000">500000</option>
                                                <option value="1000000">1000000</option>
                                                <option value="2000000">2000000</option>
                                                </select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->

				<div class="form-group" class="control-label" id="gw_timout_label" style="'.$display_timeout.'"><label>'.$lang['timeout'].' </label>
                                        <select class="form-control input-sm" type="text" id="gw_timout" name="gw_timout">
                                        <option selected>'.$grow['timout'].'</option>
                                        <option value="0">0</option>
                                        <option value="1">1</option>
                                        <option value="2">2</option>
                                        <option value="3" selected>3</option>
                                        <option value="4">4</option>
                                        <option value="5">5</option>
                                        <option value="6">6</option>
                                        <option value="7">7</option>
                                        <option value="8">8</option>
                                        <option value="9">9</option>
                                        <option value="10">10</option>
                                        <option value="15">15</option>
                                        </select>
                                        <div class="help-block with-errors"></div>
				</div>
                                <!-- /.form-group -->

				<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_version'].' </label>
					<input class="form-control input-sm" type="text" id="gw_version" name="gw_version" value="'.$grow['version'].'" disabled>
					<div class="help-block with-errors">
					</div>
				</div>
                                <!-- /.form-group -->

				<br><h4 class="info"><i class="fa fa-heartbeat red"></i> '.$lang['smart_home_gateway_scr_info'].'</h4>
				<div class=\"list-group\">';
					echo "
					<a href=\"#\" class=\"list-group-item\"> PID <span class=\"pull-right text-muted small\"><em> ".$grow['pid']."</em></span></a>
					<a href=\"#\" class=\"list-group-item\"> ".$lang['smart_home_gateway_pid'].": <span class=\"pull-right text-muted small\"><em>".$grow['pid_running_since']."</em></span></a>";

					$query = "select * FROM gateway_logs WHERE pid_datetime >= NOW() - INTERVAL 5 MINUTE;";
					$result = $conn->query($query);
					if (mysqli_num_rows($result) != 0){
						$gw_restarted = mysqli_num_rows($result);
					} else {
						$gw_restarted = '0';
					}
					echo "<a href=\"#\" class=\"list-group-item\"> ".$lang['smart_home_gateway_scr'].": <span class=\"pull-right text-muted small\"><em>".$gw_restarted."</em></span></a>";
				echo '</div>
                                <!-- /.list-group -->
			</div>
			<!-- /.modal-body -->
            		<div class="modal-footer">
				<a href="javascript:resetgw('.$grow['pid'].')" class="btn btn-default login btn-sm btn-edit">Reset GW</a>
				<a href="javascript:find_gw()" class="btn btn-default login btn-sm btn-edit">Search GW</a>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="setup_gateway()">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            		</div>
			<!-- /.modal-footer -->
        	</div>
		<!-- /.modal-content -->
    	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal fade -->
';

//Alert Setting model
echo '
<div class="modal fade" id="node_alerts" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_alerts_edit'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['node_alerts_edit_info'].' </p>';
$query = "SELECT nodes.*, battery.id AS batt
FROM nodes
LEFT JOIN battery ON battery.node_id = nodes.node_id
WHERE status = 'Active' ORDER BY nodes.node_id asc;";
$results = $conn->query($query);
echo '<table>
    <tr>
        <th class="col-xs-1">'.$lang['node_id'].'</th>
        <th class="col-xs-2">'.$lang['name'].'</th>
        <th class="col-xs-3">'.$lang['last_seen'].'</th>
        <th class="col-xs-3">'.$lang['notice_interval'].'
        <span class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="left" data-content="'.$lang['notice_interval_info'].'"</span></th>
        <th class="col-xs-3">'.$lang['min_battery_level'].'
        <span class="fa fa-info-circle fa-lg text-info" data-container="body" data-toggle="popover" data-placement="left" data-content="'.$lang['battery_level_info'].'"</span></th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    echo '
        <tr>
            <td>'.$row['node_id'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['last_seen'].'</td>
            <td><input id="interval'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="interval'.$row["node_id"].'" value="'.$row["notice_interval"].'" placeholder="Notice Interval" required></td>';
	    if(!isset($row['batt'])) {
	            echo '<td><input id="min_value'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="min_value'.$row["node_id"].'" value="N/A" readonly="readonly" placeholder="Min Value"></td>';
	    } else {
                    echo '<td><input id="min_value'.$row["node_id"].'" type="value" class="form-control pull-right" style="border: none" name="min_value'.$row["node_id"].'" value="'.$row["min_value"].'" placeholder="Min Value"></td>';
	    }
        echo '</tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="node_alerts()">
            </div>
        </div>
    </div>
</div>';
?>

<script>
$(document).ready(function(){
  $('[data-toggle="popover"]').popover();
});

$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>

<script language="javascript" type="text/javascript">
function show_hide_devices()
{
 var e = document.getElementById("node_type");
 var selected_node_type = e.options[e.selectedIndex].text;
 if(selected_node_type.includes("GPIO") || selected_node_type.includes("MQTT") || selected_node_type.includes("Dummy")) {
        document.getElementById("add_devices_label").style.visibility = 'hidden';;
        if(selected_node_type.includes("MQTT")) {
                document.getElementById("mqtt_type_label").style.display = 'block';;
		document.getElementById("nodes_max_child_id").style.visibility = 'hidden';;
        } else {
                document.getElementById("mqtt_type_label").style.display = 'none';;
        }
        if(selected_node_type.includes("Dummy")) {
                document.getElementById("dummy_type_label").style.display = 'block';;
		document.getElementById("nodes_max_child_id").style.visibility = 'visible';;
		document.getElementById("add_devices_label").style.visibility = 'visible';;
        } else {
                document.getElementById("dummy_type_label").style.display = 'none';;
        }
 } else {
        document.getElementById("nodes_max_child_id").style.visibility = 'visible';;
        document.getElementById("add_devices_label").style.visibility = 'visible';;
        document.getElementById("mqtt_type_label").style.display = 'none';;
        document.getElementById("dummy_type_label").style.display = 'none';;
 }
}
function gw_location()
{
 var e = document.getElementById("gw_type");
 var selected_gw_type = e.value;
 if(selected_gw_type.includes("virtual")) {
        document.getElementById("serial_gw").style.display = 'none';
        document.getElementById("wifi_gw").style.display = 'none';
        document.getElementById("serial_port").style.display = 'none';
        document.getElementById("wifi_port").style.display = 'none';
        document.getElementById("gw_timout_label").style.visibility = 'hidden';
        document.getElementById("gw_timout").style.display = 'none';
        document.getElementById("wifi_location").value = "";
        document.getElementById("wifi_port_num").value = "";
 } else if(selected_gw_type.includes("wifi")) {
        document.getElementById("serial_gw").style.display = 'none';
        document.getElementById("wifi_gw").style.display = 'block';
        document.getElementById("serial_port").style.display = 'none';
        document.getElementById("wifi_port").style.display = 'block';
        document.getElementById("gw_timout_label").style.visibility = 'visible';
        document.getElementById("gw_timout").style.display = 'block';
        document.getElementById("wifi_location").value = "192.168.0.100";
        document.getElementById("wifi_port_num").value = "5003";
 } else {
        document.getElementById("wifi_gw").style.display = 'none';
        document.getElementById("serial_gw").style.display = 'block';
        document.getElementById("wifi_port").style.display = 'none';
        document.getElementById("serial_port").style.display = 'block';
        document.getElementById("gw_timout_label").style.visibility = 'visible';
        document.getElementById("gw_timout").style.display = 'block';
        document.getElementById("serial_location").value = "/dev/ttyAMA0";
        document.getElementById("serial_port_speed").value = "115200";
 }
}
</script>

