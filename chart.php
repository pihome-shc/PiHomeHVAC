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

$theme = settings($conn, 'theme');

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
<br>
<div class="container-fluid">
	<div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
        	<div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
			<div class="d-flex justify-content-between">
				<span>
                               		<i class="bi bi-graph-up icon-fw"></i> <?php echo $lang['graph']; ?>
				</span>
				<span>
	                                <a class="dropdown" data-bs-toggle="dropdown" href="#" style="text-decoration: none;">
        	                        	<i class="bi bi-file-earmark-pdf text-white"></i>
                	                </a>
                        	        <ul class="dropdown-menu">
 						<li><a class="dropdown-item" href="pdf_download.php?file=displaying_temperature_sensors_graphs.pdf" target="_blank"><i class="bi bi-file-earmark-pdf icon-fw"></i>&nbsp<?php echo $lang['displaying_temperature_sensors_graphs']; ?></a></li>
                                     	</ul>
                                        <div class="btn-group"><?php echo '&nbsp;&nbsp;'.date("H:i"); ?></div>
				</span>
                       	</div>
       		</div>
               	<!-- /.card-header -->
 		<div class="card-body">
			<div class="row">
				<div class="col-xl-12">
	                        	<!-- Nav tabs -->
    					<ul class="nav nav-tabs" id="myTab">
						<?php
						if ($grow['mask'] & 0b1) {
        						echo '<li class="nav-item">
						            <a href="#temperature-pills" class="nav-link active" data-bs-toggle="tab"><i class="bi bi-graph-up"></i>&nbsp&nbsp'.$lang['temperature'].'</a>
        						</li>';
						}
						if ($grow['mask'] & 0b10) {
        						echo '<li class="nav-item">
            							<a href="#humidity-pills" class="nav-link" data-bs-toggle="tab"><i class="bi bi-graph-up"></i>&nbsp&nbsp'.$lang['humidity'].'</a>
        						</li>';
                                                }
						if ($grow['mask'] & 0b100) {
        						echo '<li class="nav-item">
            							<a href="#add-on-pills" class="nav-link" data-bs-toggle="tab"><i class="bi bi-graph-up"></i>&nbsp;&nbsp'.$lang['add_on'].'</a>
        						</li>';
                                                }
                                                if ($grow['mask'] & 0b1000) {
                                      			echo '<li class="nav-item">
            							<a href="#controller-pills" class="nav-link" data-bs-toggle="tab"><i class="bi bi-list"></i>&nbsp;&nbsp'.$lang['savings'].'</a>
        						</li>';
                                                }
                                                if ($grow['mask'] & 0b10000) {
        						echo '<li class="nav-item">
            							<a href="#month-pills" class="nav-link" data-bs-toggle="tab"><i class="bi bi-graph-up"></i>&nbsp;&nbsp'.$lang['system_controller'].'</a>
        						</li>';
                                                }
                                                if ($grow['mask'] & 0b100000) {
        						echo '<li class="nav-item">
            							<a href="#battery-pills" class="nav-link" data-bs-toggle="tab">&nbsp&nbsp<i class="bi bi-graph-up"></i>&nbsp;&nbsp'.$lang['battery'].'</a>
        					</li>';
                                                }
                                                if ($grow['mask'] & 0b1000000) {
                                                        echo '<li class="nav-item">
                                                                <a href="#min-max-pills" class="nav-link" data-bs-toggle="tab">&nbsp&nbsp<i class="bi bi-graph-up"></i>&nbsp;&nbsp'.$lang['min_max'].'</a>
                                                </li>';
                                                }
					?>
					</ul>
		        		<!-- Tab panes -->
        				<div class="tab-content">
            						<?php
							if ($grow['mask'] & 0b1) {
								echo '<div class="tab-pane fade show active" id="temperature-pills"><br>';
								include("chart_dailyusage.php");
								echo '</div>';
							}
        	                                        if ($grow['mask'] & 0b10) {
								echo '<div class="tab-pane fade active" id="humidity-pills"><br>';
								include("chart_humidity_daily.php");
								echo '</div>';
							}
            	                			if ($grow['mask'] & 0b100) {
								echo '<div class="tab-pane fade active" id="add-on-pills"><br>';
								include("chart_addonusage.php");
								echo '</div>';
							}
							if ($grow['mask'] & 0b1000) {
								echo '<div class="tab-pane fade active" id="controller-pills"><br>';
								include("chart_controllerlist.php");
								echo '</div>';
							}
							if ($grow['mask'] & 0b10000) {
								echo '<div class="tab-pane fade active" id="month-pills"><br>';
								include("chart_monthlyusage.php");
								echo '</div>';
							}
							if ($grow['mask'] & 0b100000) {
								echo '<div class="tab-pane fade active" id="battery-pills"><br>';
								include("chart_batteryusage.php");
								echo '</div>';
							}
                                                        if ($grow['mask'] & 0b1000000) {
                                                                echo '<div class="tab-pane fade active" id="min-max-pills"><br>';
                                                                include("chart_min_max.php");
                                                                echo '</div>';
                                                        }
							?>
	        			</div>
	        		</div>
        			<!-- /.col -->
                	</div>
                        <!-- /.row -->
		</div>
        	<!-- /.card-body -->
		<div class="card-footer card-footer-<?php echo theme($conn, $theme, 'color'); ?>">
			<?php
			ShowWeather($conn);
			?>
	        </div>
		<!-- /.card-footer -->
	</div>
	<!-- /.card -->
</div>
<!-- /#container -->
<?php include("footer.php"); ?>

<script>
// remove all but the first 'active' class from the tab-pane elements after initial loading of the graph data
$(document).ready(function(){
    tabpanes = document.getElementsByClassName("tab-pane");
    for (i = 1; i < tabpanes.length; i++) {
        tabpanes[i].className = tabpanes[i].className.replace(" active", "");
    }
});
</script>

