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
?>
<div class="panel panel-primary">
	<div class="panel-heading">
		<div class="Light"><i class="fa fa-home fa-fw"></i> <?php echo $lang['home']; ?>
			<div class="pull-right">
				<div class="btn-group"><?php echo date("H:i"); ?>
				</div>
			</div>
		</div>
	</div>
	<!-- /.panel-heading -->
	<div class="panel-body">
		<?php
		//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
		$system_controller_mode = settings($conn, 'mode') & 0b1;
                //GET BOILER DATA AND FAIL ZONES IF SYSTEM CONTROLLER COMMS TIMEOUT
                //query to get last system_controller operation time and hysteresis time
                $query = "SELECT * FROM system_controller LIMIT 1";
                $result = $conn->query($query);
                $row = mysqli_fetch_array($result);
                $sc_mode  = $row['sc_mode'];

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
                                        $button[0] = "fa-circle";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle";
                                        $button[7] = "fa-circle-o";
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
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle";
                                        break;
                                default:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $color[5] = "";
                                        $button[0] = "fa-circle";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        $button[5] = "fa-circle-o";
                                        $button[6] = "fa-circle-o";
                                        $button[7] = "fa-circle-o";
                        }
                } else {
                        switch ($sc_mode) {
                                case 0:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "fa-circle";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        break;
                                case 1:
                                        $color[0] = "";
                                        $color[1] = "green";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        break;
                                case 2:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "green";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                                        break;
                                case 3:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "green";
                                        $color[4] = "";
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle";
                                        $button[4] = "fa-circle-o";
                                        break;
                                case 4:
                                        $color[0] = "";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "green";
                                        $button[0] = "fa-circle-o";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle";
                                        break;
                                default:
                                        $color[0] = "green";
                                        $color[1] = "";
                                        $color[2] = "";
                                        $color[3] = "";
                                        $color[4] = "";
                                        $button[0] = "fa-circle";
                                        $button[1] = "fa-circle-o";
                                        $button[2] = "fa-circle-o";
                                        $button[3] = "fa-circle-o";
                                        $button[4] = "fa-circle-o";
                        }
                }

                if($_SESSION['admin'] == 1) {
			for ($x = 0; $x <=  4 + ($system_controller_mode * 3); $x++) { ?>
	                        <a style="color: #777; cursor: pointer; text-decoration: none;" href="javascript:set_sc_mode(<?php echo $x; ?>)">
        	                <button type="button" class="btn btn-default btn-circle btn-xxl mainbtn">
                	        <h3 class="buttontop"><small><?php echo $legend[$x]; ?></small></h3>
                        	<h3 class="degre" ><i class="fa <?php echo $button[$x]; ?> fa-1x <?php echo $color[$x]; ?>"></i></h3>
	                        <?php if ($system_controller_mode == 1) {
        	                        switch ($x) {
                	                        case 1: ?>
                        	                        <h4 class="degre2"><?php echo $lang['mode_heat'] ?></h4>
                                	                <?php break;
                                        	case 2: ?>
                                                	<h4 class="degre2"><?php echo $lang['mode_cool'] ?></h4>
	                                                <?php break;
        	                                case 3: ?>
                	                                <h4 class="degre2"><?php echo $lang['mode_auto'] ?></h4>
                        	                        <?php break;
                                	        default: ?>
                                        	        <h4 class="degre2">&nbsp</h4>
                                	<?php }
	                        } else { ?>
        	                        <h4 class="degre2">&nbsp</h4>
                	        <?php } ?>
                        	</button></a>
                <?php }
		} ?>

	</div>
	<!-- /.panel-body -->
	<div class="panel-footer">
		<?php
		ShowWeather($conn);
		?>

		<div class="pull-right">
			<div class="btn-group">
				<?php
				$query="select date(start_datetime) as date,
				sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) as total_minuts,
				sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime)) as on_minuts,
				(sum(TIMESTAMPDIFF(MINUTE, start_datetime, expected_end_date_time)) - sum(TIMESTAMPDIFF(MINUTE, start_datetime, stop_datetime))) as save_minuts
				from system_controller_logs WHERE date(start_datetime) = CURDATE() GROUP BY date(start_datetime) asc";
				$result = $conn->query($query);
				$system_controller_time = mysqli_fetch_array($result);
				$system_controller_time_total = $system_controller_time['total_minuts'];
				$system_controller_time_on = $system_controller_time['on_minuts'];
				$system_controller_time_save = $system_controller_time['save_minuts'];
				if($system_controller_time_on >0){	echo ' <i class="ionicons ion-ios-clock-outline"></i> '.secondsToWords(($system_controller_time_on)*60);}
				?>
                        </div>
                 </div>
	</div>
	<!-- /.panel-footer -->
</div>
<!-- /.panel-primary -->
<?php if(isset($conn)) { $conn->close();} ?>

