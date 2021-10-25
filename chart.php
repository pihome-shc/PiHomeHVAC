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

//create array of colours for the graphs
$query ="SELECT * FROM sensors ORDER BY id ASC;";
$results = $conn->query($query);
$counter = 0;
$count = mysqli_num_rows($results) + 2; //extra space made for system temperature graph
$sensor_color = array();
while ($row = mysqli_fetch_assoc($results)) {
        $graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
        $sensor_color[$graph_id] = graph_color($count, ++$counter);
}

//check which graphs are enabled as a 6 bit mask
$query ="SELECT mask FROM graphs LIMIT 1;";
$result = $conn->query($query);
$grow = mysqli_fetch_assoc($result);

?>
<?php include("header.php"); ?>
<div id="page-wrapper">
	<br>
        <div class="row">
        	<div class="col-lg-12">
			<div class="panel panel-primary">
                        	<div class="panel-heading">
                                        <i class="fa fa-bar-chart fa-fw"></i> <?php echo $lang['graph']; ?>
                                        <div class="dropdown pull-right">
                                                <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                                        <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                                                </a>
                                                <ul class="dropdown-menu">
 							<li><a href="pdf_download.php?file=displaying_temperature_sensors_graphs.pdf" target="_blank"><i class="fa fa-file fa-fw"></i><?php echo $lang['displaying_temperature_sensors_graphs']; ?></a></li>
                                                </ul>
                                                <div class="btn-group"><?php echo '&nbsp;&nbsp;'.date("H:i"); ?></div>
                                        </div>
        	                </div>
                	        <!-- /.panel-heading -->
 				<div class="panel-body">
                        		<!-- Nav tabs -->
	        			<ul class="nav nav-pills">
        	    					<?php
							if ($grow['mask'] & 0b1) { echo '<button class="btn-lg btn-default btn-circle active" href="#temperature-pills" data-toggle="tab"><i class="fa fa-bar-chart red"></i></i></button>'; }
                                        		if ($grow['mask'] & 0b10) { echo '<button class="btn-lg btn-default btn-circle" href="#humidity-pills" data-toggle="tab"><i class="fa fa-bar-chart blue"></i></i></button>'; }
							if ($grow['mask'] & 0b100) { echo '<button class="btn-lg btn-default btn-circle" href="#add-on-pills" data-toggle="tab"><img src="./images/icons8-light-automation-20.png"/></i></button>'; }
							if ($grow['mask'] & 0b1000) { echo '<button class="btn-lg btn-default btn-circle" href="#controller-pills" data-toggle="tab"><i class="glyphicon glyphicon-leaf green"></i></button>'; }
							if ($grow['mask'] & 0b10000) { echo '<button class="btn-lg btn-default btn-circle" href="#month-pills" data-toggle="tab"><i class="fa fa-area-chart blue"></i></button>'; }
							if ($grow['mask'] & 0b100000) { echo '<button class="btn-lg btn-default btn-circle" href="#battery-pills" data-toggle="tab"><i class="fa fa-battery-full green"></i></button>'; }
							?>
        				</ul>
	        			<!-- Tab panes -->
        				<div class="tab-content">
            						<?php
							if ($grow['mask'] & 0b1) {
								echo '<div class="tab-pane fade in active" id="temperature-pills"><br>';
								include("chart_dailyusage.php");
								echo '</div>';
							}
                                                        if ($grow['mask'] & 0b10) {
								echo '<div class="tab-pane fade" id="humidity-pills"><br>';
								include("chart_humidity_daily.php");
								echo '</div>';
							}
            	                 			if ($grow['mask'] & 0b100) {
								echo '<div class="tab-pane fade" id="add-on-pills"><br>';
								include("chart_addonusage.php");
								echo '</div>';
							}
							if ($grow['mask'] & 0b1000) {
								echo '<div class="tab-pane fade" id="controller-pills"><br>';
								include("chart_controllerlist.php");
								echo '</div>';
							}
							if ($grow['mask'] & 0b10000) {
								echo '<div class="tab-pane fade" id="month-pills"><br>';
								include("chart_monthlyusage.php");
								echo '</div>';
							}
							if ($grow['mask'] & 0b100000) {
								echo '<div class="tab-pane fade" id="battery-pills"><br>';
								include("chart_batteryusage.php");
								echo '</div>';
							}
							?>
	        			</div>
				</div>
        		        <!-- /.panel-body -->
				<div class="panel-footer">
					<?php
					ShowWeather($conn);
					?>
	        	        </div>
				<!-- /.panel-footer -->
	                </div>
			<!-- /.panel-primary -->
		</div>
	       <!-- /.col-lg-12 -->
	</div>
        <!-- /.row -->
</div>
<!-- /#page-wrapper -->
<?php include("footer.php"); ?>
