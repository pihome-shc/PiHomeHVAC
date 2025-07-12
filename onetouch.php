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

if(settings($conn, 'language') == "sk" || settings($conn, 'language') == "de") { $button_style = "btn-xxl-wide"; } else { $button_style = "btn-xxl"; }
$page_refresh = page_refresh($conn);
$theme = settings($conn, 'theme');
?>
<div class="container-fluid ps-0 pe-0">
        <div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
                <div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
				<div class="Light"><i class="bi bi-house-fill"></i> <?php echo $lang['home']; ?></div>
				<div class="btn-group" id="onetouch_date"><?php echo date("H:i"); ?></div>
			</div>
		</div>
		<!-- /.card-header -->
		<div class="card-body">
        	        <div class="row <?php echo theme($conn, $theme, 'row_justification'); ?>">
				<?php
				//Mode 0 is EU Boiler Mode, Mode 1 is US HVAC Mode
				$system_controller_mode = settings($conn, 'mode') & 0b1;
				echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`home.php?page_name=homelist`)">
                                <h3 class="buttontop"><small>'.$lang['one_touch'].'</small></h3>
                                <h3 class="degre" style="margin-top:0px;"><i class="bi bi-bullseye" style="font-size: 2rem;"></i></h3>
                                <h3 class="status"></h3>
                                </button>';

		                //select addional onetouch buttons
                		$button_params = [];
		                $query = "SELECT * FROM button_page WHERE page = 2 ORDER BY index_id ASC";
                		$results = $conn->query($query);
		                if (mysqli_num_rows($results) > 0) {
                		        while ($row = mysqli_fetch_assoc($results)) {
                                		$var = $row['function'];
		                                $var($conn, $lang[$var]);
                		                if ($row['page'] == 2) { $button_params[] = array('button_id' =>$row['id'], 'button_name' =>$row['name'], 'button_function' =>$row['function']); }
		                        }
                		        $js_button_params = json_encode($button_params);
		                }

                		if($_SESSION['admin'] == 1) {
					echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`theme.php`)">
                                        <h3 class="buttontop"><small>'.$lang['add_theme'].'</small></h3>
                                        <h3 class="degre" style="margin-top: 10px;"><i class="bi bi-plus-square-fill icon-2x orange"></i></h3>
                                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="bi icon-fw"></i></small>
                                        </h3></button>';

					echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`relay.php`)">
		                        <h3 class="buttontop"><small>'.$lang['relay_add'].'</small></h3>
                		        <h3 class="degre" style="margin-top: 10px;"><i class="bi bi-plus-square-fill red icon-2x"></i></h3>
		                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="bi icon-fw"></i></small>
                		        </h3></button>';

					echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`sensor.php`)">
		                        <h3 class="buttontop"><small>'.$lang['sensor_add'].'</small></h3>
                		        <h3 class="degre"  style="margin-top: 10px;"><i class="bi bi-plus-square-fill green icon-2x"></i></h3>
		                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="bi icon-fw"></i></small>
                		        </h3></button>';

					echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`mqtt_device.php`)">
		                        <h3 class="buttontop"><small>'.$lang['mqtt_add'].'</small></h3>
                		        <h3 class="degre" style="margin-top: 10px;"><i class="bi bi-plus-square-fill blue icon-2x"></i></h3>
		                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="bi icon-fw"></i></small>
                		        </h3></button>';

					echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow black-background '.$button_style.' mainbtn animated fadeIn" onclick="relocate_page(`zone.php`)">
		                        <h3 class="buttontop"><small>'.$lang['zone_add'].'</small></h3>
                		        <h3 class="degre" style="margin-top: 10px;"><i class="bi bi-plus-square-fill icon-2x"></i></h3>
		                        <h3 class="status"><small class="statuscircle" style="color:#048afd;"><i class="bi icon-fw"></i></small>
                		        </h3></button>';
                		}

			echo '</div>
			<!-- /.row -->
		</div>'; ?>
		<!-- /.card-body -->
                <div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
                        	<div class="btn-group" id="footer_weather">
                                	<?php ShowWeather($conn); ?>
                        	</div>

				<div class="btn-group" id="footer_running_time">
					<?php
                                        $query = "SELECT * FROM system_controller LIMIT 1";
                                        $result = $conn->query($query);
                                        $row = mysqli_fetch_array($result);
                                        $sc_count=$result->num_rows;
                                        $system_controller_id = $row['id'];

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
                                        if($system_controller_time_on >0){      echo ' <i class="bi bi-clock"></i>&nbsp'.secondsToWords(($system_controller_time_on)*60);}
					?>
	                        </div>
        	        </div>
		</div>
		<!-- /.card-footer -->
	</div>
	<!-- /.card -->
</div>
<!-- /.card-primary -->
<?php if(isset($conn)) { $conn->close();} ?>

<script language="javascript" type="text/javascript">
// Knob function for live temperature model
$(function() {
   $(".dial").knob({
       'min':0,
       'max':document.getElementById("max_c").value,
       "fgColor":"#000000",
       "skin":"tron",
       'step':0.5
   });
});

// update page data every x seconds
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';
  var live_temp_zone_id = document.getElementById("zone_id").value;

  (function loop() {
    //load() method fetch data from fetch.php page
    var data2 = '<?php echo $js_button_params ?>';
    if (data2.length > 0) {
            var obj2 = JSON.parse(data2)
//            console.log(obj2);

            for (var y = 0; y < obj2.length; y++) {
	      if (obj2[y].button_function == "live_temp") {
    		$('#load_temp').load("ajax_fetch_data.php?id=" + live_temp_zone_id + "&type=38").fadeIn("slow");
	      }
              $('#bs1_' + obj2[y].button_id).load("ajax_fetch_data.php?id=" + obj2[y].button_id + "&type=11").fadeIn("slow");
              $('#bs2_' + obj2[y].button_id).load("ajax_fetch_data.php?id=" + obj2[y].button_id + "&type=12").fadeIn("slow");
//	      console.log(obj2[y].button_name);
            }
    }

    $('#onetouch_date').load("ajax_fetch_data.php?id=0&type=13").fadeIn("slow");
    $('#footer_weather').load("ajax_fetch_data.php?id=0&type=14").fadeIn("slow");
    $('#footer_running_time').load("ajax_fetch_data.php?id=0&type=15").fadeIn("slow");
    setTimeout(loop, delay);
  })();
});
</script>

