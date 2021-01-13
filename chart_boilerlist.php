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
$query="select date(start_datetime) as date, 
sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts, 
(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
from controller_zone_logs WHERE start_datetime >= NOW() - INTERVAL 30 DAY GROUP BY date(start_datetime) desc";

$result = $conn->query($query);
echo '<table id="example" class="table table-bordered table-hover dt-responsive" width="100%">';
echo '<thead><tr><th>Date</th><th>T. Min</th><th class="all">On Min</th><th>S. Min</th><th> <i class="glyphicon glyphicon-leaf green"></th></tr></thead><tbody>';
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
 echo '</tbody></table>';?>
