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

//System Mode
$system_mode = settings($conn, 'mode');
echo '
<div class="modal fade" id="change_system_mode" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['system_controller_mode'].'</h5>
            </div>
            <div class="modal-body">
                                <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
                                <div class="form-group" class="control-label"><label>'.$lang['system_mode'].'</label>
                                <select class="form-control input-sm" type="text" id="new_mode" name="new_mode">
                                <option value="0" ' . ($system_mode==0 || $system_mode=='0' ? 'selected' : '') . '>'.$lang['boiler'].' ('.$lang['cyclic'].' control)</option>
                                <option value="1" ' . ($system_mode==1 || $system_mode=='1' ? 'selected' : '') . '>'.$lang['hvac'].' ('.$lang['cyclic'].' control)</option>
                                <option value="2" ' . ($system_mode==2 || $system_mode=='2' ? 'selected' : '') . '>'.$lang['boiler'].' ('.$lang['button'].' control)</option>
                                <option value="3" ' . ($system_mode==3 || $system_mode=='3' ? 'selected' : '') . '>'.$lang['hvac'].' ('.$lang['button'].' control)</option>
                                </select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_system_mode()">
            </div>
        </div>

    </div>
</div>';

//Units
$c_f = settings($conn, 'c_f');
if($c_f==1 || $c_f=='1')
    $TUnit='F';
else
    $TUnit='C';
echo '
<div class="modal fade" id="change_units" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['unit_change'].'</h5>
            </div>
            <div class="modal-body">
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['units'].'</label>
				<select class="form-control input-sm" type="number" id="new_units" name="new_units">
				<option value="0" ' . ($c_f==0 || $c_f=='0' ? 'selected' : '') . '>'.$lang['unit_celsius'].'</option>
				<option value="1" ' . ($c_f==1 || $c_f=='1' ? 'selected' : '') . '>'.$lang['unit_fahrenheit'].'</option>
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_units()">
            </div>
        </div>
    </div>
</div>';

//Language settings
$language = settings($conn, 'language');
echo '
<div class="modal fade" id="language" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['language'].'</h5>
            </div>
            <div class="modal-body">

				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['language'].'</label>
				<select class="form-control input-sm" type="text" id="new_lang" name="new_lang">';
				$languages = ListLanguages($language);
				for ($x = 0; $x <=  count($languages) - 1; $x++) {
					echo '<option value="'.$languages[$x][0].'" ' . ($language==$languages[$x][0] ? 'selected' : '') . '>'.$languages[$x][1].'</option>';
				}
				echo '</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_lang()">
            </div>
        </div>
    </div>
</div>';

//Graph model
echo '
<div class="modal fade" id="zone_graph" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['graph_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=displaying_temperature_sensors_graphs.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['displaying_temperature_sensors_graphs'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted">'.$lang['graph_settings_text'].'</p>';
$query = "SELECT * FROM sensors WHERE sensor_type_id = 1 ORDER BY name asc";
$results = $conn->query($query);
echo '  <table class="table table-bordered">
    <tr>
        <th class="col-xs-10"><small>'.$lang['sensor_name'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['graph_num'].'</small></th>
    </tr>';
while ($row = mysqli_fetch_assoc($results)) {
    echo '
        <tr>
            <td>'.$row["name"].'</td>
            <td><input id="graph_num'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="graph_num" size="3" value="'.$row["graph_num"].'" placeholder="Graph Number" required></td>
        </tr>';
}
echo '
</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="setup_graph()">
            </div>
        </div>
    </div>
</div>';

//Sensor Limits model
echo '
<div class="modal fade" id="sensor_limits" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_limits_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_sensor_notifications.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_sensor_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted">'.$lang['sensor_limits_settings_text'].'</p>';
$query = "SELECT sensor_limits.id, sensors.name, sensor_limits.min, sensor_limits.max, status FROM sensors, sensor_limits WHERE sensor_limits.sensor_id = sensors.id ORDER BY name asc";
$results = $conn->query($query);
echo '  <table class="table table-bordered">
    <tr>
        <th class="col-xs-4"><small>'.$lang['sensor_name'].'</small></th>
        <th class="col-xs-1"><small>'.$lang['min_val'].'</small></th>
        <th class="col-xs-1"><small>'.$lang['max_val'].'</small></th>
        <th class="col-xs-1"><small>'.$lang['enabled'].'</small></th>
        <th class="col-xs-2"><small>'.$lang['edit_delete'].'</small></th>
    </tr>';
while ($row = mysqli_fetch_assoc($results)) {
    if ($row['status'] == 1) { $enabled = $lang['yes']; } else { $enabled = $lang['no']; }
    echo '
        <tr>
            <td>'.$row["name"].'</td>
            <td>'.$row["min"].'</td>
            <td>'.$row["max"].'</td>
            <td style="text-align:center; vertical-align:middle;">'.$enabled.'</td>
            <td><a href="sensor_limits.php?id='.$row["id"].'"><button class="btn btn-primary btn-xs"><span class="ionicons ion-edit"></span></button> </a>&nbsp;&nbsp
                <a href="javascript:delete_sensor_limits('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$lang['confirm_del_sensor_limit'].'"><span class="glyphicon glyphicon-trash"></span></button> </a>
	    </td>
        </tr>';
}
echo '
</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
		<a class="btn btn-default login btn-sm" href="sensor_limits.php">'.$lang['sensor_limits_add'].'</a>
            </div>
        </div>
    </div>
</div>';

//network settings model
echo '
<div class="modal fade" id="network_setting" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['network_settings'].'</h5>
            </div>
            <div class="modal-body">';

$query = "SELECT * FROM `network_settings` ORDER BY `id` ASC;";
$result = $conn->query($query);

$rowArray = array();

while($row = mysqli_fetch_assoc($result)) {
   $rowArray[] = $row;
}

echo '<p class="text-muted">'.$lang['network_text'].'</p>';
echo '
        <form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

        <input class="form-control input-sm" type="hidden" id="n_int_type" name="n_int_type" value="'.$rowArray[0]['interface_type'].'"/>
        <div class="form-group" class="control-label"><label>'.$lang['network_interface'].'</label>
                <select class="form-control input-sm" type="text" id="n_int_num" name="n_int_num" onchange=change(this.options[this.selectedIndex].value)>
                <option value=0>wlan0</option>
                <option value=1>wlan1</option>
                <option value=2>eth0</option>
                <option value=3>eth1</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_primary'].'</label>
                <select class="form-control input-sm" type="text" id="n_primary" name="n_primary">
                <option value=0>No</option>
                <option selected value=1>Yes</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_ap_mode'].'</label>
                <select class="form-control input-sm" type="text" id="n_ap_mode" name="n_ap_mode">
                <option selected value=0>No</option>
                <option value=1>Yes</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_mac_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_mac" name="n_mac" value="'.$rowArray[0]['mac_address'].'" placeholder="MAC Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_hostname'].'</label>
                <input class="form-control input-sm" type="text" id="n_hostname" name="n_hostname" value="'.$rowArray[0]['hostname'].'" placeholder="Hostname">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_ip_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_ip" name="n_ip" value="'.$rowArray[0]['ip_address'].'" placeholder="IP Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_gateway_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_gateway" name="n_gateway" value="'.$rowArray[0]['gateway_address'].'" placeholder="Gateway Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_net_mask'].'</label>
                <input class="form-control input-sm" type="text" id="n_net_mask" name="n_net_mask" value="'.$rowArray[0]['net_mask'].'" placeholder="Net Mask">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_dns1_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_dns1" name="n_dns1" value="'.$rowArray[0]['dns1_address'].'" placeholder="DNS1 Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_dns2_address'].'</label>
                <input class="form-control input-sm" type="text" id="n_dns2" name="n_dns2" value="'.$rowArray[0]['dns2_address'].'" placeholder="DNS2 Address">
                <div class="help-block with-errors">
                </div>
        </div>

        </div>
        <!-- /.modal-body -->

            <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="setup_network()">
            </div>
            <!-- /.modal-footer -->
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal-fade -->
';
?>
<script>
function change(value){
        var jArray = <?php echo json_encode($rowArray); ?>;
        var valuetext = value;
        document.getElementById("n_primary").value = jArray[value]['primary_interface'];
        document.getElementById("n_mac").value = jArray[value]['mac_address'];
        document.getElementById("n_hostname").value = jArray[value]['hostname'];
        document.getElementById("n_ip").value = jArray[value]['ip_address'];
        document.getElementById("n_gateway").value = jArray[value]['gateway_address'];
        document.getElementById("n_net_mask").value = jArray[value]['net_mask'];
        document.getElementById("n_dns1").value = jArray[value]['dns1_address'];
        document.getElementById("n_dns2").value = jArray[value]['dns2_address'];
        switch (value) {
                case '0':
                        document.getElementById("n_int_type").value = 'wlan0';
                        break;
                case '1':
                        document.getElementById("n_int_type").value = 'wlan1';
                        break;
                case '2':
                        document.getElementById("n_int_type").value = 'eth0';
                        break;
                case '3':
                        document.getElementById("n_int_type").value = 'eth1';
                        break;
                default:
        }
}
</script>
<?php

//email settings model
echo '
<div class="modal fade" id="email_setting" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['email_settings'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                        	<li><a href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_email_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">';
$gquery = "SELECT * FROM email";
$gresult = $conn->query($gquery);
$erow = mysqli_fetch_array($gresult);

echo '<p class="text-muted">'.$lang['email_text'].'</p>';
echo '
	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

	<div class="form-group" class="control-label">
	<div class="checkbox checkbox-default checkbox-circle">';
	if ($erow['status'] == '1'){
		echo '<input id="checkbox3" class="styled" type="checkbox" value="1" name="status" checked>';
	}else {
		echo '<input id="checkbox3" class="styled" type="checkbox" value="1" name="status">';
	}
echo '

	<label for="checkbox3"> '.$lang['email_enable'].'</label></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_smtp_server'].'</label>
	<input class="form-control input-sm" type="text" id="e_smtp" name="e_smtp" value="'.$erow['smtp'].'" placeholder="e-mail SMTP Server Address ">
	<div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['port'].'</label>
        <select class="form-control input-sm" type="text" id="e_port" name="e_port" >
                <option value="25" ' . ($erow['port']==25 ? 'selected' : '') . '>25</option>
                <option value="465" ' . ($erow['port']==465 ? 'selected' : '') . '>465</option>
        </select>
        <div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_username'].' </label>
	<input class="form-control input-sm" type="text" id="e_username" name="e_username" value="'.$erow['username'].'" placeholder="Username for e-mail Server">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_password'].' </label>
	<input class="form-control input-sm" type="password" id="e_password" name="e_password" value="'.$erow['password'].'" placeholder="Password for e-mail Server">
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_from_address'].' </label>
	<input class="form-control input-sm" type="text" id="e_from_address" name="e_from_address" value="'.$erow['from'].'" placeholder="From e-mail" >
	<div class="help-block with-errors"></div></div>

	<div class="form-group" class="control-label"><label>'.$lang['email_to_address'].' </label>
	<input class="form-control input-sm" type="text" id="e_to_address" name="e_to_address" value="'.$erow['to'].'" placeholder="To e-mail Address">
	<div class="help-block with-errors"></div></div>';

echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="setup_email()">

            </div>
        </div>
    </div>
</div>';

//Time Zone
echo '
<div class="modal fade" id="time_zone" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['time_zone'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted"> '.$lang['time_zone_text'].'</p>
				<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['time_zone'].'</label>
				<select class="form-control input-sm" type="number" id="new_time_zone" name="new_time_zone" >

				<option selected >'.settings($conn, 'timezone').'</option>';
$timezones = array(
    'Pacific/Midway'       => "(GMT-11:00) Midway Island",
    'US/Samoa'             => "(GMT-11:00) Samoa",
    'US/Hawaii'            => "(GMT-10:00) Hawaii",
    'US/Alaska'            => "(GMT-09:00) Alaska",
    'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
    'America/Tijuana'      => "(GMT-08:00) Tijuana",
    'US/Arizona'           => "(GMT-07:00) Arizona",
    'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
    'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
    'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
    'America/Mexico_City'  => "(GMT-06:00) Mexico City",
    'America/Monterrey'    => "(GMT-06:00) Monterrey",
    'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
    'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
    'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
    'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
    'America/Bogota'       => "(GMT-05:00) Bogota",
    'America/Lima'         => "(GMT-05:00) Lima",
    'America/Caracas'      => "(GMT-04:30) Caracas",
    'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
    'America/La_Paz'       => "(GMT-04:00) La Paz",
    'America/Santiago'     => "(GMT-04:00) Santiago",
    'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
    'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
    'Greenland'            => "(GMT-03:00) Greenland",
    'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
    'Atlantic/Azores'      => "(GMT-01:00) Azores",
    'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
    'Africa/Casablanca'    => "(GMT) Casablanca",
    'Europe/Dublin'        => "(GMT) Dublin",
    'Europe/Lisbon'        => "(GMT) Lisbon",
    'Europe/London'        => "(GMT) London",
    'Africa/Monrovia'      => "(GMT) Monrovia",
    'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
    'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
    'Europe/Berlin'        => "(GMT+01:00) Berlin",
    'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
    'Europe/Brussels'      => "(GMT+01:00) Brussels",
    'Europe/Budapest'      => "(GMT+01:00) Budapest",
    'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
    'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
    'Europe/Madrid'        => "(GMT+01:00) Madrid",
    'Europe/Paris'         => "(GMT+01:00) Paris",
    'Europe/Prague'        => "(GMT+01:00) Prague",
    'Europe/Rome'          => "(GMT+01:00) Rome",
    'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
    'Europe/Skopje'        => "(GMT+01:00) Skopje",
    'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
    'Europe/Vienna'        => "(GMT+01:00) Vienna",
    'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
    'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
    'Europe/Athens'        => "(GMT+02:00) Athens",
    'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
    'Africa/Cairo'         => "(GMT+02:00) Cairo",
    'Africa/Harare'        => "(GMT+02:00) Harare",
    'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
    'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
    'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
    'Europe/Kyiv'          => "(GMT+02:00) Kiev",
    'Europe/Minsk'         => "(GMT+02:00) Minsk",
    'Europe/Riga'          => "(GMT+02:00) Riga",
    'Europe/Sofia'         => "(GMT+02:00) Sofia",
    'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
    'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
    'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
    'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
    'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
    'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
    'Europe/Moscow'        => "(GMT+03:00) Moscow",
    'Asia/Tehran'          => "(GMT+03:30) Tehran",
    'Asia/Baku'            => "(GMT+04:00) Baku",
    'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
    'Asia/Muscat'          => "(GMT+04:00) Muscat",
    'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
    'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
    'Asia/Kabul'           => "(GMT+04:30) Kabul",
    'Asia/Karachi'         => "(GMT+05:00) Karachi",
    'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
    'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
    'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
    'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
    'Asia/Almaty'          => "(GMT+06:00) Almaty",
    'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
    'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
    'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
    'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
    'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
    'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
    'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
    'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
    'Australia/Perth'      => "(GMT+08:00) Perth",
    'Asia/Singapore'       => "(GMT+08:00) Singapore",
    'Asia/Taipei'          => "(GMT+08:00) Taipei",
    'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
    'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
    'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
    'Asia/Seoul'           => "(GMT+09:00) Seoul",
    'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
    'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
    'Australia/Darwin'     => "(GMT+09:30) Darwin",
    'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
    'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
    'Australia/Canberra'   => "(GMT+10:00) Canberra",
    'Pacific/Guam'         => "(GMT+10:00) Guam",
    'Australia/Hobart'     => "(GMT+10:00) Hobart",
    'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
    'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
    'Australia/Sydney'     => "(GMT+10:00) Sydney",
    'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
    'Asia/Magadan'         => "(GMT+12:00) Magadan",
    'Pacific/Auckland'     => "(GMT+12:00) Auckland",
    'Pacific/Fiji'         => "(GMT+12:00) Fiji",
);

foreach($timezones as $xzone => $x_value) {
	echo '<option value="'.$xzone.'">'.$x_value.'</option>';
}
echo '</select>';
echo'
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_timezone()">
            </div>
        </div>
    </div>
</div>';

// Jobs Schedule modal
echo '
<div class="modal fade" id="jobs_schedule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['schedule_jobs'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=task_scheduling.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['task_scheduling'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['schedule_jobs_info'].' </p>';
$query = "SELECT id, job_name, script, enabled, log_it, time FROM jobs ORDER BY id asc";
$results = $conn->query($query);
echo '<br><table>
    <tr>
        <th class="col-xs-3">'.$lang['jobs_name'].'</th>
        <th class="col-xs-4">'.$lang['jobs_script'].'</th>
        <th class="col-xs-1">'.$lang['enabled'].'</th>
        <th class="col-xs-1">'.$lang['jobs_log'].'</th>
        <th class="col-xs-2">'.$lang['jobs_time'].'</th>
        <th class="col-xs-1"></th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    if ($row["log_it"] == 0) { $log_check = ''; } else { $log_check = 'checked'; }
    if ($row["enabled"] == 0) { $enabled_check = ''; } else { $enabled_check = 'checked'; }
    echo '
        <tr>
            <td><input id="jobs_name'.$row["id"].'" type="value" class="form-control pull-right" style="border: none" value="'.$row["job_name"].'" placeholder="Job Name"></td>
            <td><input id="jobs_script'.$row["id"].'" type="value" class="form-control pull-right" style="border: none" value="'.$row["script"].'" placeholder="Job Script"></td>
            <td style="text-align:center; vertical-align:middle;">
               <input type="checkbox" id="checkbox_enabled'.$row["id"].'" name="enabled" value="1" '.$enabled_check.'>
            </td>
            <td style="text-align:center; vertical-align:middle;">
               <input type="checkbox" id="checkbox_log'.$row["id"].'" name="logit" value="1" '.$log_check.'>
            </td>
            <td><input id="jobs_time'.$row["id"].'" type="value" class="form-control pull-right" style="border: none" value="'.$row["time"].'" placeholder="Run Job Every"></td>
            <td><a href="javascript:delete_job('.$row["id"].');"><button class="btn btn-danger btn-xs" data-toggle="confirmation" data-title="'.$lang['confirmation'].'" data-content="'.$content_msg.'"><span class="glyphicon glyphicon-trash"></span></button> </a></td>
        </tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default login btn-sm" data-href="#" data-toggle="modal" data-target="#add_job">'.$lang['add_job'].'</button>
                <input type="button" name="submit" value="'.$lang['apply'].'" class="btn btn-default login btn-sm" onclick="schedule_jobs()">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Job Schedule
echo '
<div class="modal fade" id="add_job" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_new_job'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_new_job_info_text'].'</p>

	<form data-toggle="validator" role="form" method="post" action="settings.php" id="form-join">

      	<div class="form-group" class="control-label">
             <div class="checkbox checkbox-default checkbox-circle">
                 <input id="checkbox_enabled" class="styled" type="checkbox" value="0" name="status" Enabled>
                 <label for="checkbox_enabled"> '.$lang['enabled'].'</label>
             </div>
        </div>

	<div class="form-group" class="control-label"><label>'.$lang['jobs_name'].'</label> <small class="text-muted">'.$lang['jobs_name_info'].'</small>
	<input class="form-control input-sm" type="text" id="job_name" name="job_name" value="" placeholder="'.$lang['jobs_name'].'">
	<div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['jobs_script'].'</label> <small class="text-muted">'.$lang['jobs_script_info'].'</small>
        <input class="form-control input-sm" type="text" id="job_script" name="job_script" value="" placeholder="'.$lang['jobs_script'].'">
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label"><label>'.$lang['jobs_time'].'</label> <small class="text-muted">'.$lang['jobs_time_info'].'</small>
        <input class="form-control input-sm" type="text" id="job_time" name="job_time" value="" placeholder="'.$lang['jobs_time'].'">
        <div class="help-block with-errors"></div></div>

        <div class="form-group" class="control-label">
             <div class="checkbox checkbox-default checkbox-circle">
                 <input id="checkbox_logit" class="styled" type="checkbox" value="0" name="status" Enabled>
                 <label for="checkbox_logit"> '.$lang['jobs_log'].'</label>
             </div>
        </div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="add_job()">

            </div>
        </div>
    </div>
</div>';

//Set Buttons model
echo '<div class="modal fade" id="set_buttons" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
		<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['set_buttons'].'</h5>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['set_buttons_text'].'</p>
		<input type="hidden" id="button_page_1" name="button_page_1" value="'.$lang['home_page'].'">
                <input type="hidden" id="button_page_2" name="button_page_2" value="'.$lang['onetouch_page'].'">';
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-md-3 text-center"><small>'.$lang['button_name'].'</small></th>
                                <th class="col-md-2 text-center"><small>'.$lang['toggle_page'].'</small></th>
                                <th class="col-md-1 text-center"><small>'.$lang['index_number'].'</small></th>
    			</tr>';
	                $query = "SELECT * FROM button_page ORDER BY index_id ASC;";
        	        $results = $conn->query($query);
			while ($row = mysqli_fetch_assoc($results)) {
				if ($row["page"] == 1) { $button_text = $lang['home_page']; } else { $button_text = $lang['onetouch_page']; }
                                echo '<tr>
                                        <td>'.$row["name"].'</td>
					<td><input type="button" id="page_button'.$row["id"].'" value="'.$button_text.'" class="btn btn-info btn-block" onclick="set_button_text('.$row["id"].')"></td>
           		                <td><input id="index'.$row["id"].'" type="text" class="pull-left text" style="border: none" name="index_id"  size="3" value="'.$row["index_id"].'" placeholder="Index ID" required></td>
					<input type="hidden" id="page_type'.$row["id"].'" name="page_type" value="'.$row["page"].'">
        			</tr>';
			}
            	echo '</table>
	    </div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                	<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_buttons()">
            </div>
        </div>
    </div>
</div>';

//Set Graph Categories to display
echo '<div class="modal fade" id="display_graphs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['enable_graphs'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['enable_graphs_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-md-2 text-center"><small>'.$lang['graph'].'</small></th>
                                <th class="col-md-1 text-center"><small>'.$lang['enabled'].'</small></th>
                        </tr>';
			$myArr = [];
			array_push($myArr, $lang['graph_temperature'], $lang['graph_humidity'], $lang['graph_addon_usage'], $lang['graph_saving'], $lang['graph_system_controller_usage'], $lang['graph_battery_usage']);
                        $query = "SELECT mask FROM graphs LIMIT 1;";
                        $result = $conn->query($query);
			$row = mysqli_fetch_assoc($result);
			$m = 1;
                        for ($x = 0; $x <=  5; $x++) {
                        	if ($row['mask'] & $m) { $enabled_check = 'checked'; } else { $enabled_check = ''; }
                                echo '<tr>
                                        <td>'.$myArr[$x].'</td>
            				<td style="text-align:center; vertical-align:middle;">
               					<input type="checkbox" id="checkbox_graph'.$x.'" name="enabled" value="1" '.$enabled_check.'>
            				</td>
                                </tr>';
				$m = $m << 1;
                        }
                echo '</table>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="enable_graphs()">
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
function set_button_text(id)
{
 var id_text = id;
 var e = document.getElementById("page_type" + id_text);
 if (e.value == 1) {
        document.getElementById("page_button" + id_text).value = document.getElementById("button_page_2").value;
        document.getElementById("page_type" + id_text).value = 2;
 } else {
        document.getElementById("page_button" + id_text).value = document.getElementById("button_page_1").value;
        document.getElementById("page_type" + id_text).value = 1;
 }
}
</script>

