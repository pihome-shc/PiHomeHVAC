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
<?php  if(isset($message_success)) { echo '<div class="notice notice-success"><i class="bi bi-check-circle icon-lg"></i> ' . $message_success . '</div>' ;}  ?>
<?php  if(isset($error_message)) { echo '<div class="notice notice-danger"> <i class="bi bi-exclamation-triangle icon-lg"></i> ' . $error_message . '</div>' ;}  ?>
<?php  if(isset($error)) { echo '<div class="notice notice-danger"><i class="bi bi-exclamation-triangle icon-lg"></i> ' . $error . '</div>' ;} ?>
<?php  if(isset($alert_message)) { echo '<div class="notice notice-warning"><i class="bi bi-exclamation-triangle icon-lg"></i> ' . $alert_message . '</div>' ;}  ?>
<?php  if(isset($info_message)) { echo '<div class="notice notice-info"><span class="bi bi-info-circle" data-notify="icon"></span> ' . $info_message . '</div>' ;}  ?>
