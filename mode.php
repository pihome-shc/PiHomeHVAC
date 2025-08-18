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

?>
<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
        <div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
        	<div class="d-flex justify-content-between">
                	<div class="Light"><i class="bi bi-house-fill"></i> <?php echo $lang['home']; ?></div>
                        <div class="btn-group" id="mode_date"><?php echo date("H:i"); ?></div>
        	</div>
	</div>
	<!-- /.card-header -->
	<div class="card-body">
		<?php
		//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
		$system_controller_mode = settings($conn, 'mode') & 0b1;
                //GET BOILER DATA AND FAIL ZONES IF SYSTEM CONTROLLER COMMS TIMEOUT
                //query to get last system_controller operation time and hysteresis time
                $query = "SELECT * FROM system_controller LIMIT 1";
                $result = $conn->query($query);
                $row = mysqli_fetch_array($result);
                $sc_mode  = $row['sc_mode'];
		$system_controller_id = $row['id'];

                $legend = array();
                if ($system_controller_mode == 0) {
                        $legend[0] = $lang['mode_off'];
                        $legend[1] = $lang['mode_timer'];
                        $legend[2] = $lang['mode_ce'];
                        $legend[3] = $lang['mode_hw'];
                        $legend[4] = $lang['mode_both'];
                } else {
                        $legend[0] = $lang['mode_off'];
                        $legend[1] = $lang['mode_timer'];
                        $legend[2] = $lang['mode_timer'];
                        $legend[3] = $lang['mode_timer'];
                        $legend[4] = $lang['mode_auto'];
                        $legend[5] = $lang['mode_fan'];
                        $legend[6] = $lang['mode_heat'];
                        $legend[7] = $lang['mode_cool'];
                }

		$color = array();
                if ($system_controller_mode == 1) {
                        switch ($sc_mode) {
                                case 0:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
					$color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square-fill";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                                        break;
                                case 1:
                                        $color[0] = "";
                                        $color[1] = "green";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square-fill";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                                        break;
                                case 2:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "green";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square-fill";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                                        break;
                                case 3:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "green";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square-fill";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                                        break;
                                case 4:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "green";
                                        $color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square-fill";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                                        break;
                                case 5:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "green";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square-fill";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                                        break;
                                case 6:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $color[6] = "green";
                                        $color[7] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square-fill";
                                        $button[7] = "bi-square";
                                        break;
                                case 7:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "green";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square-fill";
                                        break;
                                default:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $color[6] = "";
                                        $color[7] = "";
                                        $button[0] = "bi-square-fill";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        $button[5] = "bi-square";
                                        $button[6] = "bi-square";
                                        $button[7] = "bi-square";
                        }
                } else {
                        switch ($sc_mode) {
                                case 0:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "bi-square-fill";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        break;
                                case 1:
                                        $color[0] = "";
                                        $color[1] = "green";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square-fill";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        break;
                                case 2:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "green";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square-fill";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                                        break;
                                case 3:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "green";
                                        $color[4] = "";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square-fill";
                                        $button[4] = "bi-square";
                                        break;
                                case 4:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "green";
                                        $button[0] = "bi-square";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square-fill";
                                        break;
                                default:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "bi-square-fill";
                                        $button[1] = "bi-square";
                                        $button[2] = "bi-square";
                                        $button[3] = "bi-square";
                                        $button[4] = "bi-square";
                        }
                }

                if($_SESSION['access'] != 2) {
			for ($x = 0; $x <=  4 + ($system_controller_mode * 3); $x++) { ?>
	                        <a style="color: #777; cursor: pointer; text-decoration: none;" href="javascript:set_sc_mode(<?php echo $x; ?>)">
        	                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-circle btn-xxl mainbtn">
                	        <h3 class="buttontop"><small><?php echo $legend[$x]; ?></small></h3>
                        	<h3 class="buttonmode" ><i class="bi <?php echo $button[$x]; ?> icon-2x <?php echo $color[$x]; ?>"></i></h3>
	                        <?php if ($system_controller_mode == 1) {
        	                        switch ($x) {
                	                        case 1: ?>
                                                        <h3 <small class="status"><small class="statuszoon" style="margin-top:-10px"><?php echo $lang['mode_heat']; ?></small></h3>
                                                        <?php break;
                                                case 2: ?>
                                                        <h3 <small class="status"><small class="statuszoon" style="margin-top:-10px"><?php echo $lang['mode_cool']; ?></small></h3>
                                                        <?php break;
                                                case 3: ?>
                                                        <h3 <small class="status"><small class="statuszoon" style="margin-top:-10px"><?php echo $lang['mode_auto']; ?></small></h3>
                                                        <?php break;
                                                default: ?>
                                                        <h3 class="statusdegree"><small>&nbsp</small></h3>
                                        <?php }
                                } else { ?>
                                        <h3 class="statusdegree"><small>&nbsp</small></h3>
                	        <?php } ?>
                        	</button></a>
                <?php }
		} ?>

	</div>
	<!-- /.card-body -->
	<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
        	<div class="d-flex justify-content-between">
                	<div class="btn-group" id="footer_weather">
                        	<?php
                                ShowWeather($conn);
                        ?>
                        </div>

                        <div class="btn-group" id="footer_all_running_time">
                        	<?php
                                $query="select date(start_datetime) as date,
                                sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
                                sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts,
                                (sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
                                from controller_zone_logs WHERE date(start_datetime) = CURDATE() AND zone_id = ".$system_controller_id." GROUP BY date(start_datetime) asc";
                                $result = $conn->query($query);
                                $system_controller_time = mysqli_fetch_array($result);
                                $system_controller_time_total = $system_controller_time['total_minuts'];
                                $system_controller_time_on = $system_controller_time['on_minuts'];
                                $system_controller_time_save = $system_controller_time['save_minuts'];
                                if($system_controller_time_on >0){ echo ' <i class="bi bi-clock"></i> '.secondsToWords(($system_controller_time_on)*60);}
                                ?>
                        </div>
        	</div>
	</div>
	<!-- /.card-footer -->
</div>
<!-- /.card -->
<?php if(isset($conn)) { $conn->close();} ?>

<script>

// update page data every x seconds
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';

  (function loop() {
    var data = '<?php echo $js_sch_params ?>';

    $('#mode_date').load("ajax_fetch_data.php?id=0&type=13").fadeIn("slow");
    $('#footer_weather').load("ajax_fetch_data.php?id=0&type=14").fadeIn("slow");
    $('#footer_all_running_time').load("ajax_fetch_data.php?id=0&type=17").fadeIn("slow");
    setTimeout(loop, delay);
  })();
});
</script>

