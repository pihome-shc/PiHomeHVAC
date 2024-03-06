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

//require_once(__DIR__.'/session.php');
require_once(__DIR__.'/connection.php');

if(settings($conn, 'language') == "sk" || settings($conn, 'language') == "de") { $button_style = "btn-xxl-wide"; } else { $button_style = "btn-xxl"; }

global $lang;
// Time Zone Settings for PHP
//date_default_timezone_set("Europe/Dublin"); // You can set Timezone Manually and uncomment this line and comment out following line
date_default_timezone_set(settings($conn, 'timezone'));

// this function is deprecated --- prepare mysql statement
function mysqli_prep($value) {
	$magic_quotes_active = get_magic_quotes_gpc();
	$new_enough_php = function_exists("mysqli_real_escape_string");
	//if php 4.3.0 or highre
	if($new_enough_php) {
		//undo magic quotes effect so that real escape sting can do the work
		if($magic_quotes_active) { $value = stripslashes($value); }
		$value = mysqli_real_escape_string($value);
	} else {		//before php 4.3.0
		// if magic quotes are not on then add slahes
		if(!$magic_quotes_active) { $value = addslashes($value); }
		// if magic quotes are on then slashes already exist
	}
	return $value;
}

function redirect_to($location = NULL) {
	if($location != NULL) {
		header("Location: {$location}");
		exit;
	}
}

function getWeather()
  {
        $file = "./weather_current.json";
        if(file_exists($file))
        {
            $json = file_get_contents($file);
            $weather_data = json_decode($json, true);
            $arr['temp_kelvin']  = $weather_data['main']['temp'];
            $arr['wind_mps']     = $weather_data['wind']['speed'];
            $arr['temp_celsius'] = round($weather_data['main']['temp']-272.15);
            $arr['wind_kms']     = round($weather_data['wind']['speed']*1.609344, 2);
            $arr['sunrise']      = $weather_data['sys']['sunrise'];
            $arr['sunset']       = $weather_data['sys']['sunset'];
            $arr['weather_code'] = $weather_data['weather'][0]['id'];
            $arr['title']        = $weather_data['weather'][0]['main'];
            $arr['description']  = $weather_data['weather'][0]['description'];
            $arr['icon']         = $weather_data['weather'][0]['icon'];
            $arr['location']     = $weather_data['name'];
            $arr['lon']          = $weather_data['coord']['lon'];
            $arr['lat']          = $weather_data['coord']['lat'];
            return $arr;
        }else{
            return 0;
        }
  }

/**
* ShowWeather
*
* Show weather at bottom of page, echos content directly.
* Weather_c is now dependent upon the units specified in the weather_update query.
*
* @param object $conn
*   Database connection
*
*/
function ShowWeather($conn)
{
    $query="select * from weather";
    $result = $conn->query($query);
    $weather = mysqli_fetch_array($result);
    $c_f = settings($conn, 'c_f');

    echo 'Outside: ' .DispTemp($conn,$weather['c']). '&deg;&nbsp;';
    if($c_f==1 || $c_f=='1')
        echo 'F';
    else
        echo 'C';
    $Img='images/' . $weather['img'] . '.png';
    if(file_exists($Img))
        echo '<span><img border="0" width="24" src="' . $Img . '" title="' . $weather['title'] . ' - ' . $weather['description'] . '"></span>';
    echo '<span>' . $weather['title'] . ' - ' . $weather['description'] . '</span>';
}

//ref: http://stackoverflow.com/questions/14721443/php-convert-seconds-into-mmddhhmmss
// Prefix single-digit values with a zero.
function ensure2Digit($number) {
    if($number < 10) {
        $number = '0' . $number;
    }
    return $number;
}


//function to check if night climate time
//ref: http://blog.yiannistaos.com/php-check-if-time-is-between-two-times-regardless-of-date/
function TimeIsBetweenTwoTimes($from, $till, $input) {
    $f = DateTime::createFromFormat('H:i:s', $from);
    $t = DateTime::createFromFormat('H:i:s', $till);
    $i = DateTime::createFromFormat('H:i:s', $input);
    if ($f > $t) $t->modify('+1 day');
	return ($f <= $i && $i <= $t) || ($f <= $i->modify('+1 day') && $i <= $t);
}

// Convert seconds into months, days, hours, minutes, and seconds in number formate i.e 00:01:18:11:32
function secondsToTime($ss) {
    $s = ensure2Digit($ss%60);
    $m = ensure2Digit(floor(($ss%3600)/60));
    $h = ensure2Digit(floor(($ss%86400)/3600));
    $d = ensure2Digit(floor(($ss%2592000)/86400));
    $M = ensure2Digit(floor($ss/2592000));

    return "$M:$d:$h:$m:$s";
}

// Convert seconds into months, days, hours, minutes, and second ie. 1 days 18 hours 11 minutes 32 seconds
function secondsToWords($seconds)
{
    $ret = "";
    /*** get the days ***/
    $days = intval(intval($seconds) / (3600*24));
    if($days> 0)
    {
        $ret .= "$days days ";
    }
    /*** get the hours ***/
    $hours = (intval($seconds) / 3600) % 24;
    if($hours > 0)
    {
        $ret .= "$hours hours ";
    }
    /*** get the minutes ***/
    $minutes = (intval($seconds) / 60) % 60;
    if($minutes > 0)
    {
        $ret .= "$minutes minutes ";
    }
    /*** get the seconds ***/
    $seconds = intval($seconds) % 60;
    if ($seconds > 0) {
        $ret .= "$seconds seconds";
    }
    return $ret;
}

//function to search inside array ref: http://forums.phpfreaks.com/topic/195499-partial-text-match-in-array/
function searchArray($search, $array) {
    foreach($array as $key => $value) {
        if (stristr($value, $search)) {
			return $key;
        }
    }
    return false;
}

// Return realy ip address of visitor
function get_real_ip() {
    if (isset($_SERVER["HTTP_CLIENT_IP"])){return $_SERVER["HTTP_CLIENT_IP"];}
    elseif (isset($_SERVER["HTTP_X_FORWARDED_FOR"])){return $_SERVER["HTTP_X_FORWARDED_FOR"];}
    elseif (isset($_SERVER["HTTP_X_FORWARDED"])){return $_SERVER["HTTP_X_FORWARDED"];}
    elseif (isset($_SERVER["HTTP_FORWARDED_FOR"])){return $_SERVER["HTTP_FORWARDED_FOR"];}
    elseif (isset($_SERVER["HTTP_FORWARDED"])){return $_SERVER["HTTP_FORWARDED"];}
    else{ return $_SERVER["REMOTE_ADDR"];}
}

// Return Systems setting from settings table function
function settings($db, $svalue){
	$rValue = "";
	$query="SELECT * FROM system limit 1;";
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result))
    {
        if(isset($row[$svalue]))
            $rValue = $row[$svalue];
    }
	return $rValue;
}

// Return Systems setting from settings table function
function theme($db, $id, $svalue){
        $rValue = "";
        $query="SELECT * FROM theme WHERE id = {$id} LIMIT 1;";
        $result = $db->query($query);
        if ($row = mysqli_fetch_array($result))
    {
        if(isset($row[$svalue]))
            $rValue = $row[$svalue];
    }
        return $rValue;
}

// Return MySensors Logs from gateway_log function
function gw_logs($db, $value){
	$rValue = "";
	$query = ("SELECT * FROM gateway_logs order by id desc limit 1;");
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result)){	$rValue = $row[$value];	}
	return $rValue;
}

// Return MySensors Setting from gateway table function
function gw($db, $value){
	$rValue = "";
	$query = ("SELECT * FROM gateway order by id asc limit 1;");
	$result = $db->query($query);
	if ($row = mysqli_fetch_array($result)){	$rValue = $row[$value];	}
	return $rValue;
}

//get contents of and url
function url_get_contents ($Url) {
    if (!function_exists('curl_init')){
        die('CURL is not installed!');
    }
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $Url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $output = curl_exec($ch);
    curl_close($ch);
    return $output;
}


//Return Unique ID for Record Purpose
function UniqueMachineID($salt) {
	//$salt = exec ("cat /proc/cpuinfo | grep Serial | cut -d ' ' -f 2");
	$uuid = exec("sudo blkid -o value -s UUID");
	return md5($salt.md5($uuid));
}


//Get full URL ref: https://stackoverflow.com/questions/14912943/how-to-print-current-url-path
function get_current_url($strip = true) {
    static $filter, $scheme, $host;
    if (!$filter) {
        // sanitizer
        $filter = function($input) use($strip) {
            $input = trim($input);
            if ($input == '/') {
                return $input;
            }
            // add more chars if needed
            $input = str_ireplace(["\0", '%00', "\x0a", '%0a', "\x1a", '%1a'], '',
                rawurldecode($input));
            // remove markup stuff
            if ($strip) {
                $input = strip_tags($input);
            }
            // or any encoding you use instead of utf-8
            $input = htmlspecialchars($input, ENT_QUOTES, 'utf-8');

            return $input;
        };
        $host = $_SERVER['SERVER_NAME'];
        $scheme = isset($_SERVER['REQUEST_SCHEME']) ? $_SERVER['REQUEST_SCHEME'] : ('http'. (($_SERVER['SERVER_PORT'] == '443') ? 's' : ''));
    }
    return sprintf('%s://%s%s', $scheme, $host, $filter($_SERVER['REQUEST_URI']));
}

/**
* DispTemp
*
* Convert the temp, if necessary, to Fahrenheit.
*   All database records are expected to be in Celsius.
*
* @param object $conn
*   Database connection
* @param int $C
*   Degrees in C
*
* @return int
*   Degrees in C or F
*/
function DispTemp($conn,$C)
{
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1')
    {
        return round(($C*9/5)+32,1);
    }
    return round($C,1);
}
/**
* TempToDB
*
* Convert the temp from the UI, either C or F, to Celsius for storage.
*   All database records are expected to be in Celsius.
*
* @param object $conn
*   Database connection
* @param int $T
*   Degrees in C/F, from UI
*
* @return int
*   Degrees in C
*/
function TempToDB($conn,$T){
    $c_f = settings($conn, 'c_f');
    if($c_f==1 || $c_f=='1'){
        return round(($T-32)*5/9,1);
    }
    return round($T,1);
}

// Conversion functions as above but with sensor_type for none temperature sensors
function DispSensor($conn,$C,$sensor_type)
{
        if($sensor_type==1 || $sensor_type=='1') {
                $c_f = settings($conn, 'c_f');
                if($c_f==1 || $c_f=='1')
                {
                        return round(($C*9/5)+32,1);
                }
                return round($C,1);
        } else {
                return round($C,1);
        }
}

function SensorToDB($conn,$T,$sensor_type){
        if($sensor_type==1 || $sensor_type=='1') {
                $c_f = settings($conn, 'c_f');
                if($c_f==1 || $c_f=='1'){
                        return round(($T-32)*5/9,1);
                }
                return round($T,1);
        } else {
                return round($T,1);
        }
}

// Get units character to be used with sensor readings
function SensorUnits($conn, $sensor_type_id){
        $rUnits = '';
        $query="SELECT `units` FROM `sensor_type` WHERE `id` = '{$sensor_type_id}' limit 1;";
        $result = $conn->query($query);
        if ($row = mysqli_fetch_array($result)){ $rUnits = $row['units']; }
        return $rUnits;
}

function my_exec($cmd, $input='')
{
    $proc=proc_open($cmd, array(0=>array('pipe', 'r'), 1=>array('pipe', 'w'), 2=>array('pipe', 'w')), $pipes);
    fwrite($pipes[0], $input);fclose($pipes[0]);
    $stdout=stream_get_contents($pipes[1]);fclose($pipes[1]);
    $stderr=stream_get_contents($pipes[2]);fclose($pipes[2]);
    $rtn=proc_close($proc);
    return array('stdout'=>$stdout,
                 'stderr'=>$stderr,
                 'return'=>$rtn
                );
}

function Convert_CRLF($string, $line_break=PHP_EOL)
{
    $patterns = array(  "/(\r\n|\r|\n)/" );
    $replacements = array(  $line_break );
    $string = preg_replace($patterns, $replacements, $string);
    return $string;
}

function Get_GPIO_List()
{
    $file = "/var/www/st_inc/gpio_pin_list";
    if(file_exists($file))
    {
        // Open the file
        $fp = @fopen($file, 'r');

        // Add each line to an array
        if ($fp) {
            $arr = explode("\n", fread($fp, filesize($file)));
        }
            return $arr;
        }else{
            return 0;
        }

}

function ListLanguages($lang)
{
        $dir    = '/var/www/languages/';
        $fpath = $dir.$lang.'.php';
        if (file_exists($fpath)) { $Content = file_get_contents($fpath); } else { $Content = file_get_contents($dir."en.php"); }
        preg_match_all('/(?<match>.*lang_.*)/', $Content, $Matches);
        $Data = array();
        for($j = 0; $j < count($Matches[1]); $j++){
                $Field = trim($Matches[1][$j]);
                $Data[$j][0] = substr($Field, 12, 2);
                $Data[$j][1] = substr($Field, 20, -2);
        }
return($Data);
}

function getIndicators($conn, $zone_mode, $zone_temp_target)
{
	/****************************************************** */
	//Status indicator animation
	/****************************************************** */

	$zone_mode_main=floor($zone_mode/10)*10;
        $zone_mode_sub=floor($zone_mode%10);

	//not running - temperature reached or not running in this mode
	if($zone_mode_sub == 0){
		//fault or idle
		if(($zone_mode_main == 0)||($zone_mode_main == 10)||($zone_mode_main == 110)){
			$status='';
		}
		//away, holidays or hysteresis
		else if(($zone_mode_main == 40)||($zone_mode_main == 90)||($zone_mode_main == 100)){
			$status='blue';
		}
		//all other modes
		else{
			$status='orange';
		}
        $scactive='bi bi-power';
        $sccolor='';
	}
	//running
	else if($zone_mode_sub == 1 || $zone_mode_sub == 4){
		$status='red';
	}
	//not running - deadband
	else if($zone_mode_sub == 2){
		$status='blueinfo';
						}
	//not running - coop start waiting for the system_controller
	else if($zone_mode_sub == 3){
		$status='blueinfo';
	}

	/****************************************************** */
	//Icon Animation and target temperature
	/****************************************************** */

	 //idle
	if($zone_mode_main == 0){
		$shactive='';
		$shcolor='';
		$target='';     //show no target temperature
	        $scactive='bi bi-power';
        	$sccolor='';
	}
	//fault
	else if($zone_mode_main == 10){
		$shactive='bi-x-circle-fill';
		$shcolor='red';
		$target='';     //show no target temperature
                $scactive='bi bi-x-circle-fill';
                $sccolor='red';
	}
	//frost
	else if($zone_mode_main == 20){
		$shactive='bi-snow';
       		$shcolor='';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
                $scactive='bi bi-fire';
                $sccolor='colorize-red';
	}
	//overtemperature
	else if($zone_mode_main == 30){
		$shactive='bi-thermometer-high';
		$shcolor='red';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//holiday
	else if($zone_mode_main == 40){
		$shactive='bi-send-fill';
		$shcolor='';
		$target='';     //show no target temperature
                $scactive='bi bi-power';
                $sccolor='';
	}
	//nightclimate
	else if($zone_mode_main == 50){
		$shactive='bi-moon-fill';
		$shcolor='';
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}
	//boost
	else if($zone_mode_main == 60){
		$shactive='bi-rocket-takeoff';
		$shcolor='';
                if($zone_mode_sub == 4 || $zone_mode_sub == 7){
                        $target='';     //show no target temperature
                } else {
                        $target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
                }
                if($zone_mode_sub == 1){
                        $scactive='bi bi-fire';
                        $sccolor='red';
                        $status='red';
                } elseif($zone_mode_sub == 6){
                        $scactive='bi bi-snow';
                        $sccolor='blueinfo';
                        $status='blue';
                } elseif($zone_mode_sub == 3){
                        $scactive='bi bi-fire';
                        $sccolor='blueinfo';
                        $status='blue';
                } else {
                        $scactive='bi bi-power';
                        $sccolor='blue';
                        $status='';
                }
	}
        //override
        else if($zone_mode_main == 70){
                $shactive='bi-arrow-repeat';
                $shcolor='';
                // category 2 zone in manual overrride mode
                if($zone_mode_sub >= 4){
                        $target='';     //show no target temperature
                } else {
                        $target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
                }
        }
	//sheduled
/*	else if($zone_mode_main == 80){
		//if not coop start waiting for the system_controller
		if($zone_mode_sub <> 3){
			$shactive='ion-ios-clock-outline';
               	$shcolor='';
		}
		//if coop start waiting for the system_controller
		else{
			$shactive='ion-leaf';
	               	$shcolor='green';
		}
                $target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
	}*/
	//away
	else if($zone_mode_main == 90){
		$shactive='bi-box-arrow-right';
		$shcolor='';
		if($zone_mode_sub == 0){
			$target='';     //show no target temperature
		} else {
			$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
		}
                $scactive='bi bi-power';
                $sccolor='';
	}
	//hysteresis
	else if($zone_mode_main == 100){
		$shactive='bi-hourglass-split';
		$shcolor='';
		$target='';     //show no target temperature
	}
        //Add-On
        else if($zone_mode_main == 110){
                if($zone_mode_sub <= 3){
                        $shactive='bi-clock';
                } else {
                        $shactive='bi bi-power';
                }
                //add-on swtched OFF
                if($zone_mode_sub <= 3){
                        $shcolor='black';
                }
                //add-on switched ON
                else{
                        $shcolor='green';
                }
                if($zone_mode_sub <= 3 && strlen($zone_temp_target) > 0){
                        $target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
                } else {
                        $target='';     //show no target temperature
                }
        }
	//HVAC
        else if($zone_mode_main == 80 || $zone_mode_main == 120){
                if($zone_mode_sub == 1){
                        $scactive='bi bi-fire';
                        $sccolor='red';
                        $status='red';
                } elseif($zone_mode_sub == 6){
                        $scactive='bi bi-snow';
                        $sccolor='blueinfo';
                        $status='blue';
                } elseif($zone_mode_sub == 3){
                        $scactive='bi bi-fire';
                        $sccolor='blueinfo';
                        $status='blue';
                } else {
                        $scactive='bi bi-power';
                        $sccolor='blue';
                        $status='';
                }
 		if($zone_mode_main == 80){
                	//if not coop start waiting for the system_controller
                	if($zone_mode_sub <> 3){
                        	$shactive='bi-clock';
                		$shcolor='';
                	}
                	//if coop start waiting for the system_controller
                	else{
                        	$shactive='bi bi-tree-fill';
                        	$shcolor='green';
                	}
		}
		$target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
        }
        //undertemperature
        else if($zone_mode_main == 130){
                $shactive='bi-thermometer-snow';
                $shcolor='blue';
                $target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
        }
        //manual
        else if($zone_mode_main == 140){
                if($zone_mode_sub == 1){
                        $scactive='bi bi-fire';
                        $sccolor='red';
                        $status='red';
                } elseif($zone_mode_sub == 6){
                        $scactive='bi bi-snow';
                        $sccolor='blueinfo';
                        $status='blue';
                } elseif($zone_mode_sub == 3){
                        $scactive='bi bi-fire';
                        $sccolor='blueinfo';
                        $status='blue';
                } else {
                        $scactive='bi bi-power';
                        $sccolor='blue';
                        $status='';
                }
                $shactive='bi-arrow-repeat';
                $shcolor='';
                $target=number_format(DispTemp($conn,$zone_temp_target),1) . '&deg;';
        }
	//shouldn't get here
	else {
		$shactive='bi-question';
		$shcolor='';
		$target='';     //show no target temperature
	}

	return array('status'=>$status,
 		'shactive'=>$shactive,
       		'shcolor'=>$shcolor,
       		'target'=>$target,
                'scactive'=>$scactive,
                'sccolor'=>$sccolor
       	);
}

function graph_color($numOfSteps, $step) {
    $red = 0;
    $green = 0;
    $blue = 0;
    $h = $step / $numOfSteps;
    $i = floor($h * 6);
    $f = $h * 6 - $i;
    $q = 1 - $f;
    switch($i % 6){
        case 0: $red = 1; $green = $f; $blue = 0; break;
        case 5: $red = $q; $green = 1; $blue = 0; break;
        case 2: $red = 0; $green = 1; $blue = $f; break;
        case 3: $red = 0; $green = $q; $blue = 1; break;
        case 4: $red = $f; $green = 0; $blue = 1; break;
        case 1: $red = 1; $green = 0; $blue = $q; break;
    }
    $color = '#'.sprintf('%02X', $red*255).sprintf('%02X', $green*255).sprintf('%02X', $blue*255);
    return ($color);
}

function purge_tables() {
//Delete Zone tables
$query = "DELETE FROM boost WHERE `purge`= 1 LIMIT 1;
DELETE FROM override WHERE `purge`= 1  LIMIT 1;
DELETE FROM schedule_daily_time_zone WHERE `purge`= 1;
DELETE FROM schedule_night_climat_zone WHERE `purge`= 1;
DELETE FROM controller_zone_logs WHERE `purge`= 1;
DELETE FROM zone_sensors WHERE `purge`= 1;
DELETE FROM zone_relays WHERE `purge`= 1;
DELETE FROM livetemp WHERE `purge`= 1 LIMIT 1;
DELETE FROM zone WHERE `purge`= 1 LIMIT 1;
DELETE FROM schedule_daily_time_zone WHERE `purge`= 1;
DELETE FROM holidays WHERE `purge`= 1;
DELETE FROM schedule_daily_time WHERE `purge`= 1;";
return $query;
}

function service_status($service_name) {
	$rval=my_exec("/bin/systemctl status ".$service_name);
	if($rval['stdout']=='') {
        	$stat='Error';
	} else {
        	$stat='Status: Unknown';
	        $rval['stdout']=explode(PHP_EOL,$rval['stdout']);
        	foreach($rval['stdout'] as $line) {
                	if(strstr($line,'Loaded:')) {
                        	if(strstr($line,'disabled;')) {
                                	$stat='Status: Disabled';
	                        }
        	        }
                	if(strstr($line,'Active:')) {
                        	if(strstr($line,'active (running)')) {
                                	$stat=trim($line);
        	                } else if(strstr($line,'(dead)')) {
                	                $stat='Status: Dead';
                        	}
	                }
        	}
	}
	return $stat;
}

// scan directory and return array of files and folder names
function scan_dir($dir) {
        $ignored = array('.', '..', 'updates.txt');

        $files = array();
        foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored)) continue;
                $files[$file] = filemtime($dir . '/' . $file);
        }
        $files = array_keys($files);
        return ($files) ? $files : false;
}

// scan directory and return array of files sorted by name timestamp
function scan_db_update_dir($dir) {
        $ignored = array('.', '..', 'example.sql');

        $files = array();
        foreach (scandir($dir) as $file) {
                if (in_array($file, $ignored)) continue;
                // create a key value based on the first 6 characters of the filename
                if (ctype_digit(substr($file,0,6))) {
                        $x = intval(substr($file,0,2)) + (intval(substr($file,2,2)) * 31) + (intval(substr($file,4,2)) * 366);
                        $files[$x] = $file;
                }
        }
        // sort ascending by key value
        ksort($files);
        return ($files) ? $files : false;
}

// get the schedule status by zone_id, start/stop times can be sunrise/sunset dependant on flag setting
function get_schedule_status($conn,$zone_id,$holidays_status,$away_status,$int_time_stamp){

        //Set Time variables
        $date = date("Y-m-d",$int_time_stamp);
        $date1 = str_replace('-', '/', $date);
        $today_date = date('Y-m-d',strtotime($date1));
        $tomorrow_date = date('Y-m-d',strtotime($date1 . "+1 days"));
        $yesterday_date = date('Y-m-d',strtotime($date1 . "-1 days"));
        $dow = date('w', strtotime($date));
        $prev_dow = date('w', strtotime($yesterday_date));
        $end_time = $int_time_stamp;

        // get raw data
        $query = "SELECT schedule_daily_time.id AS time_id, schedule_daily_time.start, schedule_daily_time.start_sr, schedule_daily_time.start_ss, schedule_daily_time.start_offset,
        schedule_daily_time.end, schedule_daily_time.end_sr, schedule_daily_time.end_ss, schedule_daily_time.end_offset,
        schedule_daily_time.WeekDays, schedule_daily_time.status AS time_status, schedule_daily_time.sch_name, schedule_daily_time.type AS sch_type
        FROM `schedule_daily_time`, `schedule_daily_time_zone`
        WHERE (schedule_daily_time.id = schedule_daily_time_zone.schedule_daily_time_id) AND schedule_daily_time_zone.status = 1
        AND schedule_daily_time.status = 1 AND zone_id = {$zone_id}";
        if ($away_status == 1) { $query = $query." AND schedule_daily_time.type = 1"; } else { $query = $query." AND schedule_daily_time.type = 0"; }
        if ($holidays_status == 0) {
                $query = $query." AND holidays_id = 0;";
        } else {
                $query = $query." AND holidays_id > 0;";
        }
        $results = $conn->query($query);
        $rowcount=mysqli_num_rows($results);
        if ($rowcount > 0) {
                $sch_status = 0;
                $away_sch = 0;
                $sch_count = $rowcount;
                while ($row = mysqli_fetch_assoc($results)) {
                        // check each schedule for this zone
                        $time_id = $row['time_id'];
                        $start_time = $row['start'];
                        $start_sr = $row['start_sr'];
                        $start_ss = $row['start_ss'];
                        $start_offset = $row['start_offset'];
                        $end_time = $row['end'];
                        $end_sr = $row['end_sr'];
                        $end_ss = $row['end_ss'];
                        $end_offset = $row['end_offset'];
                        $WeekDays = $row['WeekDays'];
                        $time_status = $row['time_status'];
                        $sch_name = $row['sch_name'];
                        $lt = (localtime($int_time_stamp));
                        $seconds_since_midnight = $lt[2] * 3600 + $lt[3] * 60 + $lt[4];
                        $start_sec = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $start_time);
                        sscanf($start_sec, "%d:%d:%d", $hours, $minutes, $seconds);
                        $start_time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
                        $end_sec = preg_replace("/^([\d]{1,2})\:([\d]{2})$/", "00:$1:$2", $end_time);
                        sscanf($end_sec, "%d:%d:%d", $hours, $minutes, $seconds);
                        $end_time_seconds = $hours * 3600 + $minutes * 60 + $seconds;
                        if ($end_time_seconds < $start_time_seconds) { // if end time is less than start time then assume ends on next day
                                //need to check which day we are on now, ie start day or end day
                                if ($start_time_seconds - $seconds_since_midnight >= 0 && $seconds_since_midnight < $end_time_seconds) {
                                        $WeekDays = $WeekDays  & (1 << $dow);
                                        $start_time = $yesterday_date.", ".$row['start'];
                                        $end_time = $today_date.", ".$row['end'];
                                } else { //time now is on end day
                                        $WeekDays = $WeekDays  & (1 << $dow);
                                        $start_time = $today_date.", ".$row['start'];
                                        $end_time = $tomorrow_date.", ".$row['end'];
                                }
                        } else {
                                $WeekDays = $WeekDays  & (1 << $dow);
                                $start_time = $today_date.", ".$row['start'];
                                $end_time = $today_date.", ".$row['end'];
                        }
                        $start_time = strtotime($start_time);
                        $end_time = strtotime($end_time);
                        // use sunrise/sunset if any flags set
                        if ($start_sr == 1 || $start_ss == 1 || $end_sr == 1 || $end_ss == 1) {
                                // get the sunrise and sunset times
                                $query = "SELECT * FROM weather WHERE last_update > DATE_SUB( NOW(), INTERVAL 24 HOUR);";
                                $result = $conn->query($query);
                                $rowcount=mysqli_num_rows($result);
                                if ($rowcount > 0) {
                                        $wrow = mysqli_fetch_array($result);
                                        $sunrise_time = date('H:i:s', $wrow['sunrise']);
                                        $sunset_time = date('H:i:s', $wrow['sunset']);
                                        if ($start_sr == 1 || $start_ss == 1) {
                                                if ($start_sr == 1) { $start_time = strtotime($sunrise_time); } else { $start_time = strtotime($sunset_time); }
                                                $start_time = $start_time + ($start_offset * 60);
                                        }
                                        if ($end_sr == 1 || $end_ss == 1) {
                                                if ($end_sr == 1) { $end_time = strtotime($sunrise_time); } else { $end_time = strtotime($sunset_time); }
                                                $end_time = $end_time + ($end_offset * 60);
                                        }
                                }
                        }
                        $query = "SELECT * FROM schedule_time_temp_offset WHERE schedule_daily_time_id = ".$time_id." AND status = 1 LIMIT 1;";
                        $oresult = $conn->query($query);
                        $rowcount=mysqli_num_rows($oresult);
                        if ($rowcount > 0) {
                                $orow = mysqli_fetch_array($oresult);
                                $low_temp = $orow['low_temperature'];
                                $high_temp = $orow['high_temperature'];
                                $sensors_id = $orow['sensors_id'];
                                $start_time_offset = $orow['start_time_offset'];
                                $query = "SELECT current_val_1 FROM sensors WHERE id = {$sensors_id} LIMIT 1;";
                                $tresult = $conn->query($query);
                                $rowcount=mysqli_num_rows($tresult);
                                if ($rowcount > 0) {
                                        $trow = mysqli_fetch_array($tresult);
                                        $outside_temp = $trow['current_val_1'];
                                        if ($outside_temp >= $low_temp && $outside_temp <= $high_temp) {
                                                $temp_span = $high_temp - $low_temp;
                                                $step_size = $start_time_offset/$temp_span;
                                                $start_time_temp_offset = ($high_temp - $outside_temp) * $step_size;
                                        } elseif ($outside_temp < $low_temp) {
                                                $start_time_temp_offset = $start_time_offset;
                                        } else {
                                                $start_time_temp_offset = 0;
                                        }
                                        $start_time = $start_time - ($start_time_temp_offset * 60);
                                }
                        }
                        $run_time = $end_time - $start_time;
                        $query = "UPDATE schedule_daily_time SET run_time = {$run_time} WHERE id = {$time_id};";
                        $conn->query($query);
                        if ($int_time_stamp > $start_time and $int_time_stamp < $end_time and $WeekDays  > 0 and $time_status == 1) {
                                $sch_status = 1;
                                $away_sch = 1;
                                break; // exit the loop if an active schedule found
                        } else {
                                $sch_status = 0;
                                $away_sch = 0;
                        }
                } // end while ($row = mysqli_fetch_assoc($results))
        } else {
                $sch_name = "";
                $sch_status = 0;
                $time_id = 0;
                $away_sch = 0;
                $sch_count = 0;
        }
        return array('time_id'=>$time_id,
                'sch_status'=>$sch_status,
                'sch_name'=>$sch_name,
                'away_sch'=>$away_sch,
                'end_time'=>$end_time,
                'sch_count'=>$sch_count
        );
}

function boost($conn,$button) {
        global $button_style;

        $query = "SELECT status FROM boost WHERE status = '1' LIMIT 1";
        $result = $conn->query($query);
        $boost_status=mysqli_num_rows($result);
        if ($boost_status ==1) {$boost_status='red';} else {$boost_status='blueinfo';}
        echo '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn" onclick="relocate_page(`boost.php`)">
        <h3 class="buttontop"><small>'.$button.'</small></h3>
        <h3 class="degre"><i class="bi bi-rocket-takeoff"  style="font-size: 1.4rem; color: black"></i></h3>
        <h3 class="status"><small class="statuscircle" id="bs1_1"><i class="bi bi-circle-fill '.$rval['status'].'" style="font-size: 0.55rem"></i></small>
        </h3></button>';

}

function override($conn,$button) {
        global $button_style;

        $query = "SELECT status FROM override WHERE status = '1' LIMIT 1";
        $result = $conn->query($query);
        $override_status=mysqli_num_rows($result);
        if ($override_status==1) {$override_status='red';}else{$override_status='blueinfo';}
        echo '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn" onclick="relocate_page(`override.php`)">
        <h3 class="buttontop"><small>'.$button.'</small></h3>
        <h3 class="degre"><i class="bi bi-arrow-repeat"  style="font-size: 1.4rem; color: black"></i></h3>
        <h3 class="status"><small class="statuscircle" id="bs1_2"><i class="bi bi-circle-fill '.$override_status.'" style="font-size: 0.55rem;"></i></small>
        </h3></button>';
}

function offset($conn,$button) {
	include("model.php");
        global $button_style;

	$start_time_temp_offset = "";
	$offset_status='blueinfo';
        $query = "SELECT id FROM zone;";
        $zresults = $conn->query($query);
        $rowcount=mysqli_num_rows($zresults);
        if ($rowcount > 0) {
		while ($zrow = mysqli_fetch_assoc($zresults)) {
			$zone_id = $zrow['id'];
                        $query = "SELECT schedule, sch_time_id FROM zone_current_state WHERE zone_id = '{$zone_id}' LIMIT 1;";
                        $result = $conn->query($query);
			$zone_current_state = mysqli_fetch_array($result);
                        $schedule = $zone_current_state['schedule'];
                        $time_id = $zone_current_state['sch_time_id'];

                        //get the current zone schedule status
                        $sch_status = $schedule & 0b1;

                	if ($sch_status == 1) {
				$query = "SELECT * FROM schedule_time_temp_offset WHERE schedule_daily_time_id = ".$time_id." AND status = 1 LIMIT 1";
				$oresult = $conn->query($query);
				if (mysqli_num_rows($oresult) > 0) {
					$orow = mysqli_fetch_array($oresult);
					$offset_status='red';
	                                $low_temp = $orow['low_temperature'];
        	                        $high_temp = $orow['high_temperature'];
                	                $sensors_id = $orow['sensors_id'];
                        	        $start_time_offset = $orow['start_time_offset'];
                                	if ($sensors_id == 0) {
                                        	$node_id = 1;
	                                        $child_id = 0;
        	                        } else {
                	                        $query = "SELECT sensor_id, sensor_child_id FROM sensors WHERE id = ".$sensors_id." LIMIT 1;";
                        	                $sresult = $conn->query($query);
                                	        $srow = mysqli_fetch_array($sresult);
                                        	$sensor_id = $srow['sensor_id'];
	                                        $child_id = $srow['sensor_child_id'];
        	                                $query = "SELECT node_id FROM nodes WHERE id = ".$sensor_id." LIMIT 1;";
                	                        $nresult = $conn->query($query);
                        	                $nrow = mysqli_fetch_array($nresult);
                                	        $node_id = $nrow['node_id'];
	                                }
        	                        $query = "SELECT payload FROM `messages_in` WHERE `node_id` = '".$node_id."' AND `child_id` = ".$child_id." ORDER BY `datetime` DESC LIMIT 1;";
                	                $tresult = $conn->query($query);
                        	        $rowcount=mysqli_num_rows($tresult);
                                	if ($rowcount > 0) {
                                        	$trow = mysqli_fetch_array($tresult);
	                                        $outside_temp = $trow['payload'];
        	                                if ($outside_temp >= $low_temp && $outside_temp <= $high_temp) {
                	                                $temp_span = $high_temp - $low_temp;
                        	                        $step_size = $start_time_offset/$temp_span;
                                	                $start_time_temp_offset = "Start -".($high_temp - $outside_temp) * $step_size;
                                                } elseif ($outside_temp < $low_temp ) {
                                                        $start_time_temp_offset = "Start -".$start_time_offset;
                                        	} else {
							$start_time_temp_offset = "Start -0";
						}
                                	}
				}
			}
		}
	}
	echo '<div style="font-style: normal;"
	<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-toggle="modal" href="#offset_setup" data-bs-backdrop="static" data-bs-keyboard="false">
        <h3 class="buttontop"><small>'.$button.'</small></h3>
        <h3 class="degre"><i class="bi bi-clock"  style="font-size: 1.4rem; color: black"></i></h3>
        <h3 class="status"><small class="statuscircle" id="bs1_3"><i class="bi bi-circle-fill '.$offset_status.'" style="font-size: 0.55rem;"></i></small><small class="statuszoon" id="bs2_3">'.$start_time_temp_offset.'&nbsp</small>
        </h3></button></div>';
}

function night_climate($conn,$button) {
        global $button_style;

        $query = "SELECT * FROM schedule_night_climate_time LIMIT 1";
        $results = $conn->query($query);
        $row = mysqli_fetch_assoc($results);
        if ($row['status'] == 1) {$night_status='red';}else{$night_status='blueinfo';}
        echo '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn" onclick="relocate_page(`scheduling.php?nid=0`)">
        <h3 class="buttontop"><small>'.$button.'</small></h3>
        <h3 class="degre"><i class="bi bi-moon-fill" style="font-size: 1.4rem; color: black"></i></h3>
        <h3 class="status"><small class="statuscircle" id="bs1_4"><i class="bi bi-circle-fill '.$night_status.'" style="font-size: 0.55rem;"></i></small>
        </h3></button>';
}

function away($conn,$button) {
        global $button_style;

        $query = "SELECT * FROM system_controller LIMIT 1";
        $result = $conn->query($query);
        $row = mysqli_fetch_array($result);
        $sc_mode = $row['sc_mode'];

        $query = "SELECT * FROM away LIMIT 1";
        $result = $conn->query($query);
        $away = mysqli_fetch_array($result);
        if ($away['status']=='1') { $awaystatus="red"; } elseif ( $away['status']=='0' || $sc_mode == 0) { $awaystatus="blueinfo"; }
        if ($sc_mode != 0 ) {
	        echo '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn" onclick="active_away()">';
	} else {
                echo '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn">';
	}
        echo '<h3 class="buttontop"><small>'.$button.'</small></h3>
        <h3 class="degre"><i class="bi bi-box-arrow-right" style="font-size: 1.4rem; color: black"></i></h3>
        <h3 class="status"><small class="statuscircle" id="bs1_5"><i class="bi bi-circle-fill '.$awaystatus.'" style="font-size: 0.55rem;"></i></small>
        </h3></button>';
}

function holidays($conn,$button) {
	global $button_style;

        $query = "SELECT COUNT(*) AS count_holiday_schedules FROM schedule_daily_time_zone JOIN holidays hs on schedule_daily_time_zone.holidays_id = hs.id WHERE hs.status = 1;";
        $hresult = $conn->query($query);
        $hrow = mysqli_fetch_array($hresult);
        if ($hrow['count_holiday_schedules'] == 0) {
		$holidaystatus = "black";
	} else {
	        $query = "SELECT status FROM holidays WHERE NOW() between start_date_time AND end_date_time AND status = '1' LIMIT 1";
        	$result = $conn->query($query);
        	$holidays_status=mysqli_num_rows($result);
        	if ($holidays_status=='1'){$holidaystatus="red";}elseif ($holidays_status=='0'){$holidaystatus="blueinfo";}
	}
        echo '<button type="button" class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn" onclick="relocate_page(`holidays.php`)">
        <h3 class="buttontop"><small>'.$button.'</small></h3>
        <h3 class="degre"><i class="bi bi-airplane" style="font-size: 1.4rem; color: black"></i></h3>
        <h3 class="status"><small class="statuscircle" style="color:#048afd;" id="bs1_6"><i class="bi bi-circle-fill '.$holidaystatus.'" style="font-size: 0.55rem;"></i></small>
        </h3></button>';
}

function live_temp($conn,$button) {
	global $button_style;

	//query to check live temperature status
	$c_f = settings($conn, 'c_f');
	if ($c_f == 0) { $icon = 'thermostat_30_C.png'; } else { $icon = 'thermostat_30_F.png'; }
	$query = "SELECT active FROM livetemp WHERE active = 1 LIMIT 1";
	$result = $conn->query($query);
	$lt_status=mysqli_num_rows($result);
	if ($lt_status==1) {$lt_status='red';}else{$lt_status='blueinfo';}
	echo '<button class="btn btn-bm-'.theme($conn, settings($conn, 'theme'), 'color').' btn-circle no-shadow '.$button_style.' mainbtn animated fadeIn" data-bs-toggle="modal" href="#livetemperature" data-bs-backdrop="static" data-bs-keyboard="false">
	<h3 class="buttontop"><small>'.$button.'</small></h3>
	<h3 class="degre" style="margin-top:5px;"><img src="images/'.$icon.'" border="0"></h3>
	<h3 class="status"><small class="statuscircle"><i class="bi bi-circle-fill '.$lt_status.'" style="font-size: 0.55rem;"></i></small></h3>
	</button>';
}

function enc_passwd($plain_password) {
	if (file_exists("/sys/class/net/eth0")) {
    		exec("cat /sys/class/net/eth0/address", $key);
	} else {
    		exec("cat /sys/class/net/wlan0/address", $key);
	}
	$hash = openssl_encrypt($plain_password, "AES-128-ECB", $key[0]);
	return($hash);
}

function dec_passwd($e_password) {
        if (file_exists("/sys/class/net/eth0")) {
                exec("cat /sys/class/net/eth0/address", $key);
        } else {
        	exec("cat /sys/class/net/wlan0/address", $key);
        }
	$plain = openssl_decrypt($e_password, "AES-128-ECB", $key[0]);
        return($plain);
}

function page_refresh($conn) {
	$query="SHOW COLUMNS FROM system LIKE 'page_refresh'";
	$result = $conn->query($query);
	$rowcount = mysqli_num_rows($result);
	if ($rowcount > 0) {
        	$query="SELECT page_refresh FROM system LIMIT 1";
	        $result = $conn->query($query);
        	$row = mysqli_fetch_array($result);
	        return($row['page_refresh'] * 1000);
	} else {
		return(5000);
	}
}

function os_info() {
        if (strtolower(substr(PHP_OS, 0, 5)) === 'linux')
        {
            $vars = array();
            $files = glob('/etc/*-release');

            foreach ($files as $file)
            {
                $lines = array_filter(array_map(function($line) {

                    // split value from key
                    $parts = explode('=', $line);

                    // makes sure that "useless" lines are ignored (together with array_filter)
                    if (count($parts) !== 2) return false;

                    // remove quotes, if the value is quoted
                    $parts[1] = str_replace(array('"', "'"), '', $parts[1]);
                    return $parts;

                }, file($file)));

                foreach ($lines as $line)
                    $vars[$line[0]] = $line[1];
            }

        return $vars;
        }
}
?>
