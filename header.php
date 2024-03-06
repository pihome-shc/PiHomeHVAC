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
<?php //$start_time = microtime(TRUE);

require_once(__DIR__.'/st_inc/session.php');
confirm_logged_in();
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

$theme = settings($conn, 'theme');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="apple-mobile-web-app-capable" content="yes">
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
	<meta name="HandheldFriendly" content="true" />
    <meta name="description" content="PiHome Smart Heating Control">
    <meta name="author" content="Waseem Javid">
	<link rel="shortcut icon" href="images/favicon.ico" />
	<link rel="apple-touch-icon" href="images/apple-touch-icon.png"/>
    <title><?php echo settings($conn, 'name') ;?></title>

    <!-- Bootstrap Core CSS -->
    <link href="css/bootstrap.min.css" rel="stylesheet">

    <!-- MetisMenu CSS -->
    <link href="css/plugins/metisMenu/metisMenu.min.css" rel="stylesheet">
	    <!-- DataTables CSS -->
    <link href="css/plugins/dataTables.bootstrap.css" rel="stylesheet">
	<!-- extra line added later for responsive test -->
	<link href="css/plugins/dataTables.responsive.css" rel="stylesheet">
	<!-- animate CSS -->
	<link href="css/plugins/animate/animate.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="css/maxair.css" rel="stylesheet">

	<!-- Datetimepicker CSS -->
	<link href="css/plugins/datepicker/bootstrap-datetimepicker.css" rel="stylesheet">

    	<!-- Bootstrap Font Icon CSS -->
        <link href="fonts/bootstrap-icons-1.11.0/bootstrap-icons.css" rel="stylesheet" type="text/css">

	<!-- bootstrap-slider
    <link href="css/plugins/slider/bootstrap-slider.min.css" rel="stylesheet">-->

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
        <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

<script src="js/request.js"></script>
    <script type="text/javascript">
        (function(document,navigator,standalone) {
            // prevents links from apps from oppening in mobile safari
            // this javascript must be the first script in your <head>
            if ((standalone in navigator) && navigator[standalone]) {
                var curnode, location=document.location, stop=/^(a|html)$/i;
                document.addEventListener('click', function(e) {
                    curnode=e.target;
                    while (!(stop).test(curnode.nodeName)) {
                        curnode=curnode.parentNode;
                    }
                    // Condidions to do this only on links to your own app
                    // if you want all links, use if('href' in curnode) instead.
                    if('href' in curnode && ( curnode.href.indexOf('http') || ~curnode.href.indexOf(location.host) ) ) {
                        e.preventDefault();
                        location.href = curnode.href;
                    }
                },false);
            }
        })(document,window.navigator,'standalone');
    </script>
</head>
<body>
<nav class="navbar navbar-light navbar-static-top navbar-expand bg-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" role="navigation" style="margin-bottom: 0;">
	<div class="container-fluid">
            <!-- /.navbar-header -->
             <ul class="navbar-nav ms-auto">
               <li class="nav-item">
                    <a class="nav-link" href="index.php">
                        <i class="bi bi-house-fill <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                    </a>
                </li>
				<?php // Alert icon need some thinking: May be table with list of alerts and one cron job to check if any thing not communicating.
				/*<li class="dropdown">
                    <a class="dropdown-toggle" href="#">
                        <i class="fa fa-exclamation-triangle fa-fw"></i>
                    </a>
                </li>
				*/
				?>
                <li class="nav-item">
                    <a class="nav-link" href="schedule.php">
                        <i class="bi bi-clock <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="chart_open.php">
                        <i class="bi bi-graph-up <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link" data-bs-toggle="modal" href="#weather" data-bs-backdrop="static" data-bs-keyboard="false">
                        <i class="bi bi-cloud-sun-fill <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                    </a>
                </li>

		<?php if ($_SESSION['admin'] == 1) { ?>
                	<!-- /.dropdown-settings -->
                	<li class="nav-item dropdown">
                    		<a class="nav-link dropdown-toggle <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" data-bs-toggle="dropdown" href="#">
                        		<i class="bi bi-gear-fill <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem"></i>
                    		</a>
                    		<ul class="dropdown-menu dropdown-menu-end dropdown-menu-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>">
                        		<li><a class="dropdown-item float-right" href="settings.php?s_id=1"><i class="bi bi-speedometer" style="font-size: 1rem; color: orange;"></i> <?php echo $lang['system_status']; ?> </a></li>
                        		<li class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="settings.php?s_id=2"><i class="bi bi-wrench" style="font-size: 1rem; color: red;"></i> <?php echo $lang['system_maintenance']; ?> </a></li>
                                        <li class="dropdown-divider"></li>
                        		<li><a class="dropdown-item" href="settings.php?s_id=3"><i class="bi bi-gear-wide-connected" style="font-size: 1rem; color: green;"></i> <?php echo $lang['system_configuration']; ?></a></li>
                                        <li class="dropdown-divider"></li>
                                       	<li><a class="dropdown-item" href="settings.php?s_id=4"><i class="bi bi-motherboard" style="font-size: 1rem; color: red;"></i> <?php echo $lang['system_controller_configuration']; ?> </a></li>
                                        <li class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="settings.php?s_id=5"><i class="bi bi-diagram-3-fill" style="font-size: 1rem; color: blue;"></i> <?php echo $lang['node_zone_configuration']; ?> </a></li>
                                        <li class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="settings.php?s_id=6"><i class="bi bi-plug-fill" style="font-size: 1rem; color: green;"></i> <?php echo $lang['device_configuration']; ?> </a></li>
                                        <li class="dropdown-divider"></li>
                     		</ul>
                    	<!-- /.dropdown-settings -->
                	</li>
		<?php } ?>

		    <!-- /.dropdown-user -->
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" data-bs-toggle="dropdown" href="#">
                        <i class="bi bi-person-fill <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>">
                        <li><a class="dropdown-item" href="user_accounts.php"><i class="bi bi-person-fill" style="font-size: 1.2rem;"></i> <?php echo $lang['user_change_password']; ?> </a></li>
                        <li class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="bi bi-box-arrow-right" style="font-size: 1.2rem;"></i> <?php echo $lang['user_logout']; ?></a></li>
                    </ul>
                    <!-- /.dropdown-user -->
                </li>
 
                <?php if ($_SESSION['admin'] == 1 && scan_dir('/var/www/code_updates')) { ?>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="modal" href="#code_update_Modal" data-bs-backdrop="static" data-bs-keyboard="false">
                                <i class="bi bi-download <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                            </a>
                        </li>
                <?php } ?>

                <?php if ($_SESSION['admin'] == 1 && scan_dir('/var/www/database_updates')) { ?>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="modal" href="#database_update_Modal" data-bs-backdrop="static" data-bs-keyboard="false">
                                <i class="bi bi-server <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i>
                            </a>
                        </li>
                <?php } ?>
                <!-- /.dropdown -->
            </ul>
	</div>
</nav>
<?php
$user_id = $_SESSION['user_id'];
$query = "select * from user where id = '{$user_id}' LIMIT 1;";
$result = $conn->query($query);
$row = mysqli_fetch_array($result);
$fullname = $row['fullname'];
?>
<div id="user_email_Modal" class="modal fade">
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header <?php echo theme($conn, $theme, 'text_color')?> bg-<?php echo theme($conn, $theme, 'color') ?>">
                		<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                		<h4 class="modal-title">Missing e-mail address!!!</h4>
            		</div>
            		<div class="modal-body">
				<p>Thank you for using PiHome Smart Heating. Looks like your email address is missing from the system, please enter valid e-mail address to get the latest PiHome updates straight in to your inbox.</p>
                		<div class="form-group" class="control-label"><label>Enter a correctly formatted Email Address for user - '<?php echo $fullname ?>'</label>
                			<input type="email" id="email_add" class="form-control" placeholder="Email Address">
					<div class="help-block with-errors"></div>
            			</div>
            		</div>
			<!-- /.modal-body -->
            		<div class="modal-footer">
                		<input type="button" name="submit" value="<?php echo $lang['save'] ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color')?> login btn-sm" onclick="update_email()">
            		</div>
			<!-- /.modal-footer -->
        	</div>
		<!-- /.modal-content -->
    	</div>
	<!-- /.modal-dialog -->
</div>

<?php
function searchDir($path,&$data){
        if(is_dir($path)){
                $dp=dir($path);
                $ignored = array('.', '..', 'updates.txt');
                // by http://www.manongjc.com/article/1317.html
                while($file=$dp->read()){
                        if (in_array($file, $ignored)) continue;
                        searchDir($path.'/'.$file,$data);
                }
                $dp->close();
        }
        if(is_file($path)){
                if (strcmp($path, '/var/www/code_updates/updates.txt') !== 0 || strcmp($path, '/var/www/database_updates/updates.txt') !== 0) {
                        $data[]=str_replace('/code_updates', '', $path);
                }
        }
}

function getDir($dir){
        $data=array();
        searchDir($dir,$data);
        return   $data;
}

$rval = getDir('/var/www/code_updates');
?>

<div id="code_update_Modal" class="modal fade">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header <?php echo theme($conn, $theme, 'text_color')?> bg-<?php echo theme($conn, $theme, 'color') ?>">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo $lang['github_update']; ?></h4>
                        </div>
                        <div class="modal-body">
                                <?php
                                echo '<p>'.$lang['github_update_info'].'</p>';
                                echo '<ul class="list-group">';
                                $rval = getDir('/var/www/code_updates');
                                foreach($rval as $key => $value) {
                                	echo '<li class="list-group-item" style="height: 25px; border: none">'.$value.'</li>';
                                }
                                ?>
                                </ul>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <?php if (scan_dir('/var/www/database_updates')) {
					echo '<p>'.$lang['pending_database_updates'].'</p>';
				} else {
	                                echo '<button type="button" class="btn btn-bm-'.theme($conn, $theme, 'color').' btn-sm" data-bs-toggle="modal" data-bs-target="#confirm_update_Modal">'.$lang['update_code'].'</button>';
				} ?>
                                <button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color') ?> btn-sm" data-bs-dismiss="modal"><?php echo $lang['close']; ?></button>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>

<div id="confirm_update_Modal" class="modal fade">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header <?php echo theme($conn, $theme, 'text_color')?> bg-<?php echo theme($conn, $theme, 'color') ?>">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo $lang['confirm_update']; ?></h4>
                        </div>
                        <div class="modal-body">
                                <p><?php echo $lang['confirm_update_info']; ?></p>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <input type="button" name="submit" value="<?php echo $lang['yes'] ?>" class="btn btn-danger btn-sm" onclick="code_update()">
                                <button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color')?> btn-sm" data-bs-dismiss="modal"><?php echo $lang['no']; ?></button>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>

<div id="database_update_Modal" class="modal fade">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header <?php echo theme($conn, $theme, 'text_color')?> bg-<?php echo theme($conn, $theme, 'color') ?>">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo $lang['github_db_update']; ?></h4>
                        </div>
                        <div class="modal-body">
                                <p><?php echo $lang['github_db_update_info']; ?></p>
                                <ul class="list-group">
                                <?php
                                $rval = scan_dir('/var/www/database_updates');
                                foreach($rval as $key => $value) {
                                        echo '<li class="list-group-item" style="height: 25px; border: none">/var/www/MySQL_Database/database_updates/'.$value.'</li>';
                                }
                                ?>
                                </ul>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-bm-<?php echo theme($conn, $theme, 'color')?> btn-sm" data-bs-toggle="modal" data-bs-target="#confirm_db_update_Modal"><?php echo $lang['db_update_install']; ?></button>
                                <button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color')?> btn-sm" data-bs-dismiss="modal"><?php echo $lang['close']; ?></button>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>

<div id="confirm_db_update_Modal" class="modal fade">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header <?php echo theme($conn, $theme, 'text_color')?> bg-<?php echo theme($conn, $theme, 'color') ?>">
                                <button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">&times;</button>
                                <h4 class="modal-title"><?php echo $lang['confirm_db_update']; ?></h4>
                        </div>
                        <div class="modal-body">
                                <p><?php echo $lang['confirm_db_update_info']; ?></p>
                                <?php if (scan_dir('/var/www/code_updates')) {
					 echo '<p>'.$lang['confirm_code_update_info'].'</p>';
                                } ?>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
				<?php if (scan_dir('/var/www/code_updates')) {
	                                echo '<input type="button" name="submit" value="'.$lang['db_code_update'].'" class="btn btn-danger btn-sm" onclick="database_update(); code_update();">';
				} ?>
                                <input type="button" name="submit" value="<?php echo $lang['db_only_update'] ?>" class="btn btn-danger btn-sm" onclick="database_update()">
                                <button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color')?> btn-sm" data-bs-dismiss="modal"><?php echo $lang['no']; ?></button></span></p>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>

<?php
$query="select * from weather;";
$result=$conn->query($query);
$rowcount = mysqli_num_rows($result);
if($rowcount > 0) {
        $weather = mysqli_fetch_array($result);
} else {
        $weather['img'] = "04d";
}
$c_f = settings($conn, 'c_f');
if($c_f==1 || $c_f=='1')
{
    $TUnit='F';
    $WUnit='mph';
}
else
{
    $TUnit='C';
    $WUnit='km/s';
}
?>

<div class="modal fade" id="weather" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" >
	<div class="modal-dialog">
        	<div class="modal-content">
            		<div class="modal-header  <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?> bg-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>">
				<button type="button" class="close" data-bs-dismiss="modal" aria-hidden="true">x</button>
                		<h5 class="modal-title"><i class="bi bi-sun <?php echo theme($conn, settings($conn, 'theme'), 'text_color'); ?>" style="font-size: 1.2rem;"></i> <?php echo $weather['location'] ;?> <?php echo $lang['weather']; ?></h5>
            		</div>
            		<div class="modal-body">
				<div class="row">
					<div class="col-10 col-md-10 col-lg-10">
						<h5><span><img border="0" src="images/<?php echo $weather['img'];?>.png" title="<?php echo $weather['title'];?> -
						<?php echo $weather['description'];?>"></span> <span><?php echo $weather['title'];?> -
						<?php echo $weather['description'];?></span></h5>
					</div>
            				<div class="col-7 col-md-6 col-lg-6 wdata">
			        	        <?php echo $lang['sunrise']; ?>: <?php echo date('H:i', $weather['sunrise']);?> <br>
			                	<?php echo $lang['sunset']; ?>: <?php echo date('H:i', $weather['sunset']);?> <br>
				                <?php echo $lang['wind']; ?>: <?php echo $weather['wind_speed'] . '&nbsp;' . $WUnit;?>
						<?php //date_sun_info( int $weather['sunrise'], float $weather['lat'] , float $weather['lon']) ;?>
					</div>
				    	<div class="col-5 col-md-6 col-lg-6">
                				<span class="float-right degrees"><?php echo DispSensor($conn,$weather['c'],1) . '&deg;&nbsp;' . $TUnit;?></span>
	            			</div>
        			</div>
				<br>
				<div class="row">
					<div class="col-xl-12">
						<?php if(filesize('weather_6days.json')>0) { ?>
							<h4 class="text-center"><?php echo $lang['weather_six_day']; ?></h4>
							<div class="list-group">
								<?php
								$weather_api = file_get_contents('weather_6days.json');
								$weather_data = json_decode($weather_api, true);
								//echo '<pre>' . print_r($weather_data, true) . '</pre>';
								foreach($weather_data['list'] as $day => $value) {
        								echo '<a href="weather.php" class="d-flex justify-content-between list-group-item list-group-item-action">
									<span img border="0" width="28" height="28" src="images/'.$value['weather'][0]['icon'].'.png">
									'.$value['weather'][0]['main']." - " .$value['weather'][0]['description'].
									'</span>
									<span class="float-right text-muted small"><em>'.round($value['main']['temp_min'],0)."&deg; - ".round($value['main']['temp_max'],0).'&deg;</em>
									</span></a>';
								}
								?>
							</div>
						<?php } //end of filesize if ?>
						<a href="weather.php" button type="button" class="btn btn-bm-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?> login btn-sm btn-edit"><?php echo $lang['weather_3_hour']; ?></a>
						<button type="button" class="btn btn-primary-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?> btn-sm" data-bs-dismiss="modal"><?php echo $lang['close']; ?></button>
        				</div>
				</div>
			</div>
        	</div>
    	</div>
</div>

