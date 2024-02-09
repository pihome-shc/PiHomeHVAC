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

echo "<h4>".$lang['graph_addon_usage']."</h4></p>".$lang['graph_addon_state_text']."</p>";

;?>
<div class="flot-chart">
   <div class="flot-chart-content" id="addon_state"></div>
</div>
<br>
<script type="text/javascript">
// distinct color implementation for plot lines
function rainbow(numOfSteps, step) {
    var r, g, b;
    var h = step / numOfSteps;
    var i = ~~(h * 6);
    var f = h * 6 - i;
    var q = 1 - f;
    switch(i % 6){
        case 0: r = 1; g = f; b = 0; break;
        case 1: r = q; g = 1; b = 0; break;
        case 2: r = 0; g = 1; b = f; break;
        case 3: r = 0; g = q; b = 1; break;
        case 4: r = f; g = 0; b = 1; break;
        case 5: r = 1; g = 0; b = q; break;
    }
    var c = "#" + ("00" + (~ ~(r * 255)).toString(16)).slice(-2) + ("00" + (~ ~(g * 255)).toString(16)).slice(-2) + ("00" + (~ ~(b * 255)).toString(16)).slice(-2);
    return (c);
}

// create add-on dataset based on all available zones/controllers
var addon_state_dataset = [
<?php
$querya ="SELECT DISTINCT id,  relay_id, relay_child_id FROM zone_view WHERE category = 2;";
$resulta = $conn->query($querya);
$offset = 0;
$color_count=mysqli_num_rows($resulta) + 1;
$counter = 0;
while ($row = mysqli_fetch_assoc($resulta)) {
        $id=$row['id'];
        $relay_id = $row['relay_id'];
        $relay_child_id = $row['relay_child_id'];
        $query="SELECT name FROM relays WHERE relay_id = '{$relay_id}' AND relay_child_id = {$relay_child_id} LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $name = $row['name'];
	// remove any orphan records where start_datetime is set without a previous stop_datetime
        $query="SELECT id, zone_id, start_datetime, stop_datetime
		FROM add_on_log_view
		WHERE zone_id = '{$id}' AND start_datetime > current_timestamp() - interval 24 hour AND stop_datetime IS NOT NULL
		ORDER BY start_datetime ASC;";
        $results = $conn->query($query);
        $log_count=mysqli_num_rows($results);
        if ($log_count > 0) {
		$count = 0;
		while ($row_log = mysqli_fetch_assoc($results)) {
			if ($count == 0) {
				$id1 = $row_log['id']; 
				$stop1 = $row_log['stop_datetime'];
				$count++;
			} else {
				$stop2 = $row_log['stop_datetime'];
				if (!isset($stop1) && !isset($stop2)) {
					$query="DELETE FROM add_on_log WHERE id = {$id1};";
					$conn->query($query);
	                                $id1 = $row_log['id'];
        	                        $stop1 = $row_log['stop_datetime'];
				}
			}
		}
	}
//	$query = "SELECT zone_id, start_datetime, stop_datetime FROM add_on_log_view WHERE zone_id = '{$id}' AND ((SELECT COUNT(*) FROM add_on_log_view WHERE zone_id = '{$id}') > 0 AND (start_datetime > current_timestamp() - interval 36 hour AND NOT ISNULL(stop_datetime)) OR (ISNULL(stop_datetime) AND start_datetime > (SELECT stop_datetime FROM add_on_log_view WHERE zone_id = '{$id}' ORDER BY stop_datetime DESC LIMIT 1) AND (SELECT COUNT(*) FROM add_on_log_view WHERE zone_id = '{$id}' AND start_datetime > current_timestamp() - interval 36 hour) > 0));";
        $query = "SELECT zone_id, start_datetime, stop_datetime FROM add_on_log_view WHERE zone_id = '{$id}' AND start_datetime > current_timestamp() - interval 24 hour;";
        $results = $conn->query($query);
        $log_count=mysqli_num_rows($results);
        $addon_state = array();
        // initial data point to make graphs span 24 hours
        $addon_state[] = array(strtotime("-1 day") * 1000, 0 + $offset);
	if ($log_count == 0) {
        	$zone_id = $id;
                $label = $name ." - ID ".$zone_id."-".$relay_child_id;
                $graph_id = $zone_id.".0";
	} else {
	        while ($rowb = mysqli_fetch_assoc($results)) {
        	        if((--$log_count)==-1) break;
                	$zone_id = $rowb['zone_id'];
	                $label = $name ." - ID ".$zone_id."-".$relay_child_id;
        	        $graph_id = $zone_id.".0";
                	$system_controller_start = strtotime($rowb['start_datetime']) * 1000;
	                $stop = $rowb['stop_datetime'];
        	        if (is_null($stop)) {
                	        $system_controller_stop = strtotime("now") * 1000;
                        	$addon_state[] = array($system_controller_start, 0 + $offset);
	                        $addon_state[] = array($system_controller_start, 0.5 + $offset);
        	                $addon_state[] = array($system_controller_stop, 0.5 + $offset);
                	} else {
                        	$system_controller_stop = strtotime($stop) * 1000;
	                        $addon_state[] = array($system_controller_start, 0 + $offset);
        	                $addon_state[] = array($system_controller_start, 0.5 + $offset);
                	        $addon_state[] = array($system_controller_stop, 0.5 + $offset);
                        	$addon_state[] = array($system_controller_stop, 0 + $offset);
                	}
		}
        }
        // check if currently OFF and if so add final data point to make graphs span 24 hours
        if (!is_null($stop) || $log_count == 0) {
                $addon_state[] = array(strtotime("now") * 1000, 0 + $offset);
        }
        // create dataset entry using distinct color based on zone index(to have the same color everytime chart is opened)
        echo "{label: \"".$label."\", data: ".json_encode($addon_state).", color: rainbow(".$color_count.",".++$counter.") }, \n";
        $offset = $offset + 1;
}
?> ];

// create the graph y axis legends
var tick_dataset =
<?php
$ticks = array();
$querya ="SELECT DISTINCT id,  relay_id, relay_child_id FROM zone_view WHERE category = 2;";
$resulta = $conn->query($querya);
$offset = 0;
while ($row = mysqli_fetch_assoc($resulta)) {
        $ticks[] = array($offset, "OFF");
        $offset = $offset + 1;
}
// dummy entry for space above graph
$ticks[] = array($offset, "");
echo json_encode($ticks);
?> ;
</script>
