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

if(isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 0;
}
//Form submit
if (isset($_POST['submit'])) {
	$name = $_POST['name'];
        $type = $_POST['type_id'];
	$selected_relay_id = $_POST['selected_relay_id'];
        $selected_child_id = $_POST['selected_child_id'];
        $query = "SELECT id, type FROM nodes WHERE node_id = '".$selected_relay_id."' LIMIT 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $controler_id = $row['id'];
        $controller_type = $row['type'];
	$controler_child_id = $_POST['relay_child_id'];
        $sync = '0';
        $purge= '0';

        //Add or Edit relay record to relays Table
        $query = "INSERT INTO `relays` (`id`, `sync`, `purge`, `controler_id`, `controler_child_id`, `name`, `type`) VALUES ('{$id}', '{$sync}', '{$purge}', '{$controler_id}', '{$controler_child_id}', '{$name}', '{$type}') ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), controler_id='{$controler_id}', controler_child_id='{$controler_child_id}', name=VALUES(name), type=VALUES(type);";
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

        //delete existing messages_out Record
        if ($id!=0){
                $query = "DELETE FROM messages_out WHERE node_id = '{$selected_relay_id}' AND child_id = '{$selected_child_id}';";
                $conn->query($query);
        }
        //add to messages_out queue
        if(strpos($controller_type, 'Tasmota') !== false) {
                $query = "SELECT * FROM http_messages WHERE node_id = '{$selected_relay_id}' AND message_type = 0 LIMIT 1;";
                $result = $conn->query($query);
                $found_product = mysqli_fetch_array($result);
                $payload = $found_product['command']." ".$found_product['parameter'];
        } else {
                $payload = 0;
        }

        $query = "INSERT INTO `messages_out` (`sync`, `purge`, `node_id`, `child_id`, `sub_type`, `ack`, `type`, `payload`, `sent`, `datetime`, `zone_id`) VALUES ('0', '0', '{$selected_relay_id}','{$controler_child_id}', '1', '1', '2', '{$payload}', '0', '{$date_time}', '0');";
        $result = $conn->query($query);
        if ($result) {
                if ($id==0){
                        $message_success .= "<p>".$lang['messages_out_add_success']."</p>";
                } else {
                        $message_success .= "<p>".$lang['messages_out_update_success']."</p>";
                }
        } else {
                $error .= "<p>".$lang['messages_out_fail']."</p> <p>" .mysqli_error($conn). "</p>";
        }

	$message_success .= "<p>".$lang['do_not_refresh']."</p>";
	header("Refresh: 10; url=home.php");
	// After update on all required tables, set $id to mysqli_insert_id.
	if ($id==0){$id=$temp_id;}
}
?>
<!-- ### Visible Page ### -->
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>

<!-- Don't display form after submit -->
<?php if (!(isset($_POST['submit']))) { ?>

<!-- If the request is to EDIT, retrieve selected items from DB   -->
<?php if ($id != 0) {
        $query = "SELECT * FROM `relays` WHERE `id` = {$id} limit 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);

	$query = "SELECT * FROM nodes WHERE id = '{$row['controler_id']}' LIMIT 1;";
	$result = $conn->query($query);
	$rownode = mysqli_fetch_assoc($result);
}
?>

<!-- Title (e.g. Add Zone or Edit Zone) -->
<div id="page-wrapper">
<br>
            <div class="row">
                <div class="col-lg-12">
                   <div class="panel panel-primary">
                        <div class="panel-heading">
							<?php if ($id != 0) { echo $lang['relay_edit'] . ": " . $row['name']; }else{
                            echo "<i class=\"fa fa-plus fa-1x\"></i>" ." ". $lang['relay_add'];} ?>
						<div class="pull-right"> <div class="btn-group"><?php echo date("H:i"); ?></div> </div>
                        </div>
                        <!-- /.panel-heading -->
<div class="panel-body">

<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

<!-- Controller Type -->
<div class="form-group" class="control-label"><label><?php echo $lang['controller_type']; ?></label> <small class="text-muted"><?php echo $lang['controller_type_info'];?></small>
<select class="form-control input-sm" type="text" id="type" name="type" onchange=RelayTypeID(this.options[this.selectedIndex].value)>
	<?php if(isset($row['type'])) { 
		switch ($row['type']) {
			case 0:
		        	echo '<option selected >Zone</option>';
		        	break;
    			case 1:
        			echo '<option selected >Boiler</option>';
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
        <option value=2>HVAC - Heat</option>
        <option value=3>HVAC - Chill</option>
        <option value=4>HVAC - Fan</option>
</select>
<div class="help-block with-errors"></div></div>
<input type="hidden" id="type_id" name="type_id" value="<?php if(isset($row['type'])) { echo $row['type']; } else { echo '0'; }?>"/>

<script language="javascript" type="text/javascript">
function RelayTypeID(value)
{
        var valuetext = value;
        var e = document.getElementById("type");
        var selected_type_id = e.options[e.selectedIndex].value;

        document.getElementById("type_id").value = selected_type_id;
}
</script>

<!-- Relay Name -->
<div class="form-group" class="control-label"><label><?php echo $lang['relay_name']; ?></label> <small class="text-muted"><?php echo $lang['relay_name_info'];?></small>
<input class="form-control" placeholder="Relay Name" value="<?php if(isset($row['name'])) { echo $row['name']; } ?>" id="name" name="name" data-error="<?php echo $lang['relay_name_help']; ?>" autocomplete="off" required>
<div class="help-block with-errors"></div></div>

<!-- Relay ID -->
<div class="form-group" class="control-label" id="relay_id_label" style="display:block"><label><?php echo $lang['relay_id']; ?></label> <small class="text-muted"><?php echo $lang['relay_id_info'];?></small>
<select id="relay_id" onchange=RelayChildList(this.options[this.selectedIndex].value) name="relay_id" class="form-control select2" data-error="<?php echo $lang['relay_id_error']; ?>" autocomplete="off" required>
<?php if(isset($rownode['node_id'])) {
        echo '<option selected >'.$rownode['node_id']." - ".$rownode['name'].'</option>';
        $query = "SELECT id, node_id, name, max_child_id FROM nodes WHERE name LIKE '%Controller%' AND id <> ".$rownode['id']." ORDER BY node_id ASC;";
} else {
        $query = "SELECT id, node_id, name, max_child_id FROM nodes WHERE name LIKE '%Controller%' ORDER BY node_id ASC;";
}
$result = $conn->query($query);
echo "<option></option>";
while ($datarw=mysqli_fetch_array($result)) {
        if(strpos($datarw['name'], 'Add-On') !== false) { $max_child_id = 0; } else { $max_child_id = $datarw['max_child_id']; }
        echo "<option value=".$datarw['max_child_id'].">".$datarw['node_id']." - ".$datarw['name']."</option>"; } ?>
</select>
<div class="help-block with-errors"></div></div>

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
	        for(j=0;j<=valuetext;j++)
        	{
                	var optn = document.createElement("OPTION");
	                optn.text = j;
        	        optn.value = j;
                	document.getElementById("relay_child_id").options.add(optn);
        	}
	}
}
</script>
<input type="hidden" id="selected_relay_id" name="selected_relay_id" value="<?php echo $rownode['node_id']?>"/>
<input type="hidden" id="selected_child_id" name="selected_child_id" value="<?php echo $row['controler_child_id']?>"/>

<!-- Relay Child ID -->
<input type="hidden" id="gpio_pin_list" name="gpio_pin_list" value="<?php echo implode(",", array_filter(Get_GPIO_List()))?>"/>
<div class="form-group" class="control-label"><label><?php echo $lang['relay_child_id']; ?></label> <small class="text-muted"><?php echo $lang['relay_child_id_info'];?></small>
        <select id="relay_child_id" name="relay_child_id" class="form-control select2" data-error="<?php echo $lang['relay_child_id_error']; ?>" autocomplete="off" required>
                <?php if(isset($row['controler_child_id'])) {
                        echo '<option selected >'.$row['controler_child_id'].'</option>';
                        $pos=strpos($rownode["type"], "GPIO");
                        if($pos !== false) {
                                $gpio_list=Get_GPIO_List();
                                for ($x = 0; $x <= count(array_filter($gpio_list)) - 1; $x++) {
                                        echo "<option value=".$gpio_list[$x].">".$gpio_list[$x]."</option>";
                                }
                        } else {
                                for ($x = 1; $x <= $rownode['max_child_id']; $x++) {
                                        echo "<option value=".$x.">".$x."</option>";
                                }
                        }
                } ?>
        </select>
<div class="help-block with-errors"></div>
</div>

<!-- Buttons -->
<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-default btn-sm">
<a href="home.php"><button type="button" class="btn btn-primary btn-sm"><?php echo $lang['cancel']; ?></button></a>
</form>
                        </div>
                        <!-- /.panel-body -->
						<div class="panel-footer">
<?php
ShowWeather($conn);
?>
                            <div class="pull-right">
                                <div class="btn-group">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /#page-wrapper -->
<?php }  ?>
<?php include("footer.php");  ?>

