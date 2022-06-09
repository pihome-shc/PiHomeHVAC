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

$theme = settings($conn, 'theme');

if(isset($_GET['id'])) {
	$id = $_GET['id'];
} else {
	$id = 0;
}
//Form submit
if (isset($_POST['submit'])) {
        $holidays_enable = isset($_POST['holidays_enable']) ? $_POST['holidays_enable'] : "0";
        $start_date_time = $_POST['start_date_time'];
        $end_date_time = $_POST['end_date_time'];

		//Add or Edit Holiday record in Holidays Table
		$query = "INSERT INTO holidays(id, `sync`, `purge`, status, start_date_time, end_date_time) VALUES ('{$id}', '0', '0', '{$holidays_enable}', '{$start_date_time}','{$end_date_time}') ON DUPLICATE KEY UPDATE sync = VALUES(sync), status = VALUES(status), start_date_time = VALUES(start_date_time), end_date_time = VALUES(end_date_time);";
        $result = $conn->query($query);
        if ($result) {
			if ($id==0){
				$message_success = $lang['holidays_add_success'];
			}else{
				$message_success = $lang['holidays_modify_success'];
			}
			header("Refresh: 3; url=holidays.php");
        } else {
			if ($id==0){
				$error = $lang['holidays_add_error']."<p>".mysqli_error($conn)."</p>";
			}else{
				$error = $lang['holidays_modify_error']."<p>".mysqli_error($conn)."</p>";
			}
        }
 }
?>

<!-- ### Visible Page ### -->
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>

<!-- Don't display form after submit -->
<?php if (!(isset($_POST['submit']))) { ?>

<!-- If the request is to EDIT, retrieve selected items from DB   -->
<?php if ($id != 0) {
	$query = "SELECT * FROM holidays WHERE id = {$id}";
	$results = $conn->query($query);
	$holidays_row = mysqli_fetch_assoc($results);
}
?>
<!-- Title (e.g. Add or Edit Holiday) -->
<div class="container-fluid">
	<br>
	<div class="row">
		<div class="col-lg-12">
                        <div class="card <?php echo theme($conn, $theme, 'border_color'); ?>">
                                <div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> <?php echo theme($conn, $theme, 'background_color'); ?>">
                                        <div class="d-flex justify-content-between">
                                                <div>
                                                        <i class="bi bi-send-fill" style="font-size: 1.2rem;"></i>&nbsp&nbsp<?php echo $lang['holidays_add']; ?>
                                                </div>
                                                <div class="btn-group"><?php echo date("H:i"); ?></div>
                                        </div>
                                </div>
                                <!-- /.card-header -->

				<div class="card-body">

					<form data-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

						<!-- Enable Holiday -->
				                <div class="form-check">
                                			<input class="form-check-input form-check-input-<?php echo explode('-', theme($conn, settings($conn, 'theme'), 'background_color'))[1]; ?>" type="checkbox" value="1" id="checkbox0" name="holidays_enable" <?php $check = ($holidays_row['status'] == 1) ? 'checked' : ''; echo $check; ?>>
				                        <label class="form-check-label" for="checkbox0"><?php echo $lang['holidays_enable']; ?></label> </small>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Departure DateTime -->
						<div class="form-group input-append date form_datetime" class="control-label"><label><i class="bi bi-send-fill" style="font-size: 1.2rem; color: black"></i> <?php echo $lang['holidays_departure']; ?></label>
						<input class="form-control input-sm" type="text" id="start_date_time" name="start_date_time" value="<?php echo $holidays_row['start_date_time']; ?>" placeholder="Holiday Start Date & Time ">
						<span class="add-on"><i class="icon-th"></i></span>
						<div class="help-block with-errors"></div></div>

						<!-- Return DateTime -->
						<div class="form-group input-append date form_datetime" class="control-label"><label> <i class="bi bi-house-fill" style="font-size: 1.2rem; color: black"></i> <?php echo $lang['holidays_return']; ?></label>
						<input class="form-control input-sm" type="text" id="end_date_time" name="end_date_time" value="<?php echo $holidays_row['end_date_time']; ?>" placeholder="Holiday End Date & Time ">
						<span class="add-on"><i class="icon-th"></i></span>
						<div class="help-block with-errors"></div></div>

						<!-- Buttons -->
						<a href="holidays.php"><button type="button" class="btn <?php echo theme($conn, $theme, 'btn_primary'); ?> btn-sm" ><?php echo $lang['cancel']; ?></button></a>
						<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn <?php echo theme($conn, $theme, 'btn_style'); ?> btn-sm login">
					</form>
				</div>
				<!-- /.card-body -->
				<div class="card-footer <?php echo theme($conn, $theme, 'footer_color'); ?>">
					<?php 
					ShowWeather($conn);
					?>
				</div>
			</div>
		</div>
		<!-- /.col-lg-4 -->
	</div>
	<!-- /.row -->
</div>
<!-- /#card -->
<?php } ?>
<?php include("footer.php"); ?>
