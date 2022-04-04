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
?>
<div class="panel panel-primary">
       	<div class="panel-heading">
        	<i class="fa fa-clock-o fa-fw"></i> <?php echo $lang['schedule']; ?>
		<div class="dropdown pull-right">
			<a class="dropdown-toggle" data-toggle="dropdown" href="#">
				<i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
			</a>
                        <ul class="dropdown-menu">
                		<li><a href="pdf_download.php?file=setup_guide_scheduling.pdf" target="_blank"><i class="fa fa-file fa-fw"></i><?php echo $lang['setup_scheduling']; ?></a></li>
                                <li class="divider"></li>
                        	<li><a href="pdf_download.php?file=start_time_offset.pdf" target="_blank"><i class="fa fa-file fa-fw"></i><?php echo $lang['setup_start_time_offset']; ?></a></li>
                                <li class="divider"></li>
                        	<li><a href="pdf_download.php?file=away_setup.pdf" target="_blank"><i class="fa fa-file fa-fw"></i><?php echo $lang['away_setup']; ?></a></li>
                        </ul>
                        <div class="btn-group"><?php echo '&nbsp;&nbsp;'.date("H:i"); ?></div>
		</div>
        </div>
        <!-- /.panel-heading -->
        <div class="panel-body">
        	<ul class="chat">
                <li class="left clearfix">
                <a href="scheduling.php" style="color: #777; cursor: pointer;">
                <span class="chat-img pull-left">
                <div class="circle orangesch">
                       	<i class="ionicons ion-plus"></i>
                </div>
                </span>
                <div class="chat-body clearfix">
                       	<div class="header">
                               	<strong class="primary-font"> </strong>
                               	<small class="pull-right text-muted">
                                <?php echo $lang['schedule_add']; ?> <i class="fa fa-chevron-right fa-fw"></i>
                                </small>
                        </div>
               	</div>
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
                        WHERE holidays_id = 0 AND (tz_status = 1 OR (tz_status = 0 AND time_status = 1))
                        GROUP BY time_id ORDER BY start, sch_name asc";
		$results = $conn->query($query);
		while ($row = mysqli_fetch_assoc($results)) {
                        $dow = idate('w');
                        $prev_dow = $dow - 1;
                        if($row["start_sr"] == 1 || $row["start_ss"] == 1 || $row["end_sr"] == 1 || $row["end_ss"] == 1) { $sr_ss = 1; } else { $sr_ss = 0; }
			if($row["WeekDays"]  & (1 << 0)){ $Sunday_status_icon="ion-checkmark-circled"; $Sunday_status_color="orangefa"; }else{ $Sunday_status_icon="ion-close-circled"; $Sunday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 1)){ $Monday_status_icon="ion-checkmark-circled"; $Monday_status_color="orangefa"; }else{ $Monday_status_icon="ion-close-circled"; $Monday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 2)){ $Tuesday_status_icon="ion-checkmark-circled"; $Tuesday_status_color="orangefa"; }else{ $Tuesday_status_icon="ion-close-circled"; $Tuesday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 3)){ $Wednesday_status_icon="ion-checkmark-circled"; $Wednesday_status_color="orangefa"; }else{ $Wednesday_status_icon="ion-close-circled"; $Wednesday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 4)){ $Thursday_status_icon="ion-checkmark-circled"; $Thursday_status_color="orangefa"; }else{ $Thursday_status_icon="ion-close-circled"; $Thursday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 5)){ $Friday_status_icon="ion-checkmark-circled"; $Friday_status_color="orangefa"; }else{ $Friday_status_icon="ion-close-circled"; $Friday_status_color="bluefa"; }
			if($row["WeekDays"]  & (1 << 6)){ $Saturday_status_icon="ion-checkmark-circled"; $Saturday_status_color="orangefa"; }else{ $Saturday_status_icon="ion-close-circled"; $Saturday_status_color="bluefa"; }

                        if($row["time_status"]=="0"){ $shactive="bluesch"; }else{ $shactive="orangesch"; }
			$sch_name = $row['sch_name'];
                        $sch_type = $row['sch_type'];
                        $time = strtotime(date("G:i:s"));
                        $start_time = strtotime($row['start']);
                        $end_time = strtotime($row['end']);
                        $start_sr = $row['start_sr'];
                        $start_ss = $row['start_ss'];
                        $start_offset = $row['start_offset'];
                        $end_sr = $row['end_sr'];
                        $end_ss = $row['end_ss'];
                        $end_offset = $row['end_offset'];
                        if ($start_sr == 1 || $start_ss == 1 || $end_sr == 1 || $end_ss == 1) {
                                $query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
                                $result = $conn->query($query);
                                $rowcount=mysqli_num_rows($result);
                                if ($rowcount > 0) {
                                        $wrow = mysqli_fetch_array($result);
                                        $sunrise_time = date('H:i:s', $wrow['sunrise']);
                                        $sunset_time = date('H:i:s', $wrow['sunset']);
                                        if ($start_sr == 1 || $start_ss == 1) {
                                                if ($start_sr == 1) { $start_time = strtotime($sunrise_time); } else { $start_time = strtotime($sunset_time); }
                                                $start_time = $start_time + ($start_offset * 60);
                                        }
                                        if ($end_sr == 1 || $end_ss == 1) {
                                                if ($end_sr == 1) { $end_time = strtotime($sunrise_time); } else { $end_time = strtotime($sunset_time); }
                                                $end_time = $end_time + ($end_offset * 60);
                                        }
                                }
                        }
                        if ((($end_time > $start_time && $time > $start_time && $time < $end_time && ($row["WeekDays"]  & (1 << $dow)) > 0) || ($end_time < $start_time && $time < $end_time && ($row["WeekDays"]  & (1 << $prev_dow)) > 0) || ($end_time < $start_time && $time > $start_time && ($row["WeekDays"]  & (1 << $dow)) > 0)) && $row["time_status"]=="1") {
				if (($sch_type == 1 && $away_status == 1) || ($sch_type == 0 && $away_status == 0)) { $shactive="redsch"; }
                        }

			//time shchedule listing
			echo '
			<li class="left clearfix scheduleli animated fadeIn">
			<a href="javascript:active_schedule(' . $row["time_id"] . ');">
			<span class="chat-img pull-left">
                        <div class="circle ' . $shactive . '">';
                                if($row["category"] <> 2 && $row["sensor_type_id"] <> 3) {
					$unit = SensorUnits($conn,$row['sensor_type_id']);
					echo '<p class="schdegree">' . DispSensor($conn, number_format($row["max_c"], 1), $row["sensor_type_id"]) . $unit . '</p>';
				}
                        echo ' </div>
			</span>
			</a>

			<a style="color: #333; cursor: pointer; text-decoration: none;" data-toggle="collapse" data-parent="#accordion" href="#collapse' . $row['tz_id'] . '">
                        <div class="chat-body clearfix">
                                <div class="header text-info">&nbsp;&nbsp;';
                                        echo '<span class="label label-info">' . $sch_name . '</span>';
                                        if($row["category"] == 2 && $sr_ss == 1) { echo '&nbsp;&nbsp;<img src="./images/sunset.png">'; }
                                        echo '<br>&nbsp;&nbsp; '. $row['start'] . ' - ' . $row['end'] . ' &nbsp;&nbsp;

					<small class="pull-right pull-right-days pull-right-sch-list">
					&nbsp;&nbsp;&nbsp;&nbsp;S&nbsp;&nbsp;&nbsp;M&nbsp;&nbsp;&nbsp;T&nbsp;&nbsp;W&nbsp;&nbsp;&nbsp;T&nbsp;&nbsp;&nbsp;F&nbsp;&nbsp;&nbsp;S<br>
					&nbsp;&nbsp;&nbsp;
					<i class="ionicons ' . $Sunday_status_icon . ' fa-lg ' . $Sunday_status_color . '"></i>
					<i class="ionicons ' . $Monday_status_icon . ' fa-lg ' . $Monday_status_color . '"></i>
					<i class="ionicons ' . $Tuesday_status_icon . ' fa-lg ' . $Tuesday_status_color . '"></i>
					<i class="ionicons ' . $Wednesday_status_icon . ' fa-lg ' . $Wednesday_status_color . '"></i>
					<i class="ionicons ' . $Thursday_status_icon . ' fa-lg ' . $Thursday_status_color . '"></i>
					<i class="ionicons ' . $Friday_status_icon . ' fa-lg ' . $Friday_status_color . '"></i>
					<i class="ionicons ' . $Saturday_status_icon . ' fa-lg ' . $Saturday_status_color . '"></i>
					</small>
				</div>
			</div>
			</a>

			<div id="collapse' . $row["tz_id"] . '" class="panel-collapse collapse">
				<br>';

				//zone listing of each time schedule
				$query = "SELECT * FROM  schedule_daily_time_zone_view WHERE holidays_id = 0 AND time_id = {$row['time_id']} order by index_id;";
				$result = $conn->query($query);
				while ($datarw = mysqli_fetch_array($result)) {
					if ($datarw["tz_status"] == "0") {
						$status_icon = "ion-close-circled";
						$status_color = "bluefa";
					} else {
						$status_icon = "ion-checkmark-circled";
						$status_color = "orangefa";
					}
					if ($datarw["coop"] == "1") {
						$coop = '<i class="glyphicon glyphicon-leaf green" data-container="body" data-toggle="popover" data-placement="right" data-content="' . $lang['schedule_coop_help'] . '"></i>';
					} else {
						$coop = '';
					}

					echo '
					<div class="list-group">
						<div class="list-group-item">';
                                                        if ($datarw["category"] <> 2 && $datarw["sensor_type_id"] <> 3) {
                                        			$unit = SensorUnits($conn,$datarw['sensor_type_id']);
								echo '<i class="ionicons ' . $status_icon . ' fa-lg ' . $status_color . '"></i>  ' . $datarw['zone_name'] . ' ' . $coop . '<span class="pull-right text-muted small"><em>' . number_format(DispSensor($conn, $datarw['temperature'],$datarw['sensor_type_id']), 1) . $unit .'</em></span>';
							} else {
								echo '<i class="ionicons ' . $status_icon . ' fa-lg ' . $status_color . '"></i>  ' . $datarw['zone_name'] . '<span class="pull-right text-muted small"></em></span>';
							}
						echo '</div>';
				} // end while loop

				//delete and edit button for each schedule
				echo '
				<small class="pull-right"><br>
				<a href="javascript:delete_schedule(' . $row["time_id"] . ');"><button class="btn btn-danger btn-sm" data-toggle="confirmation" data-title="ARE YOU SURE?" data-content="You are about to DELETE this SCHEDULE"><span class="glyphicon glyphicon-trash"></span></button> </a> &nbsp;&nbsp;
				<a href="scheduling.php?id=' . $row["time_id"] . '" class="btn btn-default btn-sm login"><span class="ionicons ion-edit"></span></a>
				</small>
			</div>
			<!-- /.list-group -->
		</div>
		<!-- /.panel-colapse -->
		</li>';

		//calculate total time of day schedule using array schedule_time with index as sch_time_index variable
		if ($row["time_status"] == "1") {
        		$total_time = $end_time - $start_time;
                	$total_time = $total_time / 60;
	                //save all total_time variable value to schedule_time array and incriment array index (sch_time_index)
        	        $schedule_time[$sch_time_index] = $total_time;
                	$sch_time_index = $sch_time_index + 1;
     		}
      	} //end of schedule time while loop 
	?>
        </ul>
        </div>
	<!-- /.panel-body -->
        <div class="panel-footer">
        	<?php
            	ShowWeather($conn);
            	?>
            	<div class="pull-right">
                	<div class="btn-group">
                    		<?php
                    		echo '<i class="ionicons ion-ios-clock-outline"></i> All Schedule: ' . secondsToWords((array_sum($schedule_time) * 60));
                    		?>
                	</div>
            	</div>
        </div>
	<!-- /.panel-footer -->
</div>
<!-- /.panel-primary -->
<?php if (isset($conn)) {
    $conn->close();
} ?>
<script>
$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>
