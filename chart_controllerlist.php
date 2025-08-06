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

//boiler usage time
echo "<h4>".$lang['graph_saving']."</h4></p>".$lang['graph_saving_text']."</p>";
$query = "SELECT * FROM system_controller LIMIT 1";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$sc_count=$result->num_rows;
$system_controller_id = $row['id'];

$query="SELECT date(start_datetime) as date,
	sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
	sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts,
	(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
	FROM controller_zone_logs
	WHERE start_datetime >= NOW() - INTERVAL 30 DAY  AND zone_id = ".$system_controller_id." AND expected_end_date_time IS NOT NULL
	GROUP BY date(start_datetime) desc";

$result = $conn->query($query);
if (mysql_num_rows($result) != 0) {
        echo '<table id="example" class="table table-bordered table-hover dt-responsive" width="100%">';
        echo '<thead><tr><th>Date</th><th>T. Min</th><th class="all">On Min</th><th>S. Min</th><th> <i class="bi bi-tree-fill green"></th></tr></thead><tbody>';
        while ($row = mysqli_fetch_assoc($result)) {
                echo '
                <tr>
                <td class="all">' . $row['date'] . '</td>
                <td class="all">' . $row['total_minuts'] . '</td>
                <td class="all">' . $row['on_minuts'] . '</td>
                <td class="all">' . $row['save_minuts'] . '</td>
                <td class="all">'.number_format(($row['save_minuts']/$row['total_minuts'])*100,0).'%</td>
                </tr>';
        }
         echo '</tbody></table>';
}?>
