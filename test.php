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
require_once(__DIR__.'/st_inc/functions.php');

//$switches = array();

$query = "SELECT controller_relays.controler_id, controller_relays.controler_child_id, zone_controllers.controller_relay_id, zone_controllers.state, controller_relays.name FROM  zone_controllers, controller_relays WHERE (zone_controllers.controller_relay_id = controller_relays.id);";
$cresult = $conn->query($query);
$index = 0;
while ($crow = mysqli_fetch_assoc($cresult)) {
	$zone_controllers[$index] = array('controler_id' =>$crow['controler_id'], 'controler_child_id' =>$crow['controler_child_id'],'controller_relay_id' =>$crow['controller_relay_id'], 'zone_controller_state' =>$crow['state'], 'zone_controller_name' =>$crow['name']);
        $index = $index + 1;
}
$controller_count = $index;

echo $controller_count."\n";

/*for ($i = 0; $i < count($zone_controllers); $i++)  {
        //Re-add Zones Controllers Table
	$controler_id = $zone_controllers[$i]["controler_id"];
        $controler_child_id = intval($zone_controllers[$i]["controler_child_id"]);
        $controller_relay_id = intval($zone_controllers[$i]["controller_relay_id"]);
	echo $i.", ".$controler_id.", ".$controler_child_id.", ".$controller_relay_id."\n";

        $query = "SELECT * FROM nodes WHERE id = '{$controler_id}' LIMIT 1;";
        $result = $conn->query($query);
        $found_product = mysqli_fetch_array($result);
        $controller_type = $found_product['type'];
        $controler_node_id = $found_product['node_id'];
	echo $controller_type.", ".$controler_node_id."\n";
        $query = "INSERT INTO `zone_controllers` (`sync`, `purge`, `state`, `current_state`, `zone_id`, `controller_relay_id`) VALUES ('0', '0', '0', '0', '76', '{$controller_relay_id}');";
        $result = $conn->query($query);

}*/

$sync = '0';
$purge= '0';
$zone_status = '0';
$index_id = '4';
$name ="TEST";
$type_id = '2';
$max_operation_time = '10';
$id = '0';

//$query = "INSERT INTO `zone` (`sync`, `purge`, `status`, `zone_state`, `index_id`, `name`, `type_id`, `max_operation_time`) VALUES ('{$sync}', '{$purge}', '{$zone_status}', '0', '{$index_id}', '{$name}', '{$type_id}', '{$max_operation_time}');";

$query = "INSERT INTO `zone`(`id`, `sync`, `purge`, `status`, `zone_state`, `index_id`, `name`, `type_id`, `max_operation_time`) VALUES ('{$id}','{$sync}', '{$purge}','{$zone_status}', '0', '{$zone_status}','{$name}','{$type_id}','{$max_operation_time}');";

$result = $conn->query($query);
$zone_id = mysqli_insert_id($conn);
echo $zone_id."\n";
        //get the current zone id
        if ($zone_id == 0) { $cnt_id = $id; } else { $cnt_id = $zone_id; }
echo $cnt_id."\n";
$controller_count = 1;
for ($i = 0; $i < count($zone_controllers); $i++)  {
        //Re-add Zones Controllers Table
        $controler_id = $zone_controllers[$i]["controler_id"];
        $controler_child_id = intval($zone_controllers[$i]["controler_child_id"]);
        $controller_relay_id = intval($zone_controllers[$i]["controller_relay_id"]);
        echo $i.", ".$cnt_id.", ".$controler_id.", ".$controler_child_id.", ".$controller_relay_id."\n";

        $query = "SELECT * FROM nodes WHERE id = '{$controler_id}' LIMIT 1;";
        $result = $conn->query($query);
        $found_product = mysqli_fetch_array($result);
        $controller_type = $found_product['type'];
        $controler_node_id = $found_product['node_id'];
//        echo $controller_type.", ".$controler_node_id."\n";
}
?>

