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
echo "<h4>".$lang['graph_humidity']."</h4></p>".$lang['graph_humidity_24h']."</p>";

;?>
<div class="flot-chart">
   <div class="flot-chart-content" id="humidity_level"></div>
</div>
<br>
<script type="text/javascript">
// create humidity level dataset based on all available sensors
var humidity_level_dataset = [
<?php
    $querya ="SELECT * FROM temperature_sensors WHERE sensor_type_id = 2 ORDER BY id ASC;";
    $resulta = $conn->query($querya);
    $counter = 0;
    $count = mysqli_num_rows($resulta) + 1;
    while ($row = mysqli_fetch_assoc($resulta)) {
        //grab the node id to be displayed in the plot legend
        $name=$row['name'];
        $id=$row['id'];
        $graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
        $graph_num = $row['graph_num'];
        $query="select * from zone_graphs where zone_id = {$id};";
        $result = $conn->query($query);
        // create array of pairs of x and y values for every zone
        $humidity_level = array();
        while ($rowb = mysqli_fetch_assoc($result)) {
            	$humidity_level[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
        }
        // create dataset entry using distinct color based on zone index(to have the same color everytime chart is opened)
        echo "{label: \"".$name."\", data: ".json_encode($humidity_level).", color: '".$sensor_color[$graph_id]."'}, \n";
    }
?> ];
</script>
