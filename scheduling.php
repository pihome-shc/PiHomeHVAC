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

$time_id = 0;
if(isset($_GET['hol_id'])) {
        $holidays_id = $_GET['hol_id'];
        $return_url = "holidays.php";
} elseif(isset($_GET['nid'])) {
        $return_url = "home.php";
        $time_id = 1;
} else {
        $holidays_id = 0;
        $return_url = "schedule.php";
}
if(isset($_GET['id'])) {
	$time_id = $_GET['id'];
}

//check if weather api is active
$query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
$result = $conn->query($query);
$w_count=mysqli_num_rows($result);
if ($w_count > 0) {
	$wrow = mysqli_fetch_array($result);
	$sunset_time = $wrow['sunset'];
	$sunrise_time = $wrow['sunrise'];
	$sun_enabled = 1;
} else {
        $sun_enabled = 0;
}

//Form submit
if (isset($_POST['submit'])) {
	$sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
        $aw_en = isset($_POST['aw_en']) ? $_POST['aw_en'] : "0";
		//PHP: Bitwise operator
		//http://php.net/manual/en/language.operators.bitwise.php
		//https://www.w3resource.com/php/operators/bitwise-operators.php
		$mask = 0;
        $bit = isset($_POST['Sunday_en']) ? $_POST['Sunday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 0); }
        else {$mask =  $mask & (0 << 0); }
        $bit = isset($_POST['Monday_en']) ? $_POST['Monday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 1); }
        $bit = isset($_POST['Tuesday_en']) ? $_POST['Tuesday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 2); }
        $bit = isset($_POST['Wednesday_en']) ? $_POST['Wednesday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 3); }
        $bit = isset($_POST['Thursday_en']) ? $_POST['Thursday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 4); }
        $bit = isset($_POST['Friday_en']) ? $_POST['Friday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 5); }
        $bit = isset($_POST['Saturday_en']) ? $_POST['Saturday_en'] : "0";
        if ($bit) {
          $mask =  $mask | (1 << 6);
		}

	$start_time = $_POST['start_time'];
	$end_time = $_POST['end_time'];

        if(isset($_GET['nid'])) {
                $sc_en = isset($_POST['sc_en']) ? $_POST['sc_en'] : "0";
                $query = "SELECT * FROM schedule_night_climate_time;";
                $result = $conn->query($query);
                $nctcount = $result->num_rows;
                if ($nctcount == 0) {
                        $query = "INSERT INTO `schedule_night_climate_time`(`id`, `sync`, `purge`, `status`, `start_time`, `end_time`, `WeekDays`) VALUES (1,1,0,'{$sc_en}','{$start_time}','{$end_time}','{$mask}');";
                        $result = $conn->query($query);
                        if ($result) {
                                $message_success .= "<p>".$lang['night_climate_time_success']."</p>";
				header("Refresh: 3; url=".$return_url);
                        } else {
                                $error .= "<p>".$lang['night_climate_time_fail']."</p> <p>" .mysqli_error($conn). "</p>";
                        }
                }
		else {
                        $found_product = mysqli_fetch_array($result);
                        $nct_id = $found_product['id'];
	                $query = "UPDATE schedule_night_climate_time SET sync = '0', status = '{$sc_en}', start_time = '{$start_time}', end_time = '{$end_time}', WeekDays = '{$mask}' where id = {$nct_id};";
        	        $timeresults = $conn->query($query);
                	if ($timeresults) {
                        	$message_success = "<p>".$lang['night_climate_time_success']."</p>";
	                        header("Refresh: 3; url=".$return_url);
        	        } else {
                	        $error = "<p>".$lang['night_climate_error']."</p><p>".mysqli_error($conn). "</p>";
	                }
		}
                $query = "SELECT * FROM zone;";
                $result = $conn->query($query);
                $zcount = $result->num_rows;
                if ($zcount != 0) {
	                foreach($_POST['id'] as $id){
        	                $id = $_POST['id'][$id];
				$type = isset($_POST['ztype'][$id]) ? $_POST['ztype'][$id] : "1";
                	        $status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
                        	//$status = $_POST['status'][$id];
	                        $min =SensorToDB($conn,$_POST['min'][$id],$type);
        	                $max =SensorToDB($conn,$_POST['max'][$id],$type);
                	        $query = "UPDATE schedule_night_climat_zone SET sync = '0', status='$status', min_temperature='".number_format(SensorToDB($conn,$_POST['min_temp'][$id],$type),1)."', max_temperature='".number_format(SensorToDB($conn,$_POST['max_temp'][$id],$type),1)."' WHERE id='$id'";
                        	$zoneresults = $conn->query($query);
	                        if ($zoneresults) {
        	                        $message_success .= "<p>".$lang['night_climate_zone_success']."</p>";
					header("Refresh: 3; url=".$return_url);
                	        } else {
                                $error .= "<p>".$lang['night_climate_error']."</p><p>".mysqli_error($conn). "</p>";
                        	}
                	}
		}
        } else {
                $sch_name = $_POST['sch_name'];
		if (isset($_POST['start_time_state'])) {
			switch ($_POST['start_time_state']) {
				case 0:
		                        $start_sr = 0;
                		        $start_ss = 0;
                        		$start_offset = 0;
        				break;
    				case 1:
                                        $start_sr = 1;
                                        $start_ss = 0;
                                        $start_offset = isset($_POST['start_time_offset']) ? $_POST['start_time_offset'] : "0";
        				break;
    				case 2:
                                        $start_sr = 0;
                                        $start_ss = 1;
                                        $start_offset = isset($_POST['start_time_offset']) ? $_POST['start_time_offset'] : "0";
        				break;
			}
		} else {
			$start_sr = 0;
                        $start_ss = 0;
                        $start_offset = 0;
		}

                if (isset($_POST['end_time_state'])) {
                        switch ($_POST['end_time_state']) {
                                case 0:
                                        $end_sr = 0;
                                        $end_ss = 0;
                                        $end_offset = 0;
                                        break;
                                case 1:
                                        $end_sr = 1;
                                        $end_ss = 0;
                                        $end_offset = isset($_POST['end_time_offset']) ? $_POST['end_time_offset'] : "0";
                                        break;
                                case 2:
                                        $end_sr = 0;
                                        $end_ss = 1;
                                        $end_offset = isset($_POST['end_time_offset']) ? $_POST['end_time_offset'] : "0";
                                	break;
                        }
                } else {
                        $end_sr = 0;
                        $end_ss = 0;
                        $end_offset = 0;
                }

                $query = "SELECT * FROM `schedule_daily_time` WHERE id = {$time_id};";
                $result = $conn->query($query);
                $sdt_count = $result->num_rows;
               	if ($sdt_count == 0) {
	                $query = "INSERT INTO `schedule_daily_time`(`id`, `sync`, `purge`, `status`, `start`, `start_sr`, `start_ss`, `start_offset`, `end`, `end_sr`, `end_ss`, `end_offset`, `WeekDays`, `run_time`, `sch_name`, `type`) VALUES ('{$time_id}','0', '0', '{$sc_en}', '{$start_time}', '{$start_sr}', '{$start_ss}', '{$start_offset}','{$end_time}', '{$end_sr}', '{$end_ss}', '{$end_offset}','{$mask}', 0, '{$sch_name}', '{$aw_en}');";
		} else {
			$query = "UPDATE schedule_daily_time SET sync = '0', status = '{$sc_en}', start = '{$start_time}', start_sr = '{$start_sr}', start_ss = '{$start_ss}', start_offset = '{$start_offset}', end = '{$end_time}', end_sr = '{$end_sr}', end_ss = '{$end_ss}', end_offset = '{$end_offset}', WeekDays = '{$mask}', run_time = 0, sch_name = '{$sch_name}' , type = '{$aw_en}' WHERE id = '{$time_id}';";
		}
		$result = $conn->query($query);
		$schedule_daily_time_id = mysqli_insert_id($conn);

		if ($result) {
			$message_success = $lang['schedule_time_modify_success'];
			header("Refresh: 3; url=".$return_url);
		} else {
			$error = $lang['schedule_time_modify_error']."<p>".mysqli_error($conn)."</p>"."  id1: ".$time_id;
		}

		foreach($_POST['id'] as $id){
			$id = $_POST['id'][$id];
			$type = isset($_POST['ztype'][$id]) ? $_POST['ztype'][$id] : "1";
			if(isset($_GET['id'])) {
				$tzid = $id;
				$schedule_daily_time_id = $time_id;
				$zoneid = $_POST['zoneid'][$id];
			} else {
				$tzid = 0;
				$zoneid = $id;
			}
			$status = isset($_POST['status'][$id]) ? $_POST['status'][$id] : "0";
			$disabled = 0;
			$coop = isset($_POST['coop'][$id]) ? $_POST['coop'][$id] : "0";
			$temp=SensorToDB($conn,$_POST['temp'][$id],$type);
                        $ftemp = number_format($temp,1);

                        $query = "SELECT * FROM `schedule_daily_time_zone` WHERE id = {$tzid};";
                        $result = $conn->query($query);
                        $sdtzcount = $result->num_rows;
                        if ($sdtzcount == 0) {
                                $query = "INSERT INTO `schedule_daily_time_zone`(`id`, `sync`, `purge`, `status`, `schedule_daily_time_id`, `zone_id`, `temperature`, `holidays_id`, `coop`, `disabled`) VALUES ('{$tzid}', '0', '0', '{$status}', '{$schedule_daily_time_id}','{$zoneid}','".number_format($temp,1)."',{$holidays_id},{$coop}, {$disabled});";
                                $zoneresults = $conn->query($query);

                                if ($zoneresults) {
                                        $message_success = "<p>".$lang['schedule_daily_time_zone_insert_success']."</p>";
                                } else {
                                        $error = "<p>".$lang['schedule_daily_time_zone_insert_error']." </p> <p>" .mysqli_error($conn). "</p>"."  schedule_daily_time_id: ".$schedule_daily_time_id."  id: ".$id."  tzid: ".$tzid."  zone id: ".$zoneid."  holid: ".$holidays_id;
                                }
                        } else {
                                $query = "UPDATE schedule_daily_time_zone SET sync = '0', status = '{$status}', temperature = '{$ftemp}', coop = '{$coop}', disabled = {$disabled} WHERE id = '{$id}';";
                                $zoneresults = $conn->query($query);

                                if ($zoneresults) {
                                        $message_success = "<p>".$lang['schedule_daily_time_zone_update_success']."</p>";
                                } else {
                                        $error = "<p>".$lang['schedule_daily_time_zone_insert_error']." </p> <p>" .mysqli_error($conn). "</p>"."  schedule_daily_time_id: ".$schedule_daily_time_id."  id: ".$id."  tzid: ".$tzid."  zone id: ".$zoneid."  holid: ".$holidays_id;
                                }
                        }
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
<?php if(isset($_GET['nid'])) {
        $query = "SELECT `id`, `sync`, `purge`, `status`, `start_time` as start, `end_time` as end, `WeekDays` FROM schedule_night_climate_time LIMIT 1;";
        $result = $conn->query($query);
        $time_row = mysqli_fetch_assoc($result);
        $query = "select * from schedule_night_climat_zone_view where zone_status = 1;";
        $zoneresults = $conn->query($query);
} elseif ($time_id != 0) {
        $query = "SELECT * FROM schedule_daily_time WHERE id = {$time_id}";
        $results = $conn->query($query);
        $time_row = mysqli_fetch_assoc($results);

        $query = "SELECT DISTINCT sdt.id as time_id, zone.id as zone_id, sdtz.id as tz_id, zone.name as zone_name, sdtz.status as tz_status, ztype.type, ztype.category, zs.min_c, zs.max_c,
                s.sensor_type_id, st.type as stype, sdtz.coop, sdtz.temperature
                FROM zone
                JOIN zone_type ztype ON zone.type_id = ztype.id
                LEFT JOIN zone_sensors zs ON zone.id = zs.zone_id
                LEFT JOIN sensors s ON zs.zone_sensor_id = s.id
                LEFT JOIN sensor_type st ON s.sensor_type_id = st.id
                LEFT JOIN schedule_daily_time_zone sdtz ON zone.id = sdtz.zone_id
                LEFT JOIN schedule_daily_time sdt ON sdtz.schedule_daily_time_id = sdt.id
                WHERE zone.status = 1 AND zone.`purge`= 0 AND sdt.id = {$time_id}
                ORDER BY zone.index_id asc;";
        $zoneresults = $conn->query($query);
} else {
        $query = "SELECT DISTINCT zone.id as tz_id, zone.name as zone_name, zone.status as tz_status, ztype.type, ztype.category, zs.min_c, zs.max_c, s.sensor_type_id, st.type as stype
                FROM zone
                JOIN zone_type ztype ON zone.type_id = ztype.id
                LEFT JOIN zone_sensors zs ON zone.id = zs.zone_id
		LEFT JOIN sensors s ON zs.zone_sensor_id = s.id
		LEFT JOIN sensor_type st ON s.sensor_type_id = st.id
                WHERE status = 1 AND zone.`purge`= 0
                ORDER BY zone.index_id asc;";
	$zoneresults = $conn->query($query);
}

if(!isset($_GET['nid'])) {
        $querya = "SELECT * FROM schedule_daily_time WHERE type = 1 LIMIT 1";
        $resulta = $conn->query($querya);
        $rowcount=mysqli_num_rows($resulta);
        if($rowcount > 0) {
	        $away_row = mysqli_fetch_array($resulta);
		if ($away_row['id'] == $time_id) { $away_disabled = ""; } else { $away_disabled = "Disabled"; }
	} else {
        	$away_disabled = ""; 
	}
}?>

<!-- Title (e.g. Add Schedule or Edit Schedule) -->
<div class="container-fluid">
	<br>
 	<div class="row">
        	<div class="col-lg-12">
			<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>"">
			       	<div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="d-flex justify-content-between">
						<div>
					        	<i class="bi bi-clock icon-fw"></i>
                		                        <?php if(isset($_GET['nid'])) {
                                	        		echo $lang['night_climate'];
                                     			} elseif ($time_id != 0) {
		                                        	echo $lang['schedule_edit'] . ": " . $time_row['sch_name'];
                		                        } else {
                                		        	echo $lang['schedule_add'];
                                    			} ?>
						</div>
						<div class="dropdown">
							<a class="" data-bs-toggle="dropdown" href="#">
								<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
							</a>
				                        <ul class="dropdown-menu">
								<?php if(!isset($_GET['nid'])) {
	                						echo '<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_scheduling.pdf" target="_blank"><i class="bi bi-file-earmark-pdf icon-fw"></i>&nbsp'.$lang['setup_scheduling'].'</a></li>
				                	                <li class="dropdown-divider"></li>
                	        					<li><a class="dropdown-item" href="pdf_download.php?file=start_time_offset.pdf" target="_blank"><i class="bi bi-file-earmark-pdf icon-fw"></i>&nbsp'.$lang['setup_start_time_offset'].'</a></li>';
				                                } else {
        	                					echo '<li><a class="dropdown-item" href="pdf_download.php?file=setup_guide_night_climate_scheduling.pdf" target="_blank"><i class="bi bi-file-earmark-pdf icon-fw"></i>&nbsp'.$lang['setup_guide_night_climate_scheduling'].'</a></li>';
								} ?>
				                        </ul>
                        				<div class="btn-group"><?php echo '&nbsp;&nbsp;'.date("H:i"); ?></div>
						</div>
					</div>
			        </div>
                        	<!-- /.card-header -->
                        	<div class="card-body">

            				<form data-bs-toggle="validator" role="form" method="post" action="<?php $_SERVER['PHP_SELF'];?>" id="form-join">

                                        <div class="row">
	                                        <!-- Enable Schedule -->
                                                <div class="col-5">
							<div class="form-check"">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" style="accent-color: #ff8839;" type="checkbox" value="1" id="checkbox0" name="sc_en" <?php $check = ($time_row['status'] == 1) ? 'checked' : ''; echo $check; ?>>
								<label class="form-check-label" for="checkbox0"> <?php echo $lang['schedule_enable']; ?></label>
							</div>
						</div>

	                                        <!-- Enable Away Schedule -->
                                                <div class="col-7">
		                                        <div class="form-check">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox1" name="aw_en" <?php $check = ($time_row['type'] == 1) ? 'checked' : ''; echo $check. " ".$away_disabled ; ?>>
                                		                <label class="form-check-label" for="checkbox1"> <?php echo $lang['away_enable']; ?></label>
                                        		</div>
						</div>
					</div>
					<!-- /.row -->

					<!-- Day Selector -->
					<div class="row">
						<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox2" name="Sunday_en" <?php $check = (($time_row['WeekDays'] & 1) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label class="form-check-label" for="checkbox2"> <?php echo $lang['sun']; ?></label>
							</div>
						</div>

						<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox3" name="Monday_en" <?php $check = (($time_row['WeekDays'] & 2) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label class="form-check-label" for="checkbox3"> <?php echo $lang['mon']; ?></label>
							</div>
						</div>

        					<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox4" name="Tuesday_en" <?php $check = (($time_row['WeekDays'] & 4) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label class="form-check-label" for="checkbox4"> <?php echo $lang['tue']; ?></label>
							</div>
						</div>

						<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox5" name="Wednesday_en" <?php $check = (($time_row['WeekDays'] & 8) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label class="form-check-label" for="checkbox5"> <?php echo $lang['wed']; ?></label>
							</div>
						</div>

        					<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox6" name="Thursday_en" <?php $check = (($time_row['WeekDays'] & 16) > 0) ? 'checked' : ''; echo $check; ?>>
	    							<label class="form-check-label" for="checkbox6"> <?php echo $lang['thu']; ?></label>
							</div>
						</div>

						<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox7" name="Friday_en" <?php $check = (($time_row['WeekDays'] & 32) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label class="form-check-label" for="checkbox7"> <?php echo $lang['fri']; ?></label>
							</div>
						</div>

						<div class="col-3">
							<div class="form-check">
    								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox8" name="Saturday_en" <?php $check = (($time_row['WeekDays'] & 64) > 0) ? 'checked' : ''; echo $check; ?>>
    								<label class="form-check-label" for="checkbox8"> <?php echo $lang['sat']; ?></label>
							</div>
						</div>
						<div class="col-3">
                					<div class="d-flex justify-content-end">
								<input type="button" value="<?php echo $lang['toggle_days']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> login btn-sm" onclick="toggle_days()">
							</div>
                                                </div>
					</div>
					<!-- /.row -->

					<!-- Schedule Name -->
                        		<?php if(!isset($_GET['nid'])) {
                        		echo '<div class="form-group" class="control-label">
                                		<label><'.$lang['sch_name'].'></label>
                                		<input class="form-control" type="text" id="sch_name" name="sch_name" value="'.$time_row["sch_name"].'" placeholder="Schedule Name">
                                		<div class="help-block with-errors"></div>
                        		</div>'; } ?>

					<!-- Start Time -->
					<?php
					if ($sun_enabled && !isset($_GET['nid'])) {
						if($time_id != 0){
							if ($time_row['start_sr'] == '1') {
								$start_sr_check = 'checked';
								$start_ss_check = '';
								$start_n_check = '';
								$start_mode = 1;
								$start_offset = $time_row['start_offset'];
							} elseif ($time_row['start_ss'] == '1') {
        	                                        	$start_sr_check = '';
                	                                        $start_ss_check = 'checked';
                        	                                $start_n_check = '';
                                	                        $start_mode = 2;
                                        	                $start_offset = $time_row['start_offset'];
							} else {
								$start_sr_check = '';
								$start_sr_check = '';
								$start_n_check = 'checked';
								$start_mode = 0;
								$start_offset = 0;
							}
						} else {
                                               		$start_sr_check = '';
	                                                $start_sr_check = '';
        	                                        $start_n_check = 'checked';
                	                                $start_mode = 0;
                        	                        $start_offset = 0;
						}
					} else {
                                        	$start_mode = 0;
                                                $start_offset = 0;
					}
					?>
                                        <input type="hidden" id="start_time_state" name="start_time_state" value="<?php echo $start_mode; ?>">
                                        <div class="form-group" class="control-label"><label><?php echo $lang['start_time']; ?></label>
                        			<input class="form-control" type="time" id="start_time" name="start_time" value="<?php echo $time_row["start"];?>" placeholder="Start Time" required>
						<?php if ($sun_enabled && !isset($_GET['nid'])) { ?>
			                                <br>
        	                                        &nbsp;<img src="./images/sunset.png">
							<i class="bi bi-info-circle icon-lg text-info" data-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="<?php echo $lang['start_time_enable_info']; ?>"></i>
							<label class="form_check form-check-inline">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="radio" name="radioGroup1" id="radio1" value="option1" <?php echo $start_n_check; ?>  onchange="update_start_time('00:00', '0')" > Normal
							</label>
							<label class="form-check form-check-inline">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="radio" name="radioGroup1" id="radio2" value="option2" <?php echo $start_sr_check; ?> onchange="update_start_time(<?php echo $sunrise_time; ?>, '1')" > Sunrise
							</label>
							<label class="form-check form-check-inline">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="radio" name="radioGroup1" id="radio3" value="option3"  <?php echo $start_ss_check; ?>  onchange="update_start_time(<?php echo $sunset_time; ?>, '2')" > Sunset
							</label>
                                	                <input class="styled" type="text" id="start_time_offset" name="start_time_offset" style="width: 40px" value="<?php echo $start_offset; ?>"/>
						 <?php } ?>
						<div class="help-block with-errors"></div>
					</div>
					<!-- End Time -->
                                         <?php
					if ($sun_enabled && !isset($_GET['nid'])) {
                                         	if($time_id != 0){
                                         		if ($time_row['end_sr'] == '1') {
                                                		$end_sr_check = 'checked';
	                                                        $end_ss_check = '';
        	                                                $end_n_check = '';
                	                                        $end_mode = 1;
                        	                                $end_offset = $time_row['end_offset'];
                                	              	} elseif ($time_row['end_ss'] == '1') {
                                        	                $end_sr_check = '';
                                                	        $end_ss_check = 'checked';
                                                        	$end_n_check = '';
	                                                        $end_mode = 2;
        	                                                $end_offset = $time_row['end_offset'];
                	                                } else {
                        	                                $end_sr_check = '';
                                	                        $end_sr_check = '';
                                        	                $end_n_check = 'checked';
                                                	        $end_mode = 0;
								$end_offset = 0;
	                                                }
        	                              	} else {
                	                               	$end_sr_check = '';
                        	                        $end_sr_check = '';
                                	                $end_n_check = 'checked';
                                        	        $end_mode = 0;
                                                	$end_offset = 0;
						}
					} else {
                                       		$end_mode = 0;
                                                $end_offset = 0;
					}
                                        ?>
                                        <input type="hidden" id="end_time_state" name="end_time_state" value="<?php echo $end_mode; ?>">
					<div class="form-group" class="control-label"><label><?php echo $lang['end_time']; ?></label>
						<input class="form-control" type="time" id="end_time" name="end_time" value="<?php echo $time_row["end"];?>" placeholder="End Time" required>
						<?php if ($sun_enabled && !isset($_GET['nid'])) { ?>
                                                	<br>
	                                                &nbsp;<img src="./images/sunset.png">
							 <i class="bi bi-info-circle icon-lg text-info" data-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="<?php echo $lang['end_time_enable_info']; ?>"></i>
                	                                <label class="form-check form-check-inline">
                        	                                <input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="radio" name="radioGroup2" id="radio3" value="option1" <?php echo $end_n_check; ?>  onchange="update_end_time('00:00', '0')" > Normal
                                	                </label>
                                        	        <label class="form-check form-check-inline">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="radio" name="radioGroup2" id="radio4" value="option2"  <?php echo $end_sr_check; ?>  onchange="update_end_time(<?php echo $sunrise_time; ?>, '1')" > Sunrise
	                                                </label>
        	                                        <label class="form-check form-check-inline">
								<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="radio" name="radioGroup2" id="radio5" value="option3"  <?php echo $end_ss_check; ?>  onchange="update_end_time(<?php echo $sunset_time; ?>, '2')" > Sunset
                        	                        </label>
                                	                <input class="styled" type="text" id="end_time_offset" name="end_time_offset" style="width: 40px" value="<?php echo $end_offset; ?>"/>
						<?php } ?>
                                                <div class="help-block with-errors"></div>
                                        </div>

					<label><?php echo $lang['select_zone']; ?></label>
					<?php
					// Zone List Loop
					while ($row = mysqli_fetch_assoc($zoneresults)) {
						?>
						<hr>
						<!-- Zone ID (tz_id) -->
						<input type="hidden" name="id[<?php echo $row["tz_id"];?>]" value="<?php echo $row["tz_id"];?>">
                                                <input type="hidden" name="max_c" value="<?php echo $row["max_c"];?>">
                                                <input type="hidden" name="ztype[<?php echo $row["tz_id"];?>]" value="<?php echo $row["sensor_type_id"]; ?>">
						<?php if($time_id != 0){
							echo '<input type="hidden" name="zoneid['.$row["tz_id"].']" value="'.$row["zone_id"].'">';
						}?>
						<!-- Zone Enable Checkbox -->
						<div class="form-check">
							<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="<?php echo $row["tz_id"];?>" name="status[<?php echo $row["tz_id"];?>]" <?php if($time_id != 0){ $check = ($row['tz_status'] == 1) ? 'checked' : ''; echo $check;} ?> onclick="$('#collapse_<?php echo $row["tz_id"];?>').collapse('toggle');">
    							<label class="form-check-label" for="checkbox<?php echo $row["tz_id"];?>"><?php echo $row["zone_name"];?></label>

    							<div class="help-block with-errors"></div>
						</div>

						<!-- Group Zone Settings -->
						<?php
						if (($row["category"] == 0 || $row["category"] == 1 || $row["category"] == 3 || $row["category"] == 4 || $row["category"] == 5) && $row["sensor_type_id"] <> 3) {
							if($row['tz_status'] == 1 AND $time_id != 0){
								//if($time_id != 0){
								$show = "show";
							}else{
								$show = "";
							}
							 echo '<div class="collapse '.$show.'" id="collapse_'.$row["tz_id"].'">
								<div class="form-group" class="control-label">';
//                                                                        if($row["type"]=='HVAC' || $row["type"]=='HVAC-M') {
									if(strpos($row["type"], 'HVAC') !== false) {
                                                                                $min = DispSensor($conn,$row['min_c'],$row['sensor_type_id']);
                                                                        } else {
                                                                                //0=C, 1=F
                                                                                $c_f = settings($conn, 'c_f');
                                                                                if(($c_f==1 || $c_f=='1') AND ($row["type"]=='Heating')) {
                                                                                        $min = 50;
                                                                                }elseif (($c_f==1 || $c_f=='1') AND ($row["type"]=='Water' OR $row["type"]=='Immersion' OR $row["type"]=='Cooling')) {
                                                                                        $min = 50;
                                                                                }elseif (($c_f==0 || $c_f=='0') AND ($row["type"]=='Heating')) {
                                                                                        $min = 10;
                                                                                }elseif (($c_f==0 || $c_f=='0') AND ($row["type"]=='Water' OR $row["type"]=='Immersion' OR $row["type"]=='Cooling')) {
                                                                                        $min = 10;
                                                                                } else {
                                                                                        $min = 20;
                                                                                }
                                                                        }
									$max = DispSensor($conn,$row['max_c'],$row['sensor_type_id']);
        								if(!isset($_GET['nid'])) {
										if ((settings($conn, 'mode')  & 0b1) == 0 && $row['sensor_type_id'] == 1) {
											//<!-- Zone Coop Enable Checkbox -->
										       	if($time_id != 0){ $check = ($row['coop'] == 1) ? 'checked' : ''; }
										        echo '<div class="form-check">
												<input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" id="coop'.$row["tz_id"].'" name="coop['.$row["tz_id"].']" '.$check.'>
											        <label class="form-check-label" for="coop'.$row["tz_id"].'">Coop Start</label> <i class="bi bi-tree-fill green" style="font-size: 1.2rem;"></i>
											        <i class="bi bi-info-circle icon-lg text-info" data-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="'.$lang['schedule_coop_help'].'"></i>
												<div class="help-block with-errors"></div>
											</div>';
										}
									        // <!-- Temperature and Slider -->
										if($time_id != 0){ $temp = DispSensor($conn, $row['temperature'],$row['sensor_type_id']);} else { $temp = '15.0';}
										$unit = SensorUnits($conn,$row['sensor_type_id']);
										echo '<div class="slidecontainer">
											<h4>'.$row['stype'].': <span id="val'.$row["zone_id"].'" style="display: inline-flex !important; font-size:18px !important;"><output name="show_temp_val" id="temp'.$row["tz_id"].'" style="padding-top:0px !important; font-size:18px !important;">'.$temp.'</output></span>'.$unit.'</h4><br>
										        <input type="range" min="'.$min.'" max="'.$max.'" step="0.5" value="'.$temp.'" class="slider" id="bb'.$row["tz_id"].'" name="temp['.$row["tz_id"].']" oninput=update_temp(this.value,"temp'.$row["tz_id"].'")>
	                							</div>';
									} else {
										// <!-- Temperature and Slider -->
									        echo '<div class="slidecontainer">
										 	<h4>'.$lang['min_temperature'].': <span id="min_val'.$row["zone_id"].'" style="display: inline-flex !important; font-size:18px !important;"><output name="show_min_temp_val" id="min_temp'.$row["tz_id"].'" style="padding-top:0px !important; font-size:18px !important;">'.DispSensor($conn, $row['min_temperature'],1).'</output></span>&deg;</h4><br>
										        <input type="range" min="'.$min.'" max="'.$max.'" step="0.5" value="'.DispSensor($conn, $row['min_temperature'],1).'" class="slider" id="min_bb'.$row["tz_id"].'" name="min_temp['.$row["tz_id"].']" oninput=update_temp(this.value,"min_temp'.$row["tz_id"].'")>
									        </div>';

									        echo '<div class="slidecontainer">
										 	<h4>'.$lang['max_temperature'].': <span id="max_val'.$row["zone_id"].'" style="display: inline-flex !important; font-size:18px !important;"><output name="show_max_temp_val" id="max_temp'.$row["tz_id"].'" style="padding-top:0px !important; font-size:18px !important;">'.DispSensor($conn, $row['max_temperature'],1).'</output></span>&deg;</h4><br>
										        <input type="range" min="'.$min.'" max="'.$max.'" step="0.5" value="'.DispSensor($conn, $row['max_temperature'],1).'" class="slider" id="max_bb'.$row["tz_id"].'" name="max_temp['.$row["tz_id"].']" oninput=update_temp(this.value,"max_temp'.$row["tz_id"].'")>
									       	</div>'; 
									}
        								?>
    								</div>
								<!-- /.form-group -->
							</div>
                                                        <!-- /.row -->
                                                <?php }

                                        }?> <!-- End of Zone List Loop  -->
                                        <br>
					<!-- Buttons -->
					<a href="<?php echo $return_url ?>"><button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color'); ?> btn-sm" ><?php echo $lang['cancel']; ?></button></a>
                			<input type="submit" name="submit" value="<?php echo $lang['submit']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-sm login">
					</form>
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
                <!-- /.col-lg-12 -->
	</div>
        <!-- /.row -->
</div>
<!-- /#container-fluid -->
<?php }  ?>
<?php include("footer.php"); ?>

<script language="javascript" type="text/javascript">
function update_temp(value, id)
{
 var valuetext = value;
 var idtext = id;
 document.getElementById(id).innerTex = parseFloat(value);
 document.getElementById(id).value = parseFloat(value);
}

function update_start_time(stime, mode)
{
let start_unix_timestamp = stime
// Create a new JavaScript Date object based on the timestamp
// multiplied by 1000 so that the argument is in milliseconds, not seconds.
var sdate = new Date(start_unix_timestamp * 1000);
// Hours part from the timestamp
var shours = sdate.getHours();
if (shours < 10) { shours = "0" + shours; }
// Minutes part from the timestamp
var sminutes = "0" + sdate.getMinutes();

// Will display time in 10:30 format
var StartTime = shours + ':' + sminutes.substr(-2);
 document.getElementById('start_time').value = StartTime;

 document.getElementById('start_time_state').value = mode;
}

function update_end_time(etime, mode)
{
let end_unix_timestamp = etime
// Create a new JavaScript Date object based on the timestamp
// multiplied by 1000 so that the argument is in milliseconds, not seconds.
var edate = new Date(end_unix_timestamp * 1000);
// Hours part from the timestamp
var ehours = edate.getHours();
if (ehours < 10) { ehours = "0" + ehours; }
// Minutes part from the timestamp
var eminutes = "0" + edate.getMinutes();

// Will display time in 10:30 format
var EndTime = ehours + ':' + eminutes.substr(-2);
 document.getElementById('end_time').value = EndTime;

 document.getElementById('end_time_state').value = mode;

}
</script>

<script>
function toggle_days()
{
  for (let i = 2; i <= 8; i++) {
    if (document.getElementById("checkbox" + i).checked) {
        document.getElementById("checkbox" + i).checked = false;
    } else {
        document.getElementById("checkbox" + i).checked = true;
    }
  }
}
</script>

