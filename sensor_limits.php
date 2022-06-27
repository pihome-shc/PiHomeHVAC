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
//Form submit
if (isset($_POST['submit'])) {
        $status = isset($_POST['status']) ? $_POST['status'] : "0";
	$sensor_id = $_POST['selected_sensor_id'];
	$min = $_POST['min_val'];
        $max = $_POST['max_val'];
        $sync = '0';
        $purge= '0';

	//Add or Edit Sensor Limits record to sensors Table
	$query = "INSERT INTO `sensor_limits` (`id`, `sync`, `purge`, `sensor_id`, `min`, `max`, `status`) VALUES ('{$id}', '{$sync}', '{$purge}', '{$sensor_id}', '{$min}', '{$max}', '{$status}') ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), sensor_id=VALUES(sensor_id), min=VALUES(min), max=VALUES(max), status=VALUES(status);";
	$result = $conn->query($query);
        $temp_id = mysqli_insert_id($conn);
	if ($result) {
                if ($id==0){
                        $message_success = "<p>".$lang['sensor_limits_add_success']."</p>";
                } else {
                        $message_success = "<p>".$lang['sensor_limits_modify_succes']."</p>";
                }
	} else {
		$error = "<p>".$lang['sensor_limits_add_error']." </p> <p>" .mysqli_error($conn). "</p>";
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
        $query = "SELECT * FROM sensor_limits WHERE id = {$id} LIMIT 1;";
        $result = $conn->query($query);
        $rowlimits = mysqli_fetch_assoc($result);

        $query = "SELECT * FROM `sensors` WHERE `id` = ".$rowlimits['sensor_id']." limit 1;";
        $result = $conn->query($query);
        $row = mysqli_fetch_assoc($result);
}
?>

<!-- Title (e.g. Add or Edit Sensor Limits) -->
<div class="container-fluid">
	<br>
	<div class="row">
        	<div class="col-lg-12">
                        <div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
                                <div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
	                                <div class="d-flex justify-content-between">
        	                         	<div>
                	                        	<?php if ($id != 0) { echo $lang['edit_sensor_limits'] . ": " . $row['name']; }else{
							echo '<i class="bi bi-plus-square" style="font-size: 1.2rem;"></i>&nbsp&nbsp'.$lang['add_sensor_limits'];} ?>
                                	        </div>
                                		<div class="btn-group"><?php echo date("H:i"); ?></div>
                                	</div>
				</div>
                        	<!-- /.card-header -->
				<div class="card-body">
					<form data-bs-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
						<!-- Enabled -->
						<div class="form-check">
							<input class="form-check-input form-check-input-<?php echo explode('-', theme($conn, settings($conn, 'theme'), 'background_color'))[1]; ?>" id="checkbox0" class="styled" type="checkbox" name="status" value="1" <?php $check = ($rowlimits['status'] == 1) ? 'checked' : ''; echo $check; ?>>
							<label class="form-check-label" for="checkbox0"> <?php echo $lang['enabled']; ?> </label> <small class="text-muted"><?php echo $lang['sensor_limits_info'];?></small>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Sensor Name -->
						<div class="form-group" class="control-label" id="sensor_name_label" style="display:block"><label><?php echo $lang['sensor_name']; ?></label> <small class="text-muted"><?php echo $lang['sensor_limits_name_info'];?></small>
							<select id="sensor_name" onchange=set_sensor(this.options[this.selectedIndex].value) name="sensor_name" class="form-control select2" data-bs-error="<?php echo $lang['sensor_name_error']; ?>" autocomplete="off" required>
                                                                <?php 
								if(isset($row['name'])) { echo '<option selected >'.$row['name'].'</option>'; }
								$query = "SELECT id, name FROM sensors ORDER BY name ASC;";
								$result = $conn->query($query);
								echo "<option></option>";
								while ($datarw=mysqli_fetch_array($result)) {
									echo "<option value=".$datarw['id'].">".$datarw['name']."</option>";
								} ?>
							</select>
							<div class="help-block with-errors"></div>
						</div>

						<script language="javascript" type="text/javascript">
						function set_sensor(value) {
        						var valuetext = value;
							var e = document.getElementById("selected_sensor_id");
							e.value = valuetext;
						}
						</script>

						<input type="hidden" id="selected_sensor_id" name="selected_sensor_id" value="<?php echo $row['id']?>"/>

                                                <!-- Minimum Value -->
                                                <div class="form-group" class="control-label"><label><?php echo $lang['min_val']; ?></label> <small class="text-muted"><?php echo $lang['min_val_info'];?></small>
                                                        <input class="form-control" placeholder="Sensor Minimum Value" value="<?php if(isset($rowlimits['min'])) { echo $rowlimits['min']; } else { echo '0.00'; } ?>" id="min_val" name="min_val" data-bs-error="<?php echo $lang['min_val_help']; ?>" autocomplete="off" required>
                                                        <div class="help-block with-errors"></div>
                                                </div>

                                                <!-- Maximum Value -->
                                                <div class="form-group" class="control-label"><label><?php echo $lang['max_val']; ?></label> <small class="text-muted"><?php echo $lang['max_val_info'];?></small>
                                                        <input class="form-control" placeholder="Sensor Maximum Value" value="<?php if(isset($rowlimits['max'])) { echo $rowlimits['max']; } else { echo '0.00'; } ?>" id="max_val" name="max_val" data-bs-error="<?php echo $lang['max_val_help']; ?>" autocomplete="off" required>
                                                        <div class="help-block with-errors"></div>
                                                </div>

						<br>
						<!-- Buttons -->
						<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-sm">
						<a href="home.php"><button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color'); ?> btn-sm"><?php echo $lang['cancel']; ?></button></a>
					</form>
				</div>
                		<!-- /.card-body -->
                                <div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
					<?php
					ShowWeather($conn);
					?>
				</div>
				<!-- /.card-footer -->
			</div>
			<!-- /.panel card -->
		</div>
                <!-- /.col-lg-4 -->
	</div>
        <!-- /.row -->
</div>
<!-- /#container -->
<?php }  ?>
<?php include("footer.php");  ?>

