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

// show frost protection
echo '<div class="modal fade" id="show_frost" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['frost_protection'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=frost_protection.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_frost_protection'].'</a></li>
                         </ul>
                </div>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['frost_ptotection_info'].'</p>';
                $query = "SELECT sensors.sensor_id, sensors.sensor_child_id, sensors.name AS sensor_name, sensors.frost_temp, relays.name AS controller_name FROM sensors, relays WHERE (sensors.frost_controller = relays.id) AND frost_temp <> 0;";
                $results = $conn->query($query);
                echo '<table class="table table-bordered">
                        <tr>
                                <th style="text-align:center; vertical-align:middle;" class="col-xs-3"><small>'.$lang['temperature_sensor'].'</small></th>
                                <th style="text-align:center; vertical-align:middle;" class="col-xs-3"><small>'.$lang['frost_temparature'].'</small></th>
                                <th style="text-align:center; vertical-align:middle;" class="col-xs-3"><small>'.$lang['frost_controller'].'</small></th>
                                <th style="text-align:center; vertical-align:middle;" class="col-xs-1"><small>'.$lang['status'].'</small></th>
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
                                        <td style="text-align:center; vertical-align:middle;"><class="statuscircle"><i class="fa fa-circle fa-fw '.$scolor.'"></i></td>
                                </tr>';
                        }
                echo '</table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//last job log status model
echo '
<div class="modal fade" id="status_job" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['jobs_status_log'].'</h5>
                        </div>
                        <div class="modal-body">
                                <div class="form-group" class="control-label"><label>'.$lang['jobs_name'].'</label> <small class="text-muted">'.$lang['last_job_log_info'].'</small>
                                        <select class="form-control input-sm" type="text" id="job_name" name="job_name" onchange=last_job_log(this.options[this.selectedIndex].value)>';
                                        //get list of heat relays to display
                                        $query = "SELECT id, job_name, output FROM jobs;";
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
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">Close</button>
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
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['last_sw_install'].'</h4>
      </div>
      <div class="modal-body">';
        $output = file_get_contents('/var/www/cron/sw_install.txt');
        echo '<textarea id="install_status_text" style="background-color: black;color:#fff;height: 500px; min-width: 100%">'.$output.'</textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

// Documentation Model
echo '<div class="modal" id="documentation" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['documentation'].'</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted">'.$lang['documentation_info'].'</p>
        <div class=\"list-group\">';
                $path = '/var/www/documentation/pdf_format';
                $allFiles = array_diff(scandir($path . "/"), [".", ".."]); // Use array_diff to remove both period values eg: ("." , "..")
                foreach ($allFiles as $value) {
			$title = substr($value, 0, -4);
                        echo '<span class="list-group-item">
                        <i class="fa fa-file fa-2x orange"></i> '.$lang[$title].'<a href="pdf_download.php?file='.$value.'" target="_blank">
                        <button type="button" class="pull-right btn btn-default login btn-sm" >'.$lang['open'].'</button></a></span>';
                }
        echo '</div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

//OS version model
//$osversion = exec ("cat /etc/os-release");
//$lines=file('/etc/os-release');
$lines=array();
$fp=fopen('/etc/os-release', 'r');
while ($fp && !feof($fp)){
    $line=fgets($fp);
    //process line however you like
    $line=trim($line);
    //add to array
    $lines[]=$line;
}
fclose($fp);
echo '
<div class="modal fade" id="os_version" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['os_version'].'</h5>
            </div>
            <div class="modal-body">
			   <div class="list-group">
				<a href="#" class="list-group-item"><i class="fa fa-linux"></i> '.$lines[1].'</a>
				<a href="#" class="list-group-item"><i class="fa fa-linux"></i> '.$lines[3].'</a>
				</div>
           </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//MaxAir Versions
echo '
<div class="modal fade" id="maxair_versions" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
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
                                $file2 = file('https://raw.githubusercontent.com/'.$row['name'].'/PiHomeHVAC/master/st_inc/db_config.ini');
                                $pieces =  explode(' ', $file2[count($file2) - 4]);
                                $code_v_github = array_pop($pieces);
                                $pieces =  explode(' ', $file2[count($file2) - 3]);
                                $code_b_github = array_pop($pieces);
                                $pieces =  explode(' ', $file2[count($file2) - 2]);
                                $db_v_github = array_pop($pieces);
                                $pieces =  explode(' ', $file2[count($file2) - 1]);
                                $db_b_github = array_pop($pieces);

                                echo '<p class="text-muted"> '.$lang['maxair_versions_text'].' <br>'.$lang['repository'].' - https://github.com/'.$row['name'].'/PiHomeHVAC.git</p>
                                <table class="table table-bordered">
                                        <tr>
                                                <th class="col-xs-8"></th>
                                                <th class="col-xs-2" "not_mapped_style" style="text-align:center">'.$lang['maxair_update_installed'].'</th>
                                                <th class="col-xs-2" "not_mapped_style" style="text-align:center">'.$lang['maxair_update_github'].'</th>
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
                                        </tr>';

                                echo '</table>
                        </div>
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                </div>
        </div>
</div>';

//wifi model
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
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['wifi_settings'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['wifi_settings_text'].' </p>
<div class="list-group">
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['status'].': '.$wifistatus.'
</a>
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['mac'].': '.$wifimac.'
</a>
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['download'].': '.number_format($rxwifidata,0).' MB 
</a>
<a href="#" class="list-group-item">
<i class="fa fa-signal green"></i> '.$lang['upload'].': '.number_format($txwifidata,0).' MB 
</a>
</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//ethernet model
$rxdata = exec ("cat /sys/class/net/eth0/statistics/rx_bytes");
$txdata = exec ("cat /sys/class/net/eth0/statistics/tx_bytes");
$rxdata = $rxdata/1024; // convert to kb
$rxdata = $rxdata/1024; // convert to mb
$txdata = $txdata/1024; // convert to kb
$txdata = $txdata/1024; // convert to mb
$nicmac = exec ("cat /sys/class/net/eth0/address");
$nicpeed = exec ("cat /sys/class/net/eth0/speed");
$nicactive = exec ("cat /sys/class/net/eth0/operstate");
echo '
<div class="modal fade" id="eth_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['ethernet_settings'].'</h5>
            </div>
            <div class="modal-body">
			   <div class="list-group">
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['status'].': '.$nicactive.'</a>
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['speed'].': '.$nicpeed.'Mb</a>
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['mac'].': '.$nicmac.'</a>
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['download'].': '.number_format($rxdata,0).' MB </a> 
				<a href="#" class="list-group-item"><i class="ionicons ion-network green"></i>
				'.$lang['upload'].': '.number_format($txdata,0).' MB </a>
				</div>
           </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Big Thank you
echo '
<div class="modal fade" id="big_thanks" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['credits'].'</h5>
            </div>
            <div class="modal-body">
<p class="text-muted"> '.$lang['credits_text'].' </p>';
echo '	<div class=\"list-group\">';
echo " 

<a href=\"http://startbootstrap.com/template-overviews/sb-admin-2\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> SB Admin 2 Template <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://www.cssscript.com/pretty-checkbox-radio-inputs-bootstrap-awesome-bootstrap-checkbox-css\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Pretty Checkbox <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"https://fortawesome.github.io/Font-Awesome\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Font-Awesome <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://ionicons.com\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Ionicons <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://www.cssmatic.com/box-shadow\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Box Shadow CSS <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"https://daneden.github.io/animate.css\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> Animate.css <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"https://www.mysensors.org\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> MySensors <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://www.pihome.eu\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> All others if forget them... <span class=\"pull-right text-muted small\"><em>...</em></span></a>
<a href=\"http://pihome.harkemedia.de\" class=\"list-group-item\"><i class=\"ionicons ion-help-buoy blueinfo\"></i> RaspberryPi Home Automation <span class=\"pull-right text-muted small\"><em>...</em></span></a>
";
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
  $('[data-toggle="popover"]').popover();
});

$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>
