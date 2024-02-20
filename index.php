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
?>
<?php
//Error reporting on php ON
error_reporting(E_ALL);
//Error reporting on php OFF
//error_reporting(0);

require_once(__DIR__.'/st_inc/session.php');
if (logged_in()) {
	header("Location: home.php");
	exit;
}
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

$theme = settings($conn, 'theme');
$theme_name = explode(' ',theme($conn, $theme, 'name'))[0];
$logo = "maxair_logo_".theme($conn, $theme, 'color').".png";
//$lang = settings($conn, 'language');
//setcookie("PiHomeLanguage", $lang, time()+(3600*24*90));
//require_once (__DIR__.'/languages/'.$_COOKIE['PiHomeLanguage'].'.php');

//check if NetworkManager is running
if(strpos(service_status("NetworkManager.service"), 'active (running)') !== false) {
	$network_manager = 1;
} else {
        $network_manager = 0;
}

if (file_exists("/etc/systemd/system/autohotspot.service") == 1) {
	$no_ap = 1;
	//check id wlan0 interface is flagged as working in AP mode
	$query = "SELECT ap_mode FROM network_settings WHERE interface_type = 'wlan0';";
	$result_set = $conn->query($query);
	if (mysqli_num_rows($result_set) == 1) {
		$found = mysqli_fetch_array($result_set);
		$ap_mode = $found['ap_mode'];
	} else {
        	$ap_mode = 0;
	}
	//check is associated with a local wifi network
        if ($network_manager == 0) {
		//check using iwconfig
		$localSSID = exec("/sbin/iwconfig wlan0 | grep 'ESSID'  ");
		if(strpos($localSSID, 'ESSID:') !== false) {
        		$wifi_connected = 1;
		} else {
        		$wifi_connected = 0;
		}
	} else {
		//check using NetworkManager
		$localSSID = exec("nmcli con show --active | grep wlan0 | awk '{print $1}'");
		if (strlen($localSSID) > 0 && strpos($localSSID, 'HotSpot') === false) {
                        $wifi_connected = 1;
                } else {
                        $wifi_connected = 0;
                }
	}
	//check if ethernet connection is available
	$eth_found = exec("sudo /sbin/ifconfig eth0 | grep 'inet '");
	if(strpos($eth_found, 'inet ') !== false) {
        	$eth_connected = 1;
	} else {
        	$eth_connected = 0;
	}
} else {
	$no_ap = 0;
}
//$wifi_connected = 0;
// start process if data is passed from url  http://192.168.99.9/index.php?user=username&pass=password
// check session id cookie exists
if(isset($_COOKIE["maxair_login"])) $s_id = $_COOKIE["maxair_login"]; else $s_id="";
if ($s_id != "") {
	if(isset($_COOKIE["user_login"])) $u_name = $_COOKIE["user_login"]; else $u_name="";
	if ($u_name != "") {
		// check if this user has a 'persistant' type account
        	$query = "SELECT id, admin_account FROM user WHERE username = '{$u_name}' AND persist = 1 LIMIT 1;";
	        $result = $conn->query($query);
        	if (mysqli_num_rows($result) > 0) {
			$found_user = mysqli_fetch_array($result);
			// check if this user has a session which exists in the userhistory table
			$query = "SELECT s_id FROM userhistory WHERE username = '{$u_name}' ORDER BY id DESC;";
			$results = $conn->query($query);
			if (mysqli_num_rows($results) > 0) {
				while ($row = mysqli_fetch_assoc($results)) {
					if (password_verify($s_id, $row['s_id'])) {
       						// user session id found, restore session
						// Set session variables
       						$_SESSION['user_id'] = $found_user['id'];
						$_SESSION['username'] = $u_name;
						$_SESSION['admin'] = $found_user['admin_account'];
       						$_SESSION['persist'] = 1;
						header('Location:home.php');
						exit;
					}
				}
			}
		}
	}
}

if(($no_ap == 0 || $wifi_connected == 1 || $eth_connected == 1 || $ap_mode == 1) && isset($_GET['user']) && isset($_GET['password'])) {
	$username = $_GET['user'];
	$password = $_GET['password'];
	// perform validations on the form data
	if( (((!isset($_GET['user'])) || (empty($_GET['user']))) && (((!isset($_GET['password'])) || (empty($_GET['password'])))) )){
		$error_message = $lang['user_pass_empty'];
	} elseif ((!isset($_GET['user'])) || (empty($_GET['user']))) {
		$error_message = $lang['user_empty'];
	} elseif((!isset($_GET['password'])) || (empty($_GET['password']))) {
		$error_message = $lang['pass_empty'];
	}

	$username = mysqli_real_escape_string($conn, $_POST['user']);
	$password = mysqli_real_escape_string($conn,(md5($_POST['password'])));
	if ( !isset($error_message) ) {
		// Check database to see if username and the hashed password exist there.
		$query = "SELECT id, username, admin_account, persist FROM user WHERE username = '{$username}' AND password = '{$password}' AND account_enable = 1 LIMIT 1;";
		$result_set = $conn->query($query);
		if (mysqli_num_rows($result_set) == 1) {
			// username/password authenticated
			$found_user = mysqli_fetch_array($result_set);
			// Set username session variable
			$_SESSION['user_id'] = $found_user['id'];
			$_SESSION['username'] = $found_user['username'];
                        $_SESSION['admin'] = $found_user['admin_account'];
                        $_SESSION['persist'] = $found_user['persist'];

			if(!empty($_POST["remember"])) {
				setcookie ("user_login",$_POST["username"],time()+ (10 * 365 * 24 * 60 * 60));
				setcookie ("pass_login",$_POST["password"],time()+ (10 * 365 * 24 * 60 * 60));
			} else {
				if(isset($_COOKIE["user_login"])) {
					// set the expiration date to one hour ago
					setcookie("user_login", "", time() - 3600);
					setcookie("pass_login", "", time() - 3600);
				}
			}
			//$_SESSION['url'] = $_SERVER['REQUEST_URI']; // i.e. "about.php"
			$lastlogin= date("Y-m-d H:i:s");
			$query = "UPDATE userhistory SET lastlogin = '{$lastlogin}' WHERE username = '{$username}' LIMIT 1";
			$result = $conn->query($query);
			// redirect to home page after successfull login
			//redirect_to('home.php');
			if(isset($_SESSION['url'])) {
				$url = $_SESSION['url']; // holds url for last page visited.
			}else {
				$url = "index.php"; // default page for
			}
		redirect_to($url);
		}
	}
}

if (isset($_POST['submit'])) {
	if ($no_ap == 0 || $wifi_connected == 1 || $eth_connected == 1 || $ap_mode == 1) {
		if( (((!isset($_POST['username'])) || (empty($_POST['username']))) && (((!isset($_POST['password'])) || (empty($_POST['password'])))) )){
			$error_message = $lang['user_pass_empty'];
		} elseif ((!isset($_POST['username'])) || (empty($_POST['username']))) {
			$error_message = $lang['user_empty'];
		} elseif((!isset($_POST['password'])) || (empty($_POST['password']))) {
			$error_message = $lang['pass_empty'];
		}

		$username = mysqli_real_escape_string($conn, $_POST['username']);
		$password = mysqli_real_escape_string($conn,(md5($_POST['password'])));

		//get client ip address
		if (!empty($_SERVER["HTTP_CLIENT_IP"])){
			//check for ip from share internet
			$ip = $_SERVER["HTTP_CLIENT_IP"];
		}elseif (!empty($_SERVER["HTTP_X_FORWARDED_FOR"])){
			// Check for the Proxy User
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}else{
			$ip = $_SERVER["REMOTE_ADDR"];
		}
		//set date and time
		$lastlogin= date("Y-m-d H:i:s");

		if ( !isset($error_message) ) {
			// Check database to see if username and the hashed password exist there.
			$query = "SELECT id, username, admin_account, persist FROM user WHERE username = '{$username}' AND password = '{$password}' AND account_enable = 1 LIMIT 1;";
			$result_set = $conn->query($query);
			if (mysqli_num_rows($result_set) == 1) {
				// username/password authenticated
				$found_user = mysqli_fetch_array($result_set);
				// Set username session variable
				$_SESSION['user_id'] = $found_user['id'];
       				$_SESSION['username'] = $found_user['username'];
                               	$_SESSION['admin'] = $found_user['admin_account'];
                               	$_SESSION['persist'] = $found_user['persist'];

				if(!empty($_POST["remember"])) {
					setcookie ("user_login",$_POST["username"],time()+ (10 * 365 * 24 * 60 * 60));
					setcookie ("pass_login",$_POST["password"],time()+ (10 * 365 * 24 * 60 * 60));
                                        setcookie ("maxair_login",session_id(),time()+ (10 * 365 * 24 * 60 * 60));
				} else {
					if(isset($_COOKIE["user_login"])) {
						// set the expiration date to one hour ago
						setcookie("user_login", "", time() - 3600);
						setcookie("pass_login", "", time() - 3600);
					}
				}

				// add entry to database if login is success
				$s_id = password_hash(session_id(), PASSWORD_DEFAULT);
				$query = "INSERT INTO userhistory(username, password, date, audit, ipaddress, s_id) VALUES ('{$username}', '{$password}', '{$lastlogin}', 'Successful', '{$ip}', '{$s_id}')";
				$conn->query($query);
				// Set Language cookie if doesn't exist
				if(!isset($_COOKIE['PiHomeLanguage'])) {
					$query = "SELECT language FROM system;";
					$result = $conn->query($query);
					$row = mysqli_fetch_assoc($result);
					if (mysqli_num_rows($result) == 1) {
						$lang = $row['language'];
						setcookie("PiHomeLanguage", $lang, time()+(3600*24*90));
						header("Location: " . $_SERVER['HTTP_REFERER']);
					}
				}

        			// Jump to secured page
				if(isset($_SESSION['url'])) {
					$url = $_SESSION['url']; // holds url for last page visited.
				}else {
					$url = "index.php"; // default page for
				}
				redirect_to($url);
			} else {
				// add entry to database if login is success
				$query = "INSERT INTO userhistory(username, password, date, audit, ipaddress) VALUES ('{$username}', '{$password}', '{$lastlogin}', 'Failed', '{$ip}')";
				$result = $conn->query($query);
				// username/password was not found in the database
				$error_message = $lang['user_pass_error'];
			}
		}
	} else {
		if(empty($_POST["ap_mode"])) { //set the ssid and password if not working in AP mode
                        if( (((!isset($_POST['ssid'])) || (empty($_POST['ssid']))) && (((!isset($_POST['password'])) || (empty($_POST['password'])))) )){
       	                        $error_message = $lang['ssid_pass_empty'];
               	        } elseif ((!isset($_POST['ssid'])) || (empty($_POST['ssid']))) {
                       	        $error_message = $lang['ssid_empty'];
                        } elseif((!isset($_POST['password'])) || (empty($_POST['password']))) {
       	                        $error_message = $lang['pass_empty'];
               	        }
			$ssid = mysqli_real_escape_string($conn, $_POST['ssid']);
                        $password = mysqli_real_escape_string($conn, $_POST['password']);

			if ($network_manager == 0) { //not using NetworkManager
				$wpa_conf='/etc/wpa_supplicant/wpa_supplicant.conf';
				exec("sudo cat ".$wpa_conf.">myfile1.tmp");
				$reading = fopen('myfile1.tmp', 'r');
				$writing = fopen('myfile2.tmp', 'w');
    				$replaced = false;
    				while (!feof($reading)) {
					$line = fgets($reading);
					if (stristr($line,'ssid="')) {
       						$line = '    ssid="'.$ssid.'"';
       						$line = $line."\n";
        					$replaced = true;
      					}
       	        	                if (stristr($line,'psk="')) {
               	        	                $line = '    psk="'.$password.'"';
                       	        	        $line = $line."\n";
                               	        	$replaced = true;
                                	}
      					fputs($writing, $line);
				}
				fclose($reading); fclose($writing);
    				// might as well not overwrite the file if we didn't replace anything
				if ($replaced) {
					exec("sudo mv myfile2.tmp ".$wpa_conf);
					exec("sudo rm myfile*.tmp");
    				} else {
      					exec("rm myfile*.tmp");
				}
        			exec("sudo reboot");
			} else { //using NetworkManager
				$profile = "/var/www/add_on/Autohotspot/profile.txt";
				$writing = fopen($profile, "w");
				$line = $ssid."\n".$password."\n";
				fputs($writing, $line);
				fclose($writing);
				exec("sudo reboot");
			}
		} else {
			//working in Ap mode set the ap_mode flag in the network settings table
			$query = "SELECT ap_mode FROM network_settings WHERE interface_type = 'wlan0';";
			$result_set = $conn->query($query);
			if (mysqli_num_rows($result_set) == 1) {
        			$found = mysqli_fetch_array($result_set);
	       			if ($found['ap_mode'] == 0) {
					$query = "UPDATE network_settings SET ap_mode = 1 WHERE interface_type = 'wlan0';";
	       	                        $result = $conn->query($query);
				}
			} else {
                                $query = "SELECT MAX( interface_num ) AS max_interface_num FROM `network_settings`;";
                                $result = $conn->query($query);
                                $row = mysqli_fetch_array($result);
                                $max_interface_num = $row['max_interface_num'] + 1;
                                $query = "INSERT INTO `network_settings`(`sync`, `purge`, `primary_interface`, `ap_mode`, `interface_num`, `interface_type`, `mac_address`, `hostname`, `ip_address`, `gateway_address`, `net_mask`, `dns1_address`, `dns2_address`) VALUES ('0', '0', '0', '1', '{$max_interface_num}', 'wlan0', '', '', '', '', '', '', '');";
                                $result = $conn->query($query);
			}
			redirect_to('index.php');
		}
	}
} else { // Form has not been submitted.
	if (isset($_GET['logout']) && $_GET['logout'] == 1) {
		$info_message = $lang['user_logout'];
	}
}
?>

<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php  echo settings($conn, 'name') ;?></title>
        <link href="css/bootstrap.min.css" rel="stylesheet">
        <link href="fonts/bootstrap-icons-1.11.0/bootstrap-icons.css" rel="stylesheet" type="text/css">
        <link href="css/maxair.css" rel="stylesheet">
    </head>

    <body>
        <div class="container-fluid vh-100" style="margin-top:50px">
	    <div class="d-flex justify-content-center"><img src="images/<?php echo $logo; ?>" height="80"></div>
            <div class="" style="margin-top:40px">
                <div class="rounded d-flex justify-content-center">
                    <div class="col-md-4 col-sm-12 shadow-lg p-5 bg-light">
                        <div class="text-center">
                            <?php
                            if ($no_ap == 0 || $wifi_connected == 1 || $eth_connected == 1) {
                                echo '<h3 class="text-'.theme($conn, settings($conn, 'theme'), 'color').'">'.$lang['sign_in'].'</h3>';
                            } else {
                                echo '<h3 class="text-'.theme($conn, settings($conn, 'theme'), 'color').'">'.$lang['wifi_connect'].'</h3>';
                            }
                        echo '</div>
                        <form method="post" action="'.$_SERVER['PHP_SELF'].'" role="form">';
			    include("notice.php");
                            echo '<div class="p-4">';
				if ($no_ap == 0 || $wifi_connected == 1 || $eth_connected == 1 || $ap_mode == 1) {
                                    echo '<div class="input-group mb-3">
                                        <span class="input-group-text bg-'.theme($conn, $theme, 'color').'"><i class="bi bi-person-plus-fill text-white"></i></span>
                                        <input type="text" class="form-control" placeholder="Username" name="username" value="';
				        if(isset($_COOKIE["user_login"])) { echo $_COOKIE["user_login"]; }
				        echo '">
                                    </div>';
                                } else {
                                    $output = array();
                                    echo '<div class="input-group mb-3">
                                        <select class="form-control input-sm" type="text" id="ssid" name="ssid" >';
                                            if ($network_manager == 0) { //not using NetworkManager
                                                $command= "sudo /sbin/iwlist wlan0 scan | grep ESSID";
                                            } else {
                                                $command= "cat /var/www/add_on/Autohotspot/ssid.txt";
                                            }
                                            exec("$command 2>&1 &", $output);
                                            $arrayLength = count($output);
                                            $i = 0;
                                            while ($i < $arrayLength) {
                                                if ($network_manager == 0) {
                                                    preg_match('/"([^"]+)"/', trim($output[$i]), $result);
                                                    echo '<option value="'.$result[1].'">'.$result[1].'</option>';
                                                } else {
                                                    echo '<option value="'.trim($output[$i]).'">'.trim($output[$i]).'</option>';
                                                }
                                                $i++;
                                            }
                                        echo '</select>
                                    </div>';
                                }
                                echo '<div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-'.theme($conn, $theme, 'color').'" id="basic-addon1"><i class="bi bi-key-fill text-white"></i></span>
                                    </div>
                                    <input name="password" type="password" value="" class="input form-control" id="password" placeholder="password" required="true" aria-label="password" aria-describedby="basic-addon1" />
                                    <div class="input-group-append">
                                        <span class="input-group-text" onclick="password_show_hide();">
                                            <i class="bi bi-eye-fill" id="show_eye"></i>
                                            <i class="bi bi-eye-slash-fill d-none" id="hide_eye"></i>
                                        </span>
                                     </div>
                                </div>';
                                if ($no_ap == 0 || $wifi_connected == 1 || $eth_connected == 1 || $ap_mode == 1) {
                                    echo '<div class="form-check">
                                        <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" name="remember" ';
				        if(isset($_COOKIE["user_login"])) { echo 'checked >'; } else {  echo '>'; }
                                        echo '<label class="form-check-label" for="remember">
                                            Remember Me
                                        </label>
                                    </div>
				    <input type="submit" name="submit" value="'.$lang['login'].'" class="btn btn-primary-'.theme($conn, $theme, 'color').' text-center mt-2"/>';
                                } else {
                                    echo '<div class="form-check">
                                        <input class="form-check-input form-check-input-'.theme($conn, settings($conn, 'theme'), 'color').'" type="checkbox" value="1" name="ap_mode">
                                        <label class="form-check-label" for="ap_mode">
                                            AP Mode
                                        </label>
                                    </div>
                                    <input type="submit" name="submit" value="'.$lang['set_reboot'].'" class="btn btn-primary-'.theme($conn, $theme, 'color').' text-center mt-2"/>';
                                }
                            echo '</div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center" style="margin-top:20px">
                <small>';
                    $languages = ListLanguages(settings($conn, 'language'));
                        for ($x = 0; $x <= count($languages) - 1; $x++) {
                            echo '<a class="" style="text-decoration: none;" href="languages.php?lang='.$languages[$x][0].'" title="'.$languages[$x][1].'">'.$languages[$x][1].'</a>';
                            if ($x <= count($languages) - 2) { echo '&nbsp;&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;'; }
                    }
                echo '</small>
            </div>
            <div class="d-flex justify-content-center" style="margin-top:20px">'.settings($conn, 'name').' '.settings($conn, 'version')."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;".$lang['build']." ".settings($conn, 'build').'</div>
	    <div class="d-flex justify-content-center" style="margin-top:10px">&copy;&nbsp;'.$lang['copyright'].'</div>
        </div>';
    ?>
    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>

    <!-- Bootstrap Core JavaScript -->
    <script src="js/bootstrap.min.js"></script>
    <script>
    //Automatically close alert message  after 5 seconds
    window.setTimeout(function() {
        $(".alert").fadeTo(1500, 0).slideUp(500, function(){
            $(this).remove();
        });
    }, 10000);
    </script>

    <script>
    function password_show_hide() {
      var x = document.getElementById("password");
      var show_eye = document.getElementById("show_eye");
      var hide_eye = document.getElementById("hide_eye");
      hide_eye.classList.remove("d-none");
      if (x.type === "password") {
        x.type = "text";
        show_eye.style.display = "none";
        hide_eye.style.display = "block";
      } else {
        x.type = "password";
        show_eye.style.display = "block";
        hide_eye.style.display = "none";
      }
    }
    </script>
    </body>

</html>
<?php if(isset($conn)) { $conn->close();} ?>

