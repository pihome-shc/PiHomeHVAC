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

if(isset($_GET['uid'])) {
        $id = $_GET['uid'];
	$dis = '';
	$req = '';
        if ($id != 0) {
		$title = $lang['edit_user'];
		$info_text = $lang['edit_user_text'];
	        $mode = 1;
	} else {
		$title = $lang['add_user'];
		$info_text = $lang['add_user_text'];
		$mode = 2;
	}
} else {
        $id = $_SESSION['user_id'];
        $dis = 'disabled';
	$req = 'required';
	$title = $lang['user_change_password'];
	$info_text = $lang['change_password_text'];
	$mode = 0;
}
if (isset($_POST['submit'])) {
	//change password
	if ($mode == 0) {
		if ((!isset($_POST['old_pass'])) || (empty($_POST['old_pass']))) {
			$error_message = $lang['old_password_error'];
		}elseif ((!isset($_POST['new_pass'])) || (empty($_POST['new_pass']))) {
			$error_message = $lang['new_password_error'];
		} elseif((!isset($_POST['con_pass'])) || (empty($_POST['con_pass']))) {
			$error_message = $lang['conf_password_error'];
		} elseif($_POST['new_pass'] != $_POST['con_pass']) {
			$error_message = $lang['conf_password_error2'];
		}
		$old_pass = mysqli_real_escape_string($conn,(md5($_POST['old_pass'])));
		$new_pass = mysqli_real_escape_string($conn,(md5($_POST['new_pass'])));
		$con_pass = mysqli_real_escape_string($conn,(md5($_POST['con_pass'])));

		$query = "SELECT * FROM user WHERE id = {$id}";
		$results = $conn->query($query);
		$user_oldpass = mysqli_fetch_assoc($results);
		if ($user_oldpass['password'] != $old_pass ){
			$error_message = 'Your Old Password is Incorrect!';
		} else {
			if ( !isset($error_message) && ($new_pass == $con_pass)) {
				$cpdate= date("Y-m-d H:i:s");
				$query = "UPDATE user SET password = '{$new_pass}', cpdate = '{$cpdate}' WHERE id = '{$id}' LIMIT 1";
				if ($conn->query($query)) {
					$message_success = "Password is successfully changed!!!";
					header("Refresh: 10; url=home.php");
				} else {
					$error = "<p>Password change failed!!!</p>";
					$error .= "<p>".mysqli_error($conn)."</p>";
				}
			}
		}
	//edit user
	} elseif ($mode == 1) {
                if ((!isset($_POST['full_name'])) || (empty($_POST['full_name']))) {
                        $error_message = $lang['fullname_empty'];
                } elseif ((!isset($_POST['user_name'])) || (empty($_POST['user_name']))) {
                        $error_message = $lang['user_empty'];
                } elseif ((!isset($_POST['user_email'])) || (empty($_POST['user_email']))) {
                        $error_message = $lang['email_empty'];
		}
		if($id == $_SESSION['user_id']) {
                        $account_enable = 1; // editting the logged in account
		} else {
			$account_enable = isset($_POST['account_enable']) ? $_POST['account_enable'] : 0;
		}
                $access_level = isset($_POST['access_level']) ? $_POST['access_level'] : 0;
                $fullname = $_POST['full_name'];
                $username = $_POST['user_name'];
                $email = $_POST['user_email'];
                $persist = isset($_POST['persist']) ? $_POST['persist'] : 0;
		$cpdate = $account_date = date("Y-m-d H:i:s");
		if ((!isset($_POST['old_pass'])) || (empty($_POST['old_pass']))) {
			$query = "UPDATE user SET account_enable = {$account_enable},fullname = '{$fullname}',username = '{$username}',email = '{$email}', account_date = '{$account_date}',access_level = {$access_level},persist = {$persist} WHERE id = '{$id}' LIMIT 1";
	                if ($conn->query($query)) {
        	                $message_success = "User account successfully edited!!!";
                	        header("Refresh: 10; url=home.php");
	                } else {
        	                $error = "<p>User account edit failed!!!</p>";
                	        $error .= "<p>".mysqli_error($conn)."</p>";
                	}
		} else {
	                if ((!isset($_POST['new_pass'])) || (empty($_POST['new_pass']))) {
        	                $error_message = $lang['new_password_error'];
                	} elseif((!isset($_POST['con_pass'])) || (empty($_POST['con_pass']))) {
                        	$error_message = $lang['conf_password_error'];
	                } elseif($_POST['new_pass'] != $_POST['con_pass']) {
        	                $error_message = $lang['conf_password_error2'];
                	}
	                $old_pass = mysqli_real_escape_string($conn,(md5($_POST['old_pass'])));
        	        $new_pass = mysqli_real_escape_string($conn,(md5($_POST['new_pass'])));
                	$con_pass = mysqli_real_escape_string($conn,(md5($_POST['con_pass'])));

	                $query = "SELECT * FROM user WHERE id = {$id}";
        	        $results = $conn->query($query);
                	$user_oldpass = mysqli_fetch_assoc($results);
	                if ($user_oldpass['password'] != $old_pass ){
        	                $error_message = 'Your Old Password is Incorrect!';
                	} else {
                        	if ( !isset($error_message) && ($new_pass == $con_pass)) {
                        		$query = "UPDATE user SET account_enable = {$account_enable},fullname = '{$fullname}',username = '{$username}',email = '{$email}', password = '{$new_pass}', cpdate = '{$cpdate}', account_date = '{$account_date}', access_level = {$access_level} WHERE id = '{$id}' LIMIT 1";
        	                        if ($conn->query($query)) {
                	                        $message_success = "User account and Password successfully edited!!!";
                        	                header("Refresh: 10; url=home.php");
                                	} else {
                                        	$error = "<p>User account and Password edit failed!!!</p>";
	                                        $error .= "<p>".mysqli_error($conn)."</p>";
        	                        }
                	        }
                	}
		}
	} else {
                if ((!isset($_POST['full_name'])) || (empty($_POST['full_name']))) {
                        $error_message = $lang['fullname_empty'];
                } elseif ((!isset($_POST['user_name'])) || (empty($_POST['user_name']))) {
                        $error_message = $lang['user_empty'];
                } elseif ((!isset($_POST['user_email'])) || (empty($_POST['user_email']))) {
                        $error_message = $lang['email_empty'];
                } elseif ((!isset($_POST['new_pass'])) || (empty($_POST['new_pass']))) {
                        $error_message = $lang['pass_empty'];
                } elseif((!isset($_POST['con_pass'])) || (empty($_POST['con_pass']))) {
                        $error_message = $lang['conf_password_error'];
                } elseif($_POST['new_pass'] != $_POST['con_pass']) {
                        $error_message = $lang['conf_password_error2'];
                }
		$account_enable = isset($_POST['account_enable']) ? $_POST['account_enable'] : 0;
                $access_level = isset($_POST['access_level']) ? $_POST['access_level'] : 0;
		$password = mysqli_real_escape_string($conn,(md5($_POST['new_pass'])));
                $fullname = $_POST['full_name'];
                $username = $_POST['user_name'];
		$email = $_POST['user_email'];
                $persist = isset($_POST['persist']) ? $_POST['persist'] : "0";
		$cpdate = $account_date = date("Y-m-d H:i:s");
		$query = "INSERT INTO `user`(`account_enable`, `fullname`, `username`, `email`, `password`, `cpdate`, `account_date`, `access_level`, `persist`) VALUES (".$account_enable.",'".$fullname."','".$username."','".$email."','".$password."','".$cpdate."','".$account_date."',".$access_level.",".$persist.");";
                if ($conn->query($query)) {
                        $message_success = "New User account successfully added!!!";
                        header("Refresh: 10; url=home.php");
                } else {
                        $error = "<p>Add User account failed!!!</p>";
                        $error .= "<p>".mysqli_error($conn)."</p>";
                }
	}
}
$query = "SELECT * FROM user WHERE id = {$id} LIMIT 1";
$results = $conn->query($query);
$row = mysqli_fetch_assoc($results);
$aenable = $row['account_enable'];
$fname = $row['fullname'];
$uname = $row['username'];
$email = $row['email'];
$pword = $row['password'];
$aaccount = $row['access_level'];
$persist = $row['persist'];
?>
<?php include("header.php"); ?>
<?php include_once("notice.php"); ?>
<div class="container-fluid">
	<br>
	<div class="row">
                <div class="col-lg-12">
		        <div class="card border-<?php echo theme($conn, $theme, 'color'); ?>">
        		        <div class="card-header <?php echo theme($conn, $theme, 'text_color'); ?> card-header-<?php echo theme($conn, $theme, 'color'); ?>">
					<div class="d-flex justify-content-between">
						<div><?php echo $title; ?></div>
                				<div class="dropdown float-right">
                        				<a class="" data-bs-toggle="dropdown" href="#">
                                				<i class="bi bi-file-earmark-pdf text-white" style="font-size: 1.2rem;"></i>
                        				</a>
                        				<ul class="dropdown-menu dropdown-menu-<?php echo theme($conn, settings($conn, 'theme'), 'color') ?>">
                                				<li><a class="dropdown-item" href="pdf_download.php?file=setup_user_accounts.pdf" target="_blank"><i class="bi bi-file-earmark-pdf"></i>&nbsp<?php echo $lang['setup_user_accounts'] ?></a></li>
                        				</ul>
                				</div>
					</div>
                        	</div>
	                        <!-- /.card-header -->
        	      		<div class="card-body">
                	                <form method="post" action="<?php $PHP_SELF ?>" data-bs-toggle="validator" role="form" >

					<div class="form-group"><label><?php echo $info_text; ?></label>
					<br>
					<p class="text-danger"> <strong>Do not use any special character i.e
					' &nbsp;&nbsp; ` &nbsp;&nbsp; , &nbsp;&nbsp; & &nbsp;&nbsp; ? &nbsp;&nbsp; { &nbsp;&nbsp; } &nbsp;&nbsp; [ &nbsp;&nbsp; ] &nbsp;&nbsp; ( &nbsp;&nbsp; ) &nbsp;&nbsp; - &nbsp;&nbsp; &nbsp;&nbsp; ; &nbsp;&nbsp; ! &nbsp;&nbsp; ~ &nbsp;&nbsp; * &nbsp;&nbsp; % &nbsp;&nbsp; \ &nbsp;&nbsp; |</strong></p>
					</div>

					<?php if($mode != 0) { ?>
        	                        	<div class="row">
                	                		<div class="col-3">
                        	                		<div class="form-check">
									<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox0" name="account_enable" <?php if($aenable == 1 && $mode != 2) { echo 'checked'; } ?> <?php if($id == $_SESSION['user_id']) { echo 'disabled'; } ?> >
									<label class="form-check-label" for="checkbox0"> <?php echo $lang['account_enable']; ?> </label> <br><small class="text-muted"><?php echo $lang['account_enable_info'];?></small>
                                                			<div class="help-block with-errors"></div>
                                        			</div>
							</div>
							<div class="col-3">
                	                        		<div class="form-check">
									<input class="form-check-input form-check-input-<?php echo theme($conn, settings($conn, 'theme'), 'color'); ?>" type="checkbox" value="1" id="checkbox2" name="persist" <?php if($persist == 1 && $mode != 2) { echo 'checked'; } ?> <?php if($_SESSION['access'] > 0) { echo 'disabled'; } ?> >
									<label class="form-check-label" for="checkbox2"> <?php echo $lang['persist']; ?> </label> <br><small class="text-muted"><?php echo $lang['persist_info'];?></small>
                                        	        		<div class="help-block with-errors"></div>
                                        			</div>
							</div>
							<?php if (strpos($uname, "admin") === false) { ?>
                                                        	<div class="col-1">
                                                        		<select id="access_level" name="access_level" class="form-control select2" autocomplete="off">
                                                                		<?php for ($x = 1; $x <= 2; $x++) {
                                                                        		echo '<option value="'.$x.'" ' . ($x==$aaccount ? 'selected' : '') . '>'.$x.'</option>';
	                                                                        } ?>
        	                                                        </select>
                	                                                <div class="help-block with-errors"></div>
                        	                                </div>
                                	                        <div class="col-3">
                                        	                        <div class="form-group"><label><?php echo $lang['access_level'];?><br><small class="text-muted"><?php echo $lang['access_level_info'];?></small></label>
                                                	                <div class="help-block with-errors"></div>
                                                        	        </div>
                                                       		 </div>
							<?php } ?>
						</div>
						<!-- /.row -->
						<br>
					<?php } ?>
					<div class="form-group"><label><?php echo $lang['fullname']; ?></label>
        	        			<input type="text" class="form-control" placeholder="Full Name" value="<?php echo $fname ;?>" id="full_name" name="full_name" data-bs-error="Full Name is Required" autocomplete="off" required <?php echo $dis; ?>>
	        	        	</div>

	        	        	<div class="form-group"><label><?php echo $lang['username']; ?></label>
        	        			<input type="text" class="form-control" placeholder="User Name" value="<?php echo $uname ;?>" id="user_name" name="user_name" data-bs-error="User Name is Required" autocomplete="off" required <?php echo $dis; ?>>
                			</div>

                        	        <div class="form-group"><label><?php echo $lang['email_address']; ?></label>
                                	        <input type="text" class="form-control" placeholder="Email Address" value="<?php echo $email ;?>" id="user_email" name="user_email" data-bs-error="Email Address is Required" autocomplete="off" required <?php echo $dis; ?>>
	                                </div>

					<?php if($mode != 2) {
	        	        		echo '<div class="form-group"><label>'.$lang['old_password'].'</label>'; if($mode == 1) { echo '<small class="text-muted">'.$lang['old_password_info'].'</small>'; }
        	        				echo '<input class="form-control" type="password" class="form-control" placeholder="Old Password" value="" id="old_pass" name="old_pass" data-bs-error="Old Password is Required" autocomplete="off" '.$req.'>
                				<div class="help-block with-errors"></div>
						</div>';
					} ?>

		                	<div class="form-group"><label><?php echo $lang['new_password']; ?></label>
        		        		<input class="form-control" type="password" class="form-control" placeholder="New Password" value="" id="example-progress-bar" name="new_pass" data-bs-error="New Password is Required" autocomplete="off" <?php echo $req; ?>>
                				<div class="help-block with-errors"></div>
					</div>

		                	<div class="form-group"><label><?php echo $lang['confirm_password']; ?></label>
        		        		<input class="form-control" type="password" class="form-control" placeholder="Confirm New Password" value="" id="con_pass" name="con_pass" data-bs-error="Confirm New Password is Required" autocomplete="off" <?php echo $req; ?>>
                				<div class="help-block with-errors"></div>
					</div>
					<a href="home.php"><button type="button" class="btn btn-primary-<?php echo theme($conn, $theme, 'color'); ?> btn-sm"><?php echo $lang['cancel']; ?></button></a>
	        	        	<input type="submit" name="submit" value="<?php echo $lang['save']; ?>" class="btn btn-bm-<?php echo theme($conn, $theme, 'color'); ?> btn-sm">

        	        		</form>
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
	        <!-- /.col -->
	</div>
        <!-- /.row -->
</div>
<!-- /#container-fluid -->
<?php include("footer.php");  ?>
