
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

include("header.php");
include("notice.php");
?>

<div id="page-wrapper">
        <div class="row">
        	<div class="col-lg-12">
                	<div id="chart" >
                        	<div class="text-center"><br><br><p><?php echo $lang['please_wait_text']; ?></p>
					<br><br><img src="images/loader.gif">
				</div>
			</div>
                </div>
                <!-- /.col-lg-4 -->
    	</div>
       	 <!-- /.row -->
</div>
<!-- /#page-wrapper -->
<?php include("footer.php"); ?>

