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

//set argv1 from cmd line to debug
if(isset($argv[1])) {
	require_once('/var/www/st_inc/connection.php');
	require_once('/var/www/st_inc/functions.php');
}

//create array of colours for the graphs
//$query ="SELECT id, name, sensor_type_id FROM sensors ORDER BY name ASC;";
$query = "SELECT `id`, `name`, `sensor_type_id` FROM `sensors`
                UNION
                SELECT sensor_average.id, CONCAT(zone.name,' - Average') AS name, '1' AS sensor_type_id
                FROM sensor_average, zone
                WHERE sensor_average.zone_id = zone.id
                ORDER BY `id` ASC;";
$results = $conn->query($query);
$counter = 0;
$count = mysqli_num_rows($results) + 2; //extra space made for system temperature graph
$s_color = array();
$s_name = array();
$s_type = array();
while ($row = mysqli_fetch_assoc($results)) {
        $s_color[$row['id']] = graph_color($count, ++$counter);
        $s_name[$row['id']] = $row['name'];
        $s_type[$row['id']] = $row['sensor_type_id'];
}
$s_color[0] = graph_color($count, ++$counter);
$s_name[0] = "Outside Temp";
$s_type[0] = 1;

echo "<h4>".$lang['graph_min_max']."</h4></p>".$lang['graph_min_text']."</p>";
?>

<div class="flot-chart">
   <div class="flot-chart-content" id="min_readings"></div>
</div>
<br>
<?php echo "</p>".$lang['graph_max_text']."</p>"; ?>
<div class="flot-chart">
   <div class="flot-chart-content" id="max_readings"></div>
</div>
<br>

<?php
//array to hold the minimum and maximum readings by sensor name and date
$graph_min = '';
$graph_max = '';

$query = "SELECT id FROM sensors WHERE min_max_graph = 1
        UNION
        SELECT id FROM sensor_average WHERE min_max_graph = 1
        ORDER BY id ASC;";
$resultsa = $conn->query($query);
while ($rowa = mysqli_fetch_assoc($resultsa)) {
        $graph1_temp = array();
        $graph2_temp = array();
	$query = "SELECT sensor_min_max_graph.sensor_id, sensor_min_max_graph.name, sensor_min_max_graph.max, sensor_min_max_graph.min, s.sensor_type_id,
                DATE(sensor_min_max_graph.date) DateOnly
		FROM sensor_min_max_graph
		JOIN sensors s ON sensor_min_max_graph.sensor_id = s.id
		WHERE sensor_min_max_graph.sensor_id = {$rowa['id']}
		ORDER BY sensor_min_max_graph.sensor_id ASC;";
	$results = $conn->query($query);
	while ($row = mysqli_fetch_assoc($results)) {
		$date= date("d-m-Y",strtotime($row['DateOnly']));
		$graph1_temp[] = array(strtotime($date) * 1000, $row['max']);
        	$graph2_temp[] = array(strtotime($date) * 1000, $row['min']);
	}
        $graph_min = $graph_min."{label: \"".$s_name[$rowa['id']]."\", data: ".json_encode($graph1_temp).", color: '".$s_color[$rowa['id']]."', stype: '".$s_type[$rowa['id']]."'}, \n";
        $graph_max = $graph_max."{label: \"".$s_name[$rowa['id']]."\", data: ".json_encode($graph2_temp).", color: '".$s_color[$rowa['id']]."', stype: '".$s_type[$rowa['id']]."'}, \n";
}
//check if the outside temp graph is enabled
$query = "SELECT enable_archive FROM weather WHERE enable_archive = 1 LIMIT 1;";
$result = $conn->query($query);
if (mysqli_num_rows($result) > 0) {
        $graph1_temp = array();
        $graph2_temp = array();
        $query = "SELECT sensor_min_max_graph.sensor_id, sensor_min_max_graph.name, sensor_min_max_graph.max, sensor_min_max_graph.min, 1 ASsensor_type_id, DATE(sensor_min_max_graph.date) DateOnly
                FROM sensor_min_max_graph
                WHERE sensor_min_max_graph.sensor_id = 0
                ORDER BY DateOnly ASC;";
        $results = $conn->query($query);
        while ($row = mysqli_fetch_assoc($results)) {
                $date= date("d-m-Y",strtotime($row['DateOnly']));
                $graph1_temp[] = array(strtotime($date) * 1000, $row['max']);
                $graph2_temp[] = array(strtotime($date) * 1000, $row['min']);
        }
        $graph_min = $graph_min."{label: \"".$s_name[0]."\", data: ".json_encode($graph1_temp).", color: '".$s_color[0]."', stype: '".$s_type[0]."'}, \n";
        $graph_max = $graph_max."{label: \"".$s_name[0]."\", data: ".json_encode($graph2_temp).", color: '".$s_color[0]."', stype: '".$s_type[0]."'}, \n";
}
?>

<script type="text/javascript">
// create min_dataset based on all available Min Max Values
var min_dataset = [ <?php echo $graph_min ?>];
var max_dataset = [ <?php echo $graph_max ?>];
</script>
<?php
?>
