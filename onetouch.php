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
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<div class="Light"><i class="fa fa-home fa-fw"></i> <?php echo $lang['home']; ?>
			<div class="pull-right">
				<div class="btn-group"><?php echo date("H:i"); ?>
				</div>
			</div>
		</div>
	</div>
	<!-- /.panel-heading -->
	<div class="panel-body">
		<?php
		//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
		$system_controller_mode = settings($conn, 'mode');

		?>
		<!-- One touch buttons -->
		<a style="color: #777; cursor: pointer; text-decoration: none;" href="home.php?page_name=homelist">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small><?php echo $lang['one_touch']; ?></small></h3>
		<h3 class="degre" style="margin-top:0px;"><i class="fa fa-bullseye fa-2x"></i></h3>
		<h3 class="status"></h3>
		</h3></button></a>

		<?php
		//query to check live temperature status
		$query = "SELECT status FROM live_temperature WHERE status = '1' LIMIT 1";
		$result = $conn->query($query);
		$lt_status=mysqli_num_rows($result);
		if ($lt_status==1) {$lt_status='red';}else{$lt_status='blue';}
		echo '<button class="btn btn-default btn-circle btn-xxl mainbtn animated fadeIn" data-toggle="modal" href="#livetemperature" data-backdrop="static" data-keyboard="false">
		<h3 class="text-info"><small>'.$lang['live_temp'].'</small></h3>
		<h3 class="degre" ><img src="images/thermostat_20.png" border="0"></h3>
		<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$lt_status.'"></i></small></h3>
		</button>';

		//query to check override status
		$query = "SELECT status FROM override WHERE status = '1' LIMIT 1";
		$result = $conn->query($query);
		$override_status=mysqli_num_rows($result);
		if ($override_status==1) {$override_status='red';}else{$override_status='blue';}
		echo '<a style="color: #777; cursor: pointer; text-decoration: none;" href="override.php">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small>'.$lang['override'].'</small></h3>
		<h3 class="degre" ><i class="fa fa-refresh fa-1x"></i></h3>
		<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$override_status.'"></i></small>
		</h3></button></a>';

		//query to check boost status
		$query = "SELECT status FROM boost WHERE status = '1' LIMIT 1";
		$result = $conn->query($query);
		$boost_status=mysqli_num_rows($result);
		if ($boost_status ==1) {$boost_status='red';}else{$boost_status='blue';}
		echo '<a style="color: #777; cursor: pointer; text-decoration: none;" href="boost.php">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small>'.$lang['boost'].'</small></h3>
		<h3 class="degre" ><i class="fa fa-rocket fa-1x"></i></h3>
		<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$boost_status.'"></i></small>
		</h3></button></a>';
		//query to check night climate
		$query = "SELECT * FROM schedule_night_climate_time WHERE id = 1";
		$results = $conn->query($query);
		$row = mysqli_fetch_assoc($results);
		if ($row['status'] == 1) {$night_status='red';}else{$night_status='blue';}
		echo '<a style="color: #777; cursor: pointer; text-decoration: none;" href="scheduling.php?nid=0">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small>'.$lang['night_climate'].'</small></h3>
		<h3 class="degre" ><i class="fa fa-bed fa-1x"></i></h3>
		<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$night_status.'"></i></small>
		</h3></button>';

		//query to check away status
		$query = "SELECT * FROM away LIMIT 1";
		$result = $conn->query($query);
		$away = mysqli_fetch_array($result);
		if ($away['status']=='1'){$awaystatus="red";}elseif ($away['status']=='0'){$awaystatus="blue";}
		echo '<a href="javascript:active_away();">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small>'.$lang['away'].'</small></h3>
		<h3 class="degre" ><i class="fa fa-sign-out fa-1x"></i></h3>
		<h3 class="status"><small class="statuscircle"><i class="fa fa-circle fa-fw '.$awaystatus.'"></i></small>
		</h3></button></a>';

		//query to check holidays status
		$query = "SELECT status FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
		$result = $conn->query($query);
		$holidays_status=mysqli_num_rows($result);
		if ($holidays_status=='1'){$holidaystatus="red";}elseif ($holidays_status=='0'){$holidaystatus="blue";}
		?>
		<a style="color: #777; cursor: pointer; text-decoration: none;" href="holidays.php">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small><?php echo $lang['holidays']; ?></small></h3>
		<h3 class="degre" ><i class="fa fa-paper-plane fa-1x"></i></h3>
		<h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-circle fa-fw <?php echo $holidaystatus; ?>"></i></small>
		</h3></button></a>

                <a style="color: #777; cursor: pointer; text-decoration: none;" href="relay.php">
                <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
                <h3 class="buttontop"><small><?php echo $lang['relay_add']; ?></small></h3>
                <h3 class="degre" ><i class="fa fa-plus fa-1x blue"></i></h3>
                <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
                </h3></button></a>

                <a style="color: #777; cursor: pointer; text-decoration: none;" href="sensor.php">
                <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
                <h3 class="buttontop"><small><?php echo $lang['sensor_add']; ?></small></h3>
                <h3 class="degre" ><i class="fa fa-plus fa-1x green"></i></h3>
                <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
                </h3></button></a>

		<a style="color: #777; cursor: pointer; text-decoration: none;" href="zone.php">
		<button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
		<h3 class="buttontop"><small><?php echo $lang['zone_add']; ?></small></h3>
		<h3 class="degre" ><i class="fa fa-plus fa-1x"></i></h3>
		<h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="fa fa-fw"></i></small>
		</h3></button></a>
		</div>

		<?php
		// live temperature modal
		if ($system_controller_mode == 1) {
                       	$query = "SELECT id, default_c FROM zone_view WHERE type = 'HVAC' LIMIT 1";
		} else {
			$query = "SELECT id, default_c FROM zone_view WHERE type = 'Heating' LIMIT 1";
		}
		$result = $conn->query($query);
		$row = mysqli_fetch_array($result);
		echo '<input type="hidden" id="zone_id" name="zone_id" value="'.$row['id'].'"/>
		<div class="modal fade" id="livetemperature" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
						<h5 class="modal-title">'.$lang['live_temperature'].'</h5>
					</div>
					<div class="modal-body">
						<div style="text-align:center;">
							<h4><br><p>Default Temperature For The Heating Zone</p></h4><br>
							<input type="text" value="'.DispTemp($conn, $row['default_c']).'" class="dial" id="default_c" name="live_temp">
						</div>
					</div>
					<div class="modal-footer"><button type="button" class="btn btn-default btn-sm" data-dismiss="modal">'.$lang['cancel'].'</button>
                				<input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="update_defaut_c()">
					</div>
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
		<?php
		ShowWeather($conn);
		?>

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
$(function() {
   $(".dial").knob({
       'min':0,
       'max':50,
       "fgColor":"#0000FF",
       "skin":"tron",
       'step':1
   });
});
</script>

