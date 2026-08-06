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
}

$uri = $_SERVER['QUERY_STRING'];
if (strpos($uri, "id=") !== false) { $link = "settings.php?s_id=8"; } else { $link = "home.php"; }

//Form submit
if (isset($_POST['submit'])) {
        $pre_post = isset($_POST['pre_post']) ? $_POST['pre_post'] : "0";
        $index_id = $_POST['index_id'];
	$name = $_POST['name'];
        $type = $_POST['type_id'];
	$selected_relay_id = $_POST['selected_relay_id'];
        $query = "SELECT type, node_id, name FROM nodes WHERE id = '".$selected_relay_id."' LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
	$node_id = $row['node_id'];
        $node_type = $row['type'];
	$node_name = $row['name'];
	if(strpos($node_type, 'Tasmota') !== false) {
	        $query = "SELECT * FROM http_messages WHERE node_id = '{$node_id}' AND message_type = 0 LIMIT 1;";
        	$result = $conn->query($query);
	        $found_product = mysqli_fetch_array($result);
		$payload = $found_product['command']." ".$found_product['parameter'];
	} else {
		$payload = 0;
	}
        if(strpos($node_name, 'Gateway Controller Relay' || strpos($node_name, 'Olimex Gateway Controller') !== false) !== false) {
                $message_type = 25;
        } else {
                $message_type = 2;
        }
	$relay_child_id = $_POST['relay_child_id'];
	$on_trigger = $_POST['trigger'];
        $sync = '0';
        $purge= '0';
	$show_it = $_POST['show_it'];
        $message_in = $_POST['message_in'];
	$m_out_id = $_POST['m_out_id'];
	$lag_time = $_POST['lag_time'];
        $fail_timeout = $_POST['fail_timeout'];
	$group_id = $_POST['group_id'];
	//Add or Edit relay record to relays Table
        $query = "INSERT INTO `relays` (`id`, `sync`, `purge`, `relay_id`, `relay_child_id`, `name`, `type`, `index_id`, `pre_post`, `show_it`, `message_in`,`on_trigger`, `lag_time`,
                `group_id`, `schedule_prev`,`schedule`, `sch_time_id`,`user_display`, `state`, `current_val_2`, `fail_timeout`)
                VALUES ('{$id}', '{$sync}', '{$purge}', '{$selected_relay_id}', '{$relay_child_id}', '{$name}', '{$type}', '{$index_id}', 0, 1, 0, '{$on_trigger}',
                '{$lag_time}',  '{$group_id}', 0, 0, 0, 0, 0, 0, '{$fail_timeout}')
                ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), relay_id='{$selected_relay_id}', relay_child_id='{$relay_child_id}', name=VALUES(name),
                type=VALUES(type), `index_id` = VALUES(index_id), `pre_post` = VALUES(pre_post), `show_it` = '{$show_it}', `message_in` = '{$message_in}', on_trigger=VALUES(on_trigger),
                lag_time=VALUES(lag_time), group_id=VALUES(group_id), schedule_prev=VALUES(schedule_prev), schedule=VALUES(schedule), sch_time_id=(sch_time_id),
                fail_timeout=VALUES(fail_timeout);";
	$result = $conn->query($query);
        $temp_id = mysqli_insert_id($conn);
	if ($result) {
                if ($id==0){
                        $message_success = "<p>".$lang['relay_record_add_success']."</p>";
                } else {
                        $message_success = "<p>".$lang['relay_record_update_success']."</p>";
                }
	} else {
		$error = "<p>".$lang['relay_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
	}

        //Add or Edit messages_out record to messages_out Table
	$query = "INSERT INTO `messages_out` (`id`, `sync`, `purge`, `n_id`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`)
		VALUES ('{$m_out_id}', '0', '0', '{$selected_relay_id}', '{$node_id}', '{$relay_child_id}', '1', '1', '{$message_type}', '{$payload}', '0', now(), 0)
		ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), n_id='{$selected_relay_id}', node_id='{$node_id}', child_id='{$relay_child_id}', sub_type=VALUES(sub_type),
		ack=VALUES(ack), type='{$message_type}', payload=VALUES(payload), sent=VALUES(sent), datetime=VALUES(datetime), zone_id=VALUES(zone_id);";
	$result = $conn->query($query);
        if ($result) {
		if ($m_out_id==0){
               		$message_success .= "<p>".$lang['messages_out_add_success']."</p>";
		} else {
                        $message_success .= "<p>".$lang['messages_out_update_success']."</p>";
		}
        } else {
               	$error .= "<p>".$lang['messages_out_fail']."</p> <p>" .mysqli_error($conn). "</p>";
        }
        $message_success .= "<p>".$lang['do_not_refresh']."</p>";

	header("Refresh: 10; url=home.php?page_name=onetouch");
        // After update on all required tables, set $id to mysqli_insert_id.
        if ($id==0) {
                header("Refresh: 10; url=home.php?page_name=onetouch");
                $id=$temp_id;
        } else {
                header("Refresh: 10; url=settings.php?s_id=8");
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
        $query = "SELECT relays.*, IF(rg.id IS NULL, 0, rg.name) AS relay_group_id, IF(rg.id IS NULL, 'NONE', rg.name) AS relay_group_name
                FROM relays
                LEFT JOIN relay_group rg ON relays.group_id = rg.id
                WHERE relays.id = {$id} LIMIT 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);

	$query = "SELECT * FROM nodes WHERE id = '{$row['relay_id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rownode = mysqli_fetch_assoc($result);

        //check if this relay has an entry in the messages_out table
        $query = "SELECT * FROM `messages_out` WHERE `node_id` = '{$rownode['node_id']}' AND child_id = {$row['relay_child_id']} LIMIT 1;";
        $result = $conn->query($query);
        $row_messages_out = mysqli_fetch_assoc($result);
}
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
							<?php if ($id != 0) { echo $lang['relay_edit'] . ": " . $row['name']; }else{
                		            		echo '<i class="bi bi-plus-square" style="font-size: 1.2rem;"></i>&nbsp&nbsp'.$lang['relay_add'];} ?>
						</div>
						<div class="btn-group"><?php echo date("H:i"); ?></div>
					</div>
                        	</div>
                        	<!-- /.card-header -->
				<div class="card-body">
					<form data-bs-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

						<!-- Before or After System Controller Icon -->
                                                <div class="form-check">
							<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox0" name="pre_post" <?php $check = ($row['pre_post'] == 1) ? 'checked' : ''; echo $check; ?>>
							<label class="form-check-label" for="checkbox0"> <?php echo $lang['pre_sc_tile']; ?> </label> <small class="text-muted"><?php echo $lang['pre_sc_tile_info'];?></small>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Index Number -->
						<?php
						$query = "select index_id from relays order by index_id desc limit 1;";
						$result = $conn->query($query);
						$found_product = mysqli_fetch_array($result);
						$new_index_id = $found_product['index_id']+1;
						?>
						<div class="form-group" class="control-label"><label><?php echo $lang['relay_index_number']; ?>  </label> <small class="text-muted"><?php echo $lang['relay_index_number_info'];?></small>
							<input class="form-control" placeholder="<?php echo $lang['relay_index_number']; ?>r" value="<?php if(isset($row['index_id'])) { echo $row['index_id']; }else {echo $new_index_id; }  ?>" id="index_id" name="index_id" data-bs-error="<?php echo $lang['relay_index_number_help']; ?>" autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- messagaes_out table id -->
						<input type="hidden" id="m_out_id" name="m_out_id" value="<?php if(isset($row_messages_out['id'])) { echo $row_messages_out['id']; } else { echo '0'; }?>"/>

						<!-- Controller Type -->
						<div class="form-group" class="control-label"><label><?php echo $lang['controller_type']; ?></label> <small class="text-muted"><?php echo $lang['controller_type_info'];?></small>
							<select class="form-select" type="text" id="type" name="type" onchange=RelayTypeID(this.options[this.selectedIndex].value)>
								<?php if(isset($row['type'])) {
									switch ($row['type']) {
										case 0:
									        	echo '<option selected >Zone</option>';
									        	break;
						    				case 1:
						        				echo '<option selected >Boiler</option>';
						        				break;
							                        case 5:
							                                echo '<option selected >Pump</option>';
							                                break;
                                                                                case 6:
                                                                                        echo '<option selected >Stand Alone</option>';
                                                                                        break;
							    			case 2:
							        			echo '<option selected >HVAC - Heat</option>';
							        			break;
							    			case 3:
						        				echo '<option selected >HVAC - Chill</option>';
						       					 break;
						    				case 4:
						        				echo '<option selected >HVAC - Fan</option>';
							        			break;
									}
								} ?>
							        <option value=0>Zone</option>
							        <option value=1>Boiler</option>
							        <option value=5>Pump</option>
                                                                <option value=6>Stand Alone</option>
							        <option value=2>HVAC - Heat</option>
						        	<option value=3>HVAC - Chill</option>
							        <option value=4>HVAC - Fan</option>
							</select>
							<div class="help-block with-errors"></div>
						</div>
						<input type="hidden" id="type_id" name="type_id" value="<?php if(isset($row['type'])) { echo $row['type']; } else { echo '0'; }?>"/>

						<script language="javascript" type="text/javascript">
							function RelayTypeID(value)
								{
							        var valuetext = value;

							        document.getElementById("type_id").value = valuetext;
                                                                if (valuetext == 6) {
                                                                        document.getElementById("group_id_label_1").style.display = 'block';
                                                                        document.getElementById("group_id_label_2").style.visibility = 'visible';
                                                                } else {
                                                                        document.getElementById("group_id_label_1").style.display = 'none';
                                                                        document.getElementById("group_id_label_2").style.visibility = 'hidden';
                                                                }
							}
						</script>

						<!-- Relay Name -->
						<div class="form-group" class="control-label"><label><?php echo $lang['relay_name']; ?></label> <small class="text-muted"><?php echo $lang['relay_name_info'];?></small>
							<input class="form-control" placeholder="Relay Name" value="<?php if(isset($row['name'])) { echo $row['name']; } ?>" id="name" name="name" data-bs-error="<?php echo $lang['relay_name_help']; ?>" autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Relay ID -->
						<div class="form-group" class="control-label" id="relay_id_label" style="display:block"><label><?php echo $lang['relay_id']; ?></label> <small class="text-muted"><?php echo $lang['relay_id_info'];?></small>
							<select id="relay_id" onchange=RelayChildList(this.options[this.selectedIndex].value) name="relay_id" class="form-control select2" data-bs-error="<?php echo $lang['relay_id_error']; ?>" autocomplete="off" required>
								<?php if(isset($rownode['id'])) {
								        echo '<option selected >'.$rownode['id']." - ".$rownode['name'].'</option>';
								        $query = "SELECT id, node_id, name, max_child_id FROM nodes WHERE (name LIKE '%Controller%' OR name LIKE '%Relay%') AND id <> ".$rownode['id']." ORDER BY node_id ASC;";
								} else {
							        	$query = "SELECT id, node_id, name, max_child_id FROM nodes WHERE name LIKE '%Controller%' OR name LIKE '%Relay%' ORDER BY node_id ASC;";
								}
								$result = $conn->query($query);
								echo "<option></option>";
								while ($datarw=mysqli_fetch_array($result)) {
							        if(strpos($datarw['name'], 'Add-On') !== false) { $max_child_id = 0; } else { $max_child_id = $datarw['max_child_id']; }
							        echo "<option value=".$datarw['max_child_id'].">".$datarw['id']." - ".$datarw['name']."</option>"; } ?>
							</select>
							<div class="help-block with-errors"></div>
						</div>

						<script language="javascript" type="text/javascript">
							function RelayChildList(value)
								{
							        var valuetext = value;
							        var e = document.getElementById("relay_id");
						        	var selected_relay_id = e.options[e.selectedIndex].text;
							        var selected_relay_id = selected_relay_id.split(" - ");
							        var gpio_pins = document.getElementById('gpio_pin_list').value

							        document.getElementById("selected_relay_id").value = selected_relay_id[0];

							        var opt = document.getElementById("relay_child_id").getElementsByTagName("option");
							        for(j=opt.length-1;j>=0;j--)
								        {
        							        document.getElementById("relay_child_id").options.remove(j);
        							}
							        if(selected_relay_id[1].includes("GPIO")) {
							                var pins_arr = gpio_pins.split(',');
							                for(j=0;j<=pins_arr.length-1;j++)
                								{
							                        var optn = document.createElement("OPTION");
							                        optn.text = pins_arr[j];
							                        optn.value = pins_arr[j];
						        	                document.getElementById("relay_child_id").options.add(optn);
                							}
	        						} else {
								        for(j=1;j<=valuetext;j++)
        									{
							                	var optn = document.createElement("OPTION");
								                optn.text = j;
							        	        optn.value = j;
							                	document.getElementById("relay_child_id").options.add(optn);
        								}
								}
							}
						</script>
						<input type="hidden" id="selected_relay_id" name="selected_relay_id" value="<?php echo $rownode['id']?>"/>
                                                <input type="hidden" id="show_it" name="show_it" value="<?php echo $row['show_it']?>"/>
                                                <input type="hidden" id="message_in" name="message_in" value="<?php echo $row['message_in']?>"/>

						<!-- Relay Child ID -->
						<input type="hidden" id="gpio_pin_list" name="gpio_pin_list" value="<?php echo implode(",", array_filter(Get_GPIO_List()))?>"/>
						<div class="form-group" class="control-label"><label><?php echo $lang['relay_child_id']; ?></label> <small class="text-muted"><?php echo $lang['relay_child_id_info'];?></small>
						        <select id="relay_child_id" name="relay_child_id" class="form-control select2" data-bs-error="<?php echo $lang['relay_child_id_error']; ?>" autocomplete="off" required>
                                                                <?php if(strpos($rownode["type"], "GPIO") !== false) {
                                                                        $gpio_list=Get_GPIO_List();
                                                                        if(isset($row['relay_child_id'])) {
                                                                                echo '<option selected >'.$row['relay_child_id'].'</option>';
                                                                        } else {
                                                                                echo '<option selected >'.$gpio_list[0].'</option>';
                                                                        }
                                                                        for ($x = 0; $x <= count(array_filter($gpio_list)) - 1; $x++) {
                                                                                echo "<option value=".$gpio_list[$x].">".$gpio_list[$x]."</option>";
                                                                        }
                                                                } else {
                                                                        if(isset($row['relay_child_id'])) {
                                                                                echo '<option selected >'.$row['relay_child_id'].'</option>';
                                                                        } else {
                                                                                echo '<option selected >1</option>';
                                                                        }
                                                                        for ($x = 1; $x <= $rownode['max_child_id']; $x++) {
                                                                                echo "<option value=".$x.">".$x."</option>";
                                                                        }
                                                                } ?>
				        		</select>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Relay On Trigger Level -->
						<div class="form-group" class="control-label"><label><?php echo $lang['relay_trigger']; ?></label> <small class="text-muted"><?php echo $lang['relay_trigger_info'];?></small>
							<select class="form-select" type="text" id="on_trigger" name="on_trigger" onchange=OnTrigger(this.options[this.selectedIndex].value)>
								<?php if(isset($row['on_trigger'])) {
									if ($row['on_trigger'] == 0) { echo '<option selected >LOW</option>'; } else { echo '<option selected >HIGH</option>'; }
								} ?>
								<option value=0>LOW</option>
					                	<option value=1>HIGH</option>
						        </select>
							<div class="help-block with-errors"></div>
						</div>
						<input type="hidden" id="trigger" name="trigger" value="<?php if(isset($row['on_trigger'])) { echo $row['on_trigger']; } else { echo '0'; }?>"/>

						<script language="javascript" type="text/javascript">
							function OnTrigger(value)
								{
						        	var valuetext = value;
							        var e = document.getElementById("on_trigger");
							        var selected_on_trigger = e.options[e.selectedIndex].value;

							        document.getElementById("trigger").value = selected_on_trigger;
							}
						</script>

						<!-- Relay ON Lag Time -->
						<div class="form-group" class="control-label"><label><?php echo $lang['relay_lag_time']; ?></label> <small class="text-muted"><?php echo $lang['relay_lag_time_info'];?></small>
							<input class="form-control" placeholder="Relay ON Lag Time" value="<?php if(isset($row['lag_time'])) { echo $row['lag_time']; } else { echo "0"; } ?>" id="lag_time" name="lag_time" data-bs-error="<?php echo $lang['relay_lag_time_help']; ?>" autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

                                                <!-- Relay Fail Timout -->
                                                        <div class="form-group" class="control-label" id="fail_timeout_label" style="display:block"><label><?php echo$lang['fail_timeout'];?></label> <small class="text-muted"><?php echo $lang['fail_timeout_info'];?></small>
                                                        <select id="fail_timeout" name="fail_timeout" class="form-control select2" autocomplete="off">
                                                        <?php for ($x = 0; $x <=  120; $x = $x + 10) {
                                                                echo '<option value="'.$x.'" ' . ($x==$row['fail_timeout'] ? 'selected' : '') . '>'.$x.'</option>';
                                                        } ?>
                                                        </select>
                                                        <div class="help-block with-errors"></div>
                                                </div>

                                                <!-- Relay Group -->
                                                        <div class="form-group" class="control-label" id="group_id_label_1" style="display:none"><label id="group_id_label_2"><?php echo$lang['group'];?></label> <small class="text-muted"><?php echo $lang['relay_group_info'];?></small>
                                                        <select id="group_id" name="group_id" class="form-control select2" autocomplete="off">
                                                                <?php if(isset($row['relay_group_name'])) { echo '<option selected >'.$row['relay_group_name'].'</option>'; } ?>
                                                                <?php  $query = "SELECT `id`, `name` FROM `relay_group` ORDER BY `id` ASC;";
                                                                $results = $conn->query($query);
                                                                echo "<option></option>";
								if ($id == 0 || $results->num_rows == 0) {
									echo "<option selected value=0>NONE</option>";
								} else {
                                                                        echo "<option value=0>NONE</option>";
								}
                                                                while ($datarg=mysqli_fetch_array($results)) {
                                                                        echo "<option value=".$datarg['id'].">".$datarg['name']."</option>";
                                                                } ?>
                                                        </select>
                                                        <div class="help-block with-errors"></div>
                                                </div>
                                                <br>
						<!-- Buttons -->
						<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-sm">
						<a href="<?php echo $link; ?>"><button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color'); ?> btn-sm"><?php echo $lang['cancel']; ?></button></a>
					</form>
					<!-- /.form -->
				</div>
                        	<!-- /.card-body -->
				<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="text-start">
						<?php
                                                if ($id != 0) {
                                                        echo '<script type="text/javascript">',
                                                        'RelayTypeID('.$row['type'].');',
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
