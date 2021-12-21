<?php
#!/usr/bin/php
echo "\033[36m";
echo "\n";
echo "           __  __                             _         \n";
echo "          |  \/  |                    /\     (_)        \n";
echo "          | \  / |   __ _  __  __    /  \     _   _ __  \n";
echo "          | |\/| |  / _` | \ \/ /   / /\ \   | | | '__| \n";
echo "          | |  | | | (_| |  >  <   / ____ \  | | | |    \n";
echo "          |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|    \n";
echo " \033[0m \n";
echo "                \033[45m S M A R T   T H E R M O S T A T \033[0m \n";
echo "\033[31m";
echo "********************************************************\n";
echo "*   Notice Script Version 0.02 Build Date 21/12/2021   *\n";
echo "*   Calls notice.py with an unencryped password as a   *\n";
echo "*   parameter.                                         *\n";
echo "*                                                       *\n";
echo "*   Update on 21/12/2021                               *\n";
echo "*                                 Have Fun - PiHome.eu *\n";
echo "********************************************************\n";
echo " \033[0m \n";

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');
$line = "------------------------------------------------------------------\n";

//Set php script execution time in seconds
ini_set('max_execution_time', 40);
$date_time = date('Y-m-d H:i:s');

echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Script Started \n";
echo $line;

$query = "SELECT `password` FROM email LIMIT 1;";
$result = $conn->query($query);
if (mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $p_password = dec_passwd($row['password']);
        shell_exec("python3 /var/www/cron/notice.py ".$p_password." >/dev/null 2>&1");
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Python Script Finished \n";
} else {
        echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Unable to get encrypted password from the database. \n";
}
echo $line;
echo "\033[36m".date('Y-m-d H:i:s'). "\033[0m - Notice Script Finished \n";
?>
