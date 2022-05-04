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
// Five steps to closing a session
// (i.e. logging out)

// 1. Find the session
session_start();

// 2. Mark the userhistory with logged out time
$query = "SELECT id, s_id FROM userhistory ORDER BY id DESC;";
$results = $conn->query($query);
if (mysqli_num_rows($results) > 0) {
        while ($row = mysqli_fetch_assoc($results)) {
                if (password_verify(session_id(), $row['s_id'])) { // check if this session exists in the userhistory table
                        $query = "UPDATE userhistory SET logged_out = NOW() WHERE id = {$row['id']};";
                        $conn->query($query);
                        break;
                }
        }
}

// 3. Unset all the session variables
$_SESSION = array();

// 4. Destroy the session cookies
if(isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time()-42000, '/');
}
if(isset($_COOKIE['maxair_login'])) {
        setcookie('maxair_login', '', time()-42000, '/');
}

// 5. Destroy the session
session_destroy();

header("Location: index.php?logout=1");
exit;
?>
<?php if(isset($conn)) { $conn->close();} ?>
