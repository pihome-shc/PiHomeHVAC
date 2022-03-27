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
if ($model_num == 1) {
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
}

if ($model_num == 2) {
//Software Install Modal
echo '
<div class="modal fade" id="sw_install" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['software_install'].'</h5>
		                <div class="dropdown pull-right">
                		        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                		<i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
		                        </a>
                		        <ul class="dropdown-menu">
                                		<li><a href="pdf_download.php?file=software_install.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['software_install'].'</a></li>
						<li class="divider"></li>
						<li><a href="pdf_download.php?file=software_install_technical.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['software_install_technical'].'</a></li>
                                                <li class="divider"></li>
                        			<li><a href="pdf_download.php?file=setup_guide_ha_integration.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_ha_integration'].'</a></li>
                         		</ul>
                		</div>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted">'.$lang['install_software_text'].'</p>
                                <div class=\"list-group\">';
                                        $installpath = "/var/www/api/enable_rewrite.sh";
                                        $installname = "Install Apache ReWrite";
                                        if (file_exists("/etc/apache2/mods-available/rewrite.load")) {
                                                $prompt = $lang['re_install'];
                                        } else {
                                                $prompt = $lang['install'];
                                        }
                                        echo '<span class="list-group-item">
                                        <i class="fa fa-terminal fa-2x green"></i> '.$installname.'
                                        <span class="pull-right text-muted small"><button type="button" class="btn btn-default login btn-sm"
                                        onclick="install_software(`'.$installpath.'`)">'.$prompt.'</button></span>
                                        <p class="text-muted">Install ReWrite for Apache Web Server</p></span>';
                                        $path = '/var/www/add_on';
                                        $dir = new DirectoryIterator($path);
                                        foreach ($dir as $fileinfo) {
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
                                                                echo '<span class="list-group-item">
                                                                <i class="fa fa-terminal fa-2x green"></i> '.$name;
                                                                if ($installed == 0) {
                                                                        echo '<span class="pull-right text-muted small"><button type="button" class="btn btn-default login btn-sm"
                                                                        onclick="install_software(`'.$installpath.'`)">'.$lang['install'].'</button></span>';

                                                                } elseif ($installed == 1) {
                                                                        echo '<span class="pull-right text"><p> '.$lang['already_installed'].'</p></span>';
                                                                } else {
                                                                        echo '<span class="pull-right text"><p> '.$lang['no_installer'].'</p></span>';
                                                                }
                                                                echo '<p class="text-muted">'.$description.'</p></span>';
                                                        }
                                                }
                                        }
                                echo '</div>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal-fade -->
';

// Software Install Add
echo '<div class="modal" id="add_install">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['installing_sw'].'</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted">'.$lang['installing_sw_info'].'</p>';
        $output = file_get_contents('/var/www/cron/sw_install.txt');
        echo '<textarea id="install_status_text" style="background-color: black;color:#fff;height: 500px; min-width: 100%"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-sm" onclick="sw_install_close()">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

//MaxAir Code Update
echo '
<div class="modal fade" id="maxair_update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['maxair_update'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=software_update_technical.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['software_update_technical'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> '.$lang['maxair_update_text'].' </p>';
            echo '</div>
            <div class="modal-footer">
                <input type="button" name="submit" value="'.$lang['update_check'].'" class="btn btn-default login btn-sm" onclick="check_updates()">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

// backup_image
echo '
<div class="modal fade" id="backup_image" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['pihome_backup'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_database_backup.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_database_backup'].'</a></li>
                                <li class="divider"></li>
                        	<li><a href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_email_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['pihome_backup_text'].' </p>
			<form data-toggle="validator" role="form" method="post" action="#" id="form-join">
			<div class="form-group" class="control-label"><label>'.$lang['email_address'].'</label> <small class="text-muted">'.$lang['pihome_backup_email_info'].'</small>
			<input class="form-control input-sm" type="text" id="backup_email" name="backup_email" value="'.settings($conn, backup_email).'" placeholder="Email Address to Receive your Backup file">
			<div class="help-block with-errors"></div>
			</div>
			</form>';
echo '     </div>
            <div class="modal-footer">
			<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <a href="javascript:backup_email_update()"><button class="btn btn-default login btn-sm" data-toggle="confirmation" data-title="'.$lang['update_email_address'].'">'.$lang['save'].'</button> </a>
			<a href="javascript:db_backup()" class="btn btn-default login btn-sm">'.$lang['backup_start'].'</a>
            </div>
        </div>
    </div>
</div>';

//user accounts model
echo '
<div class="modal fade" id="user_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['user_accounts'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['user_accounts_text'].' </p>';
echo '<div class=\"list-group\">';
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
        echo "<div href=\"settings.php?uid=".$row['id']."\"  class=\"list-group-item\"> <i class=\"ionicons ion-person blue\"></i> ".$username."
                <span class=\"pull-right text-muted small\"><em>
                <a href=\"user_accounts.php?uid=".$row["id"]."\"><button class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></button>&nbsp</a>";
                if ($_SESSION['user_id'] != $row['id']) {
                        echo "<a href=\"javascript:del_user(".$row["id"].");\"><button class=\"btn btn-danger btn-xs\" data-toggle=\"confirmation\" data-title=".$lang["confirmation"]." data-content=\"$content_msg\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>";
                } else {
                        echo "<button class=\"btn btn-danger btn-xs disabled\"><span class=\"glyphicon glyphicon-trash\"></span></button>";
                }
                echo "</em></span>
        </div>";
}
echo '</div></div>
            <div class="modal-footer">
                <a href="user_accounts.php?uid=0"><button class="btn btn-default login btn-sm">'.$lang['add_user'].'</button></a>
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Setup Database Cleanup intervals
echo '<div class="modal fade" id="db_cleanup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['db_cleanup'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['db_cleanup_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-md-2 text-center"><small>'.$lang['table_name'].'</small></th>
                                <th class="col-md-1 text-center"><small>'.$lang['db_cleanup_value'].'</small></th>
                                <th class="col-md-1 text-center"><small>'.$lang['db_cleanup_period'].'</small></th>
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
                                        <td><input id="period'.$x.'" type="text" class="pull-left text" style="border: none" name="period'.$x.'"  size="3" value="'.$period.'" placeholder="Period" required></td>
                                        <td><select class="form-control input-sm" type="text" id="ival'.$x.'" name="ival'.$x.'" onchange=set_interval('.$x.')>
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
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_db_cleanup()">
            </div>
        </div>
    </div>
</div>';

//set GitHub Repository location
echo '<div class="modal fade" id="set_repository" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['github_repository'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['github_repository_text'].'</p>';
                $query = "SELECT id, status, name FROM repository;";
                $results = $conn->query($query);
                echo '<div class="form-group" class="control-label"><label>'.$lang['repository_url'].'</label> <small class="text-muted"> (Default Repository is - '.$lang['default_repository'].')</small>
                <select class="form-control input-sm" type="text" id="rep_id" name="rep_id" >';
                if ($results){
                        while ($frow=mysqli_fetch_array($results)) {
                                echo '<option value="'.$frow['id'].'" ' . ($frow['status']==1 ? 'selected' : '') . '>https://github.com/'.$frow['name'].'/PiHomeHVAC.git</option>';
                        }
                }
                echo '</select>
                	<div class="help-block with-errors"></div>
                </div>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['set_default'].'" class="btn btn-default login btn-sm" onclick="set_default()">
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_repository()">
            </div>
        </div>
    </div>
</div>';

//set max cpu temperature
echo '<div class="modal fade" id="max_cpu_temp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['max_cpu_temp'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['max_cpu_temp_text'].'</p>';
                $query = "SELECT max_cpu_temp FROM system LIMIT 1;";
                $result = $conn->query($query);
		$row = mysqli_fetch_array($result);
                echo '<div class="form-group" class="control-label"><label>'.$lang['temperature'].'</label> <small class="text-muted"> </small>
                <select class="form-control input-sm" type="text" id="m_cpu_temp" name="m_cpu_temp" >';
                for ($x = 40; $x <=  70; $x = $x + 5) {
                	echo '<option value="'.$x.'" ' . ($x==$row['max_cpu_temp'] ? 'selected' : '') . '>'.$x.'&deg;</option>';
                }
                echo '</select>
                        <div class="help-block with-errors"></div>
                </div>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_max_cpu_temp()">
            </div>
        </div>
    </div>
</div>';
}

if ($model_num == 3) {
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
}

if ($model_num == 4) {
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
}

if ($model_num == 5) {
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
}

if ($model_num == 6) {
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
}

// Reboot Modal
echo '
<div class="modal fade" id="reboot_system" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['reboot_system'].'</h5>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> <i class="ion-ios-refresh-outline orange"></i> '.$lang['reboot_system_text'].' </p>
                        ';
echo '            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                        <a href="javascript:reboot()" class="btn btn-default login btn-sm">'.$lang['yes'].'</a>
            </div>
        </div>
    </div>
</div>';

// Shutdown Model
echo '
<div class="modal fade" id="shutdown_system" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['shutdown_system'].'</h5>
            </div>
            <div class="modal-body">
                        <p class="text-muted"><i class="fa fa-power-off fa-1x red"></i> '.$lang['shutdown_system_text'].' </p>
                        ';
echo '            </div>
            <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                        <a href="javascript:shutdown()" class="btn btn-default login btn-sm">'.$lang['yes'].'</a>
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

function sw_install_close()
{
        $('#sw_install').modal('hide');
        $('#add_install').modal('hide');
}

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

function set_interval(id)
{
 var id_text = id;

 var e = document.getElementById("ival" + id_text);
 var f = document.getElementById("set_interval" + id_text);

 f.value = e.value;
}

function set_default()
{
 document.getElementById("rep_id").value = 1;
}
</script>
