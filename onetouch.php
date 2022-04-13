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
<div class="panel panel-primary">
	<div class="panel-heading">
		<div class="Light"><i class="fa fa-home fa-fw"></i> <?php echo $lang['home']; ?>
			<div class="pull-right">
				<div class="btn-group" id="onetouch_date"><?php echo date("H:i"); ?>
				</div>
			</div>
		</div>
	</div>
	<!-- /.panel-heading -->
	<div class="panel-body">
		<?php
		//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
		$system_controller_mode = settings($conn, 'mode') & 0b1;

		?>
		<!-- One touch buttons -->
		<a style="color: #777; cursor: pointer; text-decoration: none;" href="home.php?page_name=homelist">
		<button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn">
		<h3 class="buttontop"><small><?php echo $lang['one_touch']; ?></small></h3>
		<h3 class="degre" style="margin-top:0px;"><i class="fa fa-bullseye fa-2x"></i></h3>
		<h3 class="status"></h3>
		</h3></button></a>

		<?php
		//query to check live temperature status
                $c_f = settings($conn, 'c_f');
                if ($c_f == 0) { $icon = 'thermostat_30_C.png'; } else { $icon = 'thermostat_30_F.png'; }
		$query = "SELECT active FROM livetemp WHERE active = 1 LIMIT 1";
		$result = $conn->query($query);
		$lt_status=mysqli_num_rows($result);
		if ($lt_status==1) {$lt_status='red';}else{$lt_status='blue';}
		echo '<button class="btn btn-default btn-circle '.$button_style.' mainbtn animated fadeIn" data-toggle="modal" href="#livetemperature" data-backdrop="static" data-keyboard="false">
		<h3 class="text-info"><small>'.$lang['live_temp'].'</small></h3>
		<h3 class="degre" style="margin-top:5px;"><img src="images/'.$icon.'" border="0"></h3>
		<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$lt_status.'"></i></small></h3>
		</button>';

                //select addional onetouch buttons
                $button_params = [];
                $query = "SELECT * FROM button_page WHERE page = 2 ORDER BY index_id ASC";
                $results = $conn->query($query);
                if (mysqli_num_rows($results) > 0) {
                        while ($row = mysqli_fetch_assoc($results)) {
                                $var = $row['function'];
                                $var($conn, $lang[$var]);
                                if ($row['page'] == 2) { $button_params[] = array('button_id' =>$row['id'], 'button_name' =>$row['name']); }
                        }
                        $js_button_params = json_encode($button_params);
                }

                if($_SESSION['admin'] == 1) { ?>
                        <a style="font-style: normal; color: #777; cursor: pointer; text-decoration: none;" href="relay.php">
                        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn">
                        <h3 class="buttontop"><small><?php echo $lang['relay_add']; ?></small></h3>
                        <h3 class="degre" ><i class="fa fa-plus fa-1x orange"></i></h3>
                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
                        </h3></button></a>

                        <a style="font-style: normal; color: #777; cursor: pointer; text-decoration: none;" href="sensor.php">
                        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn">
                        <h3 class="buttontop"><small><?php echo $lang['sensor_add']; ?></small></h3>
                        <h3 class="degre" ><i class="fa fa-plus fa-1x green"></i></h3>
                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
                        </h3></button></a>

                        <a style="font-style: normal; color: #777; cursor: pointer; text-decoration: none;" href="mqtt_device.php">
                        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn">
                        <h3 class="buttontop"><small><?php echo $lang['mqtt_add']; ?></small></h3>
                        <h3 class="degre" ><i class="fa fa-plus fa-1x blue"></i></h3>
                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
                        </h3></button></a>

                        <a style="font-style: normal; color: #777; cursor: pointer; text-decoration: none;" href="zone.php">
                        <button type="button" class="btn btn-default btn-circle <?php echo $button_style; ?> mainbtn">
                        <h3 class="buttontop"><small><?php echo $lang['zone_add']; ?></small></h3>
                        <h3 class="degre" ><i class="fa fa-plus fa-1x"></i></h3>
                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
                        </h3></button></a>
                <?php } ?>

		<?php
		// live temperature modal
                $query = "SELECT zone_id, active FROM livetemp LIMIT 1";
                $result = $conn->query($query);
                $rowcount=mysqli_num_rows($result);
                if ($rowcount > 0) {
	                $row = mysqli_fetch_array($result);
        	        $livetemp_zone_id = $row['zone_id'];
                	$livetemp_active = $row['active'];
	                if ($livetemp_active == 0) { $check_visible = 'display:none'; } else { $check_visible = 'display:block'; }
        	        $query = "SELECT mode, temp_reading, temp_target FROM zone_current_state WHERE zone_id = ".$livetemp_zone_id." LIMIT 1";
                	$result = $conn->query($query);
	                $row = mysqli_fetch_array($result);
        	        $zone_mode = $row['mode'];
                	$zone_mode_main=floor($zone_mode/10)*10;
                        $query = "SELECT min_c, max_c, default_c FROM zone_view WHERE id =  ".$livetemp_zone_id." LIMIT 1";
                        $zresult = $conn->query($query);
                        $zrow = mysqli_fetch_array($zresult);
                        if ($zone_mode == 0) {
                                $set_temp = $zrow['default_c'];
                        } else {
                                $set_temp = $row['temp_target'];
                        }
        	        switch ($zone_mode_main) {
                	        case 0:
                        	        $current_mode = "";
                                	break;
	                        case 50:
        	                        $current_mode = "Night Climate";
                	                break;
                        	case 60:
                                	$current_mode = "Boost";
	                                break;
        	                case 70:
                	                $current_mode = "Override";
                        	        break;
	                        case 80:
        	                        $current_mode = "Schedule";
                	                break;
                        	case 140:
	                                $current_mode = "Manual";
        	                        break;
                	        default:
                        	        $current_mode = "";
	                }
                        echo '<input type="hidden" id="zone_id" name="zone_id" value="'.$livetemp_zone_id.'"/>
                        <input type="hidden" id="min_c" name="min_c" value="'.DispSensor($conn,$zrow['min_c'],1).'"/>
                        <input type="hidden" id="max_c" name="max_c" value="'.DispSensor($conn,$zrow['max_c'],1).'"/>';
		} // end if ($rowcount > 0)
		echo '<div class="modal fade" id="livetemperature" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<h5 class="modal-title">'.$lang['live_temperature'].'</h5>
					</div>
                                        <div class="modal-body">
                                                <div style="text-align:center;">';
                                                        if ($rowcount > 0) {
                                                                echo '<h4><br><p>Heating Zone '.$current_mode.' Temperature Control</p></h4><br>
                                                                <input type="text" value="'.DispSensor($conn, $set_temp, 1).'" class="dial" id="livetemp_c" name="live_temp">
                                                                <div style="float:right;">
                                                                        <textarea id="load_temp" class="temperature-box" readonly="readonly" row="0" col="0" ></textarea>
                                                                </div>
                                                                <div class="checkbox checkbox-default checkbox-circle" style="'.$check_visible.'">
                                                                        <input id="checkbox" class="styled" type="checkbox" value="0" name="status" checked Enabled>
                                                                        <label for="checkbox"> '.$lang['livetemp_enable'].'</label>
                                                                </div>';
                                                        } else {
                                                                echo '<h4><br><p>'.$lang['livetemp_no_control_zone'].'</p></h4><br>';
                                                        }
                                                echo '</div>
                                        </div>
                                        <!-- /.modal-body -->
                                        <div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>';
                                                if ($rowcount > 0) { echo '<input type="button" name="submit" value="'.$lang['apply'].'" class="btn btn-default login btn-sm" onclick="update_livetemp()">'; }
                                        echo '</div>
                			<!-- /.modal-footer -->
				</div>
				<!-- /.modal-content -->
			</div>
			<!-- /.modal-dialog -->
		</div>
		<!-- /.modal fade -->
	</div>'; ?>
	<!-- /.panel-body -->
                <div class="panel-footer">
                        <div class="btn-group" id="footer_weather">
                                <?php
                                ShowWeather($conn);
                                ?>
                        </div>

		<div class="pull-right">
			<div class="btn-group">
				<?php
				$query="select date(start_datetime) as date,
				sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
				sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts,
				(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
				from system_controller_logs WHERE date(start_datetime) = CURDATE() GROUP BY date(start_datetime) asc";
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
// Knob function for live temperature model
$(function() {
   $(".dial").knob({
       'min':0,
       'max':document.getElementById("max_c").value,
       "fgColor":"#000000",
       "skin":"tron",
       'step':0.5
   });
});

// update the heating zone temperature every 1 second
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';

  (function loop() {
    $('#load_temp').load("fetch_temp.php").fadeIn("slow");
    //load() method fetch data from fetch.php page

    var data2 = '<?php echo $js_button_params ?>';
    if (data2.length > 0) {
            var obj2 = JSON.parse(data2)
            //console.log(obj.length);

            for (var y = 0; y < obj2.length; y++) {
              $('#bs1_' + obj2[y].button_id).load("fetch_homelist.php?button_id=" + obj2[y].button_id + "&type=11").fadeIn("slow");
              $('#bs2_' + obj2[y].button_id).load("fetch_homelist.php?button_id=" + obj2[y].button_id + "&type=12").fadeIn("slow");
//              console.log(obj2[y].button_id);
              //load() method fetch data from fetch.php page
            }
    }

    $('#onetouch_date').load("fetch_homelist.php?zone_id=0&type=13").fadeIn("slow");
    $('#footer_weather').load("fetch_homelist.php?zone_id=0&type=14").fadeIn("slow");
    setTimeout(loop, delay);
  })();
});
</script>

