
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

$page_refresh = page_refresh($conn);

$sensors_params = [];
$query = "SELECT id FROM sensors;";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
        $sensor_params[] = array('sensor_id' =>$row['id']);
}
$js_sensor_params = json_encode($sensor_params);

if(isset($_GET["frost"])) {
	$frost_temp = $_GET['frost'];
	$info_message = "Frost Protection Temperature Changed to $frost_temp&deg;";
}
if(isset($_GET["reboot"])) {
	$info_message = "Server is Rebooting <small> Please Do not Refresh... </small>";
}
if(isset($_GET["shutdown"])) {
	$info_message = "Server is Shutting down <small> Please Do not Refresh... </small>";
}

if(isset($_GET["del_user"])) {
	$info_message = "User account removed successfully...</small>";
}

if(isset($_GET["zone_deleted"])) {
	$info_message = "Zone records removed successfully...</small>";
}

if(isset($_GET["find_gw"])) {
	$info_message = "Searching for PiHome Netwotk gateway on your local network <small> Please Do not Refresh... </small>";
}

//backup process start
 if(isset($_GET['db_backup'])) {
	$info_message = "Data Base Backup Request Started, This process may take some time complete..." ;
//	include("start_backup.php");
 }

?>
<?php include("header.php");  ?>
<?php include("notice.php");  ?>
<div class="container-fluid">
	<br>
        <div class="row">
        	<div class="col-lg-12">
                	<div id="settingslist" >
                                <div class="d-flex justify-content-center" style="margin-top:10px"><?php echo $lang['please_wait_text']; ?></div>
                                <div class="d-flex justify-content-center" style="margin-top:10px">
                                        <div class="spinner-border text-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>"
                                                role="status">
                                        </div>
                                </div>
			</div>
                </div>
                <!-- /.col-lg-4 -->
        </div>
        <!-- /.row -->
</div>
<!-- /.container -->
<?php include("footer.php");  ?>

<script>

// update page data every x seconds
$(document).ready(function(){
  var delay = '<?php echo $page_refresh ?>';

  (function loop() {
        var data = '<?php echo $js_sensor_params ?>';
        //console.log(data);
        var obj = JSON.parse(data)
        if (obj) {
                //console.log(obj.length);
                for (var y = 0; y < obj.length; y++) {
                  $('#sensor_temp_' + obj[y].sensor_id).load("ajax_fetch_data.php?id=" + obj[y].sensor_id + "&type=16").fadeIn("slow");
                  //console.log(obj[y].sensor_id);
                }
        }

        $('#settings_date').load("ajax_fetch_data.php?id=0&type=13").fadeIn("slow");
        $('#footer_weather').load("ajax_fetch_data.php?id=0&type=14").fadeIn("slow");
        $('#footer_all_running_time').load("ajax_fetch_data.php?id=0&type=17").fadeIn("slow");
        setTimeout(loop, delay);
  })();
});
</script>
