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
if(isset($_GET['page_name'])) {
        $page_name = $_GET['page_name'];
} else {
        $page_name = "homelist";
}
?>
<?php include("header.php"); ?>
        <div id="page-wrapper">
<br>
<input type="hidden" id="page_link" value="<?php echo $page_name;?>">
            <div class="row">
                <div class="col-lg-12">
                   	<div id="<?php echo $page_name; ?>" >
				   <div class="text-center"><br><br><p><?php echo $lang['please_wait_text']; ?></p>
				   <br><br><img src="images/loader.gif">
				   <br><br><br><br>
				   </div>
				   </div>
                </div>
                <!-- /.col-lg-4 -->
            </div>
			<!-- /.row -->
	<div class="col-md-8 col-md-offset-2">
	<div class="login-panel-foother">
        <h6><?php echo settings($conn, 'name').' '.settings($conn, 'version')."&nbsp;&nbsp;&nbsp;-&nbsp;&nbsp;&nbsp;".$lang['build']." ".settings($conn, 'build'); ?></h6>
        <br><br>
        <h6><?php echo "&copy;&nbsp;".$lang['copyright']; ?></h6>
	</div>
	</div>

       </div>
        <!-- /#page-wrapper -->
		<?php include("footer.php"); ?>
