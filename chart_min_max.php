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
$query ="SELECT id, name, sensor_type_id FROM sensors ORDER BY name ASC;";
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
//compile an array containg the names of those sensors with min_max_graph set
$graph_enable = array();
$query = "SELECT id FROM sensors WHERE min_max_graph = 1;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
        $graph_enable[] = $row['id'];
}

//check if the outside temp graph is enabled
$query = "SELECT enable_archive FROM weather WHERE enable_archive = 1 LIMIT 1;";
$result = $conn->query($query);
if (mysqli_num_rows($result) > 0) {
	$graph_enable[] = 0;
}

//array to hold the minimum and maximum readings by sensor name and date
$min_array = array();
$max_array = array();

// CSV file to read into an Array
$query = "SELECT archive_file FROM graphs LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$csvFile = $row['archive_file'];

if (($handle = fopen($csvFile, "r")) !== FALSE) {
        //read first line of csv file
        $data = fgetcsv($handle, 1000, ",");
        $sensor_id = $data[0];
        $sensor_min = $sensor_max = $data[2];
        //only going to use the date part from the datetime
        $date = date("d-m-Y",strtotime($data[3]));

        //loop through the rest of the file
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
		if (in_array($sensor_id, $graph_enable)) {
                        //only going to use the date part from the datetime
                        $record_date = date("d-m-Y",strtotime($data[3]));
	                if ($data[0] == $sensor_id && $record_date == $date) {
                	        if ($data[2] < $sensor_min) { $sensor_min = $data[2]; }
                        	if ($data[2] > $sensor_max) { $sensor_max = $data[2]; }
	                } else {
		                if (!array_key_exists($sensor_id, $min_array)) {
       			                $min_array[$sensor_id] = array();
               			}
       		                $min_array[$sensor_id][] = array(strtotime($date) * 1000, $sensor_min);
               		        if (!array_key_exists($sensor_id, $max_array)) {
                       		        $max_array[$sensor_id] = array();
	                        }
       		                $max_array[$sensor_id][] = array(strtotime($date) * 1000, $sensor_max);
               		        $sensor_id = $data[0];
                       		$sensor_reading = $sensor_min = $sensor_max = $data[2];
                        	$date = $record_date;
			}
		} else {
                	$sensor_id = $data[0];
                        $sensor_reading = $sensor_min = $sensor_max = $data[2];
                        //only going to use the date part from the datetime
                        $date = date("d-m-Y",strtotime($data[3]));
		}
	}
        // if last sensor in the csv file is to be archived, then capture the last data
	if ($sensor_id == end($graph_enable)) {
		$min_array[$sensor_id][] = array(strtotime($date) * 1000, $sensor_min);
	        $max_array[$sensor_id][] = array(strtotime($date) * 1000, $sensor_max);
	}
        fclose($handle);
}
//print_r($min_array);
?>
<script type="text/javascript">
// create min_dataset based on all available sensors
var min_dataset = [
<?php
//iterate through the array to compile the dataset
$keys = array_keys($min_array);
for($i = 0; $i < count($min_array); $i++) {
	echo "{label: \"".$s_name[$keys[$i]]."\", data: ".json_encode($min_array[$keys[$i]]).", color: '".$s_color[$keys[$i]]."', stype: '".$s_type[$keys[$i]]."'}, \n";
}

?> ];

// create max dataset based on all available sensors
var max_dataset = [
<?php
//iterate through the array to compile the dataset
$keys = array_keys($max_array);
for($i = 0; $i < count($max_array); $i++) {
        echo "{label: \"".$s_name[$keys[$i]]."\", data: ".json_encode($max_array[$keys[$i]]).", color: '".$s_color[$keys[$i]]."', stype: '".$s_type[$keys[$i]]."'}, \n";
}

?> ];
</script>
