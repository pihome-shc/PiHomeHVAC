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

$date_time = date('Y-m-d H:i:s');
$theme = settings($conn, 'theme');

if(isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 0;
	$controller_count = 1;
	$sensor_count = 1;
}


$uri = $_SERVER['QUERY_STRING'];
if (strpos($uri, "id=") !== false) { $link = "settings.php?s_id=9"; } else { $link = "home.php"; }

//used to suppress display of Max Operating Time and Hysteresis Time input fields, 0 = fields supressed, 1 = fields displayed
$no_max_op_hys = 0;

//Form submit
if (isset($_POST['submit'])) {
	$zsensors = array();
	foreach($_POST['selected_sensors_id'] as $index => $value) {
		$zsensors[] = array($value);
	}
	$query = "SELECT sensor_type_id FROM sensors WHERE id = '{$zsensors[0][0]}' LIMIT 1;";
	$result = $conn->query($query);
	$found_product = mysqli_fetch_array($result);
	$sensor_type_id = $found_product['sensor_type_id'];
	if($zone_category <> 2) { $maintain_default =  $_POST['m_default']; } else { $maintain_default = 0; }

	$zone_category = $_POST['selected_zone_category'];
	$zone_status = isset($_POST['zone_status']) ? $_POST['zone_status'] : "0";
	$index_id = $_POST['index_id'];
	$name = $_POST['name'];
	$type = $_POST['selected_zone_type'];
	if($zone_category < 2) { 
                $min_c = 0;
                if ($sensor_type_id < 3) {
		        $max_c = SensorToDB($conn,$_POST['max_c'],$sensor_type_id);
        		$default_c = SensorToDB($conn,$_POST['default_c'],$sensor_type_id);
			$boost_c = $max_c;
                } else {
                        $max_c = 0;
                        $default_c = 0;
                        $boost_c = 0;
                }
	} elseif ($zone_category == 3  || $zone_category == 4 || $zone_category == 5) {
	        $min_c = SensorToDB($conn,$_POST['min_c'],$sensor_type_id);
		if ($sensor_type_id < 3) {
	        	$max_c = SensorToDB($conn,$_POST['max_c'],$sensor_type_id);
        	        $default_c = SensorToDB($conn,$_POST['default_c'],$sensor_type_id);
			if ($zone_category == 5) { $boost_c = $max_c; } else { $boost_c = $min_c; }
		} else {
			$max_c = 0;
        	        $default_c = 0;
                	$boost_c = 0;
		}
	} elseif ($zone_category == 2 ){
        	$max_c = 0;
                $default_c = 0;
                $boost_c = 0;
	}
//	Removed 29/01/2022 by twa as these 2 parameters are never used, default values used to populate database in case decide to re-implement at some future date
	if ($no_max_op_hys == 1) {
		$max_operation_time = $_POST['max_operation_time'];
		$hysteresis_time = $_POST['hysteresis_time'];
	} else {
	        $max_operation_time = 60;
        	$hysteresis_time = 3;
	}
        $sp_deadband = $_POST['sp_deadband'];
	$controllers = array();
        if($zone_category <> 3) {
		foreach($_POST['selected_controler_id'] as $index => $value) {
			$controllers[] = array($value);
		}
	}
	$boost_button_id = $_POST['boost_button_id'];
	$boost_button_child_id = $_POST['boost_button_child_id'];
	if ($_POST['zone_gpio'] == 0){$gpio_pin='0';} else {$gpio_pin = $_POST['zone_gpio'];}
	$sync = '0';
	$purge= '0';

	$system_controller = explode('-', $_POST['system_controller_id'], 2);
	$system_controller_id = $system_controller[0];

	//query to search node id for temperature sensors
	if ($zone_category <> 2) {
		$query = "SELECT * FROM nodes WHERE node_id = '{$sensor_id}' LIMIT 1;";
		$result = $conn->query($query);
		$found_product = mysqli_fetch_array($result);
		$sensor_id = $found_product['id'];
	}

        //query to search type id for zone controller
        $query = "SELECT id FROM zone_type WHERE type = '{$type}' LIMIT 1;";
        $result = $conn->query($query);
        $found_product = mysqli_fetch_array($result);
        $type_id = $found_product['id'];

	//Add or Edit Zone record to Zones Table
//	$query = "INSERT INTO `zone` (`id`, `sync`, `purge`, `status`, `zone_state`, `index_id`, `name`, `type_id`, `max_operation_time`) VALUES ('{$id}', '{$sync}', '{$purge}', '{$zone_status}', '0', '{$index_id}', '{$name}', '{$type_id}', '{$max_operation_time}') ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), status=VALUES(status), index_id=VALUES(index_id), name=VALUES(name), type_id=VALUES(type_id), max_operation_time=VALUES(max_operation_time);";
	$query = "INSERT INTO `zone`(`id`, `sync`, `purge`, `status`, `zone_state`, `index_id`, `name`, `type_id`, `max_operation_time`) VALUES ('{$id}','{$sync}', '{$purge}','{$zone_status}', '0', '{$index_id}','{$name}','{$type_id}','{$max_operation_time}') ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), status=VALUES(status), index_id=VALUES(index_id), name=VALUES(name), type_id=VALUES(type_id), max_operation_time=VALUES(max_operation_time);";
	$result = $conn->query($query);
	$zone_id = mysqli_insert_id($conn);
	if ($result) {
                if ($id==0){
                        $message_success = "<p>".$lang['zone_record_add_success']."</p>";
                } else {
                        $message_success = "<p>".$lang['zone_record_update_success']."</p>";
                }
	} else {
		$error = "<p>".$lang['zone_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
	}

	//get the current zone id
        if ($zone_id == 0) { $cnt_id = $id; } else { $cnt_id = $zone_id; }

	if($zone_category == 3) {
                if ($id!=0){ //if in edit mode delete existing zone controller records for the current zone
                        $query = "DELETE FROM `zone_relays` WHERE `zone_id` = '{$cnt_id}';";
                        $result = $conn->query($query);
                }
        	$query = "INSERT INTO `zone_relays` (`sync`, `purge`, `state`, `current_state`, `zone_id`, `zone_relay_id`) VALUES ('{$sync}', '{$purge}', '0', '0', '{$cnt_id}', '0');";
                $result = $conn->query($query);
                if ($result) {
                	if ($id==0){
                        	$message_success .= "<p>".$lang['controller_record_add_success']."</p>";
                        } else {
                                $message_success .= "<p>".$lang['controller_record_update_success']."</p>";
                        }
                } else {
                          $error .= "<p>".$lang['controller_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
                }

	} else {
	        if ($id!=0){ //if in edit mode delete existing zone controller records for the current zone
        		$query = "DELETE FROM `zone_relays` WHERE `zone_id` = '{$cnt_id}';";
	        	$result = $conn->query($query);
		}
		//loop through zone controller for the current zone and replace zone_controllers and messages_out records to cope with individual deleted zone controllers
	        for ($i = 0; $i < count($controllers); $i++)  {
			//Re-add Zones Controllers Table
			$zone_relay_id = $controllers[$i][0];
			$query = "INSERT INTO `zone_relays` (`sync`, `purge`, `state`, `current_state`, `zone_id`, `zone_relay_id`) 
				VALUES ('0', '0', '0', '0', '{$cnt_id}', '{$zone_relay_id}');";
			$result = $conn->query($query);
	       		if ($result) {
        	       		if ($id==0){
                	       		$message_success .= "<p>".$lang['zone_relay_record_add_success']."</p>";
	                	} else {
       		                	$message_success .= "<p>".$lang['zone_relay_record_update_success']."</p>";
	               		}
		        } else {
       			        $error .= "<p>".$lang['zone_relay_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
		        }
			//update the zone_id entry in the messages_out table
                        $query = "SELECT `nodes`.`node_id`, `relays`.`relay_child_id` FROM `nodes`, `relays` WHERE (`nodes`.`id` = `relays`.`relay_id`)
                                AND `relays`.`id` = {$zone_relay_id} LIMIT 1;";
                        $result = $conn->query($query);
                        $found_product = mysqli_fetch_array($result);
                        $node_id = $found_product['node_id'];
                        $child_id = $found_product['relay_child_id'];
                        $query = "UPDATE `messages_out` SET `zone_id` = {$cnt_id} WHERE `node_id` = {$node_id} AND `child_id` = {$child_id};";
                        $result = $conn->query($query);
                        if ($result) {
                                $message_success .= "<p>".$lang['messages_out_update_success']."</p>";
                        } else {
                                $error .= "<p>".$lang['messages_out_fail']." </p> <p>" .mysqli_error($conn). "</p>";
                        }
		}
	}

        if ($zone_category <> 2) {
                //Add or Edit Zone record to Zone_Sensor Table
                if ($id!=0){ //if in edit mode delete existing zone sensor records for the current zone
                        $query = "DELETE FROM `zone_sensors` WHERE `zone_id` = '{$cnt_id}';";
                        $result = $conn->query($query);
                }
                //loop through zone controller for the current zone and replace zone_sensors records to cope with individual deleted zone sensors
		$db_error = 0;
                for ($i = 0; $i < count($zsensors); $i++)  {
                        //Re-add Zones Sensors Table
                        $zone_sensor_id = $zsensors[$i][0];
                        $query = "INSERT INTO `zone_sensors` (`sync`, `purge`, `zone_id`, `min_c`, `max_c`, `default_c`, `default_m`, `hysteresis_time`, `sp_deadband`, `zone_sensor_id`)
				VALUES ('{$sync}', '{$purge}', '{$cnt_id}', '{$min_c}', '{$max_c}', '{$default_c}', '{$maintain_default}', '{$hysteresis_time}', '{$sp_deadband}', '{$zone_sensor_id}');";
                        if(!$conn->query($query)){
                        	$db_error = 1;
                        	break;
			}
		}
		if ($db_error == 0) {
                	if ($id==0){
                        	$message_success .= "<p>".$lang['zone_sensor_record_add_success']."</p>";
                        } else {
                                $message_success .= "<p>".$lang['zone_sensor_record_update_success']."</p>";
                        }
                } else {
                	$error .= "<p>".$lang['zone_sensor_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
		}

                if ($id!=0){ //if in edit mode delete existing zone sensor records for the current zone
                        $query = "DELETE FROM `sensor_average` WHERE `zone_id` = '{$cnt_id}';";
                        $result = $conn->query($query);
                }
                //loop through zone controller for the current zone and replace zone_sensors records to cope with individual deleted zone sensors
                if (count($zsensors) > 1)  {
			$sensor_average_id = "zavg_".$cnt_id;
                        $query = "INSERT INTO `sensor_average` (`sync`, `purge`, `zone_id`, `sensor_id`, `graph_num`, `show_it`, `min_max_graph`, `message_in`, `current_val_1`, `last_seen`)
                                VALUES ('{$sync}', '{$purge}', '{$cnt_id}', '{$sensor_average_id}', '0', '0', '0', '1', NULL, NULL);";
                        $result = $conn->query($query);
                        if ($result) {
	                        if ($id==0){
        	                        $message_success .= "<p>".$lang['sensor_average_record_add_success']."</p>";
                	        } else {
                        	        $message_success .= "<p>".$lang['sensor_average_record_update_success']."</p>";
                        	}
                	} else {
                        	$error .= "<p>".$lang['sensor_average_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
                	}
		}

	        // if in edit mode then clear any previous sensor to zone allocations
		if ($id != 0){
        		$query = "UPDATE `sensors` SET `zone_id` = 0 WHERE `zone_id` = '{$cnt_id}';";
	                $result = $conn->query($query);
        	        if ($result) {
                		$message_success .= "<p>".$lang['sensor_record_clear_success']."</p>";
	               	} else {
        	                $error .= "<p>".$lang['sensor_record_fail']."</p> <p>" .mysqli_error($conn). "</p>";
                	}
		}
		// update the sensors to show allocated zone
	        $db_error = 0;
        	for ($i = 0; $i < count($zsensors); $i++)  {
        		//Re-add Zones Sensors Table
	                $zone_sensor_id = $zsensors[$i][0];
	                $query = "UPDATE `sensors` SET `zone_id` = '{$cnt_id}' WHERE `id` = '{$zone_sensor_id}';";
        	        if(!$conn->query($query)){
                		$db_error = 1;
                        	break;
	                }
		}
	       	if ($db_error == 0) {
        	        $message_success .= "<p>".$lang['sensor_record_update_success']."</p>";
	        } else {
        		$error .= "<p>".$lang['sensor_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
	        }
	}

        if ($zone_category <> 2) {
	        if ($id==0){
			//If boost button console isnt installed and editing existing zone, then no need to add this to message_out
			if ($boost_button_id != 0){
				//Add Zone Boost Button Console to messageout table at same time
				$query = "INSERT INTO `messages_out` (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`,  `datetime`, `zone_id`) VALUES ('0', '0', '{$boost_button_id}','{$boost_button_child_id}', '2', '0', '0', '2', '1', '{$date_time}', '{$zone_id}');";
				$result = $conn->query($query);
				if ($result) {
					$message_success .= "<p>".$lang['zone_button_message_success']."</p>";
				} else {
					$error .= "<p>".$lang['zone_button_message_fail']."</p> <p>" .mysqli_error($conn). "</p>";
				}
			}

			//Add Zone to boost table at same time
			if ((settings($conn, 'mode') & 0b1) == 0) { //boiler mode
				$query = "INSERT INTO `boost`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`, `hvac_mode`) VALUES ('0', '0', '0', '{$zone_id}', '{$date_time}', '{$boost_c}','{$max_operation_time}', '{$boost_button_id}', '{$boost_button_child_id}', '0');";
		                $result = $conn->query($query);
        		        if ($result) {
                		        $message_success .= "<p>".$lang['zone_boost_success']."</p>";
	                	} else {
        	                	$error .= "<p>".$lang['zone_boost_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	                	}
			} else {
				//insert the 3 HVAC functions FAN, HEAT and COOL
				for ($i = 3; $i <= 5; $i++) {
					if ($i == 3) {
						$temp = '0';
					} elseif ($i == 4) {
						$temp = $max_c;
					} else {
                        	                $temp = $min_c;
					}
	                        	$query = "INSERT INTO `boost`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `minute`, `boost_button_id`, `boost_button_child_id`, `hvac_mode`) VALUES ('0', '0', '0', '{$zone_id}', '{$date_time}', '{$temp}','{$max_operation_time}', '0', '0', '{$i}');";
			                $result = $conn->query($query);
                			if ($result) {
		        	                $message_success .= "<p>".$lang['zone_boost_success']."</p>";
                			} else {
		                        	$error .= "<p>".$lang['zone_boost_fail']."</p> <p>" .mysqli_error($conn). "</p>";
	                		}
				}
			}
		} elseif ($boost_button_id != 0) { // editing so update boost console in boost and messages_out Tables
        		$query = "UPDATE boost SET `boost_button_id` = {$boost_button_id}, `boost_button_child_id` = {$boost_button_child_id} WHERE `zone_id` = '{$id}';";
			$result = $conn->query($query);
			if ($result) {
				$message_success .= "<p>".$lang['zone_boost_update_success']."</p>";
			} else {
				$error .= "<p>".$lang['zone_boost_update_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			}
			//Add New Zone Boost Button Console to message_out table at same time
			$query = "INSERT INTO `messages_out` (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`,  `datetime`, `zone_id`) VALUES ('0', '0', '{$boost_button_id}','{$boost_button_child_id}', '2', '0', '0', '2', '1', '{$date_time}', '{$id}');";
			$result = $conn->query($query);
			if ($result) {
				$message_success .= "<p>".$lang['zone_button_message_success']."</p>";
			} else {
				$error .= "<p>".$lang['zone_button_message_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			}
		}
	}
	//Add or Edit Zone to override table at same time
	if ($id==0){
		if ((settings($conn, 'mode') & 0b1) == 0) { //boiler mode
			$query = "INSERT INTO `override`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `hvac_mode`) VALUES ('0', '0', '0', '{$zone_id}', '{$date_time}', '{$max_c}', '0');";
		        $result = $conn->query($query);
		        if ($result) {
                		$message_success .= "<p>".$lang['zone_override_success']."</p>";
		        } else {
                		$error .= "<p>".$lang['zone_override_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		        }
		} else {
                        for ($i = 4; $i <= 5; $i++) {
                                if ($i == 4) {
                                        $temp = $max_c;
                                } else {
                                        $temp = $min_c;
                                }
	                	$query = "INSERT INTO `override`(`sync`, `purge`, `status`, `zone_id`, `time`, `temperature`, `hvac_mode`) VALUES ('0', '0', '0', '{$zone_id}', '{$date_time}', '{$temp}', '{$i}');";
			        $result = $conn->query($query);
			        if ($result) {
			                $message_success .= "<p>".$lang['zone_override_success']."</p>";
			        } else {
			                $error .= "<p>".$lang['zone_override_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			        }
			}
		}
	} else {
                if ((settings($conn, 'mode') & 0b1) == 0) { //boiler mode
			$query = "UPDATE override SET `sync` = 0, temperature = '{$max_c}' WHERE zone_id = '{$zone_id}';";
		        $result = $conn->query($query);
		        if ($result) {
                		$message_success .= "<p>".$lang['zone_override_success']."</p>";
		        } else {
                		$error .= "<p>".$lang['zone_override_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		        }
		} else {
                        for ($i = 4; $i <= 5; $i++) {
                                if ($i == 4) {
                                        $temp = $max_c;
                                } else {
                                        $temp = $min_c;
                                }
	                        $query = "UPDATE override SET `sync` = 0, temperature = '{$temp}' WHERE hvac_mode = '{$i}';";
			        $result = $conn->query($query);
			        if ($result) {
			                $message_success .= "<p>".$lang['zone_override_success']."</p>";
			        } else {
			                $error .= "<p>".$lang['zone_override_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			        }
			}
		}
	}

	if ($zone_category <> 2) {
		//Add Zone to schedule_night_climat_zone table at same time
		if ($id==0){
			$query = "SELECT * FROM schedule_night_climate_time;";
			$result = $conn->query($query);
			$nctcount = $result->num_rows;
			if ($nctcount == 0) {
				$query = "INSERT INTO `schedule_night_climate_time` VALUES (0,0,0,0,'18:00:00','23:30:00',0);";
				$result = $conn->query($query);
				$schedule_night_climate_id = mysqli_insert_id($conn);
				if ($result) {
					$message_success .= "<p>".$lang['schedule_night_climate_time_success']."</p>";
				} else {
					$error .= "<p>".$lang['schedule_night_climate_time_fail']."</p> <p>" .mysqli_error($conn). "</p>";
				}
			} else {
				$found_product = mysqli_fetch_array($result);
				$schedule_night_climate_id = $found_product['id'];
			}
			$query = "INSERT INTO `schedule_night_climat_zone` (`sync`, `purge`, `status`, `zone_id`, `schedule_night_climate_id`, `min_temperature`, `max_temperature`) VALUES ('0', '0', '0', '{$zone_id}', '{$schedule_night_climate_id}', '18','21');";
			$result = $conn->query($query);
			if ($result) {
				$message_success .= "<p>".$lang['zone_night_climate_success']."</p>";
			} else {
				$error .= "<p>".$lang['zone_night_climate_fail']."</p> <p>" .mysqli_error($conn). "</p>";
			}
                        //Add to existing schedule selections
                        $query = "SELECT id FROM schedule_daily_time;";
                        $results = $conn->query($query);
                        while ($row = mysqli_fetch_assoc($results)) {
                                $query = "SELECT id FROM schedule_daily_time_zone WHERE schedule_daily_time_id = {$row['id']} AND zone_id = {$zone_id};";
                                $result = $conn->query($query);
                                if (mysqli_num_rows($result) == 0) {
                                        $query = "INSERT INTO `schedule_daily_time_zone`(`sync`, `purge`, `status`, `schedule_daily_time_id`, `zone_id`, `temperature`, `holidays_id`,
                                        `coop`, `disabled`) VALUES (0,0,0,{$row['id']},{$zone_id},0,0,0,0)";
                                        $result = $conn->query($query);
                                        if ($result) {
                                                $message_success .= "<p>".$lang['schedule_daily_time_zone_insert_success']."</p>";
                                        } else {
                                                $error .= "<p>".$lang['schedule_daily_time_zone_insert_error']."</p> <p>" .mysqli_error($conn). "</p>";
                                        }
                                }
                        }
		}
	}
	$date_time = date('Y-m-d H:i:s');
	//query to check if default away record exists
	$query = "SELECT * FROM away LIMIT 1;";
	$result = $conn->query($query);
	$acount = $result->num_rows;
	if ($acount == 0) {
		$query = "INSERT INTO `away` VALUES (0,0,0,0,'{$date_time}','{$date_time}',40, 4);";
		$result = $conn->query($query);
		if ($result) {
			$message_success .= "<p>".$lang['away_success']."</p>";
		} else {
			$error .= "<p>".$lang['away_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		}
	}

        //query to check if default livetemp record exist when creating a Heating zone
        if(strpos($type, 'Heating') !== false || strpos($type, 'HVAC') !== false) {
                $query = "SELECT * FROM livetemp LIMIT 1;";
                $result = $conn->query($query);
                $ltcount = $result->num_rows;
                if ($ltcount == 0) {
                        $query = "INSERT INTO `livetemp`(`sync`, `purge`, `status`, `zone_id`, `active`, `temperature`, `hvac_mode`) VALUES (0,0,0,'{$zone_id}',0,0,0);";
                        $result = $conn->query($query);
                        if ($result) {
                                $message_success .= "<p>".$lang['livetemp_success']."</p>";
                        } else {
                                $error .= "<p>".$lang['livetemp_fail']."</p> <p>" .mysqli_error($conn). "</p>";
                        }
                }
        }

	//query to check if default holiday record exists
	$query = "SELECT * FROM holidays LIMIT 1;";
	$result = $conn->query($query);
	$hcount = $result->num_rows;
	if ($hcount == 0) {
		$query = "INSERT INTO `holidays` VALUES (1,0,0,0,'{$date_time}','{$date_time}');";
		$result = $conn->query($query);
		if ($result) {
			$message_success .= "<p>".$lang['holidays_success']."</p>";
		} else {
			$error .= "<p>".$lang['holidays_fail']."</p> <p>" .mysqli_error($conn). "</p>";
		}
	}
	$message_success .= "<p>".$lang['do_not_refresh']."</p>";
	header("Refresh: 10; url=home.php?page_name=onetouch");
        // After update on all required tables, set $id to mysqli_insert_id.
        if ($id==0) {
                header("Refresh: 10; url=home.php?page_name=onetouch");
                $id=$zone_id;
        } else {
                header("Refresh: 10; url=settings.php?s_id=9");
        }
}
?>
<!-- ### Visible Page ### -->
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>

<!-- Don't display form after submit -->
<?php if (!(isset($_POST['submit']))) { ?>

<!-- If the request is to EDIT, retrieve selected items from DB   -->
<?php if ($id != 0) {
        $query = "select zone.*,zone_type.category,zone_type.type from zone,zone_type where (zone.type_id = zone_type.id) and zone.id = {$id} limit 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);

       	$query = "SELECT sensors.*, sensor_type.type FROM sensors, sensor_type WHERE (sensors.sensor_type_id = sensor_type.id) AND zone_id = '{$row['id']}' LIMIT 1;";
        $result = $conn->query($query);
	$sensorcount = $result->num_rows;
        $rowtempsensors = mysqli_fetch_assoc($result);

        if($row['category'] <> 2) {
		$query = "SELECT zone_sensors.*, sensors.sensor_id, sensors.name, sensors.sensor_child_id, sensors.sensor_type_id FROM zone_sensors, sensors WHERE (zone_sensors.zone_sensor_id = sensors.id) AND zone_sensors.zone_id = '{$row['id']}';";
        	$sresult = $conn->query($query);
//                $sensor_count = mysqli_num_rows($result);
//        	$rowzonesensors = mysqli_fetch_assoc($result);
                $index = 0;
                while ($srow = mysqli_fetch_assoc($sresult)) {
//                        $zone_sensors[$index] = array('sensor_id' =>$srow['sensor_id'], 'sensor_child_id' =>$srow['sensor_child_id'],'zone_sensor_id' =>$srow['zone_sensor_id'], 'zone_sensor_name' =>$srow['name'], 'type' =>$srow['sensor_type_id']);
                        $zone_sensors[$index] = array('sensor_id' =>$srow['sensor_id'], 'sensor_child_id' =>$srow['sensor_child_id'],'zone_sensor_id' =>$srow['zone_sensor_id'],'zone_sensor_name' =>$srow['name'], 'type' =>$srow['sensor_type_id'], 'min_c' =>$srow['min_c'], 'max_c' =>$srow['max_c'], 'default_c' =>$srow['default_c'],'default_m' =>$srow['default_m'], 'hysteresis_time' =>$srow['hysteresis_time'], 'sp_deadband' =>$srow['sp_deadband']);
                        $index = $index + 1;
                }
                $sensor_count = $index;
        } else {
                $s_type_id = "1";
        }

        if($zone_category <> 3) {
        	$query = "SELECT relays.relay_id, relays.relay_child_id, zone_relays.zone_relay_id, zone_relays.state, relays.name FROM  zone_relays, relays WHERE (zone_relays.zone_relay_id = relays.id) AND zone_id = '{$row['id']}';";
	        $cresult = $conn->query($query);
        	$index = 0;
	        while ($crow = mysqli_fetch_assoc($cresult)) {
        	        $zone_controllers[$index] = array('controler_id' =>$crow['relay_id'], 'controler_child_id' =>$crow['relay_child_id'],'controller_relay_id' =>$crow['zone_relay_id'], 'zone_controller_state' =>$crow['state'], 'zone_controller_name' =>$crow['name']);
                	$index = $index + 1;
	        }
		$controller_count = $index;
	}

	$query = "SELECT * FROM boost WHERE zone_id = '{$row['id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rowboost = mysqli_fetch_assoc($result);

//	$query = "SELECT id, controler_id, name FROM relays WHERE id = '{$row['boiler_id']}' LIMIT 1;";
        $query = "SELECT id, relay_id, name FROM relays WHERE type > 0 LIMIT 1;";
	$result = $conn->query($query);
	$rowsystem_controller = mysqli_fetch_assoc($result);
}

// get the list of available sensors in to array
$query = "SELECT id, name, sensor_type_id FROM sensors WHERE zone_id = 0 OR zone_id = {$id} ORDER BY name ASC;";
$result = $conn->query($query);
$sensorArray = array();
$sensorArray[0]["id"] = 0;
$sensorArray[0]["name"] = "NONE";
$sensorArray[0]["sensor_type_id"] = 0;
while($rowsensors = mysqli_fetch_assoc($result)) {
   $sensorArray[] = $rowsensors;
}
$count_num_sensors =count($sensorArray);
?>

<!-- Title (e.g. Add Zone or Edit Zone) -->
<div class="container-fluid">
	<br>
	<div class="row">
        	<div class="col-lg-12">
                        <div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
                                <div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="d-flex justify-content-between">
						<div>
							<?php if ($id != 0) { echo $lang['zone_edit'] . ": " . $row['name']; }else{
        		                                echo '<i class="bi bi-plus-square" style="font-size: 1.2rem;"></i>&nbsp&nbsp'.$lang['zone_add'];} ?>
						</div>
						<div class="btn-group"><?php echo date("H:i"); ?></div>
					</div>
                        	</div>
                        	<!-- /.card-header -->
				<div class="card-body">
					<form data-bs-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
						<!-- Enable Zone -->
				                <div class="form-check">
                                			<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox0" name="zone_status" <?php $check = ($row['status'] == 1) ? 'checked' : ''; echo $check; ?>>
				                        <label class="form-check-label" for="checkbox0"><?php echo $lang['zone_enable']; ?></label> <small class="text-muted"><?php echo $lang['zone_enable_info'];?></small>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Index Number -->
						<?php 
						$query = "select index_id from zone order by index_id desc limit 1;";
						$result = $conn->query($query);
						$found_product = mysqli_fetch_array($result);
						$new_index_id = $found_product['index_id']+1;
						?>
						<div class="form-group" class="control-label"><label><?php echo $lang['zone_index_number']; ?>  </label> <small class="text-muted"><?php echo $lang['zone_index_number_info'];?></small>
							<input class="form-control" placeholder="<?php echo $lang['zone_index_number']; ?>r" value="<?php if(isset($row['index_id'])) { echo $row['index_id']; }else {echo $new_index_id; }  ?>" id="index_id" name="index_id" data-bs-error="<?php echo $lang['zone_index_number_help']; ?>" autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Zone Name -->
						<div class="form-group" class="control-label"><label><?php echo $lang['zone_name']; ?></label> <small class="text-muted"><?php echo $lang['zone_name_info'];?></small>
							<input class="form-control" placeholder="Zone Name" value="<?php if(isset($row['name'])) { echo $row['name']; } ?>" id="name" name="name" data-bs-error="<?php echo $lang['zone_name_help']; ?>" autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Zone Type -->
						<input type="hidden" id="selected_zone_category" name="selected_zone_category" value="<?php if(isset($row['category'])) { echo $row['category']; } ?>"/>
						<input type="hidden" id="selected_zone_type" name="selected_zone_type" value="<?php if(isset($row['type'])) { echo $row['type']; } else { echo 'Heating'; } ?>"/>
						<div class="form-group" class="control-label"><label><?php echo $lang['zone_type']; ?></label> <small class="text-muted"><?php echo $lang['zone_type_info'];?></small>
							<select name="type" id="type" onchange="page_map(this.options[this.selectedIndex].value, '1')" class="form-select" autocomplete="off" required>
								<?php if(isset($row['type'])) { echo '<option selected >'.$row['type'].'</option>'; } ?>
								<?php  $query = "SELECT DISTINCT `type`, `category` FROM `zone_type` ORDER BY `category`, `id` ASC;";
								$result = $conn->query($query);
								echo "<option></option>";
								while ($datarw=mysqli_fetch_array($result)) {
								        echo "<option value=".$datarw['category'].">".$datarw['type']."</option>";
								} ?>
							</select>
							<div class="help-block with-errors"></div>
						</div>

                                                <!-- Sensor Type -->
                                                <input type="hidden" id="selected_sensor_type_id" name="selected_sensor_type_id" value="<?php if(isset($zone_sensors[0]['sensor_type_id'])) { echo $zone_sensors[0]['sensor_type_id']; } else {echo "1";} ?>"/>
                                                <div class="form-group" class="control-label" id="sen_type" style="display:block"><label id="sen_type_label_1"><?php echo $lang['sensor_type']; ?></label> <small class="text-muted" id="sen_type_label_2"><?php echo $lang['sensor_type_info'];?></small>
							<select name="sen_type" id="sen_type" onchange="page_map('', this.options[this.selectedIndex].value)" class="form-select" autocomplete="off" required>
                                                                <?php
                                                                if(isset($rowtempsensors['type'])) {
                                                                        echo '<option selected value="'.$rowtempsensors['sensor_type_id'].'">'.$rowtempsensors['type'].'</option>';
                                                                } else {
                                                                        echo '<option selected value="1">'.$lang['temperature'].'</option>';
                                                                }
                                                                ?>
                                                                <?php  $query = "SELECT `id`, `type` FROM `sensor_type` ORDER BY `id`, `id` ASC;";
                                                                $result = $conn->query($query);
                                                                echo "<option></option>";
                                                                while ($datarw=mysqli_fetch_array($result)) {
                                                                        echo "<option value=".$datarw['id'].">".$datarw['type']."</option>";
                                                                } ?>
                                                        </select>
                                                        <div class="help-block with-errors"></div>
                                                </div>

						<script language="javascript" type="text/javascript">
						// script to set displayed fields based on the Zone Category and Sensor Type
						// normally passed 2 parameters
						// in the case of Zone Type change - passed the new zone category and the default sensor type of 1 (Temperature
						// in the case of Sensor Type change - passed the new sensor type id and a zero length string for the zone category, the zone category is recovered from the field 'selected_zone_category'
						function page_map(zone_category, sensor_type_id)
						{
						        var zone_cat = zone_category;
                                                        if (zone_cat.length == 0) { zone_cat = document.getElementById("selected_zone_category").value; }
							var sensor_type = sensor_type_id;
                                                        if (sensor_type.length == 0) { sensor_type = document.getElementById("selected_sensor_type_id").value; }
							var str_1 = "";
						        var str_2 = "";
							var map_bin = 0b0;

						        // set 10 bit binary variable 'map_bin' to indicate which fields are displayed
						        // bit 1        -       Sensor Type
						        // bit 2        -       Default Temperature
						        // bit 3        -       Minimum Temperature
						        // bit 4        -       Maximum Temperature
						        // bit 5        -       Setpoint Deadband
						        // bit 6        -       Sensor ID
						        // bit 7        -       controller ID
						        // bit 8        -       Boost Button ID
						        // bit 9        -       Boost Button Child ID
						        // bit 10       -       System Controller ID
							// bit 11	-	Maintain Default

							switch(zone_cat) {
						  		case "0":
						    			map_bin = 0b11111111010;
						    			break;
						  		case "1":
						    			map_bin = 0b10111111011;
						    			break;
						                case "2":
						                        map_bin = 0b00001100000;
						                        break;
						                case "3":
						                        map_bin = 0b01110111110;
						                        break;
						                case "4":
						                        map_bin = 0b01111111110;
						                        break;
						                case "5":
						                        map_bin = 0b10111111111;
						                        break;
						  		default:
						    			// code block
							}
							// mask out sensor temperture fields for 'Binary' type sensors
							if ((zone_cat === "1" || zone_cat === "5") && sensor_type === "3") { map_bin = map_bin & 0b00111100001; }
                                                        //console.log(zone_cat, sensor_type, map_bin);

						        if (map_bin & 0b1) {
						                document.getElementById("sen_type_label_1").style.visibility = 'visible';
						                document.getElementById("sen_type_label_2").style.visibility = 'visible';
						                document.getElementById("sen_type").style.display = 'block';
						                document.getElementById("sen_type").required = true;
						        } else {
						                document.getElementById("sen_type").style.display = 'none';
						                document.getElementById("sen_type_label_1").style.visibility = 'hidden';
						                document.getElementById("sen_type_label_2").style.visibility = 'hidden';
						                document.getElementById("sen_type").required = false;
						        }
							if ((map_bin & 0b10) && sensor_type !== "3") {
								if (sensor_type === "1") {
									str_1 = document.getElementById("default_c_label_text").value;
									str_2 = document.getElementById("default_c_label_info").value;
								} else {
						                        str_1 = document.getElementById("default_h_label_text").value;
						                        str_2 = document.getElementById("default_h_label_info").value;
								}
								document.getElementById("default_c_label_1").style.visibility = 'visible';
								document.getElementById("default_c_label_1").innerHTML = str_1;
								document.getElementById("default_c_label_2").style.visibility = 'visible';
								document.getElementById("default_c_label_2").innerHTML = str_2;
								document.getElementById("default_c").style.display = 'block';
								document.getElementById("default_c").required = true;
							} else {
								document.getElementById("default_c").style.display = 'none';
								document.getElementById("default_c_label_1").style.visibility = 'hidden';
								document.getElementById("default_c_label_2").style.visibility = 'hidden';
						                document.getElementById("default_c").required = false;
							}
						        if ((map_bin & 0b100) && sensor_type !== "3") {
						                if (sensor_type === "1") {
						                        str_1 = document.getElementById("min_c_label_text").value;
						                        str_2 = document.getElementById("min_c_label_info").value;
						                } else {
						                        str_1 = document.getElementById("min_h_label_text").value;
						                        str_2 = document.getElementById("min_h_label_info").value;
						                }
						                document.getElementById("min_c_label_1").style.visibility = 'visible';
						                document.getElementById("min_c_label_1").innerHTML = str_1;
						                document.getElementById("min_c_label_2").style.visibility = 'visible';
						                document.getElementById("min_c_label_2").innerHTML = str_2;
						                document.getElementById("min_c").style.display = 'block';
						                document.getElementById("min_c").required = true;
						        } else {
						                document.getElementById("min_c").style.display = 'none';
						                document.getElementById("min_c_label_1").style.visibility = 'hidden';
						                document.getElementById("min_c_label_2").style.visibility = 'hidden';
						                document.getElementById("min_c").required = false;
						        }
						        if ((map_bin & 0b1000) && sensor_type !== "3") {
						                if (sensor_type === "1") {
						                        str_1 = document.getElementById("max_c_label_text").value;
						                        str_2 = document.getElementById("max_c_label_info").value;
						                } else {
						                        str_1 = document.getElementById("max_h_label_text").value;
						                        str_2 = document.getElementById("max_h_label_info").value;
						                }
						                document.getElementById("max_c_label_1").style.visibility = 'visible';
						                document.getElementById("max_c_label_1").innerHTML = str_1;
						                document.getElementById("max_c_label_2").style.visibility = 'visible';
						                document.getElementById("max_c_label_2").innerHTML = str_2;
						                document.getElementById("max_c").style.display = 'block';
						                document.getElementById("max_c").required = true;
						        } else {
						                document.getElementById("max_c").style.display = 'none';
						                document.getElementById("max_c_label_1").style.visibility = 'hidden';
						                document.getElementById("max_c_label_2").style.visibility = 'hidden';
						                document.getElementById("max_c").required = false;
						        }
						        if (map_bin & 0b10000) {
						                document.getElementById("sp_deadband").style.display = 'block';
						                document.getElementById("sp_deadband_label").style.visibility = 'visible';
						        } else {
								document.getElementById("sp_deadband").style.display = 'none';
								document.getElementById("sp_deadband_label").style.visibility = 'hidden';
						        }
							if (map_bin & 0b100000) {
								if (zone_cat === "2") {
									str_1 = document.getElementById("sensor_a_label_text").value;
								} else {
									if (sensor_type === "1") {
										str_1 = document.getElementById("sensor_c_label_text").value;
									} else if (sensor_type === "2") {
                        	                                                str_1 = document.getElementById("sensor_h_label_text").value;
									} else {
                                        	                                str_1 = document.getElementById("sensor_s_label_text").value;
									}
								}
						                document.getElementById("sensor_id_label_1").style.visibility = 'visible';
								document.getElementById("sensor_id_label_1").innerHTML = str_1;
						                document.getElementById("sensor_id_label_2").style.visibility = 'visible';
						                document.getElementById("zone_sensor_id").style.display = 'block';
						                document.getElementById("zone_sensor_id").required = true;
						        } else {
						                document.getElementById("zone_sensor_id").style.display = 'none';
						                document.getElementById("sensor_id_label_1").style.visibility = 'hidden';
						                document.getElementById("sensor_id_label_2").style.visibility = 'hidden';
						                document.getElementById("zone_sensor_id").required = false;
						        }
						        if (map_bin & 0b1000000) {
						                document.getElementById("controler_id_label").style.visibility = 'visible';
						                document.getElementById("controler_id").style.display = 'block';
						                document.getElementById("controler_id").required = true;
						        } else {
						                document.getElementById("controler_id").style.display = 'none';
						                document.getElementById("controler_id_label").style.visibility = 'hidden';
						                document.getElementById("controler_id").required = false;
						        }
						        if (map_bin & 0b10000000) {
						                document.getElementById("boost_button_id_label").style.visibility = 'visible';
						                document.getElementById("boost_button_id").style.display = 'block';
						                document.getElementById("boost_button_id").required = true;
						        } else {
						                document.getElementById("boost_button_id").style.display = 'none';
						                document.getElementById("boost_button_id_label").style.visibility = 'hidden';
						                document.getElementById("boost_button_id").required = false;
						        }
						        if (map_bin & 0b100000000) {
						                document.getElementById("boost_button_child_id_label").style.visibility = 'visible';
						                document.getElementById("boost_button_child_id").style.display = 'block';
						                document.getElementById("boost_button_child_id").required = true;
						        } else {
						                document.getElementById("boost_button_child_id").style.display = 'none';
						                document.getElementById("boost_button_child_id_label").style.visibility = 'hidden';
						                document.getElementById("boost_button_child_id").required = false;
						        }
						        if (map_bin & 0b1000000000) {
						                document.getElementById("system_controller_id_label").style.visibility = 'visible';
						                document.getElementById("system_controller_id").style.display = 'block';
						                document.getElementById("system_controller_id").required = true;
						        } else {
						                document.getElementById("system_controller_id").style.display = 'none';
						                document.getElementById("system_controller_id_label").style.visibility = 'hidden';
						                document.getElementById("system_controller_id").required = false;
						        }
                                                        if (map_bin & 0b10000000000 && sensor_type === "1") {
                                                                document.getElementById("default_m_label_1").style.visibility = 'visible';
								document.getElementById("default_m_label_2").style.visibility = 'visible';
                                                                document.getElementById("m_default").style.display = 'block';
                                                                document.getElementById("m_default").required = true;
                                                        } else {
                                                                document.getElementById("m_default").style.display = 'none';
                                                                document.getElementById("default_m_label_1").style.visibility = 'hidden';
								document.getElementById("default_m_label_2").style.visibility = 'hidden';
                                                                document.getElementById("m_default").required = false;
                                                        }

							// re-build the sensor list based on the zone type (1 = temperature, 2 = humidity)
						 	var opt = document.getElementById("zone_sensor_id").getElementsByTagName("option");
							var jArray = <?php echo json_encode($sensorArray); ?>;

						 	for(j=opt.length-1;j>=0;j--)
						 	{
						        	document.getElementById("zone_sensor_id").options.remove(j);
						 	}

						        for(j=0;j<jArray.length;j++)
						        {
						                var optn = document.createElement("OPTION");
								var stype = parseInt(jArray[j]['sensor_type_id']);
						                optn.text = jArray[j]['name'];
						                optn.value = jArray[j]['id'];
								if(stype == sensor_type || zone_cat === "2") {
						                	document.getElementById("zone_sensor_id").options.add(optn);
								}
						        }

                                                        document.getElementById("selected_zone_category").value = zone_cat;
                                                        var e = document.getElementById("type");
                                                        var selected_type = e.options[e.selectedIndex].text;
                                                        document.getElementById("selected_zone_type").value = selected_type;

                                                        //set initial sensor
//                                                        document.getElementById("zone_sensor_id").value = document.getElementById("selected_sensor_id").value;
                                                        document.getElementById("selected_sensor_type_id").value = sensor_type;
						}
						</script>

						<!-- Default Temperature -->
						<input type="hidden" id="default_c_label_text" name="default_c_label_text" value="<?php echo $lang['default_temperature']; ?>"/>
						<input type="hidden" id="default_c_label_info" name="default_c_label_info" value="<?php echo $lang['zone_default_temperature_info']; ?>"/>
						<input type="hidden" id="default_h_label_text" name="default_h_label_text" value="<?php echo $lang['default_humidity']; ?>"/>
						<input type="hidden" id="default_h_label_info" name="default_h_label_info" value="<?php echo $lang['zone_default_humidity_info']; ?>"/>
						<div class="form-group" class="control-label" style="display:block"><label id="default_c_label_1"><?php echo $lang['default_temperature']; ?></label> <small class="text-muted" id="default_c_label_2"><?php echo $lang['zone_default_temperature_info'];?></small>
							<input class="form-control" placeholder="<?php echo $lang['zone_default_temperature_help']; ?>" value="<?php if(isset($zone_sensors[0]['default_c'])) { echo DispSensor($conn,$zone_sensors[0]['default_c'],$zone_sensors[0]['sensor_type_id']); } else {echo DispSensor($conn,'25',$zone_sensors[0]['sensor_type_id']);}  ?>" id="default_c" name="default_c" data-bs-error="<?php echo $lang['zone_default_temperature_error']; ?>"  autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Maintain Default  -->
						<div class="form-group" class="control-label" style="display:block"><label id="default_m_label_1"><?php echo $lang['maintain_default_temperature']; ?></label> <small class="text-muted" id="default_m_label_2"><?php echo $lang['maintain_default_temperature_info'];?></small>
	                                                <select class="form-select" type="text" id="m_default" name="m_default" onchange=set_default_m(this.options[this.selectedIndex].value)>
								<?php
                	                                        echo '
								<option value="0" ' . ($zone_sensors[0]["default_m"]=='0' ? 'selected' : '') . '>'.$lang['no'].'</option>
                                	                        <option value="1" ' . ($zone_sensors[0]["default_m"]=='1' ? 'selected' : '') . '>'.$lang['yes'].'</option>
								';
								?>
                                                	</select>
							<div class="help-block with-errors"></div>
						</div>
                                                <script language="javascript" type="text/javascript">
                                                function set_default_m(value)
                                                {
                                                        var valuetext = value;
                                                        document.getElementById("maintain_default").value = valuetext;
                                                }
                                                </script>
                                                <input type="hidden" id="maintain_default" name="maintain_default" value="<?php echo $zone_sensors[0]["default_m"]?>"/>

						<!-- Minimum Temperature -->
                                                <input type="hidden" id="min_c_label_text" name="min_c_label_text" value="<?php echo $lang['min_temperature']; ?>"/>
                                                <input type="hidden" id="min_c_label_info" name="min_c_label_info" value="<?php echo $lang['zone_min_temperature_info']; ?>"/>
                                                <input type="hidden" id="min_h_label_text" name="min_h_label_text" value="<?php echo $lang['min_humidity']; ?>"/>
                                                <input type="hidden" id="min_h_label_info" name="min_h_label_info" value="<?php echo $lang['zone_min_humidity_info']; ?>"/>
						<div class="form-group" class="control-label" style="display:block"><label id="min_c_label_1"><?php echo $lang['min_temperature']; ?></label> <small class="text-muted" id="min_c_label_2"><?php echo $lang['zone_min_temperature_info'];?></small>
							<input class="form-control" placeholder="<?php echo $lang['zone_min_temperature_help']; ?>" value="<?php if(isset($zone_sensors[0]['min_c'])) { echo DispSensor($conn,$zone_sensors[0]['min_c'],$zone_sensors[0]['sensor_type_id']); } else {echo DispSensor($conn,'15',$zone_sensors[0]['sensor_type_id']);}  ?>" id="min_c" name="min_c" data-bs-error="<?php echo $lang['zone_min_temperature_error']; ?>"  autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Maximum Temperature -->
						<input type="hidden" id="max_c_label_text" name="max_c_label_text" value="<?php echo $lang['max_temperature']; ?>"/>
						<input type="hidden" id="max_c_label_info" name="max_c_label_info" value="<?php echo $lang['zone_max_temperature_info']; ?>"/>
						<input type="hidden" id="max_h_label_text" name="max_h_label_text" value="<?php echo $lang['max_humidity']; ?>"/>
						<input type="hidden" id="max_h_label_info" name="max_h_label_info" value="<?php echo $lang['zone_max_humidity_info']; ?>"/>
						<div class="form-group" class="control-label" style="display:block"><label id="max_c_label_1"><?php echo $lang['max_temperature']; ?></label> <small class="text-muted" id="max_c_label_2"><?php echo $lang['zone_max_temperature_info'];?></small>
							<input class="form-control" placeholder="<?php echo $lang['zone_max_temperature_help']; ?>" value="<?php if(isset($zone_sensors[0]['max_c'])) { echo DispSensor($conn,$zone_sensors[0]['max_c'],$zone_sensors[0]['sensor_type_id']); } else {echo DispSensor($conn,'25',$zone_sensors[0]['sensor_type_id']);}  ?>" id="max_c" name="max_c" data-bs-error="<?php echo $lang['zone_max_temperature_error']; ?>"  autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>
						<?php // Removed 29/01/2022 by twa as these 2 parameters are never used
						if ($no_max_op_hys == 1) { ?>
							<!-- Maximum Operation Time -->
							<div class="form-group" class="control-label" id="max_operation_time_label" style="display:block"><label><?php echo $lang['zone_max_operation_time']; ?></label> <small class="text-muted"><?php echo $lang['zone_max_operation_time_info'];?></small>
								<input class="form-control" placeholder="<?php echo $lang['zone_max_operation_time_help']; ?>" value="<?php if(isset($row['max_operation_time'])) { echo $row['max_operation_time']; } else {echo '60';}  ?>" id="max_operation_time" name="max_operation_time" data-bs-error="<?php echo $lang['zone_max_operation_time_error']; ?>"  autocomplete="off" required>
								<div class="help-block with-errors"></div>
							</div>

							<!-- Hysteresis Time -->
							<div class="form-group" class="control-label" id="hysteresis_time_label" style="display:block"><label><?php echo $lang['hysteresis_time']; ?></label> <small class="text-muted"><?php echo $lang['zone_hysteresis_info'];?></small>
								<input class="form-control" placeholder="<?php echo $lang['zone_hysteresis_time_help']; ?>" value="<?php if(isset($zone_sensors[0]['hysteresis_time'])) { echo $zone_sensors[0]['hysteresis_time']; } else {echo '3';} ?>" id="hysteresis_time" name="hysteresis_time" data-bs-error="<?php echo $lang['zone_hysteresis_time_error']; ?>"  autocomplete="off" required>
								<div class="help-block with-errors"></div>
							</div>
						<?php } ?>

						<!-- Setpoint Deadband -->
						<div class="form-group" class="control-label" id="sp_deadband_label" style="display:block"><label><?php echo $lang['zone_sp_deadband']; ?></label> <small class="text-muted"><?php echo $lang['zone_sp_deadband_info'];?></small>
							<input class="form-control" placeholder="<?php echo $lang['zone_sp_deadband_help']; ?>" value="<?php if(isset($zone_sensors[0]['sp_deadband'])) { echo $zone_sensors[0]['sp_deadband']; } else {echo '0.5';} ?>" id="sp_deadband" name="sp_deadband" data-bs-error="<?php echo $lang['zone_sp_deadband_error'] ; ?>"  autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<input type="hidden" id="sensor_c_label_text" name="sensor_c_label_text" value="<?php echo $lang['primary_temperature_sensor']; ?>"/>
						<input type="hidden" id="sensor_h_label_text" name="sensor_h_label_text" value="<?php echo $lang['humidity_sensor']; ?>"/>
						<input type="hidden" id="sensor_s_label_text" name="sensor_s_label_text" value="<?php echo $lang['switch_sensor']; ?>"/>
                                                <input type="hidden" id="sensor_a_label_text" name="sensor_a_label_text" value="<?php echo $lang['associated_sensor']; ?>"/>
                                                <input type="hidden" id="sensor_count" name="sensor_count" value="<?php echo $sensor_count?>"/>
                                                <div class="sensor_id_wrapper">
                                                        <?php for ($i = 0; $i < $sensor_count; $i++) { ?>
                                                        	<div class="wrap" id>
									<!-- Sensor ID -->
									<div class="form-group" class="control-label" id="sensor_id" style="display:block"><label id="sensor_id_label_1"><?php echo $lang['primary_temperature_sensor']; ?></label> <small class="text-muted" id="sensor_id_label_2"><?php echo $lang['zone_sensor_id_info'];?></small>
										<input type="hidden" id="selected_sensors_id[]" name="selected_sensors_id[]" value="<?php echo $zone_sensors[$i]['zone_sensor_id']?>"/>
										<div class="entry input-group col-12" id="sen_id - <?php echo $i ?>">
											<select id="sens_id<?php echo $i ?>" onchange="SensorIDList(this.options[this.selectedIndex].value, <?php echo $i ?>)" name="sens_id<?php echo $i ?>" class="form-select" data-bs-error="<?php echo $lang['zone_temp_sensor_id_error']; ?>" autocomplete="off">
												<?php if(isset($zone_sensors[$i]["zone_sensor_name"])) { echo '<option selected >'.$zone_sensors[$i]["zone_sensor_name"].'</option>'; } ?>
												<?php if ($i == 0) {
                                                                 					$query = "SELECT id, name, sensor_type_id FROM sensors WHERE sensor_type_id = 1 ORDER BY id ASC;";
												} else {
					                                                                 $query = "SELECT id, name, sensor_type_id FROM sensors WHERE sensor_type_id = 1 AND fail_timeout > 0 ORDER BY id ASC;";
												}
												$result = $conn->query($query);
												echo "<option></option>";
												while ($datarw=mysqli_fetch_array($result)) {
													echo "<option value=".$datarw['id'].">".$datarw['name']."</option>";
												} ?>
											</select>
											<div class="help-block with-errors"></div>
                                                                                	<span class="input-group-btn">
                                                                               			<?php if ($i == 0) {
                											echo '<button class="btn btn-outline add_sensor_button" type="button" data-bs-toggle="tooltip" title="'.$lang['add_sensor'].'"><img src="./images/add-icon.png"/></button>';
                                                                                       		} else {
        												echo '<button class="btn btn-outline remove_sensor_button" type="button" data-bs-toggle="tooltip" title="'.$lang['remove_sensor'].'"><img src="./images/remove-icon.png"/></button>';
	                                                                                       	} ?>
        	                                                                       	</span>
										</div>
									</div>
								</div>
							 <?php } ?>
						</div>

						<script language="javascript" type="text/javascript">
						function SensorIDList(value, index)
						{
                                                        var valuetext = value;
							var indextext = index;
                                                        var f = document.getElementsByName('selected_sensors_id[]');
                                                        f[indextext].value = valuetext;
							//console.log(indtext);
						}
						</script>
						<?php if ($sensorcount == 0) { $s = 0; } else { $s = $rowtempsensors['id']; } ?>
						<input type="hidden" id="initial_sensor_id" name="initial_sensor_id" value="<?php echo $s?>"/>

						<input type="hidden" id="controller_count" name="controller_count" value="<?php echo $controller_count?>"/>
						<div class="controler_id_wrapper">
							<?php for ($i = 0; $i < $controller_count; $i++) { ?>
								<div class="wrap" id>
									<!-- Zone Controller ID -->
									<div class="form-group" class="control-label" id="controler_id" style="display:block"><label id="controler_id_label"><?php echo $lang['zone_controller_id']; ?></label> <small class="text-muted"><?php echo $lang['zone_controler_id_info'];?></small>
										<input type="hidden" id="selected_controler_id[]" name="selected_controler_id[]" value="<?php echo $zone_controllers[$i]['controller_relay_id']?>"/>
										<div class="entry input-group col-12" id="cnt_id - <?php echo $i ?>">
											<select id="contr_id<?php echo $i ?>" onchange="ControllerIDList(this.options[this.selectedIndex].value)" name="contr_id<?php echo $i ?>" class="form-select" data-bs-error="<?php echo $lang['zone_controller_id_error']; ?>" autocomplete="off">
												<?php if(isset($zone_controllers[$i]["zone_controller_name"])) { echo '<option selected >'.$zone_controllers[$i]["zone_controller_name"].'</option>'; } ?>
												<?php  $query = "SELECT id, name, type FROM relays WHERE type = 0 OR type = 5 ORDER BY id ASC;";
												$result = $conn->query($query);
												echo "<option></option>";
												while ($datarw=mysqli_fetch_array($result)) {
													echo "<option value=".$datarw['id'].">".$datarw['name']."</option>";
												} ?>
											</select>
											<div class="help-block with-errors"></div>
                                                                                        <span class="input-group-btn">
                                                                                                <?php if ($i == 0) {
                											echo '<button class="btn btn-outline add_controller_button" type="button" data-bs-toggle="tooltip" title="'.$lang['add_controller'].'"><img src="./images/add-icon.png"/></button>';
                                                                                                } else {
        												echo '<button class="btn btn-outline remove_controller_button" type="button" data-bs-toggle="tooltip" title="'.$lang['remove_controller'].'"><img src="./images/remove-icon.png"/></button>';
                                                                                                } ?>
                                                                                        </span>
										</div>
    									</div>
								</div>
							<?php } ?>
						</div>

						<script language="javascript" type="text/javascript">
						function ControllerIDList(value)
						{
						        var valuetext = value;
						        var indtext = document.getElementById("controller_count").value - 1;

						        var e = document.getElementById("contr_id".concat(indtext));
						        var selected_controler_id = e.options[e.selectedIndex].value;
						        var f = document.getElementsByName('selected_controler_id[]');
						        f[indtext].value = selected_controler_id;
						}
						</script>

						<!-- Boost Button ID -->
						<?php
						echo '<div class="form-group" class="control-label" id="boost_button_id_label" style="display:block"><label>'.$lang['zone_boost_button_id'].'</label> <small class="text-muted">'.$lang['zone_boost_info'].'</small>
							<select id="boost_button_id" name="boost_button_id" class="form-select" data-bs-error="'.$lang['zone_boost_id_error'].'" autocomplete="off" >';
								if(isset($rowboost['boost_button_id'])) {
									echo '<option selected >'.$rowboost['boost_button_id'].'</option>';
								} else {
									echo '<option selected value="0">N/A</option>';
								}
								$query = "SELECT node_id FROM nodes where name LIKE '% Console';";
								$result = $conn->query($query);
								while ($datarw=mysqli_fetch_array($result)) {
									$node_id=$datarw["node_id"];
									echo "<option>$node_id</option>";
								}
							echo '</select>
							<div class="help-block with-errors"></div>
						</div>';
						?>

						<!-- Boost Button Child ID -->
						<?php
						echo '<div class="form-group" class="control-label" id="boost_button_child_id_label" style="display:block"><label>'.$lang['zone_boost_button_child_id'].'</label><small class="text-muted">'.$lang['zone_boost_button_info'].'</small>
							<select id="boost_button_child_id" name="boost_button_child_id" class="form-select" data-bs-error="'.$lang['zone_boost_child_id_error'].'" autocomplete="off" required>';
								if(isset($rowboost['boost_button_child_id'])) {
									echo '<option selected >'.$rowboost['boost_button_child_id'].'</option>';
								}else {
									echo '<option selected value="0">N/A</option>';
								}
								echo '<option>1</option><option>2</option><option>3</option><option>4</option><option>5</option><option>6</option><option>7</option><option>8</option>';
							echo '</select>
							<div class="help-block with-errors"></div>
						</div>';
						?>

						<!-- System Controller -->
						<div class="form-group" class="control-label" id="system_controller_id_label" style="display:block"><label><?php echo $lang['system_controller']; ?></label>
							<select id="system_controller_id" name="system_controller_id" class="form-select" data-bs-error="System Controller ID can not be empty!" autocomplete="off" required>
								<?php if(isset($rowsystem_controller['id'])) { echo '<option selected >'.$rowsystem_controller['id'].'-'.$rowsystem_controller['name'].' Controller Relay Node ID: '.$rowsystem_controller['relay_id'].'</option>'; } ?>
								<?php  $query = "SELECT id, relay_id, name FROM relays WHERE type = 1 OR type = 2;";
								$result = $conn->query($query);
								while ($datarw=mysqli_fetch_array($result)) {
									$system_controller_id=$datarw["id"].'-'.$datarw["name"].' Controller Relay Node ID: '.$datarw["relay_id"];
									echo "<option>$system_controller_id</option>";
								} ?>
							</select>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Buttons -->
						<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-sm">
						<a href="<?php echo $link; ?>"><button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color'); ?> btn-sm"><?php echo $lang['cancel']; ?></button></a>
					</form>
				</div>
                        	<!-- /.card-body -->
				<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="text-start">
						<?php
						if ($id != 0) {
							echo '<script type="text/javascript">',
						     	'page_map("'.$row['category'].'", "'.$s_type_id.'");',
     							'</script>'
							;
						}
						ShowWeather($conn);
						?>
                            		</div>
                        	</div>
				<!-- /.card-footer -->
                    	</div>
			<!-- /.card -->
		</div>
                <!-- /.col-lg-4 -->
	</div>
        <!-- /.row -->
</div>
<!-- /#container -->
<?php }  ?>
<?php include("footer.php");  ?>

<script>
$(document).ready(function(){
    $('[data-bs-toggle="tooltip"]').tooltip({
      trigger : 'hover'
    });
    $('[data-bs-toggle="tooltip"]').on('click', function () {
      $(this).tooltip('hide')
    });
});
</script>
