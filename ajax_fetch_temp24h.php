<?php
/*
   _____    _   _    _
  |  __ \  (_) | |  | |
  | |__) |  _  | |__| |   ___    _ __ ___     ___
  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \
  | |      | | | |  | | | (_) | | | | | | | |  __/
  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___|

     S M A R T   H E A T I N G   C O N T R O L

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/
require_once(__DIR__.'/st_inc/session.php');
require_once(__DIR__.'/st_inc/connection.php');

if(isset($_GET['id'])) {
	$id = $_GET['id'];
	$query = "SELECT * FROM sensors WHERE id = '{$id}' LIMIT 1;";
	$result = $conn->query($query);
	$sensor_history_Array = Array();
	$srow = mysqli_fetch_assoc($result);
       	$sensor_id = $srow['sensor_id'];
	$sensor_child_id = $srow['sensor_child_id'];
        $query = "SELECT * FROM nodes where id = {$sensor_id} LIMIT 1;";
	$nresult = $conn->query($query);
        $nrow = mysqli_fetch_array($nresult);
	$node_id = $nrow['node_id'];
	$query = "SELECT * FROM messages_in_view_24h WHERE node_id = '{$node_id}' AND child_id = {$sensor_child_id};";
        $hresults = $conn->query($query);
	while ($hrow = mysqli_fetch_assoc($hresults)) {
       		$sensor_history_Array[$id][] = $hrow;
        }
}
echo json_encode($sensor_history_Array);
?>
