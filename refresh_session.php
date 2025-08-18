<?php
require_once(__DIR__.'/st_inc/connection.php');
session_start();

// if you have more session-vars that are needed for login, also check
// if they are set and refresh them as well
$query = "SELECT id, username, s_id FROM userhistory ORDER BY id DESC;";
$results = $conn->query($query);
if (mysqli_num_rows($results) > 0) {
	while ($row = mysqli_fetch_assoc($results)) {
        	if (password_verify(session_id(), $row['s_id'])) { // check if this session exists in the userhistory table
                	$query = "SELECT * FROM user WHERE username = '{$row['username']}' LIMIT 1;";
                        $result = $conn->query($query);
			$user_row =  mysqli_fetch_assoc($result);
                        break;
                }
        }
 }
if (isset($user_row['persist'])) {
	if ($user_row['persist'] == 1) {
        	$_COOKIE['PHPSESSID'] = $_COOKIE['PHPSESSID'];
        	$_COOKIE['user_login'] =  $_COOKIE['user_login'];
        	$_COOKIE['pass_login'] = $_COOKIE['pass_login'];
        	$_SESSION['persist'] = $user_row['persist'];
        	$_SESSION['user_id'] = $user_row['id'];
        	$_SESSION['username'] = $user_row['username'];
        	$_SESSION['access'] = $user_row['access_level'];
	} else {
		$query = "UPDATE userhistory SET logged_out = NOW() WHERE id = {$row['id']};";
		$conn->query($query);
        	session_unset();     // unset $_SESSION variable for the run-time
        	session_destroy();   // destroy session data in storage
	}
}
?>
