
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
	//load sensor history if the modal is shown
        if ($('#sensors_history').is(':visible')) {
		myId = document.getElementById("s_hist_id").value;
                myName = document.getElementById("s_hist_name").value;
        	async function loadNames() {
                	var response = await fetch('ajax_fetch_temp24h.php?id=' + myId);
                	var obj = await response.json();

                        if (obj.success) {
                		if (Array.isArray(obj.state[myId])) {
                        		if (obj.state[myId].length > 0) {
                                		var table = "" ;
                                		for (var y = 0; y < obj.state[myId].length; y++){
                                        		table += '<tr>';
                                        		table += '<td style="text-align:center; vertical-align:middle;" class="col-6">' + obj.state[myId][y].datetime +'</td>'
                                                		+ '<td style="text-align:center; vertical-align:middle;" class="col-6">' + obj.state[myId][y].payload +'</td>' ;
                                        		table += '</tr>';
                                		}
                                		document.getElementById("result").innerHTML = table;
                                        	var title_text_1 = "<?php echo $lang['sensor_count_last24h'] ?>";
                                        	var title_text_2 = "<?php echo $lang['average_count_last24h'] ?>";
                                        	$('#sensorhistory_text1').text(title_text_1);
						$('#sensorhistory_value1').text(obj.state[myId].length);
                                        	$('#sensorhistory_text2').text(title_text_2);
                                        	$('#sensorhistory_value2').text(Math.floor(obj.state[myId].length/24));
                        		}
                		}
			} else {
				var title_text_1 = "<?php echo $lang['unable_to_retrieve_sensor_data'] ?>";
                        	$('#sensorhistory_text1').text(title_text_1);
				$('#sensorhistory_value1').text('');
				$('#sensorhistory_text2').text('');
				$('#sensorhistory_value2').text('');
			}
        	}

		loadNames();
        }

        if ($('#relays_history').is(':visible')) {
                myId = document.getElementById("r_hist_id").value;
                myName = document.getElementById("r_hist_name").value;
                async function loadRelayLog() {
                        var response = await fetch('ajax_fetch_relay_log.php?id=' + myId);
                        var obj = await response.json();

                        if (obj.success) {
                                if (Array.isArray(obj.state[myId])) {
                                        if (obj.state[myId].length > 0) {
                                                var table = "" ;
                                                for (var y = 0; y < obj.state[myId].length; y++){
                                                        table += '<tr>';
                                                        table += '<td style="text-align:center; vertical-align:middle;" class="col-6">' + obj.state[myId][y].datetime +'</td>'
								+ '<td style="text-align:center; vertical-align:middle;" class="col-2">' + obj.state[myId][y].message +'</td>'
                                                                + '<td style="text-align:center; vertical-align:middle;" class="col-4">' + obj.state[myId][y].zone_mode +'</td>';
                                                        table += '</tr>';
                                                }
                                                document.getElementById("relay_log_result").innerHTML = table;
                                                var title_text_1 = "<?php echo $lang['relay_logs_for_zone'] ?>" + obj.state[myId][0].zone_name;
                                                $('#relay_log_text1').text(title_text_1);
                                        }
                               }
                        } else {
                                var table = "" ;
                                document.getElementById("relay_log_result").innerHTML = table;
                                var title_text_1 = "<?php echo $lang['unable_to_retrieve_relay_log_data'] ?>";
                                $('#relay_log_text1').text(title_text_1);
                        }
                }

                loadRelayLog();
        }

        if ($('#show_services').is(':visible')) {
		//create array for service names
		let obj_services = [
  			{
    				"service": "apache2.service",
	  		},
  			{
    				"service": "mysql.service",
	  		},
        	        {
                	        "service": "mariadb.service",
	                },
        	        {
                	        "service": "pihome_jobs_schedule.service",
	                },
        	        {
                	        "service": "HA_integration.service",
	                },
        	        {
                	        "service": "pihome_amazon_echo.service",
	                },
        	        {
                	        "service": "homebridge.service",
	                },
        	        {
                	        "service": "autohotspot.service",
	                },

		]
		//update services status
	        if (obj_services) {
        	        for (var y = 0; y < obj_services.length; y++) {
                  		$("#service_" + y).load("ajax_fetch_data.php?id=" + obj_services[y].service + "&type=27").fadeIn("slow");
                	}
        	}
	}

	if ($('#status_sensors').is(':visible')) {
		$('#sensor_temps').load("ajax_fetch_data.php?id=0&type=16").fadeIn("slow");
		var sen_text = "<?php echo $lang['temperature_sensor_text'] ?>";
		$('#status_sensors_text').text(sen_text);
	}
        if ($('#sc_z_logs').is(':visible')) {
	        $('#controller_zone_logs').load("ajax_fetch_data.php?id=0&type=21").fadeIn("slow");
	}
	if ($('#s_uptime').is(':visible')) {
        	$('#system_uptime').load("ajax_fetch_data.php?id=0&type=22").fadeIn("slow");
	}
	if ($('#cpu_temp_history').is(':visible')) {
        	$('#cpu_temps').load("ajax_fetch_data.php?id=0&type=23").fadeIn("slow");
	}
        if ($('#zones_states').is(':visible')) {
	        $('#z_states').load("ajax_fetch_data.php?id=0&type=33").fadeIn("slow");
	}
        if ($('#status_scripts').is(':visible')) {
	        $('#gw_sc_scripts').load("ajax_fetch_data.php?id=0&type=34").fadeIn("slow");
	}

        if ($('#eth_setup').is(':visible')) {
                $('#eth_status').load("ajax_fetch_data.php?id=0&type=35").fadeIn("slow");
        }

        if ($('#wifi_setup').is(':visible')) {
                $('#wifi_status').load("ajax_fetch_data.php?id=0&type=36").fadeIn("slow");
        }

        if ($('#status_relays').is(':visible')) {
                $('#relay_states').load("ajax_fetch_data.php?id=0&type=37").fadeIn("slow");
        }

        $('#settings_date').load("ajax_fetch_data.php?id=0&type=13").fadeIn("slow");
        $('#footer_weather').load("ajax_fetch_data.php?id=0&type=14").fadeIn("slow");
	$('#footer_all_running_time').load("ajax_fetch_data.php?id=0&type=17").fadeIn("slow");
        $('#cpu_status').load("ajax_fetch_data.php?id=0&type=25").fadeIn("slow");
        $('#frost_status').load("ajax_fetch_data.php?id=0&type=26").fadeIn("slow");

        setTimeout(loop, delay);
  })();
});
</script>
