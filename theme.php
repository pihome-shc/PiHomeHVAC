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
	$name = $_POST['name'];
        $justification = $_POST['justification'];
	$theme_color = $_POST['theme_color'];
        $text_color = $_POST['text_color'];
        $tile_size = $_POST['tile_size'];
        $sync = '0';
        $purge= '0';

	//Add or Edit
	$query = "INSERT INTO `theme` (`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`) VALUES ('{$id}', '{$sync}', '{$purge}', '{$name}', '{$justification}', '{$theme_color}', '{$text_color}', '{$tile_size}') ON DUPLICATE KEY UPDATE sync=VALUES(sync), `purge`=VALUES(`purge`), name=VALUES(name), row_justification='{$justification}', color='{$theme_color}', text_color='{$text_color}', tile_size='{$tile_size}';";
	$result = $conn->query($query);
        $temp_id = mysqli_insert_id($conn);
	if ($result) {
                if ($id==0){
                        $message_success = "<p>".$lang['theme_record_add_success']."</p>";
                } else {
                        $message_success = "<p>".$lang['theme_record_update_success']."</p>";
                }
	} else {
		$error = "<p>".$lang['theme_record_fail']." </p> <p>" .mysqli_error($conn). "</p>";
	}
	$message_success .= "<p>".$lang['do_not_refresh']."</p>";
	header("Refresh: 10; url=home.php?page_name=onetouch");
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
        $query = "SELECT * FROM `theme` WHERE `id` = {$id} limit 1;";
	$result = $conn->query($query);
	$row = mysqli_fetch_assoc($result);
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
							<?php if ($id != 0) { echo $lang['edit_theme'] . ": " . $row['name']; }else{
                		            		echo '<i class="bi bi-plus-square" style="font-size: 1.2rem;"></i>&nbsp&nbsp'.$lang['add_theme'];} ?>
						</div>
						<div class="btn-group"><?php echo date("H:i"); ?></div>
					</div>
                        	</div>
                        	<!-- /.card-header -->
				<div class="card-body">
					<form data-bs-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">
						<!-- Theme Name -->
						<div class="form-group" class="control-label"><label class="fs-6"><?php echo $lang['theme_name']; ?></label> <small class="text-muted"><?php echo $lang['theme_name_info'];?></small>
							<input class="form-control" placeholder="Theme Name" value="<?php if(isset($row['name'])) { echo $row['name']; } ?>" id="name" name="name" data-bs-error="<?php echo $lang['theme_name_help']; ?>" autocomplete="off" required>
							<div class="help-block with-errors"></div>
						</div>

						<!-- Justification -->
						<div class="form-group" class="control-label" id="justify_label" style="display:block"><label class="fs-6"><?php echo $lang['justify']; ?></label> <small class="text-muted"><?php echo $lang['justify_info'];?></small>
        						<select class="form-select" type="text" id="justification" name="justification" >
								<?php echo'<option value="left" ' . ($row['row_justification']=="left" ? 'selected' : '') . '>'.$lang['left'].'</option>'; ?>
                						<?php echo'<option value="center" ' . ($row['row_justification']=="center" ? 'selected' : '') . '>'.$lang['center'].'</option>'; ?>
                                                                <?php echo'<option value="right" ' . ($row['row_justification']=="right" ? 'selected' : '') . '>'.$lang['right'].'</option>'; ?>
							</select>
							<div class="help-block with-errors"></div>
						</div>

                                                <!-- Theme Color -->
                                                <div class="form-group" class="control-label" id="theme_color_label" style="display:block"><label class="fs-6"><?php echo $lang['theme_color']; ?></label> <small class="text-muted"><?php echo $lang['theme_color_info'];?></small>
                                                        <select class="form-select" type="text" id="theme_color" name="theme_color" >
						    		<?php echo'<option value="red" ' . ($row['color']=="red" ? 'selected' : '') . '>'.$lang['red'].'</option>'; ?>
                                                                <?php echo'<option value="orange" ' . ($row['color']=="orange" ? 'selected' : '') . '>'.$lang['orange'].'</option>'; ?>
                                                                <?php echo'<option value="orange-red" ' . ($row['color']=="orange-red" ? 'selected' : '') . '>'.$lang['orange_red'].'</option>'; ?>
		                                                <?php echo'<option value="amber" ' . ($row['color']=="amber" ? 'selected' : '') . '>'.$lang['amber'].'</option>'; ?>
                               					<?php echo'<option value="blue" ' . ($row['color']=="blue" ? 'selected' : '') . '>'.$lang['blue'].'</option>'; ?>
		                                                <?php echo'<option value="black" ' . ($row['color']=="black" ? 'selected' : '') . '>'.$lang['black'].'</option>'; ?>
                                                                <?php echo'<option value="violet" ' . ($row['color']=="violet" ? 'selected' : '') . '>'.$lang['violet'].'</option>'; ?>
                                                        </select>
                                                        <div class="help-block with-errors"></div>
                                                </div>

                                                <!-- Text Color -->
                                                <div class="form-group" class="control-label" id="text_color_label" style="display:block"><label class="fs-6"><?php echo $lang['text_color']; ?></label> <small class="text-muted"><?php echo $lang['text_color_info'];?></small>
                                                        <select class="form-select" type="text" id="text_color" name="text_color" >
								<?php echo'<option value="text-white" ' . ($row['text_color']=="text-white" ? 'selected' : '') . '>'.$lang['white'].'</option>'; ?>
								<?php echo'<option value="text-black" ' . ($row['text_color']=="text-black" ? 'selected' : '') . '>'.$lang['black'].'</option>'; ?>
                                                        </select>
                                                        <div class="help-block with-errors"></div>
                                                </div>

                                                <!-- Button Size -->
                                                <div class="form-group" class="control-label" id="tile_size_label" style="display:block"><label class="fs-6"><?php echo $lang['button_size']; ?></label> <small class="text-muted"><?php echo $lang['button_size_info'];?></small>
                                                        <select class="form-select" type="text" id="tile_size" name="tile_size" >
								<?php echo'<option value=0 ' . ($row['tile_size']==0 ? 'selected' : '') . '>'.$lang['standard_button'].'</option>'; ?>
								<?php echo'<option value=1 ' . ($row['tile_size']==1 ? 'selected' : '') . '>'.$lang['wide_button'].'</option>'; ?>
                                                        </select>
                                                        <div class="help-block with-errors"></div>
                                                </div>

						<!-- Buttons -->
						<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-sm">
						<a href="home.php"><button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color'); ?> btn-sm"><?php echo $lang['cancel']; ?></button></a>
					</form>
					<!-- /.form -->
				</div>
                        	<!-- /.card-body -->
				<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="text-start">
						<?php
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

