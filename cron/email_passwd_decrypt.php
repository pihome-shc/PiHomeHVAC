#!/usr/bin/php
<?php

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

$query = "SELECT `password` FROM email LIMIT 1;";
$result = $conn->query($query);
if (mysqli_num_rows($result) > 0){
        $row = mysqli_fetch_assoc($result);
        $e_password = $row['password'];
        if (file_exists("/sys/class/net/eth0")) {
                exec("cat /sys/class/net/eth0/address", $key);
        } else {
                exec("cat /sys/class/net/wlan0/address", $key);
        }
        $plain = openssl_decrypt($e_password, "AES-128-ECB", $key[0]);
        echo $plain;
} else {
        echo "";
}
?>
