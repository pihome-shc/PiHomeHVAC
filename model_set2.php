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

//Software Install Modal
echo '
<div class="modal fade" id="sw_install" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
                <div class="modal-content">
                        <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                                <h5 class="modal-title">'.$lang['software_install'].'</h5>
		                <div class="dropdown pull-right">
                		        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                		<i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
		                        </a>
                		        <ul class="dropdown-menu">
                                		<li><a href="pdf_download.php?file=software_install.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['software_install'].'</a></li>
						<li class="divider"></li>
						<li><a href="pdf_download.php?file=software_install_technical.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['software_install_technical'].'</a></li>
                                                <li class="divider"></li>
                        			<li><a href="pdf_download.php?file=setup_guide_ha_integration.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_guide_ha_integration'].'</a></li>
                         		</ul>
                		</div>
                        </div>
                        <div class="modal-body">
                                <p class="text-muted">'.$lang['install_software_text'].'</p>
                                <div class=\"list-group\">';
                                        $installpath = "/var/www/api/enable_rewrite.sh";
                                        $installname = "Install Apache ReWrite";
                                        if (file_exists("/etc/apache2/mods-available/rewrite.load")) {
                                                $prompt = $lang['re_install'];
                                        } else {
                                                $prompt = $lang['install'];
                                        }
                                        echo '<span class="list-group-item">
                                        <i class="fa fa-terminal fa-2x green"></i> '.$installname.'
                                        <span class="pull-right text-muted small"><button type="button" class="btn btn-default login btn-sm"
                                        onclick="install_software(`'.$installpath.'`)">'.$prompt.'</button></span>
                                        <p class="text-muted">Install ReWrite for Apache Web Server</p></span>';
                                        $path = '/var/www/add_on';
                                        $dir = new DirectoryIterator($path);
                                        foreach ($dir as $fileinfo) {
                                                if ($fileinfo->isDir() && !$fileinfo->isDot()) {
                                                        $installpath = $path."/".$fileinfo->getFilename()."/install.sh";
                                                        if (file_exists($installpath)) {
                                                                $contents = file_get_contents($installpath);
                                                                $searchfor = 'app_name';
                                                                $pattern = preg_quote($searchfor, '/');
                                                                $pattern = "/^.*$pattern.*\$/m";
                                                                if(preg_match_all($pattern, $contents, $matches)){
                                                                        $str = implode("\n", $matches[0]);
                                                                        $name = explode(':',$str)[1];
                                                                } else {
                                                                        $name = $fileinfo->getFilename();
                                                                }
                                                                $searchfor = 'app_description';
                                                                $pattern = preg_quote($searchfor, '/');
                                                                $pattern = "/^.*$pattern.*\$/m";
                                                                if(preg_match_all($pattern, $contents, $matches)){
                                                                        $str = implode("\n", $matches[0]);
                                                                        $description = explode(':',$str)[1];
                                                                } else {
                                                                        $description = '';
                                                                }
                                                                $searchfor = 'service_name';
                                                                $pattern = preg_quote($searchfor, '/');
                                                                $pattern = "/^.*$pattern.*\$/m";
                                                                if(preg_match_all($pattern, $contents, $matches)){
                                                                        $str = implode("\n", $matches[0]);
                                                                        $service_name = explode(':',$str);
                                                                        $rval=my_exec("/bin/systemctl status " . $service_name[1]);
                                                                        if ($rval['stdout']=='') { $installed = 0; } else { $installed = 1; }
                                                                } else {
                                                                        $instaleed = 2;
                                                                }
                                                                echo '<span class="list-group-item">
                                                                <i class="fa fa-terminal fa-2x green"></i> '.$name;
                                                                if ($installed == 0) {
                                                                        echo '<span class="pull-right text-muted small"><button type="button" class="btn btn-default login btn-sm"
                                                                        onclick="install_software(`'.$installpath.'`)">'.$lang['install'].'</button></span>';

                                                                } elseif ($installed == 1) {
                                                                        echo '<span class="pull-right text"><p> '.$lang['already_installed'].'</p></span>';
                                                                } else {
                                                                        echo '<span class="pull-right text"><p> '.$lang['no_installer'].'</p></span>';
                                                                }
                                                                echo '<p class="text-muted">'.$description.'</p></span>';
                                                        }
                                                }
                                        }
                                echo '</div>
                        </div>
                        <!-- /.modal-body -->
                        <div class="modal-footer">
                                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        </div>
                        <!-- /.modal-footer -->
                </div>
                <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
</div>
<!-- /.modal-fade -->
';

// Software Install Add
echo '<div class="modal" id="add_install">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">'.$lang['close'].'</span></button>
          <h4 class="modal-title">'.$lang['installing_sw'].'</h4>
      </div>
      <div class="modal-body">
        <p class="text-muted">'.$lang['installing_sw_info'].'</p>';
        $output = file_get_contents('/var/www/cron/sw_install.txt');
        echo '<textarea id="install_status_text" style="background-color: black;color:#fff;height: 500px; min-width: 100%"></textarea>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary btn-sm" onclick="sw_install_close()">'.$lang['close'].'</button>
      </div>
    </div>
  </div>
</div>';

//MaxAir Code Update
echo '
<div class="modal fade" id="maxair_update" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['maxair_update'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=software_update_technical.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['software_update_technical'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
                        <p class="text-muted"> '.$lang['maxair_update_text'].' </p>';
            echo '</div>
            <div class="modal-footer">
                <input type="button" name="submit" value="'.$lang['update_check'].'" class="btn btn-default login btn-sm" onclick="check_updates()">
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

// backup_image
echo '
<div class="modal fade" id="backup_image" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['pihome_backup'].'</h5>
                <div class="dropdown pull-right">
                        <a class="dropdown-toggle" data-toggle="dropdown" href="#">
                                <i class="fa fa-file fa-fw"></i><i class="fa fa-caret-down"></i>
                        </a>
                        <ul class="dropdown-menu">
                                <li><a href="pdf_download.php?file=setup_database_backup.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_database_backup'].'</a></li>
                                <li class="divider"></li>
                        	<li><a href="pdf_download.php?file=setup_email_notifications.pdf" target="_blank"><i class="fa fa-file fa-fw"></i>'.$lang['setup_email_notifications'].'</a></li>
                        </ul>
                </div>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['pihome_backup_text'].' </p>
			<form data-toggle="validator" role="form" method="post" action="#" id="form-join">
			<div class="form-group" class="control-label"><label>'.$lang['email_address'].'</label> <small class="text-muted">'.$lang['pihome_backup_email_info'].'</small>
			<input class="form-control input-sm" type="text" id="backup_email" name="backup_email" value="'.settings($conn, backup_email).'" placeholder="Email Address to Receive your Backup file">
			<div class="help-block with-errors"></div>
			</div>
			</form>';
echo '     </div>
            <div class="modal-footer">
			<button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <a href="javascript:backup_email_update()"><button class="btn btn-default login btn-sm" data-toggle="confirmation" data-title="'.$lang['update_email_address'].'">'.$lang['save'].'</button> </a>
			<a href="javascript:db_backup()" class="btn btn-default login btn-sm">'.$lang['backup_start'].'</a>
            </div>
        </div>
    </div>
</div>';

//user accounts model
echo '
<div class="modal fade" id="user_setup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['user_accounts'].'</h5>
            </div>
            <div class="modal-body">
			<p class="text-muted"> '.$lang['user_accounts_text'].' </p>';
echo '<div class=\"list-group\">';
$query = "SELECT * FROM user";
$results = $conn->query($query);
while ($row = mysqli_fetch_assoc($results)) {
	$full_name=$row['fullname'];
	$username=$row['username'];
        if ($_SESSION['user_id'] == $row['id']) { $username .= " (Logged On)"; }
        if($row['account_enable'] == 1) {
                $content_msg="You are about to DELETE an ENABLED USER";
        } else {
                $content_msg="You are about to DELETE a CURRENTLY DISABLED USER";
        }
        echo "<div href=\"settings.php?uid=".$row['id']."\"  class=\"list-group-item\"> <i class=\"ionicons ion-person blue\"></i> ".$username."
                <span class=\"pull-right text-muted small\"><em>
                <a href=\"user_accounts.php?uid=".$row["id"]."\"><button class=\"btn btn-default btn-xs login\"><span class=\"ionicons ion-edit\"></span></button>&nbsp</a>";
                if ($_SESSION['user_id'] != $row['id']) {
                        echo "<a href=\"javascript:del_user(".$row["id"].");\"><button class=\"btn btn-danger btn-xs\" data-toggle=\"confirmation\" data-title=".$lang["confirmation"]." data-content=\"$content_msg\"><span class=\"glyphicon glyphicon-trash\"></span></button></a>";
                } else {
                        echo "<button class=\"btn btn-danger btn-xs disabled\"><span class=\"glyphicon glyphicon-trash\"></span></button>";
                }
                echo "</em></span>
        </div>";
}
echo '</div></div>
            <div class="modal-footer">
                <a href="user_accounts.php?uid=0"><button class="btn btn-default login btn-sm">'.$lang['add_user'].'</button></a>
                <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
            </div>
        </div>
    </div>
</div>';

//Setup Database Cleanup intervals
echo '<div class="modal fade" id="db_cleanup" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['db_cleanup'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['db_cleanup_text'].'</p>
                <table class="table table-bordered">
                        <tr>
                                <th class="col-md-2 text-center"><small>'.$lang['table_name'].'</small></th>
                                <th class="col-md-1 text-center"><small>'.$lang['db_cleanup_value'].'</small></th>
                                <th class="col-md-1 text-center"><small>'.$lang['db_cleanup_period'].'</small></th>
                        </tr>';
			$query = "SELECT * FROM db_cleanup LIMIT 1;";
			$result = $conn->query($query);
			$db_row = mysqli_fetch_assoc($result);
                        $query = "SELECT column_name
				FROM INFORMATION_SCHEMA.COLUMNS
				WHERE TABLE_SCHEMA = 'maxair' AND table_name = 'db_cleanup' AND ordinal_position > 3
				ORDER BY ordinal_position;";
			$results = $conn->query($query);
			$x = 0;
                        while ($row = mysqli_fetch_assoc($results)) {
                                $col_name = $row["column_name"];
				$per_int = $db_row[$col_name];
				$pieces = explode(" ", $per_int);
				$period = $pieces[0];
                                $interval = $pieces[1];
                                echo '<tr>
                                        <td>'.$row["column_name"].'</td>
                                        <td><input id="period'.$x.'" type="text" class="pull-left text" style="border: none" name="period'.$x.'"  size="3" value="'.$period.'" placeholder="Period" required></td>
                                        <td><select class="form-control input-sm" type="text" id="ival'.$x.'" name="ival'.$x.'" onchange=set_interval('.$x.')>
                                                <option value="HOUR" ' . ($interval=='HOUR' ? 'selected' : '') . '>'.$lang['HOUR'].'</option>
                                                <option value="DAY" ' . ($interval=='DAY' ? 'selected' : '') . '>'.$lang['DAY'].'</option>
                                                <option value="WEEK" ' . ($interval=='WEEK' ? 'selected' : '') . '>'.$lang['WEEK'].'</option>
                                                <option value="MONTH" ' . ($interval=='MONTH' ? 'selected' : '') . '>'.$lang['MONTH'].'</option>
                                        </select></td>
					<input type="hidden" id="set_interval'.$x.'" name="set_interval_type" value="'.$interval.'">
                                </tr>';
				$x = $x + 1;
                        }
                echo '</table>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_db_cleanup()">
            </div>
        </div>
    </div>
</div>';

//set GitHub Repository location
echo '<div class="modal fade" id="set_repository" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['github_repository'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['github_repository_text'].'</p>';
                $query = "SELECT id, status, name FROM repository;";
                $results = $conn->query($query);
                echo '<div class="form-group" class="control-label"><label>'.$lang['repository_url'].'</label> <small class="text-muted"> (Default Repository is - '.$lang['default_repository'].')</small>
                <select class="form-control input-sm" type="text" id="rep_id" name="rep_id" >';
                if ($results){
                        while ($frow=mysqli_fetch_array($results)) {
                                echo '<option value="'.$frow['id'].'" ' . ($frow['status']==1 ? 'selected' : '') . '>https://github.com/'.$frow['name'].'/PiHomeHVAC.git</option>';
                        }
                }
                echo '</select>
                	<div class="help-block with-errors"></div>
                </div>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['set_default'].'" class="btn btn-default login btn-sm" onclick="set_default()">
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_repository()">
            </div>
        </div>
    </div>
</div>';

//set max cpu temperature
echo '<div class="modal fade" id="max_cpu_temp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
                <h5 class="modal-title">'.$lang['max_cpu_temp'].'</h5>
            </div>
            <div class="modal-body">
                <p class="text-muted">'.$lang['max_cpu_temp_text'].'</p>';
                $query = "SELECT max_cpu_temp FROM system LIMIT 1;";
                $result = $conn->query($query);
		$row = mysqli_fetch_array($result);
                echo '<div class="form-group" class="control-label"><label>'.$lang['temperature'].'</label> <small class="text-muted"> </small>
                <select class="form-control input-sm" type="text" id="m_cpu_temp" name="m_cpu_temp" >';
                for ($x = 40; $x <=  70; $x = $x + 5) {
                	echo '<option value="'.$x.'" ' . ($x==$row['max_cpu_temp'] ? 'selected' : '') . '>'.$x.'&deg;</option>';
                }
                echo '</select>
                        <div class="help-block with-errors"></div>
                </div>
            </div>
                <div class="modal-footer">
                        <button type="button" class="btn btn-primary btn-sm" data-dismiss="modal">'.$lang['close'].'</button>
                        <input type="button" name="submit" value="'.$lang['save'].'" class="btn btn-default login btn-sm" onclick="set_max_cpu_temp()">
            </div>
        </div>
    </div>
</div>';

?>

<script>
$(document).ready(function(){
  $('[data-toggle="popover"]').popover();
});

$('[data-toggle=confirmation]').confirmation({
  rootSelector: '[data-toggle=confirmation]',
  container: 'body'
});
</script>

<script language="javascript" type="text/javascript">
function set_interval(id)
{
 var id_text = id;

 var e = document.getElementById("ival" + id_text);
 var f = document.getElementById("set_interval" + id_text);

 f.value = e.value;
}

function set_default()
{
 document.getElementById("rep_id").value = 1;
}

function sw_install_close()
{
        $('#sw_install').modal('hide');
        $('#add_install').modal('hide');
}
</script>
