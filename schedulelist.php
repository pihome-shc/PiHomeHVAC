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

$page_refresh = page_refresh($conn);
$theme = settings($conn, 'theme');
$schedule_time = [];
?>
<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
       	<div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
		<div class="d-flex justify-content-between">
			<div>
        			<i class="bi bi-clock icon-fw"></i> <?php echo $lang['schedule']; ?>
			</div>
			<div class="dropdown">
				<a class="" data-bs-toggle="dropdown" href="#">
					<i class="bi bi-file-earmark-pdf text-white"></i>
				</a>
                        	<ul class="dropdown-menu dropdown-menu-<?php echo theme($conn, $theme, 'color'); ?>">
                			<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_scheduling.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp<?php echo $lang['setup_scheduling']; ?></a></li>
	                                <li class="dropdown-divider"></li>
        	                	<li><a class="dropdown-item" href="pdf_download.php?file=start_time_offset.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp<?php echo $lang['setup_start_time_offset']; ?></a></li>
                	                <li class="dropdown-divider"></li>
                        		<li><a class="dropdown-item" href="pdf_download.php?file=away_setup.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp<?php echo $lang['away_setup']; ?></a></li>
                        	</ul>
                        	<div class="btn-group" id="schedule_date"><?php echo '&nbsp;&nbsp;'.date("H:i"); ?></div>
			</div>
		</div>
        </div>
        <!-- /.card-header -->
        <div class="card-body">
        	<ul class="list-group list-group-flush">
                	<li class="list-group-item">
        			<a href="scheduling.php" class="d-flex justify-content-between list-group-item list-group-item-action">
          				<span class="circle orangesch"><i class="bi bi-plus-lg" style="font-size: 1.2rem;"></i></span> 
                        		<span class="header">
                                		<strong class="primary-font"> </strong>
                                		<small class="text-muted">
                                			<?php echo $lang['schedule_add']; ?> <i class="bi bi-chevron-right icon-fw"></i>
                                		</small>
                        		</span>
        			</a>
	                </li>
        	        <?php
			//following variable set to 0 on start for array index.
			$sch_time_index = '0';
			//query to check away status
			$query = "SELECT * FROM away LIMIT 1";
			$result = $conn->query($query);
			$away = mysqli_fetch_array($result);
			$away_status = $away['status'];
			//$query = "SELECT time_id, time_status, `start`, `end`, tz_id, tz_status, zone_id, index_id, zone_name, temperature, max(temperature) as max_c FROM schedule_daily_time_zone_view group by time_id ORDER BY start asc";
			//$query = "SELECT time_id, time_status, `start`, `end`, WeekDays,tz_id, tz_status, zone_id, index_id, zone_name, type, `category`, temperature, FORMAT(max(temperature),2) as max_c, sch_name, max(sunset) AS sunset, sensor_type_id, stype FROM schedule_daily_time_zone_view WHERE holidays_id = 0 AND tz_status = 1 group by time_id ORDER BY start, sch_name asc";
                	$query = "SELECT time_id, time_status, `start`, `end`, WeekDays,tz_id, tz_status, zone_id, index_id, zone_name, type, `category`, temperature,
                        	FORMAT(max(temperature),2) as max_c, sch_name, sch_type, start_sr, start_ss, start_offset, end_sr, end_ss, end_offset, sensor_type_id, stype
	                        FROM schedule_daily_time_zone_view
        	                WHERE holidays_id = 0 AND (tz_status = 1 OR (tz_status = 0 AND disabled = 1))
                	        GROUP BY time_id ORDER BY start, sch_name asc";
			$results = $conn->query($query);
        	        $sch_params = [];
			while ($row = mysqli_fetch_assoc($results)) {
        	                if($row["start_sr"] == 1 || $row["start_ss"] == 1 || $row["end_sr"] == 1 || $row["end_ss"] == 1) { $sr_ss = 1; } else { $sr_ss = 0; }
				if($row["WeekDays"]  & (1 << 0)){ $Sunday_status_icon="bi-check-circle-fill"; $Sunday_status_color="orangefa"; }else{ $Sunday_status_icon="bi-x-circle-fill"; $Sunday_status_color="bluefa"; }
				if($row["WeekDays"]  & (1 << 1)){ $Monday_status_icon="bi-check-circle-fill"; $Monday_status_color="orangefa"; }else{ $Monday_status_icon="bi-x-circle-fill"; $Monday_status_color="bluefa"; }
				if($row["WeekDays"]  & (1 << 2)){ $Tuesday_status_icon="bi-check-circle-fill"; $Tuesday_status_color="orangefa"; }else{ $Tuesday_status_icon="bi-x-circle-fill"; $Tuesday_status_color="bluefa"; }
				if($row["WeekDays"]  & (1 << 3)){ $Wednesday_status_icon="bi-check-circle-fill"; $Wednesday_status_color="orangefa"; }else{ $Wednesday_status_icon="bi-x-circle-fill"; $Wednesday_status_color="bluefa"; }
				if($row["WeekDays"]  & (1 << 4)){ $Thursday_status_icon="bi-check-circle-fill"; $Thursday_status_color="orangefa"; }else{ $Thursday_status_icon="bi-x-circle-fill"; $Thursday_status_color="bluefa"; }
				if($row["WeekDays"]  & (1 << 5)){ $Friday_status_icon="bi-check-circle-fill"; $Friday_status_color="orangefa"; }else{ $Friday_status_icon="bi-x-circle-fill"; $Friday_status_color="bluefa"; }
				if($row["WeekDays"]  & (1 << 6)){ $Saturday_status_icon="bi-check-circle-fill"; $Saturday_status_color="orangefa"; }else{ $Saturday_status_icon="bi-x-circle-fill"; $Saturday_status_color="bluefa"; }
                                $sch_name = $row['sch_name'];

        	                if($row["time_status"] == "0"){
					$shactive="bluesch";
				} else {
					$query = "SELECT schedule FROM zone_current_state WHERE sch_time_id = {$row['time_id']} AND schedule = 1 LIMIT 1;";
                                        $result = $conn->query($query);
                                        $rowcount=mysqli_num_rows($result);
                                        if ($rowcount > 0) {
						$shactive="redsch";
					} else {
                                                $shactive="orangesch";
					}
				}
				$sch_params[] = array('time_id' =>$row['time_id']);
				//time shchedule listing
				echo '<li class="list-group-item">
					<div class="d-flex justify-content-between">
						<span>
							<div class="d-flex justify-content-start">
								<a href="javascript:active_schedule(' . $row["time_id"] . ');" style="text-decoration: none;">
									<span class="" id="sch_status_'.$row["time_id"].'">
                	        						<div class="circle ' . $shactive . '">';
											if ($row["tz_status"] == 1 || ($row["tz_status"] == 0 && $row["time_status"] == 1)) {
			        		        	                		if($row["category"] <> 2 && $row["sensor_type_id"] <> 3) {
													$unit = SensorUnits($conn,$row['sensor_type_id']);
													echo '<p class="schdegree">' . DispSensor($conn, number_format($row["max_c"], 1), $row["sensor_type_id"]) . $unit . '</p>';
												}
											}
		                        					echo ' </div>
									</span>
								</a>
                        					<a style="color: #333; cursor: pointer; text-decoration: none;" data-bs-toggle="collapse" href="#collapse' . $row['tz_id'] . '">
				        	                        <span class="header text-info">&nbsp;&nbsp;
                                					        <span class="label bg-info text-light">' . $sch_name . '</span>';
					                	        	if($row["category"] == 2 && $sr_ss == 1) { echo '&nbsp;&nbsp;<img src="./images/sunset.png">'; }
                                        					echo '<br>&nbsp;&nbsp; '. $row['start'] . ' - ' . $row['end'] . '
									</span>
								</a>
							</div>
						</span>
						<span>
							<a style="color: #333; cursor: pointer; text-decoration: none;" data-bs-toggle="collapse" href="#collapse' . $row['tz_id'] . '">
                        				        <span class="header text-info">
									<small>
									&nbsp;&nbsp;&nbsp;&nbsp;S&nbsp;&nbsp;&nbsp;M&nbsp;&nbsp;&nbsp;T&nbsp;&nbsp;W&nbsp;&nbsp;&nbsp;T&nbsp;&nbsp;&nbsp;F&nbsp;&nbsp;&nbsp;S<br>
									&nbsp;&nbsp;&nbsp;
									<i class="bi ' . $Sunday_status_icon . ' ' . $Sunday_status_color . '" style="font-size: 0.9rem;"></i>
									<i class="bi ' . $Monday_status_icon . ' ' . $Monday_status_color . '" style="font-size: 0.9rem;"></i>
									<i class="bi ' . $Tuesday_status_icon . ' ' . $Tuesday_status_color . '" style="font-size: 0.9rem;"></i>
									<i class="bi ' . $Wednesday_status_icon . ' ' . $Wednesday_status_color . '" style="font-size: 0.9rem;"></i>
									<i class="bi ' . $Thursday_status_icon . ' ' . $Thursday_status_color . '" style="font-size: 0.9rem;"></i>
									<i class="bi ' . $Friday_status_icon . ' ' . $Friday_status_color . '" style="font-size: 0.9rem;"></i>
									<i class="bi ' . $Saturday_status_icon . ' ' . $Saturday_status_color . '" style="font-size: 0.9rem;"></i>
									</small>
								</span>
							</a>
						<span>
					</div>
					<div class="collapse" id="collapse' . $row["tz_id"] . '">
						<br>';

						//zone listing of each time schedule
						$query = "SELECT DISTINCT * FROM  schedule_daily_time_zone_view WHERE holidays_id = 0 AND time_id = {$row['time_id']} order by index_id;";
						$result = $conn->query($query);
						while ($datarw = mysqli_fetch_array($result)) {
							if ($datarw["tz_status"] == "0") {
								$status_icon = "bi-x-circle-fill";
								$status_color = "bluefa";
							} else {
								$status_icon = "bi-check-circle-fill";
								$status_color = "orangefa";
							}
							if ($datarw["coop"] == "1") {
								$coop = '<i class="bi bi-tree-fill green" data-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="' . $lang['schedule_coop_help'] . '"></i>';
							} else {
								$coop = '';
							}

							echo '<ul class="list-group">
								<li class="list-group-item">
									<div class="d-flex justify-content-between">';
	        	                                        	        if ($datarw["category"] <> 2 && $datarw["sensor_type_id"] <> 3) {
        	        	                        				$unit = SensorUnits($conn,$datarw['sensor_type_id']);
											echo '<span>
												<i class="bi ' . $status_icon . ' ' . $status_color . '"></i>  ' . $datarw['zone_name'] . ' ' . $coop;
											echo '</span>
											<span class="text-muted small"><em>' . number_format(DispSensor($conn, $datarw['temperature'],$datarw['sensor_type_id']), 1) . $unit .'</em></span>';
										} else {
											echo '<span>
												<i class="bi ' . $status_icon . ' ' . $status_color . '"></i>  ' . $datarw['zone_name'] . '
											</span>';
										}
									echo '</div>
								</li>
							</ul>';
						} // end while loop

						//delete and edit button for each schedule
						echo '<div class="row mt-2"></div>
						<div class="d-flex justify-content-end">
						<button class="btn warning btn-danger btn-sm" onclick="delete_schedule(' . $row["time_id"] . ');"><span class="bi bi-trash-fill"></span></button> </a> &nbsp;&nbsp;
						<a href="scheduling.php?id=' . $row["time_id"] . '" class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-sm login"><span class="bi bi-pencil"></span></a>
						</div>
					</div>
					<!-- /.collapse -->
				</li>
				<!-- /.panel-colapse -->
				';

				//calculate total time of day schedule using array schedule_time with index as sch_time_index variable
				if ($row["time_status"] == "1") {
        				$total_time = $end_time - $start_time;
                			$total_time = $total_time / 60;
		        	        //save all total_time variable value to schedule_time array and incriment array index (sch_time_index)
        		        	$schedule_time[$sch_time_index] = $total_time;
	                		$sch_time_index = $sch_time_index + 1;
		     		}
      			} //end of schedule time while loop
			$js_sch_params = json_encode($sch_params);
			?>
        	</ul>
   	</div>
	<!-- /.card-body -->
        <div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
		<div class="d-flex justify-content-between">
	                <div class="btn-group" id="footer_weather">
        	                <?php ShowWeather($conn); ?>
                	</div>
                        <div class="btn-group" id="footer_all_running_time">
                    		<?php
                    		echo '<i class="bi bi-clock"></i>&nbspAll Schedule:&nbsp' . secondsToWords((array_sum($schedule_time) * 60));
                    		?>
                	</div>
            	</div>
        </div>
	<!-- /.card-footer -->
</div>
<!-- /.card -->
<?php if (isset($conn)) {
    $conn->close();
} ?>
<script>

// update page data every x seconds
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';

  (function loop() {
    var data = '<?php echo $js_sch_params ?>';
    if (data.length > 0) {
            var obj = JSON.parse(data)
            //console.log(obj.length);

                for (var y = 0; y < obj.length; y++) {
                  $('#sch_status_' + obj[y].time_id).load("ajax_fetch_data.php?id=" + obj[y].time_id + "&type=18").fadeIn("slow");
                  //console.log(obj[y].time_id);
                }
    }

    $('#schedule_date').load("ajax_fetch_data.php?id=0&type=13").fadeIn("slow");
    $('#footer_weather').load("ajax_fetch_data.php?id=0&type=14").fadeIn("slow");
    $('#footer_all_running_time').load("ajax_fetch_data.php?id=0&type=17").fadeIn("slow");
    setTimeout(loop, delay);
  })();
});
</script>

<script>
$(function() {
    $('button.warning').confirmButton({
	titletxt: "Confirmation",
        confirm: "Are you really sure you want to DELETE?"
    });
});
</script>
