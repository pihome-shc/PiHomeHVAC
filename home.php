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
if(isset($_GET['page_name'])) {
        $page_name = $_GET['page_name'];
} else {
        $page_name = "homelist";
}

include("header.php");

echo '<div class="container-fluid">
	<br>
	<input type="hidden" id="page_link" value="'.$page_name.'">
        <div class="row">
		<div class="col-xl-12">
                	<div id="'.$page_name.'" >
                                <div class="d-flex justify-content-center" style="margin-top:10px">'.$lang["please_wait_text"].'</div>
                                <div class="d-flex justify-content-center" style="margin-top:10px">
                                        <div class="spinner-border text-'.theme($conn, settings($conn, "theme"), "color").'"
                                                role="status">
                                        </div>
                                </div>
			</div>
                </div>
                <!-- /.col-lg-4 -->
        </div>
	<!-- /.row -->
        <div class="d-flex justify-content-center" style="margin-top:20px">'.settings($conn, "name").' '.settings($conn, "version")."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;".$lang["build"]." ".settings($conn, "build").'</div>
        <div class="d-flex justify-content-center" style="margin-top:10px">&copy;&nbsp;'.$lang["copyright"].'</div>
</div>
<!--  -->
';

// live temperature modal
$query = "SELECT zone_id, active FROM livetemp LIMIT 1";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount > 0) {
	$row = mysqli_fetch_array($result);
	$livetemp_zone_id = $row['zone_id'];
        $livetemp_active = $row['active'];
	if ($livetemp_active == 0) { $check_visible = 'display:none'; } else { $check_visible = 'display:block'; }
        $query = "SELECT mode, temp_reading, temp_target FROM zone_current_state WHERE zone_id = ".$livetemp_zone_id." LIMIT 1";
	$result = $conn->query($query);
	$row = mysqli_fetch_array($result);
	$zone_mode = $row['mode'];
        $zone_mode_main=floor($zone_mode/10)*10;
	$query = "SELECT name, min_c, max_c, default_c FROM zone_view WHERE id =  ".$livetemp_zone_id." LIMIT 1";
        $zresult = $conn->query($query);
	$zrow = mysqli_fetch_array($zresult);
        if ($zone_mode_main == 140) {
        	$set_temp = $zrow['default_c'];
	} else {
                $set_temp = $row['temp_target'];
	}
        switch ($zone_mode_main) {
        	case 0:
			$current_mode = "";
                	break;
	        case 50:
			$current_mode = "Night Climate";
            		break;
                case 60:
		        $current_mode = "Boost";
	        	break;
        	case 70:
		        $current_mode = "Override";
                	break;
	        case 80:
		       	$current_mode = "Schedule";
              		break;
                case 140:
			$current_mode = "Manual";
        		break;
             	default:
			$current_mode = "";
	}
	echo '<input type="hidden" id="zone_id" name="zone_id" value="'.$livetemp_zone_id.'"/>
        <input type="hidden" id="min_c" name="min_c" value="'.DispSensor($conn,$zrow['min_c'],1).'"/>
	<input type="hidden" id="max_c" name="max_c" value="'.DispSensor($conn,$zrow['max_c'],1).'"/>';
} else { // end if ($rowcount > 0)
        echo '<input type="hidden" id="zone_id" name="zone_id" value=""/>
        <input type="hidden" id="min_c" name="min_c" value=""/>
        <input type="hidden" id="max_c" name="max_c" value=""/>';
} // end if ($rowcount = 0)
echo '<div class="modal fade" id="livetemperature" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header '.theme($conn, $theme, 'text_color').' bg-'.theme($conn, $theme, 'color').'">
				<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
				<h5 class="modal-title">'.$lang['live_temperature'].'</h5>
			</div>
                	<div class="modal-body">
                        	<div style="text-align:center;">';
        		        	if ($zone_mode != 0) {
	                                	if ($rowcount > 0) {
                        	                	echo '<h4><br><p>'.$zrow["name"].' Zone '.$current_mode.' Temperature Control</p></h4><br>
		                	                <input type="text" value="'.DispSensor($conn, $set_temp, 1).'" class="dial" id="livetemp_c" name="live_temp">
                		        	        <div style="float:right;">
                                				<textarea id="load_temp" class="temperature-box card-footer-'.theme($conn, settings($conn, 'theme'), 'color').'" readonly="readonly" row="0" col="0" ></textarea>
                                           		</div>
			                                <div class="form-check" style="'.$check_visible.'">
								<input class="form-check-input" type="checkbox" value="0" id="checkbox" name="status" checked Enabled>
                                                                <label class="form-check-label" for="checkbox"> '.$lang['livetemp_enable'].'</label>
	                                                </div>';
        	                            	} else {
                	                        	echo '<h4><br><p>'.$lang['livetemp_no_control_zone'].'</p></h4><br>';
		        	                }
					} else {
						echo '<h4><br><p>'.$zrow["name"].' Zone '.$lang['no_livetemp'].'</p></h4><br>';
					}
                		echo '</div>
                  	</div>
		        <!-- /.modal-body -->
                	<div class="modal-footer"><button type="button" class="btn btn-primary-'.theme($conn, settings($conn, 'theme'), 'color').' btn-sm" data-bs-dismiss="modal">'.$lang['cancel'].'</button>';
                        	if ($rowcount > 0 && $zone_mode != 0) { echo '<input type="button" name="submit" value="'.$lang['apply'].'" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' login btn-sm" onclick="update_livetemp()">'; }
		      	echo '</div>
              		<!-- /.modal-footer -->
		</div>
		<!-- /.modal-content -->
	</div>
	<!-- /.modal-dialog -->
</div>
<!-- /.modal fade -->
';

include("footer.php");
?>

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
</script>
