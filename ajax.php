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

if(!isset($_GET['Ajax'])){
    //Check this once, instead of everytime. Should be more efficient.
    //if($DEBUG==true)
    //{
        var_dump($_GET);
        echo '<br />';
    //}
    echo __FILE__ . ' ' . __LINE__ . ' Error: Ajax action is not set.';
    return;
}

function GetModal_Sensor_Graph($conn)
{
        global $lang;
        //foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";

	//create array of colours for the graphs
	$query ="SELECT * FROM sensors ORDER BY id ASC;";
	$results = $conn->query($query);
	$counter = 0;
	$count = mysqli_num_rows($results) + 2; //extra space made for system temperature graph
	$sensor_color = array();
	while ($row = mysqli_fetch_assoc($results)) {
        	$graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
        	$sensor_color[$graph_id] = graph_color($count, ++$counter);
	}

	$pieces = explode(',', $_GET['Ajax']);
        $sensor_id = $pieces[1];
	$query="SELECT * FROM sensors WHERE id = {$pieces[1]} LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_assoc($result);
	$name = $row['name'];
	$nodes_id = $row['sensor_id'];
	$child_id = $row['sensor_child_id'];
	$type_id = $row['sensor_type_id'];
	if ($type_id == 1) {
		$title = $lang['temperature'];
	} elseif ($type_id == 2) {
		$title = $lang['humidity'];
        } elseif ($type_id == 5) {
                $title = $lang['pressure'];
        } elseif ($type_id == 7) {
                $title = $lang['gas'];
	}
        $title = $title.' '.$lang['graph'].' - '.$name;
        $graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
	$query="SELECT node_id FROM nodes WHERE id = {$nodes_id} LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
	if ($pieces[2] == 0) {
        	$query="SELECT * from messages_in_view_24h  where node_id = '{$row['node_id']}' AND child_id = {$child_id} ORDER BY id ASC;";
                $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$pieces[1].",1";
		$button_name = $lang['graph_1h'];
	} else {
                $query="SELECT * from messages_in_view_1h  where node_id = '{$row['node_id']}' AND child_id = {$child_id} ORDER BY id ASC;";
                $ajax_modal = "ajax.php?Ajax=GetModal_Sensor_Graph,".$pieces[1].",0";
                $button_name = $lang['graph_24h'];
	}
        $results = $conn->query($query);
	if (mysqli_num_rows($results) > 0) {
	        // create array of pairs of x and y values for every zone
        	$data_x = array();
	        $data_y = array();
        	while ($rowb = mysqli_fetch_assoc($results)) {
			$data_x[] = strtotime($rowb['datetime']) * 1000;
			$data_y[] = $rowb['payload'];
		        $js_array_x = json_encode($data_x);
        		$js_array_y = json_encode($data_y);
        	}
	} else {
        	$js_array_x = '';
                $js_array_y = '';
	}
	?>
	<script type="text/javascript" src="js/plugins/plotly/plotly-2.9.0.min.js"></script>
        <script type="text/javascript" src="js/plugins/plotly/d3.min.js"></script>
	<script>

        <?php if($type_id == 1) { ?>
                var ytitle = 'Temperature';
        <?php } elseif ($type_id == 2) { ?>
                var ytitle = 'Humidity';
        <?php } elseif ($type_id == 5) { ?>
                var ytitle = 'Pressure';
        <?php } elseif ($type_id == 7) { ?>
                var ytitle = 'Gas';
        <?php } else { ?>
                var ytitle = '';
        <?php } ?>
        var xValues = [...<?php echo $js_array_x ?>];
        var yValues = [...<?php echo $js_array_y ?>];

	var data = [
		{
  			type: 'scatter',
  			x: xValues,
  			y: yValues,
  			hoverlabel: {
    				bgcolor: 'black',
    				font: {color: 'white'}
  			},
			hovertemplate: 'At: %{x}<extra></extra>' +
                        '<br><b>' + ytitle + ': </b>: %{y:.2f}\xB0<br>',
			showlegend: false,
			line: {shape: 'spline', color: '<?php echo $sensor_color[$graph_id]; ?>'}
		}
	];

	var layout = {
  		xaxis: {
                title: 'Time',
                type: 'date',
                tickmode: "linear",
                <?php if ($pieces[2] == 0) { ?>
                        dtick: 2*60*60*1000,
                <?php } else { ?>
                        dtick: 10*60*1000,
                <?php } ?>
    		tickformat: '%H:%M'
  		},
  		yaxis: {
    		title: ytitle
  		},
                autosize: true,
                automargin: true
	};

        var config = {
                responsive: true, displayModeBar: true, displaylogo: false, // this is the line that hides the bar.
        };

        Plotly.react('myChart', data, layout, config);
        $('#ajaxModal').one('shown.bs.modal', function () {
                Plotly.relayout('myChart',layout);
        });
	</script>
<?php
        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <h5 class="modal-title" id="ajaxModalLabel">'.$title.'</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
        </div>
        <div class="modal-body" id="ajaxModalBody">
		<div id="myChart" style="width:100%"></div>
    	</div>
    	<div class="modal-footer" id="ajaxModalFooter">
            <button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-toggle="modal" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal.'"  onclick="sensors_Graph(this);">'.$button_name.'</button>
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
        echo '<script language="javascript" type="text/javascript">
                sensors_Graph=function(gthis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(gthis)); })};
        </script>';
    return;
}
if(explode(',', $_GET['Ajax'])[0]=='GetModal_Sensor_Graph')
{
    GetModal_Sensor_Graph($conn);
    return;
}

function GetModal_SystemController($conn)
{
        global $lang;
        //query to get last system_controller operation time
        $query = "SELECT * FROM system_controller LIMIT 1";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $system_controller_id = $row['id'];
        $system_controller_name = $row['name'];

        //Get data from nodes table
        $query = "SELECT * FROM nodes WHERE id = {$row['node_id']} AND status IS NOT NULL LIMIT 1";
        $result = $conn->query($query);
        $system_controller_node = mysqli_fetch_array($result);
        $system_controller_node_id = $system_controller_node['node_id'];
        $system_controller_seen = $system_controller_node['last_seen'];
        $system_controller_notice = $system_controller_node['notice_interval'];

        //Check System Controller Fault
        $system_controller_fault = 0;
        if($system_controller_notice > 0){
                $now=strtotime(date('Y-m-d H:i:s'));
                $system_controller_seen_time = strtotime($system_controller_seen);
                if ($system_controller_seen_time  < ($now - ($system_controller_notice*60))){
                        $system_controller_fault = 1;
                }
        }

        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title" id="ajaxModalLabel">'.$system_controller_name.' - '.$lang['system_controller_recent_logs'].'</h5>
        </div>
        <div class="modal-body" id="ajaxModalBody">
		<div id="sc_status">';
			if ($system_controller_fault == '1') {
				$date_time = date('Y-m-d H:i:s');
				$datetime1 = strtotime("$date_time");
				$datetime2 = strtotime("$system_controller_seen");
				$interval  = abs($datetime2 - $datetime1);
				$ctr_minutes   = round($interval / 60);
				echo '
					<ul class="list-group list-group-flush">
						<li class="list-group-item">
							<div class="header">
								<div class="d-flex justify-content-between">
									<span>
										<strong class="primary-font red">System Controller Fault!!!</strong>
									</span>
									<span>
										<small class="text-muted">
											<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
										</small>
									</span>
								</div>
								<br>
								<p>Node ID '.$system_controller_node_id.' last seen at '.$system_controller_seen.' </p>
								<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
							</div>
						</li>
					</ul>';
	  		}
                        $bquery = "SELECT DATE_FORMAT(start_datetime, '%H:%i') AS start_datetime, DATE_FORMAT(stop_datetime, '%H:%i') AS stop_datetime ,
					DATE_FORMAT(expected_end_date_time, '%H:%i') AS expected_end_date_time,
					IF(ISNULL(stop_datetime),TIMESTAMPDIFF(MINUTE, start_datetime, NOW()),TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) AS on_minuts
					FROM controller_zone_logs WHERE zone_id = ".$system_controller_id." ORDER BY id DESC LIMIT 5;";
			$bresults = $conn->query($bquery);
			if (mysqli_num_rows($bresults) == 0){
				echo '<div class="list-group">
					<a href="#" class="list-group-item"><i class="bi bi-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['system_controller_no_log'].'</a>
				</div>';
			} else {
				echo '<p class="text-muted">'. mysqli_num_rows($bresults) .' '.$lang['system_controller_last_records'].'</p>
				<div class="list-group">' ;
                        		echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
                                		<span>
                                        		<i class="bi bi-fire red"></i> Start &nbsp; - &nbsp;End
	                                        </span>
        	                                <span class="text-muted small">
                	                         	<em>'.$lang['system_controller_on_minuts'].'&nbsp;</em>
                        	                </span>
                                	</a>';
					while ($brow = mysqli_fetch_assoc($bresults)) {
        	                        	echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
                	                        	<span>
								<i class="bi bi-fire red"></i> '. $brow['start_datetime'].' - ' .$brow['stop_datetime'].'
                                	                </span>
                                        	    	<span class="text-muted small">
								<em>'.$brow['on_minuts'].'&nbsp;</em>
							</span>
						</a>';
					}
				 echo '</div>';
			}
        	echo '</div>
	</div>
        <div class="modal-footer" id="ajaxModalFooter">
                <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
        return;
}
if($_GET['Ajax']=='GetModal_SystemController')
{
        GetModal_SystemController($conn);
        return;
}

function GetModal_Schedule_List($conn)
{
        global $lang;

        //following variable set to current day of the week.
        $dow = idate('w');

        //query to check away status
        $query = "SELECT * FROM away LIMIT 1";
        $result = $conn->query($query);
        $away = mysqli_fetch_array($result);
        $away_status = $away['status'];

        //query to check holidays status
        $query = "SELECT * FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
        $result = $conn->query($query);
        $rowcount=mysqli_num_rows($result);
        if ($rowcount > 0) {
        	$holidays = mysqli_fetch_array($result);
                $holidays_status = $holidays['status'];
        } else {
                $holidays_status = 0;
        }

        //foreach($_GET as $variable => $value) echo $variable . "&nbsp;=&nbsp;" . $value . "<br />\r\n";
        //query to get last system_controller operation time
        $query = "SELECT * FROM system_controller LIMIT 1";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $system_controller_id = $row['id'];
        $system_controller_name = $row['name'];

        //Get data from nodes table
        $query = "SELECT * FROM nodes WHERE id = {$row['node_id']} AND status IS NOT NULL LIMIT 1";
        $result = $conn->query($query);
        $system_controller_node = mysqli_fetch_array($result);
        $system_controller_node_id = $system_controller_node['node_id'];
        $system_controller_seen = $system_controller_node['last_seen'];
	$system_controller_notice = $system_controller_node['notice_interval'];

        //Check System Controller Fault
        $system_controller_fault = 0;
        if($system_controller_notice > 0){
        	$now=strtotime(date('Y-m-d H:i:s'));
        	$system_controller_seen_time = strtotime($system_controller_seen);
                if ($system_controller_seen_time  < ($now - ($system_controller_notice*60))){
                        $system_controller_fault = 1;
                }
        }

	$pieces = explode(',', $_GET['Ajax']);
        $zone_id = $pieces[1];

        $query = "SELECT zone.name, zone_type.category  FROM zone, zone_type WHERE (zone.type_id = zone_type.id) AND zone.id = {$zone_id} LIMIT 1";
        $result = $conn->query($query);
	$row = mysqli_fetch_array($result);
        $zone_name = $row['name'];
	$zone_category = $row['category'];

        if ($zone_category <> 3) {
        	$query = "SELECT relays.relay_id, relays.relay_child_id FROM zone_relays, relays WHERE (zone_relays.zone_relay_id = relays.id) AND zone_id = '{$zone_id}' LIMIT 1;";
                $result = $conn->query($query);
                $zone_relays = mysqli_fetch_array($result);
                $zone_relay_id=$zone_relays['relay_id'];
                $zone_relay_child_id=$zone_relays['relay_child_id'];
	}

        //query to get zone current state
        $query = "SELECT * FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
        $result = $conn->query($query);
        $zone_current_state = mysqli_fetch_array($result);
        $zone_mode = $zone_current_state['mode'];
        $zone_mode_main=floor($zone_mode/10)*10;
        $zone_mode_sub=floor($zone_mode%10);
        $zone_temp_reading = $zone_current_state['temp_reading'];
        $zone_temp_target = $zone_current_state['temp_target'];
        $zone_temp_cut_in = $zone_current_state['temp_cut_in'];
        $zone_temp_cut_out = $zone_current_state['temp_cut_out'];
        $zone_ctr_fault = $zone_current_state['controler_fault'];
        $controler_seen = $zone_current_state['controler_seen_time'];
        $zone_sensor_fault = $zone_current_state['sensor_fault'];
        $sensor_seen = $zone_current_state['sensor_seen_time'];
        $temp_reading_time= $zone_current_state['sensor_reading_time'];
        $overrun= $zone_current_state['overrun'];
        $schedule = $zone_current_state['schedule'];

        //get the current zone schedule status
        $sch_status = $schedule & 0b1;
      	$away_sch = ($schedule >> 1) & 0b1;

        if ($sch_status == 1) { $active_schedule = 1; }

        //get the sensor id
        $query = "SELECT * FROM sensors WHERE zone_id = '{$zone_id}' LIMIT 1;";
        $result = $conn->query($query);
        $sensor = mysqli_fetch_array($result);
        $temperature_sensor_id=$sensor['sensor_id'];
        $temperature_sensor_child_id=$sensor['sensor_child_id'];
        $sensor_type_id=$sensor['sensor_type_id'];
        $ajax_modal_24h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor['id'].",0";
        $ajax_modal_1h = "ajax.php?Ajax=GetModal_Sensor_Graph,".$sensor['id'].",1";

        echo '<div class="modal-header '.theme($conn, settings($conn, 'theme'), 'text_color').' bg-'.theme($conn, settings($conn, 'theme'), 'color').'">
            <h5 class="modal-title" id="ajaxModalLabel">'.$zone_name.'</h5>
            <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
        </div>
        <div class="modal-body" id="ajaxModalBody">';
  		if ($system_controller_fault == '1') {
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			$datetime2 = strtotime("$system_controller_seen");
			$interval  = abs($datetime2 - $datetime1);
			$ctr_minutes   = round($interval / 60);
			echo '
			<ul class="list-group">
				<li class="list-group-item">
					<div class="header">
						<div class="d-flex justify-content-between">
							<span>
								<strong class="primary-font red">System Controller Fault!!!</strong>
							</span>
							<span>
								<small class="text-muted">
									<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
								</small>
							</span>
						</div>
						<br>
						<p>Node ID '.$system_controller_node_id.' last seen at '.$system_controller_seen.' </p>
						<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
					</div>
				</li>
			</ul>';

  		}elseif ($zone_ctr_fault == '1') {
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			echo '
			<ul class="list-group">
				<li class="list-group-item">
					<div class="header">';
						$cquery = "SELECT `zone_relays`.`zone_id`, `zone_relays`.`zone_relay_id`, n.`last_seen`, n.`notice_interval` FROM `zone_relays`
						LEFT JOIN `relays` r on `zone_relays`.`zone_relay_id` = r.`id`
						LEFT JOIN `nodes` n ON r.`relay_id` = n.`id`
						WHERE `zone_relays`.`zone_id` = ".$zone_id.";";
						$cresults = $conn->query($cquery);
						while ($crow = mysqli_fetch_assoc($cresults)) {
							$datetime2 = strtotime($crow['last_seen']);
							$interval  = abs($datetime2 - $datetime1);
							$ctr_minutes   = round($interval / 60);
							$zone_relay_id = $crow['zone_relay_id'];
        	                                        echo '<div class="d-flex justify-content-between">
	                                                        <span>
                                                  	              <strong class="primary-font red">Controller Fault!!!</strong>
                                                        	</span>
                                                        	<span>
                                                                	<small class="text-muted">
                                                                        	<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($ctr_minutes)*60).' ago
                                                                	</small>
                                                        	</span>
                                                	</div>
                                                	<br>
                                                	<p>Controller ID '.$zone_relay_id.' last seen at '.$crow['last_seen'].' </p>';
						}
						echo '<p class="text-info">Heating system will resume its normal operation once this issue is fixed. </p>
					</div>
				</li>
			</ul>';
		//echo $zone_senros_txt;
		}elseif ($zone_sensor_fault == '1'){
			$date_time = date('Y-m-d H:i:s');
			$datetime1 = strtotime("$date_time");
			$datetime2 = strtotime("$sensor_seen");
			$interval  = abs($datetime2 - $datetime1);
			$sensor_minutes   = round($interval / 60);
			echo '
			<ul class="list-group">
				<li class="list-group-item">
					<div class="header">
						<div class="d-flex justify-content-between">
							<span>
								<strong class="primary-font red">Sensor Fault!!!</strong>
							</span>
							<span>
								<small class="text-muted">
									<i class="bi bi-clock icon-fw"></i> '.secondsToWords(($sensor_minutes)*60).' ago
								</small>
							</span>
						</div>
						<br>
						<p>Sensor ID '.$zone_node_id.' last seen at '.$sensor_seen.' <br>Last Temperature reading received at '.$temp_reading_time.' </p>
						<p class="text-info"> Heating system will resume for this zone its normal operation once this issue is fixed. </p>
					</div>
				</li>
			</ul>';
		}else{
			if ($sensor_type_id != 3) {
				//if temperature control active display cut in and cut out levels
                                $c_f = settings($conn, 'c_f');
                                if ($c_f == 0) { $units = 'C'; } else { $units = 'F'; }
				if (($zone_category <= 1 || $zone_category == 5) && (($zone_mode_main == 20 ) || ($zone_mode_main == 50 ) || ($zone_mode_main == 60 ) || ($zone_mode_main == 70 ) || ($zone_mode_main == 80 ) || ($zone_mode_main == 110 ))){
                                	echo '<p>Cut In Temperature : '.DispSensor($conn,$zone_temp_cut_in,$sensor_type_id).'&deg'.$units.'</p>
                                        <p>Cut Out Temperature : ' .DispSensor($conn,$zone_temp_cut_out,$sensor_type_id).'&deg'.$units.'</p>';
				}
				//display coop start info
				if($zone_mode_sub == 3){
					echo '<p>Coop Start Schedule - Waiting for System Controller start.</p>';
				}
			}
//			$squery = "SELECT * FROM schedule_daily_time_zone_view where zone_id ='{$zone_id}' AND tz_status = 1 AND time_status = '1' AND (WeekDays & (1 << {$dow})) > 0 AND type = 0 ORDER BY start asc";
                        $squery = "SELECT schedule_daily_time.sch_name, schedule_daily_time.start, schedule_daily_time.end,
                        schedule_daily_time_zone.temperature, schedule_daily_time_zone.id AS tz_id, schedule_daily_time_zone.coop, schedule_daily_time_zone.disabled
                        FROM `schedule_daily_time`, `schedule_daily_time_zone`
                        WHERE (schedule_daily_time.id = schedule_daily_time_zone.schedule_daily_time_id) AND schedule_daily_time.status = 1
                        AND (schedule_daily_time_zone.status = 1 OR schedule_daily_time_zone.disabled = 1) AND schedule_daily_time.type = 0 AND schedule_daily_time_zone.zone_id ='{$zone_id}'
                        AND (schedule_daily_time.WeekDays & (1 << {$dow})) > 0
                        ORDER BY schedule_daily_time.start asc;";
			$sresults = $conn->query($squery);
			if (mysqli_num_rows($sresults) == 0){
				echo '<div class="list-group">
					<a href="#" class="list-group-item"><i class="bi bi-exclamation-triangle red"></i>&nbsp;&nbsp;'.$lang['schedule_active_today'].' '.$zone_name.'!!! </a>
				</div>';
			} else {
				//echo '<h4>'.mysqli_num_rows($sresults).' Schedule Records found.</h4>';
				echo '<p>'.$lang['schedule_disble'].'</p>
				<br>
				<div class="list-group">' ;
					while ($srow = mysqli_fetch_assoc($sresults)) {
						$shactive="orangesch_list";
						$time = strtotime(date("G:i:s"));
						$start_time = strtotime($srow['start']);
						$end_time = strtotime($srow['end']);
						if ($time >$start_time && $time <$end_time){$shactive="redsch_list";}
                                                if ($srow['coop'] == "1") {
							$coop = '<i class="bi bi-tree-fill green" data-container="body" data-bs-toggle="popover" data-placement="right" data-content="' . $lang['schedule_coop_help'] . '"></i>';
                                                } else {
                                                        $coop = '';
                                                }
						//this line to pass unique argument  "?w=schedule_list&o=active&wid=" href="javascript:delete_schedule('.$srow["id"].');"
		                                echo '<li class="list-group-item">
                		                        <div class="d-flex justify-content-between">
                                		                <span>
                                                		        <div class="d-flex justify-content-start">
										<a href="javascript:schedule_zone('.$srow['tz_id'].');" style="text-decoration: none;">';
											if ($srow['disabled'] == 0) {
												echo '<div id="sdtz_'.$srow['tz_id'].'"><div class="circle_list '. $shactive.'"> <p class="schdegree">'.number_format(DispSensor($conn,$srow['temperature'],$sensor_type_id),0).$unit.'</p></div></div>';
											} else {
												echo '<div id="sdtz_'.$srow['tz_id'].'"><div class="circle_list bluesch_disable"> <p class="schdegree">D</p></div></div>';
											}
										echo '</a>
										<span class="label text-info">&nbsp&nbsp'.$srow['sch_name'].'</span>
									</div>
								</span>
								<span class="text-muted"><em>'. $coop. '&nbsp'.$srow['start'].' - ' .$srow['end'].'</em></span>';
							echo '</div>
						</li>';
					}
				echo '</div>';
			}
		}
    	echo '</div>
    	<div class="modal-footer" id="ajaxModalFooter">
            <button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-toggle="modal" data-bs-remote="false" data-bs-target="#ajaxModal" data-ajax="'.$ajax_modal_24h.'" onclick="graph_sensor(this);">'.$lang['graph_24h'].'</button>
            <button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['close'].'</button>
        </div>';      //close class="modal-footer">
        echo '<script language="javascript" type="text/javascript">
               graph_sensor=function(ithis){ $("#ajaxModal").one("hidden.bs.modal", function() { $("#ajaxModal").modal("show",$(ithis)); })};
        </script>';
    return;
}
if(explode(',', $_GET['Ajax'])[0]=='GetModal_Schedule_List')
{
    GetModal_Schedule_List($conn);
    return;
}
?>
