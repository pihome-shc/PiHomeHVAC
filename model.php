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

if ($model_num == 1) {
// show frost protection
echo '<div class="modal fade" id="show_frost" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['frost_protection'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=frost_protection.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_frost_protection'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['frost_ptotection_info'].'</p>';
                $query = "SELECT sensors.sensor_id, sensors.sensor_child_id, sensors.name AS sensor_name, sensors.frost_temp, relays.name AS controller_name FROM sensors, relays WHERE (sensors.frost_controller = relays.id) AND frost_temp <> 0;";
                $results = $conn->query($query);
                echo '<table class="table table-bordered">
                        <tr>
                                <th style="text-align:center; vertical-align:middle;" class="col-3"><small>'.$lang['temperature_sensor'].'</small></th>
                                <th style="text-align:center; vertical-align:middle;" class="col-3"><small>'.$lang['frost_temparature'].'</small></th>
                                <th style="text-align:center; vertical-align:middle;" class="col-3"><small>'.$lang['frost_controller'].'</small></th>
                                <th style="text-align:center; vertical-align:middle;" class="col-1"><small>'.$lang['status'].'</small></th>
                        </tr>';
                        while ($row = mysqli_fetch_assoc($results)) {
                                $query = "SELECT node_id FROM nodes WHERE id = ".$row['sensor_id']." LIMIT 1;";
                                $result = $conn->query($query);
                                $frost_sensor_node = mysqli_fetch_array($result);
                                $frost_sensor_node_id = $frost_sensor_node['node_id'];
                                //query to get temperature from messages_in_view_24h table view
                        	$query = "SELECT * FROM messages_in_view_24h WHERE node_id = '".$frost_sensor_node_id."' AND child_id = ".$row['sensor_child_id']." LIMIT 1;";
                                $result = $conn->query($query);
                                $msg_in = mysqli_fetch_array($result);
                                $frost_sensor_c = $msg_in['payload'];
                                if ($frost_sensor_c <= $row["frost_temp"]) { $scolor = "red"; } else { $scolor = "blue"; }
                                echo '
                                <tr>
                                        <td>'.$row["sensor_name"].'</td>
                                        <td style="text-align:center; vertical-align:middle;">'.$row["frost_temp"].'</td>
                                        <td>'.$row["controller_name"].'</td>
                                        <td style="text-align:center; vertical-align:middle;"><class="statuscircle"><i class="bi bi-circle-fill '.$scolor.'" style="font-size: 0.55rem;"></i></td>
                                </tr>';
                        }
                echo '</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//last job log status model
echo '
<div class="modal fade" id="status_job" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['jobs_status_log'].'</h5>
                        </div>
                        <div class="modal-body">
                                <div class="form-group" class="control-label"><label>'.$lang['jobs_name'].'</label> <small class="text-muted">'.$lang['last_job_log_info'].'</small>
                                        <select class="form-select" type="text" id="job_name" name="job_name" onchange=last_job_log(this.options[this.selectedIndex].value)>';
                                        //get list of heat relays to display
                                        $query = "SELECT id, job_name, output FROM jobs WHERE enabled = 1;";
                                        $result = $conn->query($query);
                                        if ($result){
                                                while ($jrow=mysqli_fetch_array($result)) {
                                                        echo '<option value="'.$jrow['output'].'">'.$jrow['job_name'].'</option>';
                                                }
                                        }
                                        echo '</select>
                                        <div class="help-block with-errors"></div>
                                </div>
                                <!-- /.form-group -->';
                                $query = "select id, output from jobs where id = 1 limit 1";
                                $results = $conn->query($query);
                                $row = mysqli_fetch_assoc($results);
                                echo '<textarea id="job_status_text" style="background-color: black;color:#fff;height: 500px; min-width: 100%"><pre>'.$row['output'].'</pre></textarea>
                        </div>
                        <div class="modal-footer" id="ajaxModalFooter_1">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">Close</button>
                        </div>
                </div>
        </div>
</div>';
?>
<script>
function last_job_log(value){
        var valuetext = value;
        document.getElementById("job_status_text").value = valuetext;
}
</script>
<?php

// Last Software Install Model
echo '<div class="modal" id="last_sw_install" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
        <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['last_sw_install'].'</h4>
      </div>
      <div class="modal-body">';
        $output = file_get_contents('/var/www/cron/sw_install.txt');
        echo '<textarea id="install_status_text" style="background-color: black;color:#fff;height: 500px; min-width: 100%">'.$output.'</textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

// Documentation Model
echo '<div class="modal" id="documentation" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
        <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['documentation'].'</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted">'.$lang['documentation_info'].'</p>
        <div class="list-group">';
                $path = '/var/www/documentation/pdf_format';
                $allFiles = array_diff(scandir($path . "/"), [".", ".."]); // Use array_diff to remove both period values eg: ("." , "..")
		// Sort by document title
		$tmp = array();
		foreach ($allFiles as $file) {
		        $tmp[] = array('pdf' => $file, 'title' => $lang[substr($file, 0, -4)]);
		}
		$columns = array_column($tmp, 'title');
		array_multisort($columns, SORT_ASC, $tmp);
                foreach ($tmp as $doc) {
        		echo '<a href="pdf_download.php?file='.$doc['pdf'].'" target="_blank" class="d-flex justify-content-between list-group-item list-group-item-action">
          			<span class=""><i class="bi bi-file-earmark-pdf" style="font-size: 1.5rem;"></i>&nbsp&nbsp'.$doc['title'].'</span>
			</a>';
                }
        echo '</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

//OS version model
$rval = os_info();
echo '
<div class="modal fade" id="os_version" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['os_version'].'</h5>
                        </div>
                        <div class="modal-body">
                                <div class="list-group">
                                        <a href="#" class="list-group-item"><img src="images/linux.svg" width="20" height="20" alt="">&nbspNAME - '.$rval["NAME"].'</a>';
					if (array_key_exists("VERSION",$rval)) {
                                        	echo '<a href="#" class="list-group-item"><img src="images/linux.svg" width="20" height="20" alt="">&nbspVERSION - '.$rval["VERSION"].'</a>';
					} elseif (array_key_exists("BUILD_ID",$rval)) {
                                                echo '<a href="#" class="list-group-item"><img src="images/linux.svg" width="20" height="20" alt="">&nbspBUILD ID - '.$rval["BUILD_ID"].'</a>';
					}
                                        echo '<a href="#" class="list-group-item"><img src="images/linux.svg" width="20" height="20" alt="">&nbspDISTRIBUTION - '.$rval["ID"].'</a>
                                </div>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
        </div>
</div>';

//MaxAir Versions
echo '
<div class="modal fade" id="maxair_versions" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['maxair_versions'].'</h5>
                        </div>
                        <div class="modal-body">';
                                $file1 = file('/var/www/st_inc/db_config.ini');
                                $pieces =  explode(' ', $file1[count($file1) - 4]);
                                $code_v_installed = array_pop($pieces);
                                $pieces =  explode(' ', $file1[count($file1) - 3]);
                                $code_b_installed = array_pop($pieces);
                                $pieces =  explode(' ', $file1[count($file1) - 2]);
                                $db_v_installed = array_pop($pieces);
                                $pieces =  explode(' ', $file1[count($file1) - 1]);
                                $db_b_installed = array_pop($pieces);

                                $query = "SELECT name FROM repository WHERE status = 1 LIMIT 1;";
                                $result = $conn->query($query);
                                $row = mysqli_fetch_assoc($result);
                                $file2 = file('https://raw.githubusercontent.com/'.$row['name'].'/master/st_inc/db_config.ini');
                                $pieces =  explode(' ', $file2[count($file2) - 4]);
                                $code_v_github = array_pop($pieces);
                                $pieces =  explode(' ', $file2[count($file2) - 3]);
                                $code_b_github = array_pop($pieces);
                                $pieces =  explode(' ', $file2[count($file2) - 2]);
                                $db_v_github = array_pop($pieces);
                                $pieces =  explode(' ', $file2[count($file2) - 1]);
                                $db_b_github = array_pop($pieces);
                                //get latest Bootstrap version number
                                $result = file_get_contents('https://getbootstrap.com/docs/versions/');
                                if (strlen($result) > 0) {
                                        $search = 'Last update was ';
                                        $start = stripos($result, $search);
                                        if ($start !== false) {
                                                $start = $start + strlen($search) + 1;
                                                $end = stripos($result, '.</p>', $offset = $start);
                                                $length = $end - $start;
                                                $bootstrap_ver = substr($result, $start, $length);
                                        } else {
                                                $bootstrap_ver = "Not Found";
                                        }
                                } else {
                                        $bootstrap_ver = "Not Found";
                                }

                                echo '<p class="text-muted"> '.$lang['maxair_versions_text'].' <br>'.$lang['repository'].' - https://github.com/'.$row['name'].'.git</p>
                                <table class="table table-bordered">
                                        <tr>
                                                <th class="col-8"></th>
                                                <th class="col-2" "not_mapped_style" style="text-align:center">'.$lang['maxair_update_installed'].'</th>
                                                <th class="col-2" "not_mapped_style" style="text-align:center">'.$lang['maxair_update_github'].'</th>
                                        </tr>

                                        <tr>
                                                <td style="font-weight:bold">'.$lang['maxair_update_code_v'].'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$code_v_installed.'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$code_v_github.'</td>
                                        </tr>
                                        <tr>
                                                <td style="font-weight:bold">'.$lang['maxair_update_code_b'].'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$code_b_installed.'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$code_b_github.'</td>
                                        </tr>
                                        <tr>
                                                <td style="font-weight:bold">'.$lang['maxair_update_db_v'].'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$db_v_installed.'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$db_v_github.'</td>
                                        </tr>
                                        <tr>
                                                <td style="font-weight:bold">'.$lang['maxair_update_db_b'].'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$db_b_installed.'</td>
                                                <td style="text-align:center; vertical-align:middle;">'.$db_b_github.'</td>
                                        </tr>
                                        <tr>
                                                <td style="font-weight:bold">'.$lang['bs_ver'].'</td>
                                                <td id="bs_local" style="text-align:center; vertical-align:middle;"></td>
                                                <td style="text-align:center; vertical-align:middle;">'.$bootstrap_ver.'</td>
                                        </tr>';

                                echo '</table>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
        </div>
</div>';

//wifi model
if (is_dir("/sys/class/net/wlan0")) {
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
        echo '
        <div class="modal fade" id="wifi_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                        <div class="modal-content">
                                <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                        <h5 class="modal-title">'.$lang['wifi_settings'].'</h5>
                                </div>
                                <div class="modal-body">
                                        <p class="text-muted"> '.$lang['wifi_settings_text'].' </p>
                                        <div class="list-group" id="wifi_status"></div>
                                </div>
                                <div class="modal-footer">
                                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                </div>
                        </div>
                </div>
        </div>';
} else {
        echo '
        <div class="modal fade" id="wifi_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
                <div class="modal-dialog">
                        <div class="modal-content">
                                <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                        <h5 class="modal-title">'.$lang['wifi_settings'].'</h5>
                                </div>
                                <div class="modal-body">
                                        <p class="text-muted"> '.$lang['wifi_not_found'].' </p>
                                </div>
                                <div class="modal-footer">
                                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                </div>
                        </div>
                </div>
        </div>';
}

//ethernet model
echo '
<div class="modal fade" id="eth_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['ethernet_settings'].'</h5>
            </div>
            <div class="modal-body">
			   <div class="list-group" id="eth_status"></div>
           </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

// Scripts status model
echo '<div class="modal" id="status_scripts" tabindex="-1">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['scripts_status'].'</h5>
            		</div>
            		<div class="modal-body">
                		<p class="text-muted">'.$lang['scripts_status_text'].'</p>
                                <div id="gw_sc_scripts"></div>
            		</div>
                	<div class="modal-footer">
                        	<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            		</div>
        	</div>
    	</div>
</div>';

//Big Thank you
echo '
<div class="modal fade" id="big_thanks" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['credits'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['credits_text'].' </p>';
echo '	<div class="list-group">';
echo '

<a href="https://blog.getbootstrap.com/" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> Bootstrap <span class="float-right text-muted small"><em>...</em></span></a>
<a href="http://www.cssscript.com/pretty-checkbox-radio-inputs-bootstrap-awesome-bootstrap-checkbox-css" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> Pretty Checkbox <span class="float-right text-muted small"><em>...</em></span></a>
<a href="http://www.cssmatic.com/box-shadow" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> Box Shadow CSS <span class="float-right text-muted small"><em>...</em></span></a>
<a href="https://daneden.github.io/animate.css" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> Animate.css <span class="float-right text-muted small"><em>...</em></span></a>
<a href="https://www.mysensors.org" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> MySensors <span class="float-right text-muted small"><em>...</em></span></a>
<a href="http://www.pihome.eu" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> All others if forget them... <span class="float-right text-muted small"><em>...</em></span></a>
<a href="http://pihome.harkemedia.de" class="list-group-item"><i class="bi bi-life-preserver blueinfo"></i> RaspberryPi Home Automation <span class="float-right text-muted small"><em>...</em></span></a>
';
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Controller Zone Logs Modal
echo '
<div class="modal fade" id="sc_z_logs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['controller_zone_logs'].'</h5>
            		</div>
            		<div class="modal-body">
                                <p class="text-muted"> '.$lang['controller_zone_logs_text'].' </p>
                                <table class="table table-bordered">
                                        <thead>
                                                <tr>
                                                        <th class="col-2"><small>'.$lang['zone_name'].'</small></th>
                                                        <th class="col-2"><small>'.$lang['start_datetime'].'</small></th>
                                                        <th class="col-2"><small>'.$lang['start_cause'].'</small></th>
                                                        <th class="col-2"><small>'.$lang['stop_datetime'].'</small></th>
                                                        <th class="col-2"><small>'.$lang['stop_cause'].'</small></th>
                                                        <th class="col-2"><small>'.$lang['expected_end_date_time'].'</small></th>
                                                </tr>
                                        </thead>
                                        <tbody id="controller_zone_logs"></tbody>
                                </table>
			</div>
            		<div class="modal-footer">
                		<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            		</div>
        	</div>
    	</div>
</div>';

//System Uptime Modal
echo '
<div class="modal fade" id="s_uptime" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['system_uptime'].'</h5>
                        </div>
                        <div class="modal-body">';
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
				</div>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
        </div>
</div>';

// CPU Temperature History Modal
echo '
<div class="modal fade" id="cpu_temp_history" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['cpu_temperature'].'</h5>
                        </div>
                        <div class="modal-body">';
                                $query = "select * from messages_in where node_id = '0' order by datetime desc limit 5";
                                $results = $conn->query($query);
                                echo '<p class="text-muted"> '.$lang['cpu_temperature_text'].' </p>
		                <div id="cpu_temps">
                		        <div class="list-group">';
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
        	        	</div>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
        </div>
</div>';

// status sensors model
echo '<div class="modal" id="status_sensors" tabindex="-1">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['temperature_sensor'].'</h5>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted" id="status_sensors_text">'.$lang['please_wait_text'].'</p>
				<div class="list-group" id="sensor_temps">
				</div>
	                        <!-- /.list-group -->
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
function sensor_last24h(id, name, node_id, child_id)
{
        var myId = id;
        var myName = name;
	var myNodeId = node_id;
	var myChildId = child_id;

	$("#s_hist_id").val(myId);
        $("#s_hist_name").val(myName);

        var title1 = "<?php echo $lang['sensor_last24h'] ?>"
        var title2 = "<?php echo $lang['for_sensor_id'] ?>" + myNodeId + '\xa0(' + myChildId + '), ' + myName;
        $('#sensorhistory_title1').text(title1);
        $('#sensorhistory_title2').text(title2);
        $('#sensorhistory_text1').text('<?php echo $lang["please_wait_text"] ?>');
        $('#sensorhistory_value1').text("");
        $('#sensorhistory_text2').text("");
        $('#sensorhistory_value2').text("");
	$('#status_sensors').modal('hide');
        $('#sensors_history').modal('show');
}
</script>

<?php

// Sensors  model
echo '<div class="modal" id="sensors_history" tabindex="-1">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title"><div id="sensorhistory_title1"></div><div id="sensorhistory_title2"></div></h5>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted">
					<div class="row align-items-center" style="margin-top: -1.5rem">
						<div class="col-md-10" id="sensorhistory_text1"></div>
						<div class="col-md-2 text-center" style="font-size: 15px;" id="sensorhistory_value1"></div>
					</div>
					<div class="row align-items-center" style="margin-bottom: -1rem">
                                        	<div class="col-md-10" id="sensorhistory_text2"></div>
                                                <div class="col-md-2 text-center" style="font-size: 15px;" id="sensorhistory_value2"></div>
                                        </div>
                                </p>
				<input type="hidden" id="s_hist_id" name="s_hist_id" value="0">
                                <input type="hidden" id="s_hist_name" name="s_hist_name" value="0">
 	                        <table class="table table-fixed">
        	                        <thead>
                	                        <tr>
                                	                <th style="text-align:center; vertical-align:middle;" class="col-6"><small>'.$lang['reading_timestamp'].'</small></th>
                                        	        <th style="text-align:center; vertical-align:middle;" class="col-6"><small>'.$lang['value'].'</small></th>
	                                        </tr>
        	                        </thead>
					<tbody id= "result"></tbody>
                        	</table>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" id="button_history_hide">'.$lang['close'].'</button>
                        </div>
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
$("#button_history_hide").on("click", function(){
	$("#sensors_history").modal("show");
	$('#sensors_history').on("hidden.bs.modal", function (e) {
		$("#status_sensors").modal("show");
	})
	$("#sensors_history").modal("hide");
});
</script>
<?php

// zone state model
echo '<div class="modal" id="zones_states" tabindex="-1">
    <div class="modal-dialog modal-lg">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['zone_state'].'</h5>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted">'.$lang['zone_state_text'].'</p>
                                <table class="table table-fixed">
                                        <thead>
                                                <tr>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['zone_name'].'</small></th>
                                                        <th style="text-align:center;" class="col-3"><small>'.$lang['zone_mode'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['zone_active'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['active_schedule'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['sensor_reading'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['target_temp'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['cut_in_temp'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['cut_out_temp'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['zone_fault'].'</small></th>
                                                        <th style="text-align:center;" class="col-1"><small>'.$lang['sensor_fault'].'</small></th>
                                                </tr>
                                        </thead>
                                        <tbody id="z_states"></tbody>
                                </table>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';

// Relays Status Model
echo '<div class="modal" id="status_relays" tabindex="-1">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
				<button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
				<h4 class="modal-title">'.$lang['relays_status'].'</h4>
			</div>
			<div class="modal-body">
                                <p class="text-muted">'.$lang['relays_status_text'].'</p>
                                <table class="table table-fixed">
                                        <thead>
                                                <tr>
                                                        <th class="col-6"><small>'.$lang['relay_name'].'</small></th>
                                                        <th class="col-2" style="text-align:center; vertical-align:middle;" class="col-2"><small>'.$lang['state'].'</small></th>
                                                 </tr>
                                        </thead>
                                        <tbody id= "relay_states"></tbody>
                                </table>
			</div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
function relay_log(id, name, relay_id)
{
        var myId = id;
        var myName = name;
        var myRelayId = relay_id;

        $("#r_hist_id").val(myId);
        $("#r_hist_name").val(myName);

        var title1 = "<?php echo $lang['log_for_relay'] ?>"
        var title2 = myName;
        $('#relay_log_title1').text(title1);
        $('#relay_log_title2').text(title2);
        $('#relay_log_text1').text('<?php echo $lang["please_wait_text"] ?>');
        $('#status_relays').modal('hide');
        $('#relays_history').modal('show');
}
</script>

<?php
// Relay Log  model
echo '<div class="modal" id="relays_history" tabindex="-1">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title"><div id="relay_log_title1"></div><div id="relay_log_title2"></div></h5>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted" id="relay_log_text1"></p>
                                <input type="hidden" id="r_hist_id" name="r_hist_id" value="0">
                                <input type="hidden" id="r_hist_name" name="r_hist_name" value="0">
                                <table class="table table-fixed">
                                        <thead>
                                                <tr>
                                                        <th style="text-align:center; vertical-align:middle;" class="col-6"><small>'.$lang['relay_log_timestamp'].'</small></th>
                                                        <th style="text-align:center; vertical-align:middle;" class="col-2"><small>'.$lang['state'].'</small></th>
                                                        <th style="text-align:center; vertical-align:middle;" class="col-4"><small>'.$lang['zone_mode'].'</small></th>
                                                 </tr>
                                        </thead>
                                        <tbody id= "relay_log_result"></tbody>
                                </table>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" id="button_relay_log_hide">'.$lang['close'].'</button>
                        </div>
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
$("#button_relay_log_hide").on("click", function(){
        $("#relays_history").modal("show");
        $('#relays_history').on("hidden.bs.modal", function (e) {
                $("#status_relays").modal("show");
        })
        $("#relays_history").modal("hide");
});
</script>
<?php
}

if ($model_num == 2) {
//Theme
echo '<div class="modal fade" id="theme" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['theme'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
				<li><a class="dropdown-item" href="pdf_download.php?file=configure_themes.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['configure_themes'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['change_theme_settings_text'].'</p>';
                $query = "SELECT theme FROM system;";
                $result = $conn->query($query);
                $row = mysqli_fetch_array($result);
                $theme_id = $row['theme'];
                $query = "SELECT id, name FROM theme ORDER BY name ASC;";
                $results = $conn->query($query);
                echo '<div class="form-group" class="control-label"><label>'.$lang['theme_name'].'</label> <small class="text-muted"> </small>
	                <select class="form-select" type="text" id="theme_id" name="theme_id" >';
        	                while ($row = mysqli_fetch_array($results)) {
                	                echo '<option value="'.$row['id'].'" ' . ($theme_id==$row['id'] ? 'selected' : '') . '>'.$row['name'].'</option>';
                        	}
	                echo '</select>
        	        <div class="help-block with-errors"></div>
		</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_theme()">
            </div>
        </div>
    </div>
</div>';

//set GitHub Repository location
echo '<div class="modal fade" id="set_repository" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['github_repository'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['github_repository_text'].'</p>';
                $query = "SELECT id, status, name FROM repository;";
                $results = $conn->query($query);
		echo '<div class="form-group" class="control-label"><label>'.$lang['repository_url'].'</label> <small class="text-muted"> (Default Repository is - '.$lang['default_repository'].')</small>
                <select class="form-select" type="text" id="rep_id" name="rep_id" >';
                if ($results){
                        while ($frow=mysqli_fetch_array($results)) {
                                echo '<option value="'.$frow['id'].'" ' . ($frow['status']==1 ? 'selected' : '') . '>https://github.com/'.$frow['name'].'.git</option>';
                        }
                }
                echo '</select>
                        <div class="help-block with-errors"></div>
                </div>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['set_default'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_default()">
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_repository()">
            </div>
        </div>
    </div>
</div>';

//MaxAir Code Update
echo '
<div class="modal fade" id="maxair_update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['maxair_update'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
				<li><a class="dropdown-item" href="pdf_download.php?file=software_update_technical.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['software_update_technical'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> '.$lang['maxair_update_text'].' </p>';
            echo '</div>
            <div class="modal-footer">
                <input type="button" name="submit" value="'.$lang['update_check'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="check_updates()">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

// backup_image
echo '
<div class="modal fade" id="backup_image" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['pihome_backup'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_database_backup.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_database_backup'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_email_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> '.$lang['backup_location_text'].' </p>
                        <p class="text-muted"> '.$lang['pihome_backup_text'].' </p>
                        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
                        <div class="form-group" class="control-label"><label>'.$lang['email_address'].'</label> <small class="text-muted">'.$lang['pihome_backup_email_info'].'</small>
        			<input class="form-control" type="text" id="backup_email" name="backup_email" value="'.settings($conn, 'backup_email').'" placeholder="Email Address to Receive your Backup file">
                        	<div class="help-block with-errors"></div>
                        </div>
            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <button class="btn warning btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="backup_email_update()" data-confirm="'.$lang['update_email_address'].'">'.$lang['save'].'</button>
                        <a href="javascript:db_backup()" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm">'.$lang['backup_start'].'</a>
            </div>
        </div>
    </div>
</div>';

//Setup Auto Backup
echo '<div class="modal fade" id="auto_backup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            	<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                	<h5 class="modal-title">'.$lang['auto_backup'].'</h5>
	                <div class="dropdown float-right">
        	                <a class="" data-bs-toggle="dropdown" href="#">
                	                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        	</a>
	                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
        	                        <li><a class="dropdown-item" href="pdf_download.php?file=setup_database_backup.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_database_backup'].'</a></li>
                	                <li class="dropdown-divider"></li>
					<li><a class="dropdown-item" href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_email_notifications'].'</a></li>
                        	</ul>
                	</div>
            	</div>
            	<div class="modal-body">
                        <p class="text-muted">'.$lang['auto_backup_text'].'</p>';
                        $query = "SELECT * FROM email LIMIT 1;";
                        $result = $conn->query($query);
			if (mysqli_num_rows($result) == 0) {
				$disabled = "disabled";
	                        echo '<p class="text-info"><small>'.$lang['no_email'].'</small></p>';
			} else {
				$disabled = "";
			}

	                $query = "SELECT * FROM auto_backup LIMIT 1;";
        	        $result = $conn->query($query);
                	$row = mysqli_fetch_assoc($result);
			$f = explode(' ',$row['frequency']);
                        $r = explode(' ',$row['rotation']);

	                echo '<hr></hr><div class="form-group" class="control-label">
				<div class="row mb-3">
					<div class="col-3">
                				<div class="form-check">';
                					if ($row['enabled'] == '1'){
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox1" name="ab_enabled" checked>';
                        				} else {
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox1" name="ab_enabled">';
	                        			}
        	                			echo '<label class="form-check-label" for="checkbox1">'.$lang['enabled'].'</label>
						</div>
					</div>
                                	<div class="col-4">
                                        	<div class="form-check">';
                                                	if ($row['email_backup'] == '1'){
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox2" name="ab_email_database" checked '.$disabled.'>';
	                                                } else {
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox2" name="ab_email_database" '.$disabled.'>';
                	                                }
                        	                        echo '<label class="form-check-label" for="checkbox2">'.$lang['email_backup'].'</label>
                                	        </div>
	                                </div>
                                        <div class="col-4">
                                                <div class="form-check">';
                                                        if ($row['email_confirmation'] == '1'){
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox3" name="ab_email_confirmation" checked '.$disabled.'>';
                                                        } else {
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox3" name="ab_email_confirmation" '.$disabled.'>';
                                                        }
                                                        echo '<label class="form-check-label" for="checkbox3">'.$lang['email_confirmation'].'</label>
                                                </div>
                                        </div>
        	                </div>
             		</div>

	                <div class="form-group" class="control-label"><label><h5>'.$lang['frequency'].'</h5></label><small class="text-muted">&nbsp'.$lang['frequency_info'].'</small>
        	                <div class="row mb-3">
                	                <div class="col-2">
						<input class="form-control" placeholder="0" value="'.$f[0].'" id="fval1" name="fval1" autocomplete="off" required>
                                	        <div class="help-block with-errors"></div>
	                                </div>
        	                        <div class="col-4">
						<select class="form-select" type="text" id="fval2" name="fval2" onchange=set_frequency(this.options[this.selectedIndex].value)>
        	        	                        <option value="DAY" ' . ($f[1]=='DAY' ? 'selected' : '') . '>'.$lang['DAY'].'</option>
	                        	                <option value="WEEK" ' . ($f[1]=='WEEK' ? 'selected' : '') . '>'.$lang['WEEK'].'</option>
						</select>
						<input type="hidden" id="set_f" name="set_f" value="'.$f[1].'">
						<div class="help-block with-errors"></div>
					</div>
				</div>
			</div>

        	        <div class="form-group" class="control-label"><label><h5>'.$lang['rotation'].'</h5></label><small class="text-muted">&nbsp'.$lang['rotation_info'].'</small>
                	        <div class="row mb-3">
                        	        <div class="col-2">
						<input class="form-control" placeholder="0" value="'.$r[0].'" id="rval1" name="rval1" autocomplete="off" required>
                                        	<div class="help-block with-errors"></div>
	                                </div>
        	                        <div class="col-4">
                	                        <select class="form-select" type="text" id="rval2" name="rval2" onchange=set_rotation(this.options[this.selectedIndex].value)>
                        	                        <option value="DAY" ' . ($r[1]=='DAY' ? 'selected' : '') . '>'.$lang['DAY'].'</option>
                                	                <option value="WEEK" ' . ($r[1]=='WEEK' ? 'selected' : '') . '>'.$lang['WEEK'].'</option>
                                        	</select>
                                                <input type="hidden" id="set_r" name="set_r" value="'.$r[1].'">
		                                <div class="help-block with-errors"></div>
        	                        </div>
	                        </div>
                	</div>

	                <div class="form-group" class="control-label"><label><h5>'.$lang['destination'].'</h5></label><small class="text-muted">&nbsp'.$lang['destination_info'].'</small>
        	                <div class="row mb-3">
                	                <div class="col-12">
                        	                <input class="form-control" placeholder="" value="'.$row['destination'].'" id="dest" name="dest" autocomplete="off" required>
                                	        <div class="help-block with-errors"></div>
                                	</div>
				</div>
			</div>

            	</div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_auto_backup()">
            </div>
        </div>
    </div>
</div>';

//restore backup
echo '
<div class="modal fade" id="backup_restore" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                        <h5 class="modal-title">'.$lang['restore_db'].'</h5>
                        <div class="dropdown float-right">
                                <a class="" data-bs-toggle="dropdown" href="#">
                                        <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                        <li><a class="dropdown-item" href="pdf_download.php?file=setup_database_restore.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_database_restore'].'</a></li>
                                </ul>
                        </div>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted"> '.$lang['restore_db_text'].' </p>';
                                echo '<ul class="list-group">';
					$fileList = array();

					//default update and backup directory
					$dir = "/var/www/MySQL_Database/database_backups";

					//check if Auto Backup directory has been configured
					$query = "SELECT destination FROM auto_backup LIMIT 1";
					$result = $conn->query($query);
					$rowcount=mysqli_num_rows($result);
					//if configured get .gz files into array
					if ($rowcount > 0) {
					        $auto_backup = mysqli_fetch_array($result);
				        	$destination = $auto_backup['destination'];
						if (strcmp(substr($destination, -1), "/") !== 0) { $destination = $destination."/"; }
					        $files = glob($destination.'*.gz');
					        foreach ($files as $file)  {
                					$fileList[filemtime($file)] = $file;;
					        }
					}

					//check the default update and backup folder and find any .zip (update) or .gz (backup) files and add to array
					$files = glob('/var/www/MySQL_Database/database_backups/*.zip');
					foreach ($files as $file) {
					    $fileList[filemtime($file)] = $file;;
					}
					$files = glob('/var/www/MySQL_Database/database_backups/*.gz');
					foreach ($files as $file) {
					    $fileList[filemtime($file)] = $file;
					}

					//sort the array by date
					ksort($fileList);
					$fileList = array_reverse($fileList, TRUE);
					foreach ($fileList as $file) {
						echo '<button type="button" class="warning list-group-item list-group-item-action" style="font-size: 0.6rem;" onclick="restore_db(`'.$file.'`);" data-confirm="'.$lang['restore_db_warning'].$lang["restore_db_delay"].'">'.$file.'</button>';
					}
				echo '</ul>
			</div>
            		<div class="modal-footer">
                		<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            		</div>
        	</div>
    	</div>
</div>';

//Setup Auto Image
$query = "SELECT table_name FROM information_schema.tables WHERE table_schema = 'maxair' AND table_name = 'auto_image';";
$result = $conn->query($query);
if (mysqli_num_rows($result) != 0) {
	echo '<div class="modal fade" id="auto_image" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog">
        		<div class="modal-content">
	            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
        	        		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                			<h5 class="modal-title">'.$lang['auto_image'].'</h5>
	                		<div class="dropdown float-right">
        	                		<a class="" data-bs-toggle="dropdown" href="#">
                	                		<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
	                        		</a>
		                        	<ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
        		                        	<li><a class="dropdown-item" href="pdf_download.php?file=setup_image_file_creation.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_image_file_creation'].'</a></li>
                		                	<li class="dropdown-divider"></li>
							<li><a class="dropdown-item" href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_email_notifications'].'</a></li>
	                        		</ul>
        	        		</div>
            			</div>
	            		<div class="modal-body">
        	                	<p class="text-muted">'.$lang['auto_image_text'].'</p>';
	                	        $query = "SELECT * FROM email LIMIT 1;";
        	                	$result = $conn->query($query);
					if (mysqli_num_rows($result) == 0) {
						$disabled = "disabled";
	        	        	        echo '<p class="text-info"><small>'.$lang['no_email'].'</small></p>';
					} else {
						$disabled = "";
					}

		        	        $query = "SELECT * FROM auto_image LIMIT 1;";
        		        	$result = $conn->query($query);
	                		$row = mysqli_fetch_assoc($result);
					$f = explode(' ',$row['frequency']);
        		                $r = explode(' ',$row['rotation']);

		        	        echo '<hr></hr><div class="form-group" class="control-label">
						<div class="row mb-3">
							<div class="col-3">
                						<div class="form-check">';
                							if ($row['enabled'] == '1'){
										echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox1" name="ai_enabled" checked>';
	                        					} else {
										echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox1" name="ai_enabled">';
			                        			}
        			                			echo '<label class="form-check-label" for="checkbox1">'.$lang['enabled'].'</label>
								</div>
							</div>
                                	        	<div class="col-4">
                                        	        	<div class="form-check">';
                                                	        	if ($row['email_confirmation'] == '1'){
										echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox3" name="ai_email_confirmation" checked '.$disabled.'>';
		                                                        } else {
										echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox3" name="ai_email_confirmation" '.$disabled.'>';
                		                                        }
                        		                                echo '<label class="form-check-label" for="checkbox3">'.$lang['email_confirmation'].'</label>
                                		                </div>
                                        		</div>
	        	                	</div>
        	     			</div>
	        	        	<div class="form-group" class="control-label"><label><h5>'.$lang['frequency'].'</h5></label><small class="text-muted">&nbsp'.$lang['frequency_info'].'</small>
        	        	        	<div class="row mb-3">
                	        	        	<div class="col-2">
								<input class="form-control" placeholder="0" value="'.$f[0].'" id="fval1" name="fval1" autocomplete="off" required>
                                	        		<div class="help-block with-errors"></div>
		                                	</div>
        		                        	<div class="col-4">
								<select class="form-select" type="text" id="fval2" name="fval2" onchange=set_ai_frequency(this.options[this.selectedIndex].value)>
        	        		                        	<option value="DAY" ' . ($f[1]=='DAY' ? 'selected' : '') . '>'.$lang['DAY'].'</option>
		                        		                <option value="WEEK" ' . ($f[1]=='WEEK' ? 'selected' : '') . '>'.$lang['WEEK'].'</option>
								</select>
								<input type="hidden" id="set_ai_f" name="set_ai_f" value="'.$f[1].'">
								<div class="help-block with-errors"></div>
							</div>
						</div>
					</div>
	        	        	<div class="form-group" class="control-label"><label><h5>'.$lang['rotation'].'</h5></label><small class="text-muted">&nbsp'.$lang['rotation_info'].'</small>
        	        	        	<div class="row mb-3">
                	        	        	<div class="col-2">
								<input class="form-control" placeholder="0" value="'.$r[0].'" id="rval1" name="rval1" autocomplete="off" required>
	                                	        	<div class="help-block with-errors"></div>
		                                	</div>
	        		                        <div class="col-4">
        	        		                        <select class="form-select" type="text" id="rval2" name="rval2" onchange=set_ai_rotation(this.options[this.selectedIndex].value)>
                	        		                        <option value="DAY" ' . ($r[1]=='DAY' ? 'selected' : '') . '>'.$lang['DAY'].'</option>
                        	        		                <option value="WEEK" ' . ($r[1]=='WEEK' ? 'selected' : '') . '>'.$lang['WEEK'].'</option>
                                	        		</select>
                                        	        	<input type="hidden" id="set_ai_r" name="set_ai_r" value="'.$r[1].'">
			                                	<div class="help-block with-errors"></div>
		        	                        </div>
			                        </div>
                			</div>
	                		<div class="form-group" class="control-label"><label><h5>'.$lang['destination'].'</h5></label><small class="text-muted">&nbsp'.$lang['destination_info'].'</small>
        	                		<div class="row mb-3">
                	                		<div class="col-12">
                        	                		<input class="form-control" placeholder="" value="'.$row['destination'].'" id="dest" name="dest" autocomplete="off" required>
		                                	        <div class="help-block with-errors"></div>
        		                        	</div>
						</div>
					</div>
		            	</div>
				<div class="modal-footer">
               				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
		                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_auto_image()">
	        		</div>
        		</div>
    		</div>
	</div>';
}

//user accounts model
echo '
<div class="modal fade" id="user_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['user_accounts'].'</h5>
		                <div class="dropdown float-right">
                			<a class="" data-bs-toggle="dropdown" href="#">
                                		<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                                	</a>
                                	<ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                        	<li><a class="dropdown-item" href="pdf_download.php?file=setup_user_accounts.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_user_accounts'].'</a></li>
                                	</ul>
                        	</div>
            		</div>
            		<div class="modal-body">
                        	<p class="text-muted"> '.$lang['user_accounts_text'].' </p>';
				echo '<ul class="list-group">';
					$query = "SELECT * FROM user";
					$results = $conn->query($query);
					while ($row = mysqli_fetch_assoc($results)) {
					        $full_name=$row['fullname'];
					        $username=$row['username'];
					        if ($_SESSION['user_id'] == $row['id']) { $username .= " (Logged On)"; }
					        if($row['account_enable'] == 1) {
					                $content_msg="You are about to DELETE an ENABLED USER";
				        	} else {
				                	$content_msg="You are about to DELETE a CURRENTLY DISABLED USER";
					        }
						echo '<li class="list-group-item">
							<div class="d-flex justify-content-between">
        							<div href="settings.php?uid='.$row['id'].'  class="list-group-item"> <i class="bi bi-person-fill blue" style="font-size: 1.2rem;"></i> '.$username.'</div>
                						<div class="text-muted small">
									<a href="user_accounts.php?uid='.$row["id"].'" style="text-decoration: none;"><button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs login"><span class="bi bi-pencil"></span></button>&nbsp</a>';
                								if ($_SESSION['user_id'] != $row['id']) {
											echo '<button class="first btn btn-danger btn-xs" onclick="del_user('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button></a>';
                								} else {
                        								echo '<button class="btn btn-danger btn-xs disabled"><span class="bi bi-trash-fill black"></span></button>';
                								}
								echo '</div>
							</div>
						</li>';
					}
				echo '</ul>
			</div>
            		<div class="modal-footer">
                		<a href="user_accounts.php?uid=0"><button class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm">'.$lang['add_user'].'</button></a>
                		<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            		</div>
        	</div>
    	</div>
</div>';

//Software Install Modal
echo '
<div class="modal fade" id="sw_install" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['software_install'].'</h5>
                                <div class="dropdown float-right">
                                        <a class="" data-bs-toggle="dropdown" href="#">
                                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                				<li><a class="dropdown-item" href="pdf_download.php?file=software_install.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['software_install'].'</a></li>
                                                <li class="dropdown-divider"></li>
                				<li><a class="dropdown-item" href="pdf_download.php?file=software_install_technical.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['software_install_technical'].'</a></li>
                                                <li class="dropdown-divider"></li>
                				<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_ha_integration.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_ha_integration'].'</a></li>
                                        </ul>
                                </div>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted">'.$lang['install_software_text'].'</p>
                                <div class="list-group">';
                                        $installpath = "/var/www/api/enable_rewrite.sh";
                                        $installname = "Install Apache ReWrite";
                                        if (file_exists("/etc/apache2/mods-available/rewrite.load")) {
                                                $prompt = $lang['re_install'];
                                        } else {
                                                $prompt = $lang['install'];
                                        }
                                        echo '<div class="list-group-item">
						<div class="d-flex justify-content-between">
							<div>
                                        			<i class="bi bi-terminal-fill green" style="font-size: 2rem;"></i> '.$installname.'
							</div>
							<div>
			                                        <span class="text-muted small"><button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm"
                                        			onclick="install_software(`'.$installpath.'`)">'.$prompt.'</button></span>
							</div>
						</div>
                                        	<p class="text-muted">Install ReWrite for Apache Web Server</p>
					</div>';
                                        $path = '/var/www/add_on';
                                        $dir = new DirectoryIterator($path);
                                        foreach ($dir as $fileinfo) {
						$installed = 0;
                                                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                                                        $installpath = $path."/".$fileinfo->getFilename()."/install.sh";
                                                        if (file_exists($installpath)) {
                                                                $contents = file_get_contents($installpath);
                                                                $searchfor = 'app_name';
                                                                $pattern = preg_quote($searchfor, '/');
                                                                $pattern = "/^.*$pattern.*\$/m";
                                                                if(preg_match_all($pattern, $contents, $matches)){
                                                                        $str = implode("\n", $matches[0]);
                                                                        $name = explode(':',$str)[1];
                                                                } else {
                                                                        $name = $fileinfo->getFilename();
                                                                }
                                                                $searchfor = 'app_description';
                                                                $pattern = preg_quote($searchfor, '/');
                                                                $pattern = "/^.*$pattern.*\$/m";
                                                                if(preg_match_all($pattern, $contents, $matches)){
                                                                        $str = implode("\n", $matches[0]);
                                                                        $description = explode(':',$str)[1];
                                                                } else {
                                                                        $description = '';
                                                                }
                                                                $searchfor = 'service_name';
                                                                $pattern = preg_quote($searchfor, '/');
                                                                $pattern = "/^.*$pattern.*\$/m";
                                                                if(preg_match_all($pattern, $contents, $matches)){
                                                                        $str = implode("\n", $matches[0]);
                                                                        $service_name = explode(':',$str);
                                                                        $rval=my_exec("/bin/systemctl status " . $service_name[1]);
                                                                        if ($rval['stdout']=='') { $installed = 0; } else { $installed = 1; }
                                                                } else {
                                                                        $instaleed = 2;
                                                                }
                                                                echo '<div class="list-group-item">
                                                                        <div class="d-flex justify-content-between">
                                                                                <div>
                                                                                        <i class="bi bi-terminal-fill green" style="font-size: 2rem;"></i> '.$name.'
                                                                                </div>
                                                                                <div>';
                                                                                        if ($installed == 0) {
                                                                echo '<span class="text-muted small"><button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm"
                                                                                                onclick="install_software(`'.$installpath.'`)">'.$lang['install'].'</button></span>';
                                                                                        } elseif ($installed == 1) {
                                                                                                echo '<span class="text"><p> '.$lang['already_installed'].'</p></span>';
                                                                                        } else {
                                                                                                echo '<span class="text"><p> '.$lang['no_installer'].'</p></span>';
                                                                                        }
                                                                                echo '</div>
                                                                        </div>
                                                                        <p class="text-muted">'.$description.'</p>
                                                                </div>';
                                                        }
                                                }
                                        }
                                echo '</div>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal-fade -->
';

//Setup Database Cleanup intervals
echo '<div class="modal fade" id="db_cleanup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['db_cleanup'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['db_cleanup_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-2 text-center"><small>'.$lang['table_name'].'</small></th>
                                <th class="col-1 text-center"><small>'.$lang['db_cleanup_value'].'</small></th>
                                <th class="col-2 text-center"><small>'.$lang['db_cleanup_period'].'</small></th>
                        </tr>';
                        $query = "SELECT * FROM db_cleanup LIMIT 1;";
                        $result = $conn->query($query);
                        $db_row = mysqli_fetch_assoc($result);
                        $query = "SELECT column_name
                                FROM INFORMATION_SCHEMA.COLUMNS
                                WHERE TABLE_SCHEMA = 'maxair' AND table_name = 'db_cleanup' AND ordinal_position > 3
                                ORDER BY ordinal_position;";
                        $results = $conn->query($query);
                        $x = 0;
                        while ($row = mysqli_fetch_assoc($results)) {
                                $col_name = $row["column_name"];
                                $per_int = $db_row[$col_name];
                                $pieces = explode(" ", $per_int);
                                $period = $pieces[0];
                                $interval = $pieces[1];
                                echo '<tr>
                                        <td>'.$row["column_name"].'</td>
                			<td><input id="period'.$x.'" type="text" class="float-left text" style="border: none" name="period'.$x.'"  size="3" value="'.$period.'" placeholder="Period" required></td>
                                        <td><select class="form-select" type="text" id="ival'.$x.'" name="ival'.$x.'" onchange=set_interval('.$x.')>
                                                <option value="HOUR" ' . ($interval=='HOUR' ? 'selected' : '') . '>'.$lang['HOUR'].'</option>
                                                <option value="DAY" ' . ($interval=='DAY' ? 'selected' : '') . '>'.$lang['DAY'].'</option>
                                                <option value="WEEK" ' . ($interval=='WEEK' ? 'selected' : '') . '>'.$lang['WEEK'].'</option>
                                                <option value="MONTH" ' . ($interval=='MONTH' ? 'selected' : '') . '>'.$lang['MONTH'].'</option>
                                        </select></td>
                                        <input type="hidden" id="set_interval'.$x.'" name="set_interval_type" value="'.$interval.'">
                                </tr>';
                                $x = $x + 1;
                        }
                echo '</table>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_db_cleanup()">
            </div>
        </div>
    </div>
</div>';

//set max cpu temperature
echo '<div class="modal fade" id="max_cpu_temp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['max_cpu_temp'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['max_cpu_temp_text'].'</p>';
                $query = "SELECT max_cpu_temp FROM system LIMIT 1;";
                $result = $conn->query($query);
                $row = mysqli_fetch_array($result);
                echo '<div class="form-group" class="control-label"><label>'.$lang['temperature'].'</label> <small class="text-muted"> </small>
                <select class="form-select" type="text" id="m_cpu_temp" name="m_cpu_temp" >';
                for ($x = 40; $x <=  70; $x = $x + 5) {
                        echo '<option value="'.$x.'" ' . ($x==$row['max_cpu_temp'] ? 'selected' : '') . '>'.$x.'&deg;</option>';
                }
                echo '</select>
                        <div class="help-block with-errors"></div>
                </div>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_max_cpu_temp()">
            </div>
        </div>
    </div>
</div>';

// Software Install Add
echo '<div class="modal" id="add_install">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
        <button type="button" class="close" data-bs-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['installing_sw'].'</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted">'.$lang['installing_sw_info'].'</p>';
        $output = file_get_contents('/var/www/cron/sw_install.txt');
        echo '<textarea id="install_status_text" style="background-color: black;color:#fff;height: 500px; min-width: 100%"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" onclick="sw_install_close()">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

//Page Refresh Rate
echo '<div class="modal fade" id="change_refresh" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['change_refresh'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['change_refresh_text'].'</p>';
                $query = "SELECT page_refresh FROM system LIMIT 1;";
                $result = $conn->query($query);
                $row = mysqli_fetch_array($result);
                echo '<div class="form-group" class="control-label"><label>'.$lang['seconds'].'</label> <small class="text-muted"> </small>
                <select class="form-select" type="text" id="new_refresh" name="new_refresh" >';
                for ($x = 1; $x <=  15; $x++) {
                        echo '<option value="'.$x.'" ' . ($x==$row['page_refresh'] ? 'selected' : '') . '>'.$x.'</option>';
                }
                echo '</select>
                        <div class="help-block with-errors"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_refresh()">
            </div>
        </div>
    </div>
</div>';
?>
<script>
function set_default()
{
 document.getElementById("rep_id").value = 1;
}

function set_interval(id)
{
 var id_text = id;

 var e = document.getElementById("ival" + id_text);
 var f = document.getElementById("set_interval" + id_text);

 f.value = e.value;
}

function sw_install_close()
{
        $('#sw_install').modal('hide');
        $('#add_install').modal('hide');
}

function set_frequency(f)
{
 document.getElementById("set_f").value = f;
//console.log(f);
}

function set_rotation(r)
{
 document.getElementById("set_r").value = r;
// console.log(r);
}

function set_ai_frequency(f)
{
 document.getElementById("set_ai_f").value = f;
//console.log(f);
}

function set_ai_rotation(r)
{
 document.getElementById("set_ai_r").value = r;
// console.log(r);
}
</script>
<?php

//Services
echo '<div class="modal fade" id="show_services" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['services'].'</h5>
            		</div>
            		<div class="modal-body">
                		<p class="text-muted">'.$lang['services_text'].'</p>';
    				$SArr=[['name'=>'Apache','service'=>'apache2.service'],
           				['name'=>'MySQL','service'=>'mysql.service'],
           				['name'=>'MariaDB','service'=>'mariadb.service'],
           				['name'=>'PiHome JOBS','service'=>'pihome_jobs_schedule.service'],
           				['name'=>'HomeAssistant Integration','service'=>'HA_integration.service'],
	   				['name'=>'Amazon Echo','service'=>'pihome_amazon_echo.service'],
           				['name'=>'Homebridge','service'=>'homebridge.service'],
           				['name'=>'Autohotspot','service'=>'autohotspot.service']];
    				echo '<div class="list-group">';
    					$index = 0;
    					foreach($SArr as $SArrKey=>$SArrVal) {
    						$rval=my_exec("/bin/systemctl status " . $SArrVal['service']);
                                                $sval=my_exec("/bin/journalctl -u " . $SArrVal['service'] . " -n 10 --no-pager");
                                                $per='';
                                                similar_text($sval['stderr'],'Hint: You are currently not seeing messages from other users and the system. Users in the \'systemd-journal\' group can see all messages. Pass -q to turn off this notice. No journal files were opened due to insufficient permissions.',$per);
                                                if($per>80) {
                                                        $sval['stdout']=$web_user_name.' cannot access journalctl.<br/><br/>If you would like it to be able to, run<br/><code>sudo usermod -a -G systemd-journal '.$web_user_name.'</code><br/>and then reboot the RPi.';
						}
        					echo '<span class="list-group-item">
							<div class="d-flex justify-content-start">';
        							echo $SArrVal['name'];
							echo '</div>
							<div class="d-flex justify-content-between">
        							<span class="text-muted small">
									<div id="service_'.$index++.'">';
			        						if($rval['stdout']=='') {
											$stat = 'Error: ' . $rval['stderr'];
            										echo $stat;
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
            										echo $stat;
        									}
									echo '</div>
        							</span>
        							<span class="text-muted small" style="width:200px;text-align:right;" data-bs-toggle="tooltip" title="'.$lang['services_info'].'">
                                         				<button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs" onclick="service_info(`'.$SArrVal['name'].'`,`'.$SArrVal['service'].'`,`'.$stat.'`,`'.$sval['stdout'].'`);"><span class="bi bi-info-circle"></span></button>
        							</span>
        						</div>
						</span>';
    					}
    				echo '</div>
	                        <!-- /.list-group -->
            		</div>
                        <!-- /.modal-body -->
			<div class="modal-footer">
                		<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            		</div>
        	</div>
                <!-- /.modal-content -->
    	</div>
        <!-- /.modal-dialogue -->
</div>
<!-- /.modal -->';
?>
<script>
function service_info(name, service, status, journal)
{
	var myName = name;
        var myService = service;
	var myStatus = status;
	var myJournal = journal;

//	console.log(myName);
//	console.log(myService);
//	console.log(myStatus);

	document.getElementById('serv_status').setAttribute('name','serv_status_' + myService);
	document.getElementById("serv_name").innerHTML = myName;
        document.getElementById("serv_status").innerHTML = myStatus
	if (myService.includes("pihome.") || myService.includes("pihome_") || myService.includes("homebridge") || myService.includes("autohotspot") || myService.includes("HA_Integration")) {
		document.getElementById("serv_buttons").style.setProperty('display', 'block');
		document.getElementById('button_serv_start').setAttribute('onclick','service_action(`' + myService + '`,28)');
		document.getElementById('button_serv_stop').setAttribute('onclick','service_action(`' + myService + '`,29)');
		document.getElementById('button_serv_enable').setAttribute('onclick','service_action(`' + myService + '`,30)');
		document.getElementById('button_serv_disable').setAttribute('onclick','service_action(`' + myService + '`,31)');
	} else {
		document.getElementById("serv_buttons").style.setProperty('display', 'none');
	}

        document.getElementById("serv_journal").innerHTML = myJournal;
	if (myService.includes("echo.service")) {
                document.getElementById("serv_echo").style.setProperty('display', 'block');
	} else {
		document.getElementById("serv_echo").style.setProperty('display', 'none');
	}
        $('#serv_status').load("ajax_fetch_data.php?id=" + myService + "&type=32").fadeIn("slow");

        $('#show_services').modal('hide');
        $('#service_info').modal('show');

}
</script>
<?php

//Service Info
echo '<div class="modal fade" id="service_info" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['services_info'].'</h5>
                        </div>
                        <div class="modal-body">
					<div class="list-group">
						<span class="list-group-item">
							<div id="serv_name"></div>
							<span class="text-muted small">
								<div id="serv_status"></div>
							</span>
						</span>
						<div style="display:none" id="serv_buttons">
							<span class="list-group-item" style="height:55px;">&nbsp;
                                        			<span class="float-right text-muted small">
                							<button class="btn btn-warning-'.theme($conn, $theme, 'color').' btn-xs" id="button_serv_start" onclick="">'.$lang['start'].'</button>
                							<button class="btn btn-warning-'.theme($conn, $theme, 'color').' btn-xs" id="button_serv_stop" onclick="">'.$lang['stop'].'</button>
              								<button class="btn btn-warning-'.theme($conn, $theme, 'color').' btn-xs" id="button_serv_enable" onclick="">'.$lang['enable'].'</button>
              								<button class="btn btn-warning-'.theme($conn, $theme, 'color').' btn-xs" id="button_serv_disable" onclick="">'.$lang['disable'].'</button>
                                        			</span>
                                			</span>
						</div>
						<span class="list-group-item" style="overflow:hidden;">&nbsp
                                			Status: <i class="bi bi-bootstrap-reboot orange" style="font-size: 1.2rem;"></i><br/>
                                			<span class="text-muted small">
								<div id="serv_journal"></div>
								<br/>
                                			</span>
                        			</span>
						<div style="display:none" id="serv_echo">
                                			<span class="list-group-item" style="overflow:hidden;">Install Service:
                                        			<span class="float-right text-muted small">Edit /lib/systemd/system/pihome_amazon_echo.service<br/>
                                        	        		<code>sudo nano /lib/systemd/system/pihome_amazon_echo.service</code>
                                                			<br/>
                                                			Put the following contents in the file:
                                                			<br/>
                                         		       		(make sure the -u is supplied to python
                                                			<br/>
                                                			to ensure the output is not buffered and delayed)<br/>
                                                			<code>[Unit]
                                                			<br/>
                                                			Description=Amazon Echo Service<br/>
                                               		 		After=multi-user.target<br/>
                                                			<br/>
                                                			[Service]
                                                			<br/>
                                                			Type=simple
                                                			<br/>
                                                			ExecStart=/usr/bin/python -u /var/www/add_on/amazon_echo/echo_pihome.py<br/>
                                                			Restart=on-abort
                                                			<br/>
                                                			<br/>
                                                			[Install]
                                                			<br/>
                                                			WantedBy=multi-user.target</code>
                                                			<br/>
                                                			Update the file permissions:
                                                			<br/>
                                                			<code>sudo chmod 644 /lib/systemd/system/pihome_amazon_echo.service</code>
                                                			<br/>
                                                			Update systemd:
                                                			<br/>
                                                			<code>sudo systemctl daemon-reload</code>
                                                			<br/>
                                                			<br/>
                                                			For improved performance, lower SD card writes:
                                                			<br/>
                                                			Edit /etc/systemd/journald.conf
                                                			<br/>
                                                			<code>sudo nano /etc/systemd/journald.conf</code>
                                                			<br/>
                                                			Edit/Add the following:
                                                			<br/>
                                                			<code>Storage=volatile
                                                			<br/>
                                                			RuntimeMaxUse=50M</code>
                                                			<br/>
                                                			Then restart journald:
                                                			<br/>
                                                			<code>sudo systemctl restart systemd-journald</code>
                                                			<br/>
                                                			Refer to: <a href="www.freedesktop.org/software/systemd/man/journald.conf.html">www.freedesktop.org/software/systemd/man/journald.conf.html</a>
                                                			<br/>
                                        			</span>
                                			</span>
						</div>
					</div>
					<!-- /.list-group -->
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" id="button_service_hide">'.$lang['close'].'</button>
                        </div>
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialogue -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
$("#button_service_hide").on("click", function(){
        $("#service_info").modal("show");
        $('#service_info').on("hidden.bs.modal", function (e) {
                $("#show_services").modal("show");
        })
        $("#service_info").modal("hide");
});
</script>

<script>
function service_action(service, type)
{
        var myService = service;
        var myType = type;
        var str = "ajax_fetch_data.php?id=" + myService + "&type=" + myType

        $('#serv_status').load(str).fadeIn("slow");
//	console.log(str);
}
</script>
<?php
}

if ($model_num == 3) {
//System Mode
$system_mode = settings($conn, 'mode');
echo '
<div class="modal fade" id="change_system_mode" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['system_controller_mode'].'</h5>
            </div>
            <div class="modal-body">
                                <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
                                <div class="form-group" class="control-label"><label>'.$lang['system_mode'].'</label>
                                <select class="form-select" type="text" id="new_mode" name="new_mode">
                                <option value="0" ' . ($system_mode==0 || $system_mode=='0' ? 'selected' : '') . '>'.$lang['boiler'].' ('.$lang['cyclic'].' control)</option>
                                <option value="1" ' . ($system_mode==1 || $system_mode=='1' ? 'selected' : '') . '>'.$lang['hvac'].' ('.$lang['cyclic'].' control)</option>
                                <option value="2" ' . ($system_mode==2 || $system_mode=='2' ? 'selected' : '') . '>'.$lang['boiler'].' ('.$lang['button'].' control)</option>
                                <option value="3" ' . ($system_mode==3 || $system_mode=='3' ? 'selected' : '') . '>'.$lang['hvac'].' ('.$lang['button'].' control)</option>
                                </select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_system_mode()">
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['unit_change'].'</h5>
            </div>
            <div class="modal-body">
				<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['units'].'</label>
				<select class="form-select" type="number" id="new_units" name="new_units">
				<option value="0" ' . ($c_f==0 || $c_f=='0' ? 'selected' : '') . '>'.$lang['unit_celsius'].'</option>
				<option value="1" ' . ($c_f==1 || $c_f=='1' ? 'selected' : '') . '>'.$lang['unit_fahrenheit'].'</option>
				</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_units()">
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['language'].'</h5>
            </div>
            <div class="modal-body">
				<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['language'].'</label>
				<select class="form-select" type="text" id="new_lang" name="new_lang">';
				$languages = ListLanguages($language);
				for ($x = 0; $x <=  count($languages) - 1; $x++) {
					echo '<option value="'.$languages[$x][0].'" ' . ($language==$languages[$x][0] ? 'selected' : '') . '>'.$languages[$x][1].'</option>';
				}
				echo '</select>
                <div class="help-block with-errors"></div></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_lang()">
            </div>
        </div>
    </div>
</div>';

//Graph model
echo '<div class="modal fade" id="zone_graph" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['graph_settings'].'</h5>
                		<div class="dropdown float-right">
                        		<a class="" data-bs-toggle="dropdown" href="#">
                                		<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        		</a>
                        		<ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                		<li><a class="dropdown-item" href="pdf_download.php?file=displaying_temperature_sensors_graphs.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['displaying_temperature_sensors_graphs'].'</a></li>
                        		</ul>
                		</div>
            		</div>
            		<div class="modal-body" id="zone_graph_body">
				<p class="text-muted">'.$lang['graph_settings_text'].'</p>';
				$query = "SELECT id, sensor_id, name, graph_num, min_max_graph, name AS sname
					FROM sensors
					WHERE sensor_type_id = 1
                                        UNION
                                        SELECT sensor_average.id, sensor_average.sensor_id, zone.name, sensor_average.graph_num, sensor_average.min_max_graph, zone.name AS sname FROM sensor_average, zone WHERE sensor_average.zone_id = zone.id
					UNION
					SELECT 0 AS id, '' AS sensor_id, 'Outside Temp' AS name, -1 AS graph_num, enable_archive AS min_max_graph, 'zzz' AS sname
					FROM weather
					ORDER BY sname ASC;";
				$results = $conn->query($query);
				echo '<table class="table table-bordered">
    					<tr>
        					<th class="col-8"><small>'.$lang['sensor_name'].'</small></th>
        					<th class="col-2"><small>'.$lang['graph_num'].'</small></th>
        					<th class="col-2"><small>'.$lang['min_max_graph'].'</small></th>
    					</tr>';
					while ($row = mysqli_fetch_assoc($results)) {
                                                $s_name = $row["name"];
						if (strpos($row['sensor_id'], "zavg_") !== false) { $s_name = $s_name." (Avg)"; }
    						if ($row['min_max_graph'] == 1) { $enabled_check = 'checked'; } else { $enabled_check = ''; }
    						echo '<tr>
            						<td>'.$s_name.'</td>';
							if ($row['graph_num'] == -1) {
								echo '<td>N/A</td>';
							} else {
            							echo '<td><input id="graph_num'.$row["id"].'" type="text" class="float-left text" style="border: none" name="graph_num" size="3" value="'.$row["graph_num"].'" placeholder="Graph Number" required></td>';
							}
            						echo '<td style="text-align:center; vertical-align:middle;">';
                						if ($row['graph_num'] != 0) {
									echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_enable_graph'.$row['id'].'" name="enable_archive" value="1" '.$enabled_check.'>';
								} else {
									echo "N/A";
								}
            						echo '</td>
        					</tr>';
					}
				echo '</table>
			</div>
            		<div class="modal-footer">
                		<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                		<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="setup_graph()">
            		</div>
        	</div>
    	</div>
</div>';

//Sensor Limits model
echo '
<div class="modal fade" id="sensor_limits" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_limits_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_sensor_notifications.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_sensor_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted">'.$lang['sensor_limits_settings_text'].'</p>';
$query = "SELECT sensor_limits.id, sensors.name, sensor_limits.min, sensor_limits.max, status FROM sensors, sensor_limits WHERE sensor_limits.sensor_id = sensors.id ORDER BY name asc";
$results = $conn->query($query);
echo '  <table class="table table-bordered">
    <tr>
        <th class="col-4"><small>'.$lang['sensor_name'].'</small></th>
        <th class="col-1"><small>'.$lang['min_val'].'</small></th>
        <th class="col-1"><small>'.$lang['max_val'].'</small></th>
        <th class="col-1"><small>'.$lang['enabled'].'</small></th>
        <th class="col-4"><small>'.$lang['edit_delete'].'</small></th>
    </tr>';
while ($row = mysqli_fetch_assoc($results)) {
    if ($row['status'] == 1) { $enabled = $lang['yes']; } else { $enabled = $lang['no']; }
    echo '
        <tr>
            <td style="text-align:center; vertical-align:middle;">'.$row["name"].'</td>
            <td style="text-align:center; vertical-align:middle;">'.$row["min"].'</td>
            <td style="text-align:center; vertical-align:middle;">'.$row["max"].'</td>
            <td style="text-align:center; vertical-align:middle;">'.$enabled.'</td>
            <td><a href="sensor_limits.php?id='.$row["id"].'" style="text-decoration: none;"><button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs"><i class="bi bi-pencil"></i></button></a>&nbsp;
                <button class="btn warning btn-danger btn-xs" onclick="delete_sensor_limits('.$row["id"].');" data-confirm="'.$lang['confirm_del_sensor_limit'].'"><span class="bi bi-trash-fill black"></span></button> </a>
	    </td>
        </tr>';
}
echo '
</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
		<a class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" href="sensor_limits.php">'.$lang['sensor_limits_add'].'</a>
            </div>
        </div>
    </div>
</div>';

//livetemp zone
echo '
<div class="modal fade" id="livetemp_zone" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['change_livetemp_zone'].'</h5>
            </div>
            <div class="modal-body">
            	<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
                <div class="form-group" class="control-label"><label>'.$lang['zone'].'</label>
                        <select class="form-select" type="text" id="livetemp_zone_id" name="livetemp_zone_id" >';
				//get the id of the zone currently attached to the live temperature control
				$query = "SELECT zone_id FROM livetemp LIMIT 1;";
				$result = $conn->query($query);
				$lrow=mysqli_fetch_array($result);
                                //get list of zone names to display
                                $query = "SELECT id, name FROM zone;";
                                $results = $conn->query($query);
                                if ($results){
                                       	while ($zrow=mysqli_fetch_array($results)) {
						echo '<option value="'.$zrow['id'].'" ' . ($zrow['id']==$lrow['zone_id'] ? 'selected' : '') . '>'.$zrow['name'].'</option>';
                                        }
                                }
                       	echo '</select>
                	<div class="help-block with-errors"></div>
		</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_livetemp_zone()">
            </div>
        </div>
    </div>
</div>';

//network settings model
echo '
<div class="modal fade" id="network_setting" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
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
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <input class="form-control" type="hidden" id="n_int_type" name="n_int_type" value="'.$rowArray[0]['interface_type'].'"/>
        <div class="form-group" class="control-label"><label>'.$lang['network_interface'].'</label>
                <select class="form-select" type="text" id="n_int_num" name="n_int_num" onchange=change(this.options[this.selectedIndex].value)>
                <option value=0>wlan0</option>
                <option value=1>wlan1</option>
                <option value=2>eth0</option>
                <option value=3>eth1</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_primary'].'</label>
                <select class="form-select" type="text" id="n_primary" name="n_primary">
                <option value=0>No</option>
                <option selected value=1>Yes</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_ap_mode'].'</label>
                <select class="form-select" type="text" id="n_ap_mode" name="n_ap_mode">
                <option selected value=0>No</option>
                <option value=1>Yes</option>
                </select>
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_mac_address'].'</label>
                <input class="form-control" type="text" id="n_mac" name="n_mac" value="'.$rowArray[0]['mac_address'].'" placeholder="MAC Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_hostname'].'</label>
                <input class="form-control" type="text" id="n_hostname" name="n_hostname" value="'.$rowArray[0]['hostname'].'" placeholder="Hostname">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_ip_address'].'</label>
                <input class="form-control" type="text" id="n_ip" name="n_ip" value="'.$rowArray[0]['ip_address'].'" placeholder="IP Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_gateway_address'].'</label>
                <input class="form-control" type="text" id="n_gateway" name="n_gateway" value="'.$rowArray[0]['gateway_address'].'" placeholder="Gateway Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_net_mask'].'</label>
                <input class="form-control" type="text" id="n_net_mask" name="n_net_mask" value="'.$rowArray[0]['net_mask'].'" placeholder="Net Mask">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_dns1_address'].'</label>
                <input class="form-control" type="text" id="n_dns1" name="n_dns1" value="'.$rowArray[0]['dns1_address'].'" placeholder="DNS1 Address">
                <div class="help-block with-errors">
                </div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['network_dns2_address'].'</label>
                <input class="form-control" type="text" id="n_dns2" name="n_dns2" value="'.$rowArray[0]['dns2_address'].'" placeholder="DNS2 Address">
                <div class="help-block with-errors">
                </div>
        </div>
        </div>
        <!-- /.modal-body -->
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="setup_network()">
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['email_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                        	<li><a class="dropdown-item" href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_email_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">';
$gquery = "SELECT * FROM email";
$gresult = $conn->query($gquery);
$erow = mysqli_fetch_array($gresult);

echo '<p class="text-muted">'.$lang['email_text'].'</p>';
echo '
	<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	<div class="form-group" class="control-label">
		<div class="form-check">';
			if ($erow['status'] == '1'){
  				echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox3" name="status" checked>';
			} else {
        	                echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox3" name="status">';
			}
	  		echo '<label class="form-check-label" for="checkbox3">'.$lang['email_enable'].'</label>
		</div>
	</div>
	<div class="form-group" class="control-label"><label>'.$lang['email_smtp_server'].'</label>
	<input class="form-control" type="text" id="e_smtp" name="e_smtp" value="'.$erow['smtp'].'" placeholder="e-mail SMTP Server Address ">
	<div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['port'].'</label>
        <select class="form-select" type="text" id="e_port" name="e_port" >
                <option value="25" ' . ($erow['port']==25 ? 'selected' : '') . '>25</option>
                <option value="465" ' . ($erow['port']==465 ? 'selected' : '') . '>465</option>
        </select>
        <div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['email_username'].' </label>
	<input class="form-control" type="text" id="e_username" name="e_username" value="'.$erow['username'].'" placeholder="Username for e-mail Server">
	<div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['email_password'].' </label>
	<input class="form-control" type="password" id="e_password" name="e_password" value="'.$erow['password'].'" placeholder="Password for e-mail Server">
	<div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['email_from_address'].' </label>
	<input class="form-control" type="text" id="e_from_address" name="e_from_address" value="'.$erow['from'].'" placeholder="From e-mail" >
	<div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['email_to_address'].' </label>
	<input class="form-control" type="text" id="e_to_address" name="e_to_address" value="'.$erow['to'].'" placeholder="To e-mail Address">
	<div class="help-block with-errors"></div></div>';

echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="setup_email()">
            </div>
        </div>
    </div>
</div>';

//Time Zone
echo '
<div class="modal fade" id="time_zone" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['time_zone'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted"> '.$lang['time_zone_text'].'</p>
				<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label"><label>'.$lang['time_zone'].'</label>
				<select class="form-select" type="number" id="new_time_zone" name="new_time_zone" >
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
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_timezone()">
            </div>
        </div>
    </div>
</div>';

//Schedule Test Set False Time
echo '<div class="modal fade" id="schedule_test" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['schedule_test'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['schedule_test_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-lg-2 text-center"><small>'.$lang['false_time'].'</small></th>
                                <th class="col-lg-1 text-center"><small>'.$lang['enabled'].'</small></th>
                        </tr>';
//                        $query = "SELECT test_mode, test_run_time FROM system LIMIT 1;";
		        $query = "SELECT `test_mode`, DATE_FORMAT(`test_run_time`, '%Y-%m-%d %H:%i') AS test_run_time FROM `system`;";
                        $result = $conn->query($query);
                        $row = mysqli_fetch_assoc($result);
                        if ($row['test_mode'] == 3) { $enabled_check = 'checked'; } else { $enabled_check = ''; }
                        echo '<tr>
                                <td><input class="form-control" type="datetime-local" id="false_time" name="false_time" value="'.$row['test_run_time'].'" placeholder="Start Time" required></td>
            			<td style="text-align:center; vertical-align:middle;">
               				<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="test_time_enabled" name="test_time_enabled" value="1" '.$enabled_check.'>
            			</td>
                       </tr>
                </table>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_false_time()">
            </div>
        </div>
    </div>
</div>';

// Jobs Schedule modal
echo '
<div class="modal fade" id="jobs_schedule" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['schedule_jobs'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=task_scheduling.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['task_scheduling'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['schedule_jobs_info'].' </p>';
$query = "SELECT id, job_name, script, enabled, log_it, time FROM jobs WHERE job_name NOT LIKE 'shutdown_reboot' ORDER BY id asc";
$results = $conn->query($query);
echo '<br><table>
    <tr>
        <th class="col-3">'.$lang['jobs_name'].'</th>
        <th class="col-4">'.$lang['jobs_script'].'</th>
        <th class="col-1">'.$lang['enabled'].'</th>
        <th class="col-1">'.$lang['jobs_log'].'</th>
        <th class="col-2">'.$lang['jobs_time'].'</th>
        <th class="col-1"></th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    if ($row["log_it"] == 0) { $log_check = ''; } else { $log_check = 'checked'; }
    if ($row["enabled"] == 0) { $enabled_check = ''; $content_msg = "You are about to DELETE an Disabled Job";} else { $enabled_check = 'checked'; $content_msg = "You are about to DELETE an Enabled Job"; }
    echo '
        <tr>
            <td><input id="jobs_name'.$row["id"].'" type="value" class="form-control float-right" style="border: none" value="'.$row["job_name"].'" placeholder="Job Name"></td>
            <td><input id="jobs_script'.$row["id"].'" type="value" class="form-control float-right" style="border: none" value="'.$row["script"].'" placeholder="Job Script"></td>
            <td style="text-align:center; vertical-align:middle;">
               <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_enabled'.$row["id"].'" name="enabled" value="1" '.$enabled_check.'>
            </td>
            <td style="text-align:center; vertical-align:middle;">
               <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_log'.$row["id"].'" name="logit" value="1" '.$log_check.'>
            </td>
            <td><input id="jobs_time'.$row["id"].'" type="value" class="form-control float-right" style="border: none" value="'.$row["time"].'" placeholder="Run Job Every"></td>
            <td><button class="btn warning btn-danger btn-xs" onclick="delete_job('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button> </a></td>
        </tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_job">'.$lang['add_job'].'</button>
                <input type="button" name="submit" value="'.$lang['apply'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="schedule_jobs()">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Job Schedule
echo '
<div class="modal fade" id="add_job" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_new_job'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_new_job_info_text'].'</p>
	<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
      	<div class="form-group" class="control-label">
             <div class="form-check">
                 <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" id="checkbox_enabled" class="styled" type="checkbox" value="0" name="status" Enabled>
                 <label class="form-check-label" for="checkbox_enabled"> '.$lang['enabled'].'</label>
             </div>
        </div>
	<div class="form-group" class="control-label"><label>'.$lang['jobs_name'].'</label> <small class="text-muted">'.$lang['jobs_name_info'].'</small>
	<input class="form-control" type="text" id="job_name" name="job_name" value="" placeholder="'.$lang['jobs_name'].'">
	<div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['jobs_script'].'</label> <small class="text-muted">'.$lang['jobs_script_info'].'</small>
        <input class="form-control" type="text" id="job_script" name="job_script" value="" placeholder="'.$lang['jobs_script'].'">
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['jobs_time'].'</label> <small class="text-muted">'.$lang['jobs_time_info'].'</small>
        <input class="form-control" type="text" id="job_time" name="job_time" value="" placeholder="'.$lang['jobs_time'].'">
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label">
             <div class="form-check">
                 <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" id="checkbox_logit" class="styled" type="checkbox" value="0" name="status" Enabled>
                 <label class="form-check-label" for="checkbox_logit"> '.$lang['jobs_log'].'</label>
             </div>
        </div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_job()">
            </div>
        </div>
    </div>
</div>';

//Set Buttons model
echo '<div class="modal fade" id="set_buttons" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['set_buttons'].'</h5>
                <div class="dropdown float-right">
                	<a class="" data-bs-toggle="dropdown" href="#">
                        	<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                        	<li><a class="dropdown-item" href="pdf_download.php?file=setup_user_accounts.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_user_accounts'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['set_buttons_text'].'</p>
		<input type="hidden" id="button_page_1" name="button_page_1" value="'.$lang['home_page'].'">
                <input type="hidden" id="button_page_2" name="button_page_2" value="'.$lang['onetouch_page'].'">';
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-lg-3 text-center"><small>'.$lang['button_name'].'</small></th>
                                <th class="col-lg-2 text-center"><small>'.$lang['toggle_page'].'</small></th>
                                <th class="col-lg-1 text-center"><small>'.$lang['index_number'].'</small></th>
    			</tr>';
	                $query = "SELECT * FROM button_page ORDER BY index_id ASC;";
        	        $results = $conn->query($query);
			while ($row = mysqli_fetch_assoc($results)) {
				if ($row["page"] == 1) { $button_text = $lang['home_page']; } else { $button_text = $lang['onetouch_page']; }
                                echo '<tr>
                                        <td>'.$row["name"].'</td>
					<td><input type="button" id="page_button'.$row["id"].'" value="'.$button_text.'" class="btn btn-primary-'.theme($conn, $theme, 'color').' d-grid gap-2 col-12 mx-auto" onclick="set_button_text('.$row["id"].')"></td>
           		                <td><input id="index'.$row["id"].'" type="text" class="float-left text" style="border: none" name="index_id"  size="3" value="'.$row["index_id"].'" placeholder="Index ID" required></td>
					<input type="hidden" id="page_type'.$row["id"].'" name="page_type" value="'.$row["page"].'">
        			</tr>';
			}
            	echo '</table>
	    </div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                	<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="set_buttons()">
            </div>
        </div>
    </div>
</div>';

//Set Graph Categories to display
echo '<div class="modal fade" id="display_graphs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['enable_graphs'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['enable_graphs_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-lg-2 text-center"><small>'.$lang['graph'].'</small></th>
                                <th class="col-lg-1 text-center"><small>'.$lang['enabled'].'</small></th>
                        </tr>';
			$myArr = [];
			array_push($myArr, $lang['graph_temperature'], $lang['graph_humidity'], $lang['graph_addon_usage'], $lang['graph_saving'], $lang['graph_system_controller_usage'], $lang['graph_battery_usage']);
                        // only display min_max selection if archiving has been enabled
                        $query = "SELECT archive_enable FROM graphs LIMIT 1;";
                        $result = $conn->query($query);
                        $row = mysqli_fetch_assoc($result);
                        if ($row["archive_enable"] != 0) {
                                array_push($myArr, $lang['graph_min_max']);
                                $graph_cnt = 6;
                        } else {
                                $graph_cnt = 5;
                        }
                        $query = "SELECT mask FROM graphs LIMIT 1;";
                        $result = $conn->query($query);
			$row = mysqli_fetch_assoc($result);
			$m = 1;
                        for ($x = 0; $x <= $graph_cnt; $x++) {
                        	if ($row['mask'] & $m) { $enabled_check = 'checked'; } else { $enabled_check = ''; }
                                echo '<tr>
                                        <td>'.$myArr[$x].'</td>
            				<td style="text-align:center; vertical-align:middle;">
						<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_graph'.$x.'" name="enabled" value="1" '.$enabled_check.'>
            				</td>
                                </tr>';
				$m = $m << 1;
                        }
                echo '</table>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="enable_graphs()">
            </div>
        </div>
    </div>
</div>';
?>
<script>
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
<?php

//Add Theme
echo '<div class="modal fade" id="add_theme" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['theme_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=configure_themes.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['configure_themes'].'</a></li>
                    	</ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['theme_settings_text'].'</p>';
		$query = "SELECT * FROM theme ORDER BY name ASC";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-2" style="text-align:center;"><small>'.$lang['name'].'</small></th>
                                <th class="col-2" style="text-align:center;"><small>'.$lang['justify'].'</small></th>
                                <th class="col-1" style="text-align:center;"><small>'.$lang['theme_color'].'</small></th>
                                <th class="col-1" style="text-align:center;"><small>'.$lang['text_color'].'</small></th>
                                <th class="col-2" style="text-align:center;"><small>'.$lang['tile_size'].'</small></th>
                                <th class="col-4"></th>
    			</tr>';
			while ($row = mysqli_fetch_assoc($results)) {
                                echo '<tr>
                                        <td style="text-align:center; vertical-align:middle;">'.$row["name"].'</small></td>
                                        <td class="text-capitalize" style="text-align:center; vertical-align:middle;">'.$row["row_justification"].'</td>';
                                        echo '<td class="text-capitalize" style="text-align:center; vertical-align:middle;">'.$row["color"].'</td>';
                                        $color = explode('-', $row["text_color"]);
                                        echo '<td class="text-capitalize" style="text-align:center; vertical-align:middle;">'.$color[1].'</td>';
					if ($row["tile_size"] == 0) { $tile_size = explode(' ', $lang['standard_button'])[0]; } else { $tile_size = explode(' ', $lang['wide_button'])[0]; }
					echo '<td class="text-capitalize" style="text-align:center; vertical-align:middle;">'.$tile_size.'</td>
	    				<td style="text-align:center; vertical-align:middle;"><a href="theme.php?id='.$row["id"].'"><button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-xs"><i class="bi bi-pencil"></i></button></a>&nbsp;&nbsp';
                                        echo '<button class="btn warning btn-danger btn-xs" onclick="delete_theme('.$row["id"].');" data-confirm="'.$lang['confirm_del_theme'].'"><span class="bi bi-trash-fill black"></span></button></td>';


        			echo '</tr>';
			}
		echo '</table>
	    </div>
	        <!-- /.modal-body -->
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                	<a class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' login btn-sm" href="theme.php">'.$lang['add_theme'].'</a>
            </div>
        </div>
    </div>
</div>';

//Open Weather Modal
echo '<div class="modal fade" id="modal_openweather" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['openweather_settings'].'</h5>
                        </div>
                        <div class="modal-body">';
                                $openweather_api = settings($conn,'openweather_api');
                                $country = settings($conn,'country');
    				$city = settings($conn,'city');
                                $zip = settings($conn,'zip');
				if($city != NULL) {
					$city_checked = "checked";
                                        $zip_checked = "";
					$CityZip_label = "City";
					$CityZip = 0;
				} else {
                                        $city_checked = "";
                                        $zip_checked = "checked";
                                        $CityZip_label = "Zip";
                                        $CityZip = 1;
				}
                                echo '<input type="hidden" id="CityZip" name="CityZip" value="'.$CityZip.'"/>
                                <input type="hidden" id="country_code" name="country_code" value="'.$country.'"/>
                                <input type="hidden" id="city" name="city" value="'.$city.'"/>
                                <input type="hidden" id="zip" name="zip" value="'.$zip.'"/>
            			<p class="text-muted">'.$lang['openweather_text1'].' <a class="green" target="_blank" href="http://OpenWeatherMap.org">'.$lang['openweather_text2'].'</a> '.$lang['openweather_text3'].'
            			<p>'.$lang['openweather_text4'].'
	                        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
            				<div class="form-group">
                				<label>Country</label>&nbsp;(ISO-3166-1: Alpha-2 Codes)
                				<select class="form-control" id="sel_Country" name="sel_Country" onchange=set_country(this.options[this.selectedIndex].value)>
                    					<option value="AF" ' . ($country=="AF" ? 'selected' : '') . '>Afghanistan</option>
                    					<option value="AX" ' . ($country=="AX" ? 'selected' : '') . '>Åland Islands</option>
                    					<option value="AL" ' . ($country=="AL" ? 'selected' : '') . '>Albania</option>
					                <option value="DZ" ' . ($country=="DZ" ? 'selected' : '') . '>Algeria</option>
                    					<option value="AS" ' . ($country=="AS" ? 'selected' : '') . '>American Samoa</option>
                    					<option value="AD" ' . ($country=="AD" ? 'selected' : '') . '>Andorra</option>
                    					<option value="AO" ' . ($country=="AO" ? 'selected' : '') . '>Angola</option>
							<option value="AI" ' . ($country=="AI" ? 'selected' : '') . '>Anguilla</option>
                    					<option value="AQ" ' . ($country=="AQ" ? 'selected' : '') . '>Antarctica</option>
                    					<option value="AG" ' . ($country=="AG" ? 'selected' : '') . '>Antigua and Barbuda</option>
                    					<option value="AR" ' . ($country=="AR" ? 'selected' : '') . '>Argentina</option>
                    					<option value="AM" ' . ($country=="AM" ? 'selected' : '') . '>Armenia</option>
                    					<option value="AW" ' . ($country=="AW" ? 'selected' : '') . '>Aruba</option>
                    					<option value="AU" ' . ($country=="AU" ? 'selected' : '') . '>Australia</option>
                    					<option value="AT" ' . ($country=="AT" ? 'selected' : '') . '>Austria</option>
                    					<option value="AZ" ' . ($country=="AZ" ? 'selected' : '') . '>Azerbaijan</option>
                    					<option value="BS" ' . ($country=="BS" ? 'selected' : '') . '>Bahamas</option>
                    					<option value="BH" ' . ($country=="BH" ? 'selected' : '') . '>Bahrain</option>
                    					<option value="BD" ' . ($country=="BD" ? 'selected' : '') . '>Bangladesh</option>
                    					<option value="BB" ' . ($country=="BB" ? 'selected' : '') . '>Barbados</option>
                    					<option value="BY" ' . ($country=="BY" ? 'selected' : '') . '>Belarus</option>
                    					<option value="BE" ' . ($country=="BE" ? 'selected' : '') . '>Belgium</option>
                    					<option value="BZ" ' . ($country=="BZ" ? 'selected' : '') . '>Belize</option>
                    					<option value="BJ" ' . ($country=="BJ" ? 'selected' : '') . '>Benin</option>
                    					<option value="BM" ' . ($country=="BM" ? 'selected' : '') . '>Bermuda</option>
                    					<option value="BT" ' . ($country=="BT" ? 'selected' : '') . '>Bhutan</option>
                    					<option value="BO" ' . ($country=="BO" ? 'selected' : '') . '>Bolivia, Plurinational State of</option>
                    					<option value="BQ" ' . ($country=="BQ" ? 'selected' : '') . '>Bonaire, Sint Eustatius and Saba</option>
                    					<option value="BA" ' . ($country=="BA" ? 'selected' : '') . '>Bosnia and Herzegovina</option>
                    					<option value="BW" ' . ($country=="BW" ? 'selected' : '') . '>Botswana</option>
                    					<option value="BV" ' . ($country=="BV" ? 'selected' : '') . '>Bouvet Island</option>
                    					<option value="BR" ' . ($country=="BR" ? 'selected' : '') . '>Brazil</option>
                    					<option value="IO" ' . ($country=="IO" ? 'selected' : '') . '>British Indian Ocean Territory</option>
                    					<option value="BN" ' . ($country=="BN" ? 'selected' : '') . '>Brunei Darussalam</option>
                    					<option value="BG" ' . ($country=="BG" ? 'selected' : '') . '>Bulgaria</option>
                    					<option value="BF" ' . ($country=="BF" ? 'selected' : '') . '>Burkina Faso</option>
                    					<option value="BI" ' . ($country=="BI" ? 'selected' : '') . '>Burundi</option>
                    					<option value="KH" ' . ($country=="KH" ? 'selected' : '') . '>Cambodia</option>
                    					<option value="CM" ' . ($country=="CM" ? 'selected' : '') . '>Cameroon</option>
                    					<option value="CA" ' . ($country=="CA" ? 'selected' : '') . '>Canada</option>
                    					<option value="CV" ' . ($country=="CV" ? 'selected' : '') . '>Cape Verde</option>
                    					<option value="KY" ' . ($country=="KY" ? 'selected' : '') . '>Cayman Islands</option>
                    					<option value="CF" ' . ($country=="CF" ? 'selected' : '') . '>Central African Republic</option>
                    					<option value="TD" ' . ($country=="TD" ? 'selected' : '') . '>Chad</option>
                    					<option value="CL" ' . ($country=="CL" ? 'selected' : '') . '>Chile</option>
                    					<option value="CN" ' . ($country=="CN" ? 'selected' : '') . '>China</option>
                    					<option value="CX" ' . ($country=="CX" ? 'selected' : '') . '>Christmas Island</option>
                    					<option value="CC" ' . ($country=="CC" ? 'selected' : '') . '>Cocos (Keeling) Islands</option>
                    					<option value="CO" ' . ($country=="CO" ? 'selected' : '') . '>Colombia</option>
                    					<option value="KM" ' . ($country=="KM" ? 'selected' : '') . '>Comoros</option>
                    					<option value="CG" ' . ($country=="CG" ? 'selected' : '') . '>Congo</option>
                    					<option value="CD" ' . ($country=="CD" ? 'selected' : '') . '>Congo, the Democratic Republic of the</option>
                    					<option value="CK" ' . ($country=="CK" ? 'selected' : '') . '>Cook Islands</option>
                    					<option value="CR" ' . ($country=="CR" ? 'selected' : '') . '>Costa Rica</option>
                    					<option value="CI" ' . ($country=="CI" ? 'selected' : '') . '>Côte d\'Ivoire</option>
                    					<option value="HR" ' . ($country=="HR" ? 'selected' : '') . '>Croatia</option>
                    					<option value="CU" ' . ($country=="CU" ? 'selected' : '') . '>Cuba</option>
                    					<option value="CW" ' . ($country=="CW" ? 'selected' : '') . '>Curaçao</option>
                                                        <option value="CY" ' . ($country=="CY" ? 'selected' : '') . '>Cyprus</option>
                                                        <option value="CZ" ' . ($country=="CZ" ? 'selected' : '') . '>Czech Republic</option>
                                                        <option value="DK" ' . ($country=="DK" ? 'selected' : '') . '>Denmark</option>
                                                        <option value="DJ" ' . ($country=="DJ" ? 'selected' : '') . '>Djibouti</option>
                                                        <option value="DM" ' . ($country=="DM" ? 'selected' : '') . '>Dominica</option>
                                                        <option value="DO" ' . ($country=="DO" ? 'selected' : '') . '>Dominican Republic</option>
                                                        <option value="EC" ' . ($country=="EC" ? 'selected' : '') . '>Ecuador</option>
                                                        <option value="EG" ' . ($country=="EG" ? 'selected' : '') . '>Egypt</option>
                                                        <option value="SV" ' . ($country=="SV" ? 'selected' : '') . '>El Salvador</option>
                                                        <option value="GQ" ' . ($country=="GQ" ? 'selected' : '') . '>Equatorial Guinea</option>
                                                        <option value="ER" ' . ($country=="ER" ? 'selected' : '') . '>Eritrea</option>
                                                        <option value="EE" ' . ($country=="EE" ? 'selected' : '') . '>Estonia</option>
                                                        <option value="ET" ' . ($country=="ET" ? 'selected' : '') . '>Ethiopia</option>
                                                        <option value="FK" ' . ($country=="FR" ? 'selected' : '') . '>Falkland Islands (Malvinas)</option>
                                                        <option value="FO" ' . ($country=="FO" ? 'selected' : '') . '>Faroe Islands</option>
                                                        <option value="FJ" ' . ($country=="FJ" ? 'selected' : '') . '>Fiji</option>
                                                        <option value="FI" ' . ($country=="FI" ? 'selected' : '') . '>Finland</option>
                                                        <option value="FR" ' . ($country=="FR" ? 'selected' : '') . '>France</option>
                                                        <option value="GF" ' . ($country=="GF" ? 'selected' : '') . '>French Guiana</option>
                                                        <option value="PF" ' . ($country=="PF" ? 'selected' : '') . '>French Polynesia</option>
                                                        <option value="TF" ' . ($country=="TF" ? 'selected' : '') . '>French Southern Territories</option>
                                                        <option value="GA" ' . ($country=="GA" ? 'selected' : '') . '>Gabon</option>
                                                        <option value="GM" ' . ($country=="GM" ? 'selected' : '') . '>Gambia</option>
                                                        <option value="GE" ' . ($country=="GE" ? 'selected' : '') . '>Georgia</option>
                                                        <option value="DE" ' . ($country=="DE" ? 'selected' : '') . '>Germany</option>
                                                        <option value="GH" ' . ($country=="GH" ? 'selected' : '') . '>Ghana</option>
                                                        <option value="GI" ' . ($country=="GI" ? 'selected' : '') . '>Gibraltar</option>
                                                        <option value="GR" ' . ($country=="GR" ? 'selected' : '') . '>Greece</option>
                                                        <option value="GL" ' . ($country=="GL" ? 'selected' : '') . '>Greenland</option>
                                                        <option value="GD" ' . ($country=="GD" ? 'selected' : '') . '>Grenada</option>
                                                        <option value="GP" ' . ($country=="GP" ? 'selected' : '') . '>Guadeloupe</option>
                                                        <option value="GU" ' . ($country=="GU" ? 'selected' : '') . '>Guam</option>
                                                        <option value="GT" ' . ($country=="GT" ? 'selected' : '') . '>Guatemala</option>
                                                        <option value="GG" ' . ($country=="GG" ? 'selected' : '') . '>Guernsey</option>
                                                        <option value="GN" ' . ($country=="GN" ? 'selected' : '') . '>Guinea</option>
                                                        <option value="GW" ' . ($country=="GW" ? 'selected' : '') . '>Guinea-Bissau</option>
                                                        <option value="GY" ' . ($country=="GY" ? 'selected' : '') . '>Guyana</option>
                                                        <option value="HT" ' . ($country=="HT" ? 'selected' : '') . '>Haiti</option>
                                                        <option value="HM" ' . ($country=="HM" ? 'selected' : '') . '>Heard Island and McDonald Islands</option>
                                                        <option value="VA" ' . ($country=="VA" ? 'selected' : '') . '>Holy See (Vatican City State)</option>
                                                        <option value="HN" ' . ($country=="HN" ? 'selected' : '') . '>Honduras</option>
                                                        <option value="HK" ' . ($country=="HK" ? 'selected' : '') . '>Hong Kong</option>
                                                        <option value="HU" ' . ($country=="HU" ? 'selected' : '') . '>Hungary</option>
                                                        <option value="IS" ' . ($country=="IS" ? 'selected' : '') . '>Iceland</option>
                                                        <option value="IN" ' . ($country=="IN" ? 'selected' : '') . '>India</option>
                                                        <option value="ID" ' . ($country=="ID" ? 'selected' : '') . '>Indonesia</option>
                                                        <option value="IR" ' . ($country=="IR" ? 'selected' : '') . '>Iran, Islamic Republic of</option>
                                                        <option value="IQ" ' . ($country=="IQ" ? 'selected' : '') . '>Iraq</option>
                                                        <option value="IE" ' . ($country=="IE" ? 'selected' : '') . '>Ireland</option>
                                                        <option value="IM" ' . ($country=="IM" ? 'selected' : '') . '>Isle of Man</option>
                                                        <option value="IL" ' . ($country=="IL" ? 'selected' : '') . '>Israel</option>
                                                        <option value="IT" ' . ($country=="IT" ? 'selected' : '') . '>Italy</option>
                                                        <option value="JM" ' . ($country=="JM" ? 'selected' : '') . '>Jamaica</option>
                                                        <option value="JP" ' . ($country=="JP" ? 'selected' : '') . '>Japan</option>
                                                        <option value="JE" ' . ($country=="JE" ? 'selected' : '') . '>Jersey</option>
                                                        <option value="JO" ' . ($country=="JO" ? 'selected' : '') . '>Jordan</option>
                                                        <option value="KZ" ' . ($country=="KZ" ? 'selected' : '') . '>Kazakhstan</option>
                                                        <option value="KE" ' . ($country=="KE" ? 'selected' : '') . '>Kenya</option>
                                                        <option value="KI" ' . ($country=="KI" ? 'selected' : '') . '>Kiribati</option>
                                                        <option value="KP" ' . ($country=="KP" ? 'selected' : '') . '>Korea, Democratic People\'s Republic of</option>
                                                        <option value="KR" ' . ($country=="KR" ? 'selected' : '') . '>Korea, Republic of</option>
                                                        <option value="KW" ' . ($country=="KW" ? 'selected' : '') . '>Kuwait</option>
                                                        <option value="KG" ' . ($country=="KG" ? 'selected' : '') . '>Kyrgyzstan</option>
                                                        <option value="LA" ' . ($country=="LA" ? 'selected' : '') . '>Lao People\'s Democratic Republic</option>
                                                        <option value="LV" ' . ($country=="LV" ? 'selected' : '') . '>Latvia</option>
                                                        <option value="LB" ' . ($country=="LB" ? 'selected' : '') . '>Lebanon</option>
                                                        <option value="LS" ' . ($country=="LS" ? 'selected' : '') . '>Lesotho</option>
                                                        <option value="LR" ' . ($country=="LR" ? 'selected' : '') . '>Liberia</option>
                                                        <option value="LY" ' . ($country=="LY" ? 'selected' : '') . '>Libya</option>
                                                        <option value="LI" ' . ($country=="LI" ? 'selected' : '') . '>Liechtenstein</option>
                                                        <option value="LT" ' . ($country=="LT" ? 'selected' : '') . '>Lithuania</option>
                                                        <option value="LU" ' . ($country=="LU" ? 'selected' : '') . '>Luxembourg</option>
                                                        <option value="MO" ' . ($country=="MO" ? 'selected' : '') . '>Macao</option>
                                                        <option value="MK" ' . ($country=="MK" ? 'selected' : '') . '>Macedonia, the former Yugoslav Republic of</option>
                                                        <option value="MG" ' . ($country=="MG" ? 'selected' : '') . '>Madagascar</option>
                                                        <option value="MW" ' . ($country=="MW" ? 'selected' : '') . '>Malawi</option>
                                                        <option value="MY" ' . ($country=="MY" ? 'selected' : '') . '>Malaysia</option>
                                                        <option value="MV" ' . ($country=="MV" ? 'selected' : '') . '>Maldives</option>
                                                        <option value="ML" ' . ($country=="ML" ? 'selected' : '') . '>Mali</option>
                                                        <option value="MT" ' . ($country=="MT" ? 'selected' : '') . '>Malta</option>
                                                        <option value="MH" ' . ($country=="MH" ? 'selected' : '') . '>Marshall Islands</option>
                                                        <option value="MQ" ' . ($country=="MQ" ? 'selected' : '') . '>Martinique</option>
                                                        <option value="MR" ' . ($country=="MR" ? 'selected' : '') . '>Mauritania</option>
                                                        <option value="MU" ' . ($country=="MU" ? 'selected' : '') . '>Mauritius</option>
                                                        <option value="YT" ' . ($country=="YT" ? 'selected' : '') . '>Mayotte</option>
                                                        <option value="MX" ' . ($country=="MX" ? 'selected' : '') . '>Mexico</option>
                                                        <option value="FM" ' . ($country=="FM" ? 'selected' : '') . '>Micronesia, Federated States of</option>
                                                        <option value="MD" ' . ($country=="MD" ? 'selected' : '') . '>Moldova, Republic of</option>
                                                        <option value="MC" ' . ($country=="MC" ? 'selected' : '') . '>Monaco</option>
                                                        <option value="MN" ' . ($country=="MN" ? 'selected' : '') . '>Mongolia</option>
                                                        <option value="ME" ' . ($country=="ME" ? 'selected' : '') . '>Montenegro</option>
                                                        <option value="MS" ' . ($country=="MS" ? 'selected' : '') . '>Montserrat</option>
                                                        <option value="MA" ' . ($country=="MA" ? 'selected' : '') . '>Morocco</option>
                                                        <option value="MZ" ' . ($country=="MZ" ? 'selected' : '') . '>Mozambique</option>
                                                        <option value="MM" ' . ($country=="MM" ? 'selected' : '') . '>Myanmar</option>
                                                        <option value="NA" ' . ($country=="NA" ? 'selected' : '') . '>Namibia</option>
                                                        <option value="NR" ' . ($country=="NR" ? 'selected' : '') . '>Nauru</option>
                                                        <option value="NP" ' . ($country=="NP" ? 'selected' : '') . '>Nepal</option>
                                                        <option value="NL" ' . ($country=="NL" ? 'selected' : '') . '>Netherlands</option>
                                                        <option value="NC" ' . ($country=="NC" ? 'selected' : '') . '>New Caledonia</option>
                                                        <option value="NZ" ' . ($country=="NZ" ? 'selected' : '') . '>New Zealand</option>
                                                        <option value="NI" ' . ($country=="NI" ? 'selected' : '') . '>Nicaragua</option>
                                                        <option value="NE" ' . ($country=="NE" ? 'selected' : '') . '>Niger</option>
                                                        <option value="NG" ' . ($country=="NG" ? 'selected' : '') . '>Nigeria</option>
                                                        <option value="NU" ' . ($country=="NU" ? 'selected' : '') . '>Niue</option>
                                                        <option value="NF" ' . ($country=="NF" ? 'selected' : '') . '>Norfolk Island</option>
                                                        <option value="MP" ' . ($country=="MP" ? 'selected' : '') . '>Northern Mariana Islands</option>
                                                        <option value="NO" ' . ($country=="NO" ? 'selected' : '') . '>Norway</option>
                                                        <option value="OM" ' . ($country=="OM" ? 'selected' : '') . '>Oman</option>
                                                        <option value="PK" ' . ($country=="PK" ? 'selected' : '') . '>Pakistan</option>
                                                        <option value="PW" ' . ($country=="PW" ? 'selected' : '') . '>Palau</option>
                                                        <option value="PS" ' . ($country=="PS" ? 'selected' : '') . '>Palestinian Territory, Occupied</option>
                                                        <option value="PA" ' . ($country=="PA" ? 'selected' : '') . '>Panama</option>
                                                        <option value="PG" ' . ($country=="PG" ? 'selected' : '') . '>Papua New Guinea</option>
                                                        <option value="PY" ' . ($country=="PY" ? 'selected' : '') . '>Paraguay</option>
                                                        <option value="PE" ' . ($country=="PE" ? 'selected' : '') . '>Peru</option>
                                                        <option value="PH" ' . ($country=="PH" ? 'selected' : '') . '>Philippines</option>
                                                        <option value="PN" ' . ($country=="PN" ? 'selected' : '') . '>Pitcairn</option>
                                                        <option value="PL" ' . ($country=="PL" ? 'selected' : '') . '>Poland</option>
                                                        <option value="PT" ' . ($country=="PT" ? 'selected' : '') . '>Portugal</option>
                                                        <option value="PR" ' . ($country=="PR" ? 'selected' : '') . '>Puerto Rico</option>
                                                        <option value="QA" ' . ($country=="QA" ? 'selected' : '') . '>Qatar</option>
                                                        <option value="RE" ' . ($country=="RE" ? 'selected' : '') . '>Réunion</option>
                                                        <option value="RO" ' . ($country=="RO" ? 'selected' : '') . '>Romania</option>
                                                        <option value="RU" ' . ($country=="RU" ? 'selected' : '') . '>Russian Federation</option>
                                                        <option value="RW" ' . ($country=="RW" ? 'selected' : '') . '>Rwanda</option>
                                                        <option value="BL" ' . ($country=="BL" ? 'selected' : '') . '>Saint Barthélemy</option>
                                                        <option value="SH" ' . ($country=="SH" ? 'selected' : '') . '>Saint Helena, Ascension and Tristan da Cunha</option>
                                                        <option value="KN" ' . ($country=="KN" ? 'selected' : '') . '>Saint Kitts and Nevis</option>
                                                        <option value="LC" ' . ($country=="LC" ? 'selected' : '') . '>Saint Lucia</option>
                                                        <option value="MF" ' . ($country=="MF" ? 'selected' : '') . '>Saint Martin (French part)</option>
                                                        <option value="PM" ' . ($country=="PM" ? 'selected' : '') . '>Saint Pierre and Miquelon</option>
                                                        <option value="VC" ' . ($country=="VC" ? 'selected' : '') . '>Saint Vincent and the Grenadines</option>
                                                        <option value="WS" ' . ($country=="WS" ? 'selected' : '') . '>Samoa</option>
                                                        <option value="SM" ' . ($country=="SM" ? 'selected' : '') . '>San Marino</option>
                                                        <option value="ST" ' . ($country=="ST" ? 'selected' : '') . '>Sao Tome and Principe</option>
                                                        <option value="SA" ' . ($country=="SA" ? 'selected' : '') . '>Saudi Arabia</option>
                                                        <option value="SN" ' . ($country=="SN" ? 'selected' : '') . '>Senegal</option>
                                                        <option value="RS" ' . ($country=="RS" ? 'selected' : '') . '>Serbia</option>
                                                        <option value="SC" ' . ($country=="SC" ? 'selected' : '') . '>Seychelles</option>
                                                        <option value="SL" ' . ($country=="SL" ? 'selected' : '') . '>Sierra Leone</option>
                                                        <option value="SG" ' . ($country=="SG" ? 'selected' : '') . '>Singapore</option>
                                                        <option value="SX" ' . ($country=="SX" ? 'selected' : '') . '>Sint Maarten (Dutch part)</option>
                                                        <option value="SK" ' . ($country=="SK" ? 'selected' : '') . '>Slovakia</option>
                                                        <option value="SI" ' . ($country=="SI" ? 'selected' : '') . '>Slovenia</option>
                                                        <option value="SB" ' . ($country=="SB" ? 'selected' : '') . '>Solomon Islands</option>
                                                        <option value="SO" ' . ($country=="SO" ? 'selected' : '') . '>Somalia</option>
                                                        <option value="ZA" ' . ($country=="ZA" ? 'selected' : '') . '>South Africa</option>
                                                        <option value="GS" ' . ($country=="GS" ? 'selected' : '') . '>South Georgia and the South Sandwich Islands</option>
                                                        <option value="SS" ' . ($country=="SS" ? 'selected' : '') . '>South Sudan</option>
                                                        <option value="ES" ' . ($country=="ES" ? 'selected' : '') . '>Spain</option>
                                                        <option value="LK" ' . ($country=="LK" ? 'selected' : '') . '>Sri Lanka</option>
                                                        <option value="SD" ' . ($country=="SD" ? 'selected' : '') . '>Sudan</option>
                                                        <option value="SR" ' . ($country=="SR" ? 'selected' : '') . '>Suriname</option>
                                                        <option value="SJ" ' . ($country=="SJ" ? 'selected' : '') . '>Svalbard and Jan Mayen</option>
                                                        <option value="SZ" ' . ($country=="SZ" ? 'selected' : '') . '>Swaziland</option>
                                                        <option value="SE" ' . ($country=="SE" ? 'selected' : '') . '>Sweden</option>
                                                        <option value="CH" ' . ($country=="CH" ? 'selected' : '') . '>Switzerland</option>
                                                        <option value="SY" ' . ($country=="SY" ? 'selected' : '') . '>Syrian Arab Republic</option>
                                                        <option value="TW" ' . ($country=="TW" ? 'selected' : '') . '>Taiwan, Province of China</option>
                                                        <option value="TJ" ' . ($country=="TJ" ? 'selected' : '') . '>Tajikistan</option>
                                                        <option value="TZ" ' . ($country=="TZ" ? 'selected' : '') . '>Tanzania, United Republic of</option>
                                                        <option value="TH" ' . ($country=="TH" ? 'selected' : '') . '>Thailand</option>
                                                        <option value="TL" ' . ($country=="TL" ? 'selected' : '') . '>Timor-Leste</option>
                                                        <option value="TG" ' . ($country=="TG" ? 'selected' : '') . '>Togo</option>
                                                        <option value="TK" ' . ($country=="TK" ? 'selected' : '') . '>Tokelau</option>
                                                        <option value="TO" ' . ($country=="TO" ? 'selected' : '') . '>Tonga</option>
                                                        <option value="TT" ' . ($country=="TT" ? 'selected' : '') . '>Trinidad and Tobago</option>
                                                        <option value="TN" ' . ($country=="TN" ? 'selected' : '') . '>Tunisia</option>
                                                        <option value="TR" ' . ($country=="TR" ? 'selected' : '') . '>Turkey</option>
                                                        <option value="TM" ' . ($country=="TM" ? 'selected' : '') . '>Turkmenistan</option>
                                                        <option value="TC" ' . ($country=="TC" ? 'selected' : '') . '>Turks and Caicos Islands</option>
                                                        <option value="TV" ' . ($country=="TV" ? 'selected' : '') . '>Tuvalu</option>
                                                        <option value="UG" ' . ($country=="UG" ? 'selected' : '') . '>Uganda</option>
                                                        <option value="UA" ' . ($country=="UA" ? 'selected' : '') . '>Ukraine</option>
                                                        <option value="AE" ' . ($country=="AE" ? 'selected' : '') . '>United Arab Emirates</option>
                                                        <option value="GB" ' . ($country=="GB" ? 'selected' : '') . '>United Kingdom</option>
                                                        <option value="US" ' . ($country=="US" ? 'selected' : '') . '>United States</option>
                                                        <option value="UM" ' . ($country=="UM" ? 'selected' : '') . '>United States Minor Outlying Islands</option>
                                                        <option value="UY" ' . ($country=="UY" ? 'selected' : '') . '>Uruguay</option>
                                                        <option value="UZ" ' . ($country=="UZ" ? 'selected' : '') . '>Uzbekistan</option>
                                                        <option value="VU" ' . ($country=="VU" ? 'selected' : '') . '>Vanuatu</option>
                                                        <option value="VE" ' . ($country=="VE" ? 'selected' : '') . '>Venezuela, Bolivarian Republic of</option>
                                                        <option value="VN" ' . ($country=="VN" ? 'selected' : '') . '>Viet Nam</option>
                                                        <option value="VG" ' . ($country=="VG" ? 'selected' : '') . '>Virgin Islands, British</option>
                                                        <option value="VI" ' . ($country=="VI" ? 'selected' : '') . '>Virgin Islands, U.S.</option>
                                                        <option value="WF" ' . ($country=="WF" ? 'selected' : '') . '>Wallis and Futuna</option>
                                                        <option value="EH" ' . ($country=="EH" ? 'selected' : '') . '>Western Sahara</option>
                                                        <option value="YE" ' . ($country=="YE" ? 'selected' : '') . '>Yemen</option>
                                                        <option value="ZM" ' . ($country=="ZM" ? 'selected' : '') . '>Zambia</option>
							<option value="ZW" ' . ($country=="ZW" ? 'selected' : '') . '>Zimbabwe</option>
						</select>
            				</div>
            				<div class="form-group">
                				<label>City or Zip</label>
						<div class="form-check">
  							<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="radio" name="rad_CityZip" id="rad_CityZip_City" value="City" onchange=rad_CityZip_Changed("City") '.$city_checked.'>
  							<label class="form-check-label" for="rad_CityZip_City">
    								City
  							</label>
						</div>
						<div class="form-check">
  							<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="radio" name="rad_CityZip" id="rad_CityZip_Zip" value="Zip" onchange=rad_CityZip_Changed("Zip") '.$zip_checked.'>
  							<label class="form-check-label" for="rad_CityZip_Zip">
    								Zip
  							</label>
						</div>

            				</div>
            				<div class="form-group">
                				<label for="inp_City_Zip" id="label_City_Zip">'.$CityZip_label.'</label>';
							if ($city_checked == "checked") {
                						echo '<input type="text" class="form-control" name="inp_City_Zip" id="inp_City_Zip" value="'.$city.'">';
							} else {
                                                                echo '<input type="text" class="form-control" name="inp_City_Zip" id="inp_City_Zip" value="'.$zip.'">';
							}
            				echo '</div>
            				<div class="form-group">
						<label for="inp_APIKEY">API Key:</label>
                				<input type="text" class="form-control" name="inp_APIKEY" id="inp_APIKEY" value="'.$openweather_api.'">
            				</div>
                        </div>
		        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_openweather()">
                        </div>
                </div>
	        <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script>
function rad_CityZip_Changed(label_text){

        document.getElementById("label_City_Zip").innerHTML = label_text;
        if (label_text == "City") {
                document.getElementById("inp_City_Zip").value = document.getElementById("city").value;
        	document.getElementById("CityZip").value = 0;
	} else {
                document.getElementById("inp_City_Zip").value = document.getElementById("zip").value;
                document.getElementById("CityZip").value = 1;
	}
}

function set_country(value){

	var valuetext = value;
        document.getElementById("country_code").value = valuetext;
}

</script>
<?php

//MQTT Connection Modal
echo '<div class="modal fade" id="mqtt_connection" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
				<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['mqtt_connections'].'</h5>
            			<div class="dropdown float-right">
                			<a class="" data-bs-toggle="dropdown" href="#">
                        			<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                			</a>
                			<ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                        			<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_mqtt.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_mqtt'].'</a></li>
                        			<li class="dropdown-divider"></li>
                        			<li><a class="dropdown-item" href="pdf_download.php?file=setup_zigbee2mqtt.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_zigbee2mqtt'].'</a></li>
                			</ul>
             			</div>
            		</div>
            		<div class="modal-body">
				<p class="text-muted">'.$lang['mqtt_connections_text'].'</p>';
   				$query = "SELECT * FROM `mqtt` ORDER BY `name`;";
    				$results = $conn->query($query);
    				echo '<div class="list-group">';
    					while ($row = mysqli_fetch_assoc($results)) {
                                                $myArray[$row['id']] = $row;
                                                //overwrite the raw password entry with the decoded value
                                                $myArray[$row['id']]['password'] = dec_passwd($row['password']);
        					echo '<span class="list-group-item">
							<div class="d-flex justify-content-between">
								<div>';
        								echo $row['name'] . ($row['enabled'] ? '' : ' (Disabled)');
								echo '</div>
								<div>
        								<span class="text-muted small" style="width:200px;text-align:right;">Username:&nbsp;' . $row['username'] . '</span>
								</div>
							</div>
                					<div class="d-flex justify-content-between">
								<div>
									<span class="text-muted small">Type:&nbsp;';
        									if($row['type']==0) echo 'Default, monitor.';
        									else if($row['type']==1) echo 'Sonoff Tasmota.';
        									else if($row['type']==2) echo 'MQTT Node.';
        									else if($row['type']==3) echo 'Home Assistant.';
        									else echo 'Unknown.';
        								echo '</span>
								</div>
								<div>
        								<span class="text-muted small" style="width:200px;text-align:right;">Password:&nbsp;' . dec_passwd($row['password']) . '</span>
								</div>
							</div>
                					<div class="d-flex justify-content-between">
                        					<div>
        								<span class="text-muted small">' . $row['ip'] . '&nbsp;:&nbsp;' . $row['port'] . '</span>
								</div>
								<div>
        								<span class="text-muted small" style="width:200px;text-align:right;">
										<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-xs" onclick="button_mqtt_connection(`'.$row['id'].'`);"><i class="bi bi-pencil"></i></button>
                                                				<button class="btn warning btn-danger btn-xs" onclick="delete_mqtt_connection('.$row["id"].');" data-confirm="'.$lang['confirm_del_mqtt'].'"><span class="bi bi-trash-fill black"></span></button>
        								</span>
        							</div>
							</div>
						</span>';
    					}
    				echo '</div>
			        <!-- /.list-group -->
			</div>
		        <!-- /.modal-body -->
			<div class="modal-footer">
                	        <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <button class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="button_mqtt_connection(0);">'.$lang['add_connection'].'</button>
			</div>
        	</div>
	        <!-- /.modal-content -->
    	</div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
function button_mqtt_connection(id)
{
	var myId = id;
	if (myId == 0) {
        	$("#conn_id").val(myId);
                var myName = "";
                var myIp = "";
                var myPort = "";
                var myUsername = "";
                var myPassword = "";
                var myEnabled = 1;
                var myType = 0;
		var title = "<?php echo $lang['add_connection']  ?>";
                var title_text = "<?php echo $lang['add_connection_text']  ?>";
	} else {
                var title = "<?php echo $lang['edit_connection']  ?>";
                var title_text = "<?php echo $lang['edit_connection_text']  ?>";
	        var data = '<?php echo json_encode($myArray) ?>';
        	if (data.length > 0) {
                	var obj = JSON.parse(data);
        	        var myName = obj[myId].name;
	                var myIp = obj[myId].ip;
                	var myPort = obj[myId].port;
        	        var myUsername = obj[myId].username;
	                var myPassword = obj[myId].password;
                	var myEnabled = obj[myId].enabled;
                	var myType = obj[myId].type;
	        }
	}

        $("#conn_id").val(myId);
        $("#inp_Name").val(myName);
        $("#inp_Ip").val(myIp);
        $("#inp_Port").val(myPort);
        $("#inp_Username").val(myUsername);
        $("#inp_Password").val(myPassword);
        $("#sel_Enabled").val(myEnabled);
        $("#inp_Enabled").val(myEnabled);
        $("#sel_Type").val(myType);
        $("#inp_Type").val(myType);
	$('#mqtt_addedit').text(title);
        $('#mqtt_addedit_text').text(title_text);

        $('#mqtt_connection').modal('hide');
        $('#add_mqtt_connection').modal('show');
}
</script>
<?php

//Add or Edit MQTT Connection
echo '
<div class="modal fade" id="add_mqtt_connection" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        	<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 id="mqtt_addedit" class="modal-title">'.$lang['add_edit_connection'].'</h5>
            		</div>
            		<div class="modal-body">';
				echo '<p id="mqtt_addedit_text" class="text-muted"></p>
				<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
			    	<input type="hidden" id="conn_id" name="conn_id" value="0" autocomplete="off" required>
                                <input type="hidden" id="inp_Enabled" name="inp_Enabled" value="0" autocomplete="off" required>
                                <input type="hidden" id="inp_Type" name="inp_Type" value="0" autocomplete="off" required>

                                <div class="form-group">
                                	<label>Name</label>
                                        <input type="text" class="form-control" name="inp_Name" id="inp_Name" placeholder="Enter Broker Name">
                                        <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                        <label>IP</label>
                                        <input type="text" class="form-control" name="inp_Ip" id="inp_Ip" placeholder="Enter Broker IP Address">
                                        <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                        <label>Port</label>
                                        <input type="text" class="form-control" name="inp_Port" id="inp_Port" placeholder="Enter Broker Port">
                                        <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                        <label>Username</label>
                                        <input type="text" class="form-control" name="inp_Username" id="inp_Username" placeholder="Enter Connectiom Username">
                                        <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                        <label>Password</label>
                                        <input type="password" class="form-control" name="inp_Password" id="inp_Password" placeholder="Enter Connection Password">
                                        <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                        <label>Enabled</label>
                                        <select class="form-control" id="sel_Enabled" name="sel_Enabled" onchange="set_enabled(this.options[this.selectedIndex].value)" >
                                                <option value="0">'.$lang['disabled'].'</option>
                                                <option value="1">'.$lang['enabled'].'</option>
                                        </select>
                                        <div class="help-block with-errors"></div>
                                </div>
                                <div class="form-group">
                                        <label>Type</label>
                                        <select class="form-control" id="sel_Type" name="sel_Type" onchange="set_type(this.options[this.selectedIndex].value)" >
                                                <option value="0">Default - view all</option>
                                                <option value="1">Sonoff - Tasmota</option>
                                                <option value="2">MQTT Node</option>
                                                <option value="3">Home Assistant integration</option>
                                        </select>
                                        <div class="help-block with-errors"></div>
                                </div>
                        </div>
		        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" id="button_mqtt_connection_hide">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_update_mqtt_broker()">
                        </div>
                </div>
	        <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal -->';
?>
<script language="javascript" type="text/javascript">
$("#button_mqtt_connection_hide").on("click", function(){
        $("#add_mqtt_connection").modal("show");
        $('#add_mqtt_connection').on("hidden.bs.modal", function (e) {
                $("#mqtt_connection").modal("show");
        })
        $("#add_mqtt_connection").modal("hide");
});

function set_enabled(value){
        var valuetext = value;
        document.getElementById("inp_Enabled").value = valuetext;
}

function set_type(value){
        var valuetext = value;
        document.getElementById("inp_Type").value = valuetext;
}
</script>
<?php

//enable graph archiving
echo '<div class="modal fade" id="archive_graphs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['archive_graphs'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['archive_graphs_text'].'</p>';
                $query = "SELECT * FROM graphs LIMIT 1;";
                $result = $conn->query($query);
		$row = mysqli_fetch_array($result);
        	echo '<div class="form-group" class="control-label">
                	<div class="form-check">';
                        	if ($row['archive_enable'] == '1'){ $checked = "checked"; } else { $checked = "" ; }
				echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox6" name="archive_status" '. $checked .'>
                        	<label class="form-check-label" for="checkbox6">'.$lang['enable_graph_archive'].'</label>
                	</div>
        	</div>
		<div class="form-group" class="control-label"><label>'.$lang['archive_file_path'].'</label>
			<input class="form-control" type="text" id="graph_archive_file" name="graph_archive_file" value="'.$row['archive_file'].'" placeholder="Full Path Name of Graph Archive File ">
        		<div class="help-block with-errors"></div></div>
            	</div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="graph_archiving()">
            </div>
        </div>
    </div>
</div>';

//Zone Current State Logs
echo '<div class="modal fade" id="zone_current_state_logs" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_current_state_logs'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['enable_zone_current_state_logs_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-lg-2 text-center"><small>'.$lang['zone'].'</small></th>
                                <th class="col-lg-1 text-center"><small>'.$lang['enabled'].'</small></th>
                        </tr>';
                        $query = "SELECT zone_id, z.name, log_it
                                  FROM zone_current_state
                                  JOIN zone z ON zone_id = z.id
                                  ORDER BY z.type_id, z.id ASC;";
                        $results = $conn->query($query);
                        while ($row = mysqli_fetch_assoc($results)) {
                                if ($row['log_it']) { $enabled_check = 'checked'; } else { $enabled_check = ''; }
                                echo '<tr>
                                        <td>'.$row['name'].'</td>
                                        <td style="text-align:center; vertical-align:middle;">
                                                <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_zone_current_state'.$row['zone_id'].'" name="enabled" value="1" '.$enabled_check.'>
                                        </td>
                                </tr>';
                        }
                echo '</table>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="enable_zone_current_state_logs()">
            </div>
        </div>
    </div>
</div>';

}

if ($model_num == 4) {
//System Controller settings
echo '
<div class="modal fade" id="system_controller" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
				<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title">'.$lang['system_controller_settings'].'</h5>
                		<div class="dropdown float-right">
                        		<a class="" data-bs-toggle="dropdown" href="#">
                                		<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        		</a>
                        		<ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                        		<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_system_controller.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_system_controller'].'</a></li>
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
					<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
					<div class="form-group" class="control-label">
				                <div class="form-check">';
							if ($bcount > 0) {
					                        if ($bresult and $brow['status'] == '1'){
        	                      					echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox2" name="status" checked Disabled>';
					                        } else {
                        	        				echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox2" name="status" Disabled>';
					                        }
							} else {
								echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="0" id="checkbox2" name="status" Enabled>';
							}
				                        echo '<label class="form-check-label" for="checkbox2">'.$lang['system_controller_enable'].'</label>
				                </div>
					</div>
					<!-- /.form-group -->
					<div class="form-group" class="control-label"><label>'.$lang['system_controller_name'].'</label>
						<input class="form-control" type="text" id="name" name="name" value="'.$brow['name'].'" placeholder="System Controller Name to Display on Screen ">
						<div class="help-block with-errors">
						</div>
					</div>
					<!-- /.form-group -->
                        		<div class="form-group" class="control-label"><label>'.$lang['heat_relay_id'].'</label> <small class="text-muted">'.$lang['heat_relay_id_info'].'</small>
                                                <select class="form-select" type="text" id="heat_relay_id" name="heat_relay_id" >';
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
                                                <select class="form-select" type="text" id="cool_relay_id" name="cool_relay_id" >';
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
                                                <select class="form-select" type="text" id="fan_relay_id" name="fan_relay_id" >';
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
						<select class="form-select" type="text" id="hysteresis_time" name="hysteresis_time">
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
						<select class="form-select" type="text" id="max_operation_time" name="max_operation_time">
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
							<select class="form-select" type="text" id="overrun" name="overrun">
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
                                                        <select class="form-select" type="text" id="weather_factoring" name="weather_factoring">
                                        			<option value="0" ' . ($brow['weather_factoring'] == 0 || $brow['weather_factoring'] == '0' ? 'selected' : '') . '>'.$lang['disabled'].'</option>
                                        			<option value="1" ' . ($brow['weather_factoring'] == 1 || $brow['weather_factoring'] == '1' ? 'selected' : '') . '>'.$lang['enabled'].'</option>
                                                        </select>
                                                        <div class="help-block with-errors">
                                                        </div>
                                                </div>
                                                <!-- /.form-group -->
                                		<div class="form-group" class="control-label"><label>'.$lang['weather_sensor'].'</label> <small class="text-muted">'.$lang['weather_sensor_info'].'</small>
                                                	<select class="form-select" type="text" id="weather_sensor_id" name="weather_sensor_id" >
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
        	   	<div class="modal-footer">';
				if ($brow['heat_relay_id'] != 0) { echo'<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>'; }
				if ($ncount > 0) { echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="system_controller_settings('.(settings($conn, 'mode') & 0b1).')">'; }

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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['boost_settings'].'</h5>
            </div>
            <div class="modal-body">';
if ((settings($conn, 'mode') & 0b1) == 0) {
	echo '<p class="text-muted"> '.$lang['boost_settings_text'].' </p>';
} else {
        echo '<p class="text-muted"> '.$lang['hvac_boost_settings_text'].' </p>';
}

if ((settings($conn, 'mode') & 0b1) == 0) {
	$query = "SELECT DISTINCT boost.id, boost.`status`, boost.sync, boost.zone_id, zone_idx.index_id, zone_type.category, zone.name, 
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
        	<th class="col-4"><small>'.$lang['zone'].'</small></th>
        	<th class="col-2"><small>'.$lang['boost_time'].'</small></th>
        	<th class="col-2"><small>'.$lang['boost_temp'].'</small></th>
        	<th class="col-2"><small>'.$lang['boost_console_id'].'</small></th>
        	<th class="col-1"><small>'.$lang['boost_button_child_id'].'</small></th>
        	<th class="col-1"></th>
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
                <th class="col-2"><small>'.$lang['hvac_function'].'</small></th>
                <th class="col-2"><small>'.$lang['boost_time'].'</small></th>
                <th class="col-2"><small>'.$lang['boost_temp'].'</small></th>
                <th class="col-1"></th>
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
            	<td><input id="minute'.$row["id"].'" type="text" class="float-left text" style="border: none" name="minute" size="3" value="'.$minute.'" placeholder="Minutes" required></td>';
	    	if($row["category"] < 2 || $row["category"] == 5) {
            		echo '<td><input id="temperature'.$row["id"].'" type="text" class="float-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>
            		<td><input id="boost_button_id'.$row["id"].'" type="text" class="float-left text" style="border: none" name="button_id"  size="3" value="'.$boost_button_id.'" placeholder="Button ID" required></td>
            		<td><input id="boost_button_child_id'.$row["id"].'" type="text" class="float-left text" style="border: none" name="button_child_id" size="3" value="'.$boost_button_child_id.'" placeholder="Child ID" required></td>';
	    	} else {
            		echo '<td><input id="temperature'.$row["id"].'" type="hidden" class="float-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>
            		<td><input id="boost_button_id'.$row["id"].'" type="hidden" class="float-left text" style="border: none" name="button_id"  size="3" value="'.$boost_button_id.'" placeholder="Button ID" required></td>
            		<td><input id="boost_button_child_id'.$row["id"].'" type="hidden" class="float-left text" style="border: none" name="button_child_id" size="3" value="'.$boost_button_child_id.'" placeholder="Child ID" required></td>';
	    	}
	     	echo '<input type="hidden" id="zone_id'.$row["id"].'" name="zone_id" value="'.$row["zone_id"].'">
		<input type="hidden" id="hvac_mode'.$row["id"].'" name="hvac_mode" value="'.$row["hvac_mode"].'">
                <input type="hidden" id="sensor_type'.$row["id"].'" name="sensor_type" value="'.$row["sensor_type_id"].'">
                <td>';
                if ($b_count > 1) { 
			echo '<button class="btn warning btn-danger btn-xs '.$disabled.'" onclick="delete_boost('.$row["id"].');" data-confirm="You are about to DELETE this BOOST Setting"';
		} else {
	                echo '<button class="btn btn-danger btn-xs '.$disabled.'" ';
		}
                echo '><span class="bi bi-trash-fill black"></span></button> </td>
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
            	<td><input id="minute'.$row["id"].'" type="text" class="float-left text" style="border: none" name="minute" size="3" value="'.$minute.'" placeholder="Minutes" required></td>';
	    	if($hvac_mode > 3) {
            		echo '<td><input id="temperature'.$row["id"].'" type="text" class="float-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>';
	    	} else {
            		echo '<td><input id="temperature'.$row["id"].'" type="hidden" class="float-left text" style="border: none" name="temperature" size="3" value="'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'" placeholder="Temperature" required></td>';
	    	}
	     	echo '<input type="hidden" id="zone_id'.$row["id"].'" name="zone_id" value="'.$row["zone_id"].'">
		<input type="hidden" id="hvac_mode'.$row["id"].'" name="hvac_mode" value="'.$row["hvac_mode"].'">
                <input type="hidden" id="sensor_type'.$row["id"].'" name="sensor_type" value="'.$row["sensor_type_id"].'">
                <td>';
                echo '<button class="btn fist btn-danger btn-xs '.$disabled.'" ';
                if ($b_count > 1) { echo 'onclick="delete_boost('.$row["id"].');" data-dismiss="You are about to DELETE this BOOST Setting"'; }
                echo '><span class="bi bi-trash-fill black"></span></button> </td>
            </tr>';
    }
}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_boost()">
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_boost">'.$lang['add_boost'].'</button>
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_boost'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$info_text.'</p>
	<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	<div class="form-group" class="control-label"><label>'.$zone_hvac.'</label> 
	<select class="form-select" type="text" id="zone_id" name="zone_id">';
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
	<select class="form-select" type="text" id="boost_temperature" name="boost_temperature">
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
	<select class="form-select" type="text" id="boost_time" name="boost_time">
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
		<select class="form-select" type="text" id="boost_console_id" name="boost_console_id">';
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
		<select class="form-select" type="text" id="boost_button_child_id" name="boost_button_child_id">
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
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>';
				if ((settings($conn, 'mode') & 0b1) == 0) {
					echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_boost(0)">';
				} else {
                                        echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_boost(1)">';
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['override_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_override.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_override'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['override_settings_text'].'</p>';
if ((settings($conn, 'mode') & 0b1) == 0) { //boiler mode
	$query = "SELECT DISTINCT override.`status`, override.sync, override.purge, override.zone_id, zone_idx.index_id, zone_type.category, zone.name,
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
echo '	<div class="list-group">';
while ($row = mysqli_fetch_assoc($results)) {
	if ((settings($conn, 'mode') & 0b1) == 0) {
		$name = $row['name'];
	} else {
		if ($row['hvac_mode']  == 4) { $name = 'HEAT'; } else { $name = 'COOL'; };
	}
	echo '<a href="#" class="list-group-item">
		<div class="d-flex justify-content-between">
			<div>
				<i class="bi bi-arrow-repeat blue" style="font-size: 1.2rem;"></i>&nbsp'.$name.'
			</div>
    			<div class="text-muted small"><em>'.DispSensor($conn,$row["temperature"],$row["sensor_type_id"]).'&deg; </em>
			</div>
		</div>
	</a>';
}
echo '</div></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';
}

if ($model_num == 5) {
//nodes modal
echo '
<div class="modal fade" id="nodes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_setting'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['node_settings_text'].' </p>';

$query = "SELECT * FROM nodes;";
$results = $conn->query($query);
echo '<table class="table table-bordered">
    <tr>
        <th class="col-2"><small>'.$lang['type'].'</small></th>
        <th class="col-2"><small>'.$lang['node_id'].'</small></th>
        <th class="col-2"><small>'.$lang['max_child'].'</small></th>
        <th class="col-4"><small>'.$lang['name'].'</small></th>
        <th class="col-1"></th>
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
                $zcount = 0;
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
					$query = "SELECT zone.name FROM zone_relays, zone where (zone.id = zone_relays.zone_id) AND zone_relays.zone_relay_id = {$r_row['id']} LIMIT 1;";
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
            <td><input id="max_child_id'.$row["node_id"].'" type="value" class="form-control float-right" style="border: none" name="max_child_id'.$row["node_id"].'" value="'.$row["max_child_id"].'" placeholder="Max Child ID"></td>
            <td>'.$row["name"].'</td>';
	    if($zcount != 0) {
		echo '<td><div class="tooltip-wrapper" data-bs-toggle="tooltip" title="'.$content_msg_z.'"><button class="btn btn-danger btn-xs disabled"><span class="bi bi-trash-fill black"></span></button> </div></td>';
	    } else {
		echo '<td><button class="btn warning btn-danger btn-xs" onclick="delete_node('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button> </td>';
	    }
        echo '</tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_max_child_id()">
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_node">'.$lang['node_add'].'</button>
            </div>
        </div>
    </div>
</div>';


//Add Node
echo '
<div class="modal fade" id="add_node" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['node_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['node_add_info_text'].'</p>
	<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
	<div class="form-group" class="control-label"><label>'.$lang['node_type'].'</label> <small class="text-muted">'.$lang['node_type_info'].'</small>
	<select class="form-select" type="text" id="node_type" onchange=show_hide_devices() name="node_type">
	<option value="I2C" selected="selected">I2C</option>
	<option value="GPIO">GPIO</option>
        <option value="Tasmota">Tasmota</option>
        <option value="Dummy">Dummy</option>
        <option value="Switch">Switch</option>
        <option value="MQTT">MQTT</option>
	</select>
    <div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['node_id'].'</label> <small class="text-muted">'.$lang['node_id_info'].'</small>
		<input class="form-control" type="text" id="add_node_id" name="add_node_id" value="" placeholder="'.$lang['node_id'].'">
	<div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label" id="dummy_type_label" style="display:none"><label>'.$lang['node_name'].'</label> <smallclass="text-muted">'.$lang['dummy_name_info'].'</small>
                <select class="form-select" type="text" id="dummy_type" name="dummy_type">
                        <option value="Sensor" selected="selected">Dummy Sensor</option>
                        <option value="Controller">Dummy Controller</option>
                </select>
                <div class="help-block with-errors"></div>
        </div>
        <div class="form-group" class="control-label" id="mqtt_type_label" style="display:none"><label>'.$lang['node_name'].'</label> <smallclass="text-muted">'.$lang['mqtt_name_info'].'</small>
                <select class="form-select" type="text" id="mqtt_type" name="mqtt_type">
                        <option value="Sensor" selected="selected">MQTT Sensor</option>
                        <option value="Controller">MQTT Controller</option>
                </select>
                <div class="help-block with-errors"></div>
        </div>
	<div class="form-group" class="control-label" id="add_devices_label" style="display:block"><label>'.$lang['node_child_id'].'</label> <small class="text-muted">'.$lang['node_child_id_info'].'</small>
		<input class="form-control" type="text" id="nodes_max_child_id" name="nodes_max_child_id" value="0" placeholder="'.$lang['node_max_child_id'].'">
		<div class="help-block with-errors"></div>
	</div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_node()">
            </div>
        </div>
    </div>
</div>';

//Zone Type
echo '
<div class="modal fade" id="zone_types" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_type'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=zone_types.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['zone_types'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=switch_zones.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['switch_zones'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['zone_type_text'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-4"><small>'.$lang['type'].'</small></th>
        <th class="col-7"><small>'.$lang['category'].'</small></th>
        <th class="col-1"></th>
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
            <td><button class="btn warning btn-danger btn-xs" onclick="delete_zone_type('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button> </td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_zone_type">'.$lang['zone_type_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Zone Type
echo '
<div class="modal fade" id="add_zone_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_type_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['zone_type_add_info_text'].'</p>
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['zone_type'].'</label> <small class="text-muted">'.$lang['zone_type_info'].'</small>
        <input class="form-control" type="text" id="zone_type" name="zone_type" value="" placeholder="'.$lang['zone_type'].'">
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['category'].'</label> <small class="text-muted">'.$lang['zone_category_info'].'</small>
        <select class="form-select" type="text" id="category" name="category">
        <option value=0 selected>'.$lang['zone_category0'].'</option>
        <option value=1>'.$lang['zone_category1'].'</option>
        <option value=5>'.$lang['zone_category5'].'</option>
        <option value=2>'.$lang['zone_category2'].'</option>
        <option value=3>'.$lang['zone_category3'].'</option>
        <option value=4>'.$lang['zone_category4'].'</option>
        </select>
    <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_zone_type()">
            </div>
        </div>
    </div>
</div>';

//Zone model
echo '
<div class="modal fade" id="zone_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['zone_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_zones.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_zones'].'</a></li>
                                <li class="dropdown-divider"></li>
	                        <li><a class="dropdown-item" href="pdf_download.php?file=setup_pump_type_relays.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_pump_type_relays'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=switch_zones.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['switch_zones'].'</a></li>
                                <li class="dropdown-divider"></li>
                        	<li><a class="dropdown-item" href="pdf_download.php?file=switch_zone_state_control.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['switch_zone_state_control'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=negative_gradient_zone.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['negative_gradient_zone'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_multiple_sensors.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_multiple_sensors'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['zone_settings_text'].'</p>';
		$query = "select * from zone order by index_id asc";
		$results = $conn->query($query);
		echo '<div class="list-group">';
			while ($row = mysqli_fetch_assoc($results)) {
        			if($row['status'] == 1) {
                			$content_msg=$lang['confirm_dell_active_zone'];
        			} else {
                			$content_msg=$lang['confirm_dell_de_active_zone'];
        			}
				echo '<div class="list-group-item">';
        				$query = "select * from zone_view WHERE id = '{$row['id']}'order by index_id asc";
        				$vresult = $conn->query($query);
					$count = 0;
        				while ($vrow = mysqli_fetch_assoc($vresult)) {
						$unit = SensorUnits($conn,$vrow['sensor_type_id']);
						if ($count == 0) {
							echo '<div class="d-flex justify-content-between">
								<span><i class="bi bi-columns-gap orange" style="font-size: 1rem;"></i> '.$row['name'].'</span>';
		                                                if ($vrow['r_type'] == 5) {
									echo '<span>&nbsp;&nbsp;<small> '.$lang['pump_relay'].': '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
                						} elseif ($vrow['category'] == 2) {
									echo '<span>&nbsp;&nbsp;<small> '.$lang['controller'].': '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
								} elseif ($vrow['category'] == 3) {
									echo '<span><em>&nbsp;&nbsp;<small> '.$lang['min'].' '.DispSensor($conn,$vrow['min_c'],$vrow['sensor_type_id']).$unit.', '.$lang['max'].' '.$vrow['max_c'].$unit.' </em> - '.$lang['sensor'].': '.$vrow['sensors_id'].'&nbsp</small></span>';
                						} elseif ($vrow['category'] == 5) {
                        						echo '<span><em>&nbsp;&nbsp;<small> '.$lang['min'].' '.DispSensor($conn,$vrow['min_c'],$vrow['sensor_type_id']).$unit.' </em> - '.$lang['sensor'].': '.$vrow['sensors_id'].' - '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
                						} else {
                        						echo '<span><em>&nbsp;&nbsp;<small> '.$lang['max'].' '.DispSensor($conn,$vrow['max_c'],$vrow['sensor_type_id']).$unit.' </em> - '.$lang['sensor'].': '.$vrow['sensors_id'].' - '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
                						}
							echo '</div>';
						} else {
							echo '<div class="d-flex justify-content-end">';
		                                                if ($vrow['r_type'] == 5) {
                						        echo '<span>&nbsp;&nbsp;<small> '.$lang['pump_relay'].': '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
		                                                } elseif ($vrow['category'] == 2) {
                						        echo '<span>&nbsp;&nbsp;<small> '.$lang['controller'].': '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
		                                                } elseif ($vrow['category'] == 3) {
						                        echo '<span><em>&nbsp;&nbsp;<small> '.$lang['min'].' '.DispSensor($conn,$vrow['min_c'],$vrow['sensor_type_id']).$unit.', '.$lang['max'].' '.$vrow['max_c'].$unit.' </em> - '.$lang['sensor'].': '.$vrow['sensors_id'].'&nbsp</small></span>';
                                                		} elseif ($vrow['category'] == 5) {
					                        	echo '<span><em>&nbsp;&nbsp;<small> '.$lang['min'].' '.DispSensor($conn,$vrow['min_c'],$vrow['sensor_type_id']).$unit.' </em> - '.$lang['sensor'].': '.$vrow['sensors_id'].' - '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
                                                		} else {
					                        	echo '<span><em>&nbsp;&nbsp;<small> '.$lang['max'].' '.DispSensor($conn,$vrow['max_c'],$vrow['sensor_type_id']).$unit.' </em> - '.$lang['sensor'].': '.$vrow['sensors_id'].' - '.$vrow['relay_type'].': '.$vrow['relay_id'].'-'.$vrow['relay_child_id'].'&nbsp</small></span>';
								}
							echo '</div>';
 						}
						$count++;
        				}
        				echo '<span class="d-flex justify-content-end"><small>
        					<a href="zone.php?id='.$row['id'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs login"><span class="bi bi-pencil" style="font-size: 1rem;"</span></a>&nbsp;&nbsp;
        					<button class="btn warning btn-danger btn-xs" onclick="delete_zone('.$row['id'].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black" style="font-size: 1rem;"></span></button>
        				</small></span>
        			</div>';
			}
		echo '</div>
	    </div>
	    <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <a class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" href="zone.php?id=0">'.$lang['zone_add'].'</a>
            </div>
        </div>
    </div>
</div>';
if ($show_zone_modal == 1) {
        ?>
        <script type="text/javascript">
                $(function(){
                        //instantiate your content as modal
                        $('#zone_setup').modal({
                        //modal options here, like keyboard: false for e.g.
                        });

                        //show the modal when dom is ready
                        $('#zone_setup').modal('show');
                });
        </script>
        <?php
}

//gateway model
echo '
<div class="modal fade" id="sensor_gateway" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
				<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
               			<h5 class="modal-title">'.$lang['smart_home_gateway'].'</h5>
                                <div class="dropdown float-right">
                                        <a class="" data-bs-toggle="dropdown" href="#">
                                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                		<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_gateway.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_gateway'].'</a></li>
                                        </ul>
                                </div>
            		</div>
            		<div class="modal-body">';
                                $gquery = "SELECT * FROM `nodes` WHERE `node_id` = '0' AND `name` LIKE '%Gateway%'";
                                $result = $conn->query($gquery);
				$row = mysqli_fetch_array($result);
				$sketch_version = $row['sketch_version'];
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
                                                $display_heartbeat = "display:block";
                                        } elseif ($gateway_type=='serial') {
                                                echo $lang['smart_home_gateway_text_serial'];
                                                $display_wifi = "display:none";
                                                $display_serial = "display:block";
                                                $display_timeout = "display:block";
                                                $display_heartbeat = "display:none";
                                        } elseif ($gateway_type=='virtual') {
                                                echo $lang['smart_home_gateway_text_virtual'];
                                                $display_wifi = "display:none";
                                                $display_serial = "display:none";
                                                $display_timeout = "display:none";
                                                $display_heartbeat = "display:none";
                                        }
                                }
				echo '</p>';
				echo '
				<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
				<div class="form-group" class="control-label">
                                	<div class="form-check">';
                                        	if ($grow['status'] == '1'){
							echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox1" name="status" checked>';
                                                } else {
							echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="checkbox1" name="status">';
                                                }
                                                        echo '<label class="form-check-label" for="checkbox1">'.$lang['smart_home_gateway_enable'].'</label>
					</div>
				</div>
                               	<!-- /.form-group -->
                                <div class="form-group" class="control-label">
                                        <div class="form-check">';
                                                if ($grow['enable_outgoing'] == '1'){
                                                        echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" id="checkbox4" class="styled" type="checkbox" value="1" name="enable_outgoing" checked>';
                                                }else {
                                                        echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" id="checkbox4" class="styled" type="checkbox" value="1" name="enable_outgoing">';
                                                }
                                                echo '
                                                <label class="form-check-label" for="checkbox4"> '.$lang['enable_outgoing'].'</label>
                                        </div>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_type'].'</label>
                                        <select class="form-select" type="text" id="gw_type" name="gw_type" onchange=gw_location()>
                                        <option value="wifi" ' . ($gateway_type=='wifi' ? 'selected' : '') . '>'.$lang['wifi'].'</option>
                                        <option value="serial" ' . ($gateway_type=='serial' ? 'selected' : '') . '>'.$lang['serial'].'</option>
                                        <option value="virtual" ' . ($gateway_type=='virtual' ? 'selected' : '') . '>'.$lang['virtual'].'</option>
                                        </select>
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group" class="control-label" id="wifi_gw" style="'.$display_wifi.'"><label>'.$lang['wifi_gateway_location'].'</label>
                                	<input class="form-control" type="text" id="wifi_location" name="wifi_location" value="'.$grow['location'].'" placeholder="Gateway Location">
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group" class="control-label" id="serial_gw" style="'.$display_serial.'"><label>'.$lang['serial_gateway_location'].'</label>
                                        <select class="form-select" type="text" id="serial_location" name="serial_location">
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
                                        <input class="form-control" type="text" id="wifi_port_num" name="wifi_port_num" value="'.$grow['port'].'" placeholder="Gateway Port">
                                        <div class="help-block with-errors">
                                        </div>
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group" class="control-label" id="serial_port" style="'.$display_serial.'"><label>'.$lang['serial_gateway_port'].' </label>
                                        <select class="form-select" type="text" id="serial_port_speed" name="serial_port_speed">
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
				<div class="form-group" class="control-label" id="gw_timeout" style="'.$display_timeout.'"><label id="gw_timeout_label">'.$lang['interface_timeout'].' </label> <small class="text-muted">'.$lang['seconds'].'</small>
                                        <select class="form-select" type="text" id="gw_timout" name="gw_timout">
                                        <option selected>'.$grow['timout'].'</option>
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
                                        <div class="help-block with-errors"></div>
				</div>
                                <!-- /.form-group -->
                                <div class="form-group" class="control-label" id="heartbeat" style="'.$display_heartbeat.'"><label id="heartbeat_label">'.$lang['heartbeat_timeout'].' </label> <small class="text-muted">'.$lang['seconds'].'</small>
                                        <select class="form-select" type="text" id="gw_heartbeat" name="gw_heartbeat">
                                        <option selected>'.$grow['heartbeat_timeout'].'</option>
                                        <option value="0">0</option>
                                        <option value="30">30</option>
                                        <option value="60">60</option>
                                        <option value="90">90</option>
                                        <option value="120">120</option>
                                        <option value="150">150</option>
                                        <option value="180">180</option>
                                        <option value="210">210</option>
                                        <option value="240">240</option>
                                        <option value="270">270</option>
                                        <option value="300">300</option>
                                        </select>
                                        <div class="help-block with-errors"></div>
                                </div>
                                <!-- /.form-group -->
				<div class="form-group" class="control-label"><label>'.$lang['smart_home_gateway_version'].' </label>
					<input class="form-control" type="text" id="gw_version" name="gw_version" value="'.$grow['version'].' ('.$sketch_version.')" disabled>
					<div class="help-block with-errors">
					</div>
				</div>
                                <!-- /.form-group -->
			</div>
			<!-- /.modal-body -->
            		<div class="modal-footer">
				<a href="javascript:resetgw('.$grow['pid'].')" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm btn-edit">Reset GW</a>
				<a href="javascript:find_gw()" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm btn-edit">Search GW</a>
				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="setup_gateway()">
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
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
        <th class="col-1">'.$lang['node_id'].'</th>
        <th class="col-2">'.$lang['name'].'</th>
        <th class="col-3">'.$lang['last_seen'].'</th>
        <th class="col-3">'.$lang['notice_interval'].'
        <span class="bi bi-info-circle-fill blue" data-container="body" data-bs-toggle="popover" data-bs-placement="left" data-bs-content="'.$lang['notice_interval_info'].'"</span></th>
        <th class="col-3">'.$lang['min_battery_level'].'
        <span class="bi bi-info-circle-fill blue" data-container="body" data-bs-toggle="popover" data-bs-placement="left" data-bs-content="'.$lang['battery_level_info'].'"</span></th>
    </tr>';

while ($row = mysqli_fetch_assoc($results)) {
    echo '
        <tr>
            <td>'.$row['node_id'].'</td>
            <td>'.$row['name'].'</td>
            <td>'.$row['last_seen'].'</td>
            <td><input id="interval'.$row["node_id"].'" type="value" class="form-control float-right" style="border: none" name="interval'.$row["node_id"].'" value="'.$row["notice_interval"].'" placeholder="Notice Interval" required></td>';
	    if(!isset($row['batt'])) {
	            echo '<td><input id="min_value'.$row["node_id"].'" type="value" class="form-control float-right" style="border: none" name="min_value'.$row["node_id"].'" value="N/A" readonly="readonly" placeholder="Min Value"></td>';
	    } else {
                    echo '<td><input id="min_value'.$row["node_id"].'" type="value" class="form-control float-right" style="border: none" name="min_value'.$row["node_id"].'" value="'.$row["min_value"].'" placeholder="Min Value"></td>';
	    }
        echo '</tr>';

}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="node_alerts()">
            </div>
        </div>
    </div>
</div>';
?>
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
        document.getElementById("gw_timeout_label").style.visibility = 'hidden';
        document.getElementById("gw_timeout").style.display = 'none';
        document.getElementById("heartbeat_label").style.visibility = 'hidden';
        document.getElementById("heartbeat").style.display = 'none';
        document.getElementById("wifi_location").value = "";
        document.getElementById("wifi_port_num").value = "";
 } else if(selected_gw_type.includes("wifi")) {
        document.getElementById("serial_gw").style.display = 'none';
        document.getElementById("wifi_gw").style.display = 'block';
        document.getElementById("serial_port").style.display = 'none';
        document.getElementById("wifi_port").style.display = 'block';
        document.getElementById("gw_timeout_label").style.visibility = 'visible';
	document.getElementById("gw_timeout").style.display = 'block';
       	document.getElementById("heartbeat_label").style.visibility = 'visible';
	document.getElementById("heartbeat").style.display = 'block';
        document.getElementById("wifi_location").value = "192.168.0.100";
        document.getElementById("wifi_port_num").value = "5003";
 } else {
        document.getElementById("wifi_gw").style.display = 'none';
        document.getElementById("serial_gw").style.display = 'block';
        document.getElementById("wifi_port").style.display = 'none';
        document.getElementById("serial_port").style.display = 'block';
        document.getElementById("gw_timeout_label").style.visibility = 'visible';
        document.getElementById("gw_timeout").style.display = 'block';
        document.getElementById("heartbeat_label").style.visibility = 'hidden';
        document.getElementById("heartbeat").style.display = 'none';
        document.getElementById("serial_location").value = "/dev/ttyAMA0";
        document.getElementById("serial_port_speed").value = "115200";
 }
}
</script>
<?php
}

if ($model_num == 6) {
//Relay model
echo '<div class="modal fade" id="relay_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['relay_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=configure_relay_devices.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['configure_relay_devices'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=setup_pump_type_relays.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_pump_type_relays'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=delete_zones_relays_sensors_nodes.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['delete_zones_relays_sensors_nodes'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['relay_settings_text'].'</p>';
                $query = "SELECT DISTINCT relays.id, relays.relay_id, relays.relay_child_id, relays.on_trigger, relays.lag_time, relays.name, relays.type,
                        IF(zr.zone_id IS NULL, 0, 1) OR IF(sc.heat_relay_id IS NULL, 0, 1) AS attached, nd.node_id, nd.last_seen
                        FROM relays
                        LEFT join zone_relays zr ON relays.id = zr.zone_relay_id
                        LEFT JOIN zone z ON zr.zone_id = z.id
                        JOIN nodes nd ON relays.relay_id = nd.id
                        LEFT JOIN system_controller sc ON relays.id = sc.heat_relay_id
                        ORDER BY relay_id asc, relay_child_id ASC;";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
        			<th class="col-md-3"><small>'.$lang['relay_name'].'</small></th>
        			<th class="col-md-1"><small>'.$lang['type'].'</small></th>
        			<th class="col-md-2"><small>'.$lang['node_id'].'</small></th>
        			<th class="col-md-2"><small>'.$lang['relay_child_id'].'</small></th>
        			<th class="col-md-1">'.$lang['relay_trigger'].'</th>
                                <th class="col-md-1">'.$lang['lag_time'].'</th>
                                <th class="col-md-2"></th>
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
                                        <td>'.$row["lag_time"].'</td>
            				<td><a href="relay.php?id='.$row["id"].'"><button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs"><i class="bi bi-pencil"></i></button></a>&nbsp';
            				if($row['attached'] == 1) {
						echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" title="'.$lang['confirm_del_relay_2'].$attached_to.'"><button class="btn btn-danger btn-xs disabled"><i class="bi bi-trash-fill black"></i></button></span></td>';
	    				} else {
                				echo '<button class="btn warning btn-danger btn-xs" onclick="delete_relay('.$row["id"].');" data-confirm="'.$lang['confirm_del_relay_1'].'"><span class="bi bi-trash-fill black"></span></button> </td>';
            				}
        			echo '</tr>';
			}
		echo '</table>
	    </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <a class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" href="relay.php?id=0">'.$lang['relay_add'].'</a>
            </div>
        </div>
    </div>
</div>';
if ($show_relay_modal == 1) {
        ?>
        <script type="text/javascript">
                $(function(){
                        //instantiate your content as modal
                        $('#relay_setup').modal({
                        //modal options here, like keyboard: false for e.g.
                        });

                        //show the modal when dom is ready
                        $('#relay_setup').modal('show');
                });
        </script>
        <?php
}

//Test Relays
echo '<div class="modal fade" id="test_relays" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['test_relays'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['test_relays_text'].'</p>
                <input type="hidden" id="relay_state_0" name="relay_state_0" value="'.$lang['relay_off'].'">
                <input type="hidden" id="relay_state_1" name="relay_state_1" value="'.$lang['relay_on'].'">
                <table class="table table-bordered">
                        <tr>
                                <th class="col-sm-3 text-center"><small>'.$lang['relay_name'].'</small></th>
                                <th class="col-sm-2 text-center"><small>'.$lang['toggle_relay'].'</small></th>
                        </tr>';
			$relay_map = 0;
                        $query = "SELECT  relays.id, relays.name, messages_out.payload FROM relays, messages_out
                                  WHERE (messages_out.n_id = relays.relay_id) AND (messages_out.child_id = relays.relay_child_id)
                                  ORDER BY relays.relay_id, relays.relay_child_id ASC;";
                        $results = $conn->query($query);
                        while ($row = mysqli_fetch_assoc($results)) {
                                $button_text = $lang['relay_off'];
				$relay_map = ($relay_map << 1) + $row["payload"];
                                echo '<tr>
                                        <td>'.$row["name"].'</td>
                                        <td><input type="button" id="relay_state'.$row["id"].'" value="'.$button_text.'" class="btn btn-primary-'.theme($conn, $theme, 'color').' d-grid gap-2 col-8 mx-auto" onclick="toggle_relay('.$row["id"].');"></td>
                                        <input type="hidden" id="relay_state_value'.$row["id"].'" name="relay_state_value" value="0">
                                </tr>';
                        }
                        echo '<input type="hidden" id="relay_map" name="relay_map" value="'.$relay_map.'">
                </table>
            </div>
                <div class="modal-footer">
                        <input type="button" name="exit" value="'.$lang['exit'].'" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" onclick="toggle_relay_exit()">
            </div>
        </div>
    </div>
</div>';
?>
<script>
function toggle_relay(id)
{
 var id_text = id;
 var e = document.getElementById("relay_state_value" + id_text);
 if (e.value == 0) {
        document.getElementById("relay_state" + id_text).value = document.getElementById("relay_state_1").value;
        document.getElementById("relay_state_value" + id_text).value = 1;
 } else {
        document.getElementById("relay_state" + id_text).value = document.getElementById("relay_state_0").value;
        document.getElementById("relay_state_value" + id_text).value = 0;
 }
 console.log(id_text);
 toggle_relay_state(id_text);
}

//exit test mode if the pop-up modal looses focus
$('#test_relays').on('hidden.bs.modal', function () {
    toggle_relay_exit()
});

//enter test mode
$('#test_relays').on('show.bs.modal', function () {
  toggle_relay_load()
});
</script>
<?php

//Sensor model
echo '<div class="modal fade" id="sensor_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=sensor_types.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['sensor_types'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=humidity_sensors.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['humidity_sensors'].'</a></li>
				<li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=ds18b20_temperature_sensor.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['ds18b20_temperature_sensor'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=import_sensor_readings.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['import_sensor_readings'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=delete_zones_relays_sensors_nodes.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['delete_zones_relays_sensors_nodes'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_sensors.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_sensors'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=setup_sensor_notifications.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_sensor_notifications'].'</a></li>
                                <li class="dropdown-divider"></li>
				<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_multiple_sensors.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_multiple_sensors'].'</a></li>
                    	</ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['sensor_settings_text'].'</p>';
		$query = "select sensors.id, sensors.name, sensors.sensor_child_id, sensors.correction_factor, sensors.mode, sensors.timeout, sensors.resolution, sensors.zone_id, sensors.show_it, sensors.message_in, nodes.node_id, nodes.last_seen from sensors, nodes WHERE sensors.sensor_id = nodes.id order by name asc";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-lg-2"><small>'.$lang['sensor_name'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['node_id'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['sensor_child_id'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['sensor_mode'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['correct_factor'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['res'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['zone_name'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['show'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['msg_in'].'</small></th>
                                <th class="col-lg-2"></th>
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
                                $check_msg_in = ($row['message_in'] == 1) ? 'checked' : '';
                                $cf = ($row['correction_factor'] == 0) ? '0' : $row['correction_factor'];
                                echo '<tr>
                                        <td style="text-align:center; vertical-align:middle;">'.$row["name"].'<br> <small>('.$row["last_seen"].')</small></td>
                                        <td style="text-align:center; vertical-align:middle;">'.$row["node_id"].'</td>
                                        <td style="text-align:center; vertical-align:middle;">'.$row["sensor_child_id"].'</td>';
					if ($row['mode'] == 0) {
						echo'<td style="text-align:center; vertical-align:middle;"><i class="bi bi-caret-right-square-fill green" style="font-size: 1.2rem;"></i></td>';
					} else {
                                                echo'<td style="text-align:center; vertical-align:middle;"><i class="bi bi-bar-chart-fill green" style="font-size: 1.2rem;"> '.$row['timeout'].'</i></td>';
					}
                                        echo '<td style="text-align:center; vertical-align:middle;">'.$cf.'</td>
                                        <td style="text-align:center; vertical-align:middle;">'.$row["resolution"].'</td>
            				<td style="text-align:center; vertical-align:middle;">'.$zone_name.'</td>';
            				if (empty($row['zone_id'])) {
						echo '<td style="text-align:center; vertical-align:middle;">
                				<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox'.$row["id"].'" name="checkbox'.$row["id"].'" value="1" '.$check.'>
            					</td>
                                        	<td style="text-align:center; vertical-align:middle;">
                                        		<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_msg_in'.$row["id"].'" name="checkbox_msg_in'.$row["id"].'" value="1" '.$check_msg_in.'>
                                        	</td>';
	    				} else {
                				echo '<td style="text-align:center; vertical-align:middle;">
                				<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox'.$row["id"].'" name="checkbox'.$row["id"].'" value="1" '.$check.' disabled>
                				</td>
                                        	<td style="text-align:center; vertical-align:middle;">
                                        		<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox_msg_in'.$row["id"].'" name="checkbox_msg_in'.$row["id"].'" value="1" '.$check_msg_in.' disabled>
                                        	</td>';
	    				}
	    				echo '<td style="text-align:center; vertical-align:middle;"><a href="sensor.php?id='.$row["id"].'"><button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs"><i class="bi bi-pencil"></i></button></a>&nbsp';
	    				if (empty($row['zone_id'])) {
						echo '<button class="btn warning btn-danger btn-xs" onclick="delete_sensor('.$row["id"].');" data-confirm="'.$lang['confirm_del_sensor_4'].'"><span class="bi bi-trash-fill black"></span></button> </td>'; 
	    				} else {
						echo '<span class="tooltip-wrapper" data-bs-toggle="tooltip" title="'.$lang['confirm_del_sensor_5'].$zone_name.'"><button class="btn btn-danger btn-xs disabled"><i class="bi bi-trash-fill black"></i></button></span></td>';
	    				}
        			echo '</tr>';
			}
		echo '</table>
	    </div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                	<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="show_sensors()">
                	<a class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" href="sensor.php?id=0">'.$lang['sensor_add'].'</a>
            </div>
        </div>
    </div>
</div>';
if ($show_sensor_modal == 1) {
        ?>
        <script type="text/javascript">
                $(function(){
                        //instantiate your content as modal
                        $('#sensor_setup').modal({
                        //modal options here, like keyboard: false for e.g.
                        });

                        //show the modal when dom is ready
                        $('#sensor_setup').modal('show');
                });
        </script>
        <?php
}

//Sensor Message
echo '
<div class="modal fade" id="sensor_messages" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['custom_sensor_messages'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=custom_sensor_messages.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['custom_sensor_messages'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['custom_sensor_messages_info'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-2"><small>'.$lang['sensor'].'</small></th>
        <th class="col-2"><small>'.$lang['msg_id'].'</small></th>
        <th class="col-2"><small>'.$lang['message_position'].'</small></th>
        <th class="col-3"><small>'.$lang['message'].'</small></th>
        <th class="col-2"><small>'.$lang['color'].'</small></th>
       <th class="col-1"></th>
    </tr>';

$content_msg = "DELETE This Message";

$query = "SELECT sensor_messages.*, sensors.name FROM sensor_messages, sensors WHERE (sensor_messages.sensor_id = sensors.id) AND sensor_messages.purge = 0;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
    if ($row["sub_type"] == 0 || $row["sub_type"] == '0') { $pos = $lang['centre']; } else { $pos = $lang['lower_right']; }
    echo '
        <tr>
            <td>'.$row["name"].'</td>
            <td>'.$row["message_id"].'</td>
            <td>'.$pos.'</td>
            <td>'.$row["message"].'</td>
            <td>'.$row["status_color"].'</td>
            <td><button class="btn warning btn-danger btn-xs" onclick="delete_sensor_message('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_sensor_messages">'.$lang['msg_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Sensor Message
echo '
<div class="modal fade" id="add_sensor_messages" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_message'].'</h5>
            </div>
            <div class="modal-body">';
		$query = "SELECT id, name FROM sensors WHERE sensor_type_id = 3 or sensor_type_id = 4;";
		$results = $conn->query($query);

	echo '<p class="text-muted">'.$lang['add_message_info_text'].'</p>
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['sensor'].'</label> <small class="text-muted">'.$lang['msg_sensor_id_info'].'</small>
	        <select class="form-select" type="text" id="msg_sensor_id" name="msg_sensor_id">';
        		while ($srow=mysqli_fetch_array($results)) {
                		echo '<option value="'.$srow['id'].'">'.$srow['name'].'</option>';
        		}
        	echo '</select>
        	<div class="help-block with-errors"></div>
	</div>
        <div class="form-group" class="control-label"><label>'.$lang['msg_id'].'</label> <small class="text-muted">'.$lang['msg_id_info'].'</small>
        	<input class="form-control" type="text" id="msg_id" name="msg_id" value="" placeholder="'.$lang['msg_id'].'">
        	<div class="help-block with-errors"></div>
	</div>
        <div class="form-group" class="control-label"><label>'.$lang['message_position'].'</label> <small class="text-muted">'.$lang['message_position_info'].'</small>
	        <select <input class="form-select" type="text" id="msg_type_id" name="msg_type_id" value="" placeholder="'.$lang['message_type'].'">
                        <option selected value="0">'.$lang['centre'].'</option>
                        <option value="1">'.$lang['lower_right'].'</option>
        	</select>
        	<div class="help-block with-errors"></div>
	</div>
        <div class="form-group" class="control-label"><label>'.$lang['msg_text'].'</label> <small class="text-muted">'.$lang['msg_text_info'].'</small>
                <input class="form-control" type="text" id="msg_text" name="msg_text" value="" placeholder="'.$lang['msg_text'].'">
                <div class="help-block with-errors"></div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['status_color'].'</label> <small class="text-muted">'.$lang['status_color_info'].'</small>
        	<input class="form-control" type="text" id="msg_status_color" name="msg_status_color" value="" placeholder="'.$lang['status_color_type1'].'">
        	<div class="help-block with-errors"></div>
	</div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_sensor_message()">
            </div>
        </div>
    </div>
</div>';

//Sensor Type
echo '
<div class="modal fade" id="sensor_types" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_type'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=sensor_types.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['sensor_types'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['sensor_type_info'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-8"><small>'.$lang['type'].'</small></th>
         <th class="col-3"><small>'.$lang['units_character'].'</small></th>
       <th class="col-1"></th>
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
            <td><button class="btn warning btn-danger btn-xs" onclick="delete_sensor_type('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_sensor_type">'.$lang['sensor_type_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add Sensor Type
echo '
<div class="modal fade" id="add_sensor_type" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['sensor_type_add'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['sensor_type_add_info_text'].'</p>
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['sensor_type'].'</label> <small class="text-muted">'.$lang['sensor_type_info'].'</small>
        <input class="form-control" type="text" id="sensor_type" name="sensor_type" value="" placeholder="'.$lang['sensor_type'].'">
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['units_character'].'</label> <small class="text-muted">'.$lang['units_character_info'].'</small>
        <input class="form-control" type="text" id="sensor_units" name="sensor_units" value="" placeholder="'.$lang['sensor_units'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_sensor_type()">
            </div>
        </div>
    </div>
</div>';

//Hide Sensor/Relay model
echo '<div class="modal fade" id="hide_sensor_relay" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['hide_sensor_relay'].'</h5>
                <div class="dropdown float-right">
                	<a class="" data-bs-toggle="dropdown" href="#">
                        	<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                        	<li><a class="dropdown-item" href="pdf_download.php?file=setup_user_accounts.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_user_accounts'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['hide_sensor_relay_text'].'</p>';
              	$query = "SELECT `fullname`, `username` FROM `user` WHERE `username` NOT LIKE 'admin' ORDER BY `id`;";
              	$user_results = $conn->query($query);
		$user_count = mysqli_num_rows($user_results);
		if ($user_count > 0) {
			$query = "SELECT `sensors`.`id`, `sensors`.`name`, 'Sensor' AS type, `sensors`.`user_display`
				FROM `sensors`
				WHERE `sensors`.`zone_id` = 0
				UNION
				SELECT `relays`.`id`, `relays`.`name`, 'Relay' AS type, `relays`.`user_display`
				FROM `relays`
				JOIN `zone_relays` zr ON `relays`.`id` = zr.zone_relay_id
				LEFT JOIN `zone` z ON `z`.`id` = `zr`.`zone_id`
				WHERE `z`.`type_id` IS NULL OR `z`.`type_id` != 2;";
			$results = $conn->query($query);
			echo '<table class="table table-bordered">
    				<tr>
                                	<th class="col-lg-2"><small>'.$lang['name'].'</small></th>
	                                <th class="col-lg-1" style="text-align:center; vertical-align:middle;"><small>'.$lang['type'].'</small></th>';
					while ($user_row = mysqli_fetch_assoc($user_results)) {
						echo '<th class="col-lg-1" style="text-align:center; vertical-align:middle;"><small>'.$user_row['username'].'</small></th>';
					}
                                echo '</tr>';
				while ($row = mysqli_fetch_assoc($results)) {
                	                echo '<tr>
                        	                <td>'.$row["name"].'</td>
                                	        <td style="text-align:center; vertical-align:middle;">'.$row["type"].'</td>';
	                                	for ($x = 0; $x < $user_count; $x++) {
							$user_mask = pow(2,$x);
							$check = (($row["user_display"] & $user_mask) > 0) ? 'checked' : '';
			                                if (strpos($row["type"], "Sensor") !== false) { $id = $x."s".$row["id"]; } else { $id = $x."r".$row["id"]; }
							echo '<td style="text-align:center; vertical-align:middle;">
                					<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" id="checkbox'.$id.'" name="checkbox'.$id.'" value="1" '.$check.'>
            						</td>';
						}
	        			echo '</tr>';
				}
			echo '</table>';
		} else {
			echo '<p class="text-center fs-2 font-weight-bold">'.$lang['only_admin_account'].'</p>';
		}
	    echo '</div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>';
			if ($user_count > 0) {
                		echo '<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="hide_sensors_relays()">';
			}
            echo '</div>
        </div>
    </div>
</div>';

//Add HTTP Message model
echo '
<div class="modal fade" id="add_on_http" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>';
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
                echo '<div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_tasmota_lamp_zone.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_add_on_device'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">';

if ($zcount + $ncount > 0) {
        echo '<p class="text-muted"> '.$lang['add_on_settings_text'].' </p>';

        $query = "SELECT zn.name AS zone_name, message_type, command, parameter, nid.type
        FROM http_messages
        LEFT JOIN nodes nid ON http_messages.node_id = nid.node_id
        LEFT JOIN zone zn ON http_messages.zone_id = zn.id;";
        $results = $conn->query($query);
        echo '<table class="table table-bordered">
        <tr>
                <th class="col-2"><small>'.$lang['type'].'</small></th>
                <th class="col-3"><small>'.$lang['zone_name'].'</small></th>
                <th class="col-2"><small>'.$lang['message_type'].'</small></th>
                <th class="col-2"><small>'.$lang['command'].'</small></th>
                <th class="col-2"><small>'.$lang['parameter'].'</small></th>
                <th class="col-1"></th>
        </tr>';
        while ($row = mysqli_fetch_assoc($results)) {
                echo '
                        <tr>
                        <td>'.$row["type"].'</td>
                        <td>'.$row["zone_name"].'</td>
                        <td>'.$row["message_type"].'</td>
                        <td>'.$row["command"].'</td>
                        <td>'.$row["parameter"].'</td>
                        <td><button class="btn warning btn-danger btn-xs" onclick="delete_http_msg('.$row["id"].');" data-confirm="You are about to delete on HTTP Message"><span class="bi bi-trash-fill black"></span></button> </td>
                        </tr>';
        }
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>';
                if ($zcount > 0) {
			echo '<button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#zone_add_http_msg">'.$lang['zone_add_http_msg'].'</button>';
                }
                if ($ncount > 0) {
			echo '<button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#node_add_http_msg">'.$lang['node_add_http_msg'].'</button>';
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
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_on_messages'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_on_add_info_text'].'</p>
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
       	<div class="form-group" class="control-label"><label>'.$lang['zone_name'].'</label> <small class="text-muted">'.$lang['add_zone_name_info'].'</small>
        <select class="form-select" type="text" id="zone_http_id" name="zone_http_id">';
        while ($zrow=mysqli_fetch_array($zresult)) {
        	echo '<option value="'.$zrow['name'].'">'.$zrow['name'].'</option>';
        }
        echo '</select>
    	<div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['message_type'].'</label> <small class="text-muted">'.$lang['message_type_info'].'</small>
	<select <input class="form-select" type="text" id="zone_add_msg_type" name="zone_add_msg_type" value="" placeholder="'.$lang['message_type'].'">
	<option selected value="0">0 </option>
        <option value="1">1 </option>
	</select>
	<div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['http_command'].'</label> <small class="text-muted">'.$lang['http_command_info'].'</small>
	<input class="form-control" type="text" id="zone_http_command" name="zone_http_command" value="" placeholder="'.$lang['http_command'].'">
	<div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['http_parameter'].'</label> <small class="text-muted">'.$lang['http_parameter_info'].'</small>
        <input class="form-control" type="text" id="zone_http_parameter" name="zone_http_parameter" value="" placeholder="'.$lang['http_parameter'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_zone_http_msg()">
            </div>
        </div>
    </div>
</div>';

//Add New HTTP Message based on Node ID
echo '
<div class="modal fade" id="node_add_http_msg" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_on_messages'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['add_on_add_info_text'].'</p>
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['node_id'].'</label> <small class="text-muted">'.$lang['add_node_id_info'].'</small>
        <select class="form-select" type="text" id="node_http_id" name="node_http_id">';
        while ($nrow=mysqli_fetch_array($nresult)) {
                echo '<option value="'.$nrow['node_id'].'">'.$nrow['node_id'].'</option>';
        }
        echo '</select>
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['message_type'].'</label> <small class="text-muted">'.$lang['message_type_info'].'</small>
        <select <input class="form-select" type="text" id="node_add_msg_type" name="node_add_msg_type" value="" placeholder="'.$lang['message_type'].'">
        <option selected value="0">0 </option>
        <option value="1">1 </option>
        </select>
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['http_command'].'</label> <small class="text-muted">'.$lang['http_command_info'].'</small>
        <input class="form-control" type="text" id="node_http_command" name="node_http_command" value="" placeholder="'.$lang['http_command'].'">
        <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['http_parameter'].'</label> <small class="text-muted">'.$lang['http_parameter_info'].'</small>
        <input class="form-control" type="text" id="node_http_parameter" name="node_http_parameter" value="" placeholder="'.$lang['http_parameter'].'">
        <div class="help-block with-errors"></div></div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_node_http_msg()">
            </div>
        </div>
    </div>
</div>';

//MQTT Devices
echo '<div class="modal fade" id="mqtt_devices" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['mqtt_device'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_mqtt.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_guide_mqtt'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_zigbee2mqtt.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_zigbee2mqtt'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
		<p class="text-muted">'.$lang['mqtt_device_text'].'</p>';
		$query = "SELECT `mqtt_devices`.`id`, `mqtt_devices`.`nodes_id` AS `mqtt_nodes_id`, `nodes`.`name` AS `node`, `nodes`.`node_id` AS `node_id`, `mqtt_devices`.`type`,
			`mqtt_devices`.`child_id` AS `child`, `mqtt_devices`.`name` AS `name`, `mqtt_devices`.`mqtt_topic`, `mqtt_devices`.`on_payload`,
			`mqtt_devices`.`off_payload`, `mqtt_devices`.`attribute`, `mqtt_devices`.`notice_interval`, `mqtt_devices`.`min_value`
			FROM `mqtt_devices`, `nodes`
			WHERE `mqtt_devices`.`nodes_id` = `nodes`.`id`
			ORDER BY `mqtt_devices`.`nodes_id`, `mqtt_devices`.`child_id`;";
		$results = $conn->query($query);
		echo '<table class="table table-bordered">
    			<tr>
                                <th class="col-lg-1"><small>'.$lang['node'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['mqtt_child_id'].'</small></th>
                                <th class="col-lg-2"><small>'.$lang['mqtt_child_name'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['mqtt_topic'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['mqtt_on_payload'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['mqtt_off_payload'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['mqtt_attribute'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['notice_interval'].'</small></th>
                                <th class="col-lg-1"><small>'.$lang['min_battery_level'].'</small></th>
                                <th class="col-lg-2"></th>
    			</tr>';
			while ($row = mysqli_fetch_assoc($results)) {
				//check if a STATE record with a matching controller
				if ($row["type"] == 0) {
					$found_product = "SELECT * FROM `mqtt_devices` WHERE `nodes_id` = '{$row['mqtt_nodes_id']}' AND `child_id` = {$row['child']} AND `type` = 1 LIMIT 1;";
					$result = $conn->query($found_product);
					$rowcount=mysqli_num_rows($result);
					if ($rowcount > 0) { $state_record = 1; } else { $state_record = 0; }
				}
                                echo '<tr>
                                        <td><small>'.$row["node_id"].' - '.$row["node"].'</small></td>
                                        <td><small>'.$row["child"].'</small></td>
                                        <td><small>'.$row["name"].'</small></td>
                                        <td><small>'.$row["mqtt_topic"].'</small></td>
                                        <td><small>'.$row["on_payload"].'</small></td>
            				<td><small>'.$row["off_payload"].'</small></td>
                                        <td><small>'.$row["attribute"].'</small></td>';
                                        if ($row["type"] == 0) {
                                        	echo '<td><small>'.$row["notice_interval"].'</small></td>';
					} else {
                                                echo '<td></td>';
                                        }
                                        if ($row["type"] == 0) {
                                        	echo '<td><small>'.$row["min_value"].'</small></td>';
                                        } else {
                                                echo '<td></td>';
                                        }
					if (!$state_record || $row["type"] == 1) {
	    					echo '<td><a href="mqtt_device.php?id='.$row["id"].'" style="text-decoration: none;"><button class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-xs"><i class="bi bi-pencil"></i></button></a>&nbsp
						<button class="btn warning btn-danger btn-xs" onclick="delete_mqtt_device('.$row["id"].');" data-confirm="'.$lang['confirm_del_mqtt_child'].'"><span class="bi bi-trash-fill black"></span></button> </td>';
					} else {
                                                echo '<td></td>';
					}
        			echo '</tr>';
			}
		echo '</table>
	    </div>
		<div class="modal-footer">
                	<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                	<a class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" href="mqtt_device.php">'.$lang['mqtt_add_device'].'</a>
            </div>
        </div>
    </div>
</div>';
}

//EBus Commands
echo '
<div class="modal fade" id="ebus_commands" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['ebus_commands'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=setup_ebus_communication.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_ebus_communication'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=custom_sensor_messages.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['custom_sensor_messages'].'</a></li>
                                <li class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="pdf_download.php?file=import_sensor_readings.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['import_sensor_readings'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['ebus_commands_info'].' </p>';

echo '<table class="table table-bordered">
    <tr>
        <th class="col-3"><small>'.$lang['message'].'</small></th>
        <th class="col-3"><small>'.$lang['message_position'].'</small></th>
        <th class="col-2"><small>'.$lang['ebus_message_offset'].'</small></th>
        <th class="col-3"><small>'.$lang['sensor'].'</small></th>
       <th class="col-1"></th>
    </tr>';

$content_msg = "DELETE This Message";

$query = "SELECT ebus_messages.*, sensors.name FROM ebus_messages, sensors WHERE (ebus_messages.sensor_id = sensors.id) AND ebus_messages.purge = 0;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
    if ($row["position"] == 0 || $row["position"] == '0') { $pos = $lang['centre']; } else { $pos = $lang['lower_right']; }
    echo '
        <tr>
            <td>'.$row["message"].'</td>
            <td>'.$pos.'</td>
            <td>'.$row["offset"].'</td>
            <td>'.$row["name"].'</td>
            <td><button class="btn warning btn-danger btn-xs" onclick="delete_ebus_command('.$row["id"].');" data-confirm="'.$content_msg.'"><span class="bi bi-trash-fill black"></span></button> </a></td>
        </tr>';
}
echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_ebus_command">'.$lang['command_add'].'</button>
            </div>
        </div>
    </div>
</div>';

//Add EBus Command
echo '
<div class="modal fade" id="add_ebus_command" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['ebus_add_command'].'</h5>
            </div>
            <div class="modal-body">';
		$query = "SELECT id, name FROM sensors;";
		$results = $conn->query($query);

	echo '<p class="text-muted">'.$lang['ebus_add_command_info_text'].'</p>
        <form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label"><label>'.$lang['sensor'].'</label> <small class="text-muted">'.$lang['ebus_sensor_id_info'].'</small>
	        <select class="form-select" type="text" id="ebus_sensor_id" name="ebus_sensor_id">';
        		while ($srow=mysqli_fetch_array($results)) {
                		echo '<option value="'.$srow['id'].'">'.$srow['name'].'</option>';
        		}
        	echo '</select>
        	<div class="help-block with-errors"></div>
	</div>
        <div class="form-group" class="control-label"><label>'.$lang['msg_text'].'</label> <small class="text-muted">'.$lang['ebus_message_info'].'</small>
                <input class="form-control" type="text" id="ebus_msg" name="ebus_msg" value="" placeholder="'.$lang['msg_text'].'">
                <div class="help-block with-errors"></div>
        </div>
        <div class="form-group" class="control-label"><label>'.$lang['message_position'].'</label> <small class="text-muted">'.$lang['message_position_info'].'</small>
	        <select <input class="form-select" type="text" id="ebus_position" name="ebus_position" value="" placeholder="'.$lang['message_type'].'">
        		<option selected value="0">'.$lang['centre'].'</option>
        		<option value="1">'.$lang['lower_right'].'</option>
        	</select>
        	<div class="help-block with-errors"></div>
	</div>
        <div class="form-group" class="control-label"><label>'.$lang['ebus_message_offset'].'</label> <small class="text-muted">'.$lang['ebus_message_offset_info'].'</small>
                <input class="form-control" type="text" id="ebus_offset" name="ebus_offset" value="0">
                <div class="help-block with-errors"></div>
        </div>
</div>
            <div class="modal-footer">
                                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_ebus_command()">
            </div>
        </div>
    </div>
</div>';

// Reboot Modal
echo '
<div class="modal fade" id="reboot_system" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['reboot_system'].'</h5>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> <i class="bi bi-bootstrap-reboot orange" style="font-size: 1.2rem;"></i>&nbsp'.$lang['reboot_system_text'].' </p>

            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                        <a href="javascript:reboot()" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm">'.$lang['yes'].'</a>
            </div>
        </div>
    </div>
</div>';

// Shutdown Model
echo '
<div class="modal fade" id="shutdown_system" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['shutdown_system'].'</h5>
            </div>
            <div class="modal-body">
                        <p class="text-muted"><i class="bi bi-toggle2-off red" style="font-size: 1.2rem;"></i> '.$lang['shutdown_system_text'].' </p>
                        ';
echo '            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>
                        <a href="javascript:shutdown()" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm">'.$lang['yes'].'</a>
            </div>
        </div>
    </div>
</div>';

// Offset Modal
global $lang;
//check if weather api is active
$query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
$result = $conn->query($query);
$w_count=mysqli_num_rows($result);
if ($w_count > 0) { $weather_enabled = 1; } else { $weather_enabled = 0; }
echo '
<div class="modal fade" id="offset_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
                        <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['offset_settings'].'</h5>
                <div class="dropdown float-right">
                        <a class="" data-bs-toggle="dropdown" href="#">
                                <i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-'.theme($conn, settings($conn, 'theme'), 'color').'">
                                <li><a class="dropdown-item" href="pdf_download.php?file=start_time_offset.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp'.$lang['setup_start_time_offset'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">';
	echo '<p class="text-muted"> '.$lang['offset_settings_text'].' </p>';

	$query = "SELECT schedule_time_temp_offset.id, schedule_time_temp_offset.status, schedule_time_temp_offset.low_temperature,
	schedule_time_temp_offset.high_temperature, schedule_time_temp_offset.start_time_offset, schedule_time_temp_offset.sensors_id,
	IFNULL(ss.name, 'Weather') AS sensor_name, `schedule_daily_time_id`, sdt.sch_name, IFNULL(ts.sensor_type_id, 1) AS sensor_type_id
        FROM schedule_time_temp_offset
        JOIN schedule_daily_time sdt ON schedule_time_temp_offset.schedule_daily_time_id = sdt.id
        LEFT JOIN sensors ss ON schedule_time_temp_offset.sensors_id = ss.id
        LEFT JOIN sensors ts ON schedule_time_temp_offset.sensors_id = ts.id
        ORDER BY id ASC;";
	$results = $conn->query($query);
	echo '<table class="table table-bordered">
	    <tr>
        	<th class="col-lg-4"><small>'.$lang['sch_name'].'</small></th>
                <th class="col-lg-1"><small>'.$lang['enabled'].'</small></th>
        	<th class="col-lg-1"><small>'.$lang['low_temp'].'</small></th>
        	<th class="col-lg-1"><small>'.$lang['high_temp'].'</small></th>
        	<th class="col-lg-1"><small>'.$lang['offset'].'</small></th>
        	<th class="col-lg-3"><small>'.$lang['control_temp'].'</small></th>
        	<th class="col-lg-1"></th>
    	</tr>';

while ($row = mysqli_fetch_assoc($results)) {
	if ($row["status"]) { $offset_enabled = "checked"; } else { $offset_enabled = ""; }
    	echo '
            <tr>
	        <td><select class="form-select" type="text" id="schedule_id" name="schedule_id" onchange=set_schedule_daily_time_id((this.options[this.selectedIndex].value),'.$row["id"].')>';
        	//Get schedule List
	        $query = "SELECT DISTINCT sch_name, `schedule_daily_time_zone`.`schedule_daily_time_id`
        	FROM `schedule_daily_time`, `schedule_daily_time_zone`
	        WHERE (`schedule_daily_time`.`id` = `schedule_daily_time_zone`.`schedule_daily_time_id`) AND `schedule_daily_time_zone`.`status` = 1;";
        	$result = $conn->query($query);
	        if ($result){
        	        while ($zrow=mysqli_fetch_array($result)) {
				echo '<option value="'.$zrow['schedule_daily_time_id'].'" '.($row['schedule_daily_time_id']==$zrow['schedule_daily_time_id'] ? 'selected' : '').'>'.$zrow['sch_name'].'</option>';
	                }
        	}
        	echo '
        	</select></td>
                <td style="text-align:center; vertical-align:middle;">
                	<input type="checkbox" id="checkbox_offset'.$row["id"].'" name="offset_enabled" size="1" value="1" '.$offset_enabled.'>
                </td>
                <td><input id="low_temp'.$row["id"].'" type="text" class="float-left text" style="border: none" name="low_temp" size="1" value="'.DispSensor($conn,$row["low_temperature"],$row["sensor_type_id"]).'" placeholder="Low Temperature" required></td>
            	<td><input id="high_temp'.$row["id"].'" type="text" class="float-left text" style="border: none" name="high_temp" size="1" value="'.DispSensor($conn,$row["high_temperature"],$row["sensor_type_id"]).'" placeholder="High Temperature" required></td>
            	<td><input id="offset_id'.$row["id"].'" type="text" class="float-left text" style="border: none" name="offset_id" size="1" value="'.$row['start_time_offset'].'" placeholder="Max Time Offset" required></td>
		<td><select class="form-select" type="text" id="sensor'.$row["id"].'" name="sensor" onchange=set_sensors_id((this.options[this.selectedIndex].value),'.$row["id"].')>';
                if ($weather_enabled) { echo '<option value="0" '.($row['sensors_id']==0 ? 'selected' : '').'>Weather</option>'; }
                //get list from sensors table to display
                $query = "SELECT id, name FROM sensors ORDER BY name ASC;";
                $result = $conn->query($query);
                if ($result){
                        while ($srow=mysqli_fetch_array($result)) {
                                echo '<option value="'.$srow['id'].'" '.($row['sensors_id']==$srow['id'] ? 'selected' : '').'>'.$srow['name'].'</option>';
                        }
                }
                echo '</select></td>
	     	<input type="hidden" id="sensors_id'.$row["id"].'" name="sensors_id" value="'.$row["sensors_id"].'">
		<input type="hidden" id="sch_id'.$row["id"].'" name="sch_id" value="'.$row["schedule_daily_time_id"].'">
                <input type="hidden" id="sensor_type'.$row["id"].'" name="sensor_type" value="'.$row["sensor_type_id"].'">
            	<td><button class="btn warning btn-danger btn-xs" onclick="delete_offset('.$row["id"].');" data-confirm="You are about to DELETE this OFFSET Setting"><span class="bi bi-trash-fill black"></span></button> </td>
            </tr>';
}

echo '</table></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="update_offset()">
                <button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" data-bs-href="#" data-bs-toggle="modal" data-bs-target="#add_offset">'.$lang['add_schedule_offset'].'</button>
            </div>
        </div>
    </div>
</div>';
?>
<script>
function set_sensors_id(value, id){
        var valuetext = value;
	var id_text = id;
	document.getElementById("sensors_id" + id_text).value = valuetext;
}

function set_schedule_daily_time_id(value, id){
        var valuetext = value;
        var id_text = id;
        document.getElementById("sch_id" + id_text).value = valuetext;
}
</script>
<?php

//Add Offset
//check if weather api is active
$query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
$result = $conn->query($query);
$w_count=mysqli_num_rows($result);
if ($w_count > 0) { $weather_enabled = 1; } else { $weather_enabled = 0; }
$c_f = settings($conn, 'c_f');
echo '
<div class="modal fade" id="add_offset" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
			<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['add_offset'].'</h5>
            </div>
            <div class="modal-body">';
echo '<p class="text-muted">'.$lang['offset_info_text'].'</p>
	<form data-bs-toggle="validator" role="form" method="post" action="settings.php" id="form-join">
        <div class="form-group" class="control-label">
        	<div class="form-check">';
                	echo '<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" id="checkbox5" class="styled" type="checkbox" value="1" name="offset_status" checked Enabled>';
                        echo '<label class="form-check-label" for="checkbox5"> '.$lang['offset_enable'].'</label>
               	</div>
        </div>
        <!-- /.form-group -->
	<div class="form-group" class="control-label"><label>'.$lang['schedule'].'</label> 
	<select class="form-select" type="text" id="schedule_daily_time_id" name="schedule_daily_time_id">';
	//Get schedule List
	$query = "SELECT DISTINCT sch_name, `schedule_daily_time_zone`.`schedule_daily_time_id` 
	FROM `schedule_daily_time`, `schedule_daily_time_zone`
	WHERE (`schedule_daily_time`.`id` = `schedule_daily_time_zone`.`schedule_daily_time_id`) AND `schedule_daily_time_zone`.`status` = 1;";
	$result = $conn->query($query);
	if ($result){
		while ($zrow=mysqli_fetch_array($result)) {
			echo '<option value="'.$zrow['schedule_daily_time_id'].'">'.$zrow['sch_name'].'</option>';
		}
	}
	echo '
	</select>
    <div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['low_temp'].'</label> <small class="text-muted">'.$lang['low_temp_info'].'</small>
        <select class="form-select" type="text" id="low_temperature" name="low_temperature">';
        if ($c_f == 0) {
                echo '<option value="5">5</option>
                <option value="4">4</option>
                <option value="3">3</option>
                <option value="2">2</option>
                <option value="1">1</option>
                <option value="0">0</option>
                <option value="-1">-1</option>
                <option value="-2">-2</option>
                <option value="-3">-3</option>
                <option value="-4">-4</option>
                <option value="-5">-5</option>';
        } else {
                echo '<option value="40">40</option>
                <option value="39">39</option>
                <option value="38">38</option>
                <option value="37">37</option>
                <option value="36">36</option>
                <option value="35">35</option>
                <option value="34">34</option>
                <option value="33">33</option>
                <option value="32">32</option>
                <option value="31">31</option>
                <option value="30">30</option>
                <option value="29">29</option>
                <option value="28">28</option>
                <option value="27">27</option>
                <option value="26">26</option>
                <option value="25">25</option>
                <option value="24">24</option>
                <option value="23">23</option>
                <option value="22">22</option>
                <option value="21">21</option>
                <option value="20">20</option>';
        }
        echo '</select>
    <div class="help-block with-errors"></div></div>
        <div class="form-group" class="control-label"><label>'.$lang['high_temp'].'</label> <small class="text-muted">'.$lang['high_temp_info'].'</small>
        <select class="form-select" type="text" id="high_temperature" name="high_temperature">';
        if ($c_f == 0) {
                echo '<option value="15">15</option>
                <option value="14">14</option>
                <option value="13">13</option>
                <option value="12">12</option>
                <option value="11">11</option>
                <option value="10">10</option>
                <option value="9">9</option>
                <option value="8">8</option>
                <option value="7">7</option>
                <option value="6">6</option>
                <option value="5">5</option>';
        } else {
                echo '<option value="60">60</option>
                <option value="59">59</option>
                <option value="58">58</option>
                <option value="57">57</option>
                <option value="56">56</option>
                <option value="55">55</option>
                <option value="54">54</option>
                <option value="53">53</option>
                <option value="52">52</option>
                <option value="51">51</option>
                <option value="50">50</option>
                <option value="49">49</option>
                <option value="48">48</option>
                <option value="47">47</option>
                <option value="46">46</option>
                <option value="45">45</option>
                <option value="44">44</option>
                <option value="43">43</option>
                <option value="42">42</option>
                <option value="41">41</option>
                <option value="40">40</option>';
        }
        echo '</select>
    <div class="help-block with-errors"></div></div>
	<div class="form-group" class="control-label"><label>'.$lang['start_time_offset'].'</label> <small class="text-muted">'.$lang['start_time_offset_info'].'</small>
	<select class="form-select" type="text" id="start_time_offset" name="start_time_offset">
	<option value="5">5</option>
	<option value="10">10</option>
	<option value="15">15</option>
	<option value="20">20</option>
	<option value="25">25</option>
	<option value="30">30</option>
	<option value="35">35</option>
	<option value="40">40</option>
	<option value="45">45</option>
	<option value="50">50</option>
	<option value="55">55</option>
	<option value="60">60</option>
	<option value="70">70</option>
	<option value="80">80</option>
	<option value="90">90</option>
	<option value="100">100</option>
	<option value="110">110</option>
	<option value="120">120</option>
	</select>
    <div class="help-block with-errors"></div></div>
    <div class="form-group" class="control-label"><label>'.$lang['sensor_name'].'</label> <small class="text-muted">'.$lang['sensor_name_info'].'</small>
		<select class="form-select" type="text" id="sensor_id" name="sensor_id">';
		if ($weather_enabled) { echo '<option value="0">Weather</option>'; }
		//get list from sensors table to display 
		$query = "SELECT id, name FROM sensors ORDER BY name ASC;";
		$result = $conn->query($query);
		if ($result){
			while ($srow=mysqli_fetch_array($result)) {
				echo '<option value="'.$srow['id'].'">'.$srow['name'].'</option>';
			}
		}
		echo '</select>
	    <div class="help-block with-errors"></div></div>';
echo '</div>
            <div class="modal-footer">
				<button type="button" class="btn btn-primary-'.theme($conn, $theme, 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
                                <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-bm-'.theme($conn, $theme, 'color').' login btn-sm" onclick="add_offset()">
            </div>
        </div>
    </div>
</div>';
?>

<script>
$(document).ready(function(){
 setInterval(function(){//setInterval() method execute on every interval until called clearInterval()
  $('#install_status_text').load("check_install_status.php").fadeIn("slow");
  //load() method fetch data from fetch.php page
 }, 1000);
  $('[data-bs-toggle="popover"]').popover();
  $('[data-bs-toggle="tooltip"]').tooltip();

  $("#maxair_versions").on('show.bs.modal', function () {
    document.getElementById("bs_local").innerHTML=$.fn.popover.Constructor.VERSION;
  });
});

</script>

<script>
$(function() {
    $('button.warning').confirmButton({
        titletxt: "Confirmation"
    });
});
</script>
