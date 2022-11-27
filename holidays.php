<?php 
/*
   _____    _   _    _                             
  |  __ \  (_) | |  | |                            
  | |__) |  _  | |__| |   ___    _ __ ___     ___  
  |  ___/  | | |  __  |  / _ \  | |_  \_ \   / _ \ 
  | |      | | | |  | | | (_) | | | | | | | |  __/ 
  |_|      |_| |_|  |_|  \___/  |_| |_| |_|  \___| 

     S M A R T   H E A T I N G   C O N T R O L 

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
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
?>
<?php include("header.php");  ?>
<?php include_once("notice.php"); ?>
<div class="container-fluid">
	<br>
	<div class="row">
        	<div class="col-lg-12">
                	<div id="holidayslist" >
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
<!-- /#container-fluid -->
<?php include("footer.php");  ?>

