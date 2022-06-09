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
include("header.php");

if(isset($message_success)) { echo '<div class="alert alert-success alert-dismissable"> <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>'. $message_success .'</div>';}
if(isset($error)) { echo '<div class="alert alert-danger alert-dismissable"> <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button>'.$error.'</div>';}
if(isset($info_message)) { echo '<div class="alert alert-info alert-dismissable"> <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">&times;</button><span class="bi bi-info-circle-fill" data-notify="icon"></span>'.$info_message.'</div>';}

$theme = settings($conn, 'theme');
?>
<div class="container-fluid">
	<br>
	<div class="row">
        	<div class="col-lg-12">
                    	<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
                        	<div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="d-flex justify-content-between">
						<div>
		                            		<i class="bi bi-gear-fill"></i>   <?php echo $lang['weather_outside']; ?> <?php echo $weather['c'] ;?>&deg;
						</div>
						<div class="btn-group"><?php echo date("H:i"); ?></div>
					</div>
                        	</div>
                        	<!-- /.card-heading -->
                        	<div class="card-body">
					<?php
					echo '<div class="list-group">';
						$weather_api = file_get_contents('weather_5days.json');
						$weather_data = json_decode($weather_api, true);
						//echo '<pre>' . print_r($weather_data, true) . '</pre>';
						foreach($weather_data['list'] as $day => $value) {
							//date('H:i', $weather['sunrise'])

							//echo date("D H:i", strtotime($value['dt_txt']));
        						echo '<a href="#" class="d-flex justify-content-between list-group-item list-group-item-action">
							<span>'
							.date("D H:i", strtotime($value['dt_txt'])).
							'<img border="0" width="28" height="28" src="images/'.$value['weather'][0]['icon'].'.png">'
							.$value['weather'][0]['main']." - " .$value['weather'][0]['description'].
							'</span>
							<span class="text-muted small"><em>'

							.round($value['main']['temp_min'],0)."&deg; - ".round($value['main']['temp_max'],0).

							'&deg;</em></span></a>';
						}
						?>
					</div>
                        	</div>
                        	<!-- /.card-body -->
				<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
                            		<?php echo $lang['schedule_next']; ?>:
                        	</div>
                    	</div>
		</div>
                <!-- /.col-lg-4 -->
	</div>
        <!-- /.row -->
</div>
<!-- /#container -->
<?php include("footer.php");  ?>
