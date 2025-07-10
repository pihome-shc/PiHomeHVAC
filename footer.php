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
    </div>
    <!-- /#controller_wrapper -->
    <!-- /#sensor_wrapper -->
    <!-- jQuery -->
    <script src="js/jquery.min.js"></script>
    <script type="text/javascript">
        $.ajaxSetup ({
            // Disable caching of AJAX responses
            cache: false
        });
    </script>
    <!-- Bootstrap Core JavaScript -->
    <script src="js/popper.min.js"></script>
    <script src="js/bootstrap.min.js"></script>

    <!-- Metis Menu Plugin JavaScript -->
    <script src="js/plugins/metisMenu/metisMenu.min.js"></script>

	<!-- bootstrap datepicker JavaScript -->
	<script src="js/plugins/datepicker/bootstrap-datetimepicker.js"></script>

    <!-- Custom Theme JavaScript -->
	<script src="js/validator.min.js"></script>
	<script type="text/javascript" src="js/request.js"></script>
	<!-- bootstrap waiting for JavaScript -->
	<script src="js/plugins/waitingfor/bootstrap-waitingfor.min.js"></script>

	<!-- bootstrap slider -->
	<script src="js/plugins/slider/bootstrap-slider.min.js"></script>

        <!-- bootstrap confirmation -->
	<script src="js/plugins/confirm_dialog_button_bootstrap/confirmbutton.js"></script>

        <!-- jquery knob -->
        <script src="js/plugins/knob/jquery.knob.js"></script>

<script>
$(document).ready(function() {
    console.log('Bootstrap ' + $.fn.popover.Constructor.VERSION);
    var maxField = 10; //Input fields increment limitation
    var AddControllerButton = $('.add_controller_button'); //Add button selector
    var controller_wrapper = $('.controler_id_wrapper'); //Input field wrapper
//    var fieldHTML = '<div><input type="text" name="field_name[]" value=""/><a href="javascript:void(0);" class="remove_button"><img src="./images/remove-icon.png"/></a></div>'; //New input field html 

    var controller_HTML = `
		<div class="wrap" id>
			<!-- Zone Controller ID -->
			<div class="form-group" class="control-label" id="controler_id" style="display:block"><label><?php echo $lang['zone_controller_id']; ?></label id="controler_id_label"> <small class="text-muted"><?php echo $lang['zone_controler_id_info'];?></small>
	        	        <input type="hidden" id="selected_controler_id[]" name="selected_controler_id[]" value="<?php echo $zone_controllers[$i]['controller_relay_id']?>"/>
				<div class="entry input-group col-xs-12" id="cnt_id - <?php echo $i ?>">
					<select id="contr_idx" onchange="ControllerIDList(this.options[this.selectedIndex].value)" name="contr_idx" class="form-select" data-bs-error="<?php echo $lang['zone_controller_id_error']; ?>" autocomplete="off">
						<?php if(isset($zone_controllers[$i]["zone_controller_name"])) { echo '<option selected >'.$zone_controllers[$i]["zone_controller_name"].'</option>'; } ?>
						<?php  $query = "SELECT id, name, type FROM relays WHERE type = 0 OR type = 5 ORDER BY id ASC;";
						$result = $conn->query($query);
						echo "<option></option>";
						while ($datarw=mysqli_fetch_array($result)) {
							echo "<option value=".$datarw['id'].">".$datarw['name']."</option>";
						} ?>
					</select>
					<div class="help-block with-errors"></div>
					<span class="input-group-btn">
                                        	<?php if ($i == 0) {
							echo '<button class="btn btn-outline add_controller_button" type="button" data-bs-toggle="tooltip" title="'.$lang['add_controller'].'"><img src="./images/add-icon.png"/></button>';
                                                } else {
							echo '<button class="btn btn-outline remove_controller_button" type="button" data-bs-toggle="tooltip" title="'.$lang['remove_controller'].'"><img src="./images/remove-icon.png"/></button>';
                                                } ?>
					</span>
				</div>
    			</div>
		</div>
		`;

    //Once add controller button is clicked
    $(AddControllerButton).click(function(){
        //Check maximum number of input fields
	var x = document.getElementById("controller_count").value
        var temp_HTML = controller_HTML.replace(/controler_idx/g, "controler_id".concat(x));
        temp_HTML = temp_HTML.replace(/contr_idx/g, "contr_id".concat(x));
        if(x < maxField){ 
            $(controller_wrapper).append(temp_HTML); //Add field html
            x++; //Increment field counter
	    document.getElementById("controller_count").value = x;
            //enable a tooltip for this addition
            $('[data-bs-toggle="tooltip"]').tooltip({
                trigger : 'hover'
            });
            $('[data-bs-toggle="tooltip"]').on('click', function () {
                $(this).tooltip('hide')
            });
        }
    });

    //Once remove controller button is clicked
    $(controller_wrapper).on('click', '.remove_controller_button', function(e){
	var x = document.getElementById("controller_count").value
        e.preventDefault();
        x--; //Decrement field counter
        document.getElementById("controller_count").value = x;
        $(this).parents('.wrap:first').remove(); //Remove field html
    });

    var AddSensorButton = $('.add_sensor_button'); //Add button selector
    var sensor_wrapper = $('.sensor_id_wrapper'); //Input field wrapper
    var sensor_HTML = `
		<div class="wrap" id>
			<!-- Sensor ID -->
			<div class="form-group" class="control-label" id="sensor_idx" style="display:block"><label id="sensor_idx_label_1"><?php echo $lang['secondary_temperature_sensor']; ?></label> <small class="text-muted" id="sensor_idx_label_2"><?php echo $lang['zone_sensor_id_info'];?></small>
				<input type="hidden" id="selected_sensors_id[]" name="selected_sensors_id[]" value="<?php echo $zone_sensors[$i]['zone_sensor_id']?>"/>
				<div class="entry input-group col-12" id="sen_id - <?php echo $i ?>">
					<select id="sens_idx" onchange="SensorIDList(this.options[this.selectedIndex].value, s_index)" name="sens_idx" class="form-select" data-bs-error="<?php echo $lang['zone_temp_sensor_id_error']; ?>" autocomplete="off">
                                                <?php if(isset($zone_sensors[$i]["zone_sensor_name"])) { echo '<option selected >'.$zone_sensors[$i]["zone_sensor_name"].'</option>'; } ?>
                                                <?php  if ($i == 0) {
							$query = "SELECT id, name, sensor_type_id FROM sensors WHERE sensor_type_id = 1 ORDER BY id ASC;";
						} else {
                                                        $query = "SELECT id, name, sensor_type_id FROM sensors WHERE sensor_type_id = 1 AND fail_timeout > 0 ORDER BY id ASC;";
						}
                                                $result = $conn->query($query);
                                                echo "<option></option>";
                                                while ($datarw=mysqli_fetch_array($result)) {
                                                        echo "<option value=".$datarw['id'].">".$datarw['name']."</option>";
                                                } ?>
 					</select>
					<div class="help-block with-errors"></div>
					<span class="input-group-btn">
						<?php if ($i == 0) {
							echo '<button class="btn btn-outline add_sensor_button" type="button" data-bs-toggle="tooltip" title="'.$lang['add_sensor'].'"><img src="./images/add-icon.png"/></button>';
						} else {
							echo '<button class="btn btn-outline remove_sensor_button" type="button" data-bs-toggle="tooltip" title="'.$lang['remove_sensor'].'"><img src="./images/remove-icon.png"/></button>';
						} ?>
					</span>
				</div>
			</div>
		</div>
                `;

    //Once add sensor button is clicked
    $(AddSensorButton).click(function(){
        //Check maximum number of input fields
        var x = document.getElementById("sensor_count").value
        var temp_HTML = sensor_HTML.replace(/sensor_idx/g, "sensor_id".concat(x));
        temp_HTML = temp_HTML.replace(/sens_idx/g, "sens_id".concat(x));
        temp_HTML = temp_HTML.replace(/s_index/g, x);
        if(x < maxField){
            $(sensor_wrapper).append(temp_HTML); //Add field html
            x++; //Increment field counter
            document.getElementById("sensor_count").value = x;
            //enable a tooltip for this addition
            $('[data-bs-toggle="tooltip"]').tooltip({
                trigger : 'hover'
            });
            $('[data-bs-toggle="tooltip"]').on('click', function () {
                $(this).tooltip('hide')
            });
        }
    });

    //Once sensor controller button is clicked
    $(sensor_wrapper).on('click', '.remove_sensor_button', function(e){
        var x = document.getElementById("sensor_count").value
        e.preventDefault();
        x--; //Decrement field counter
        document.getElementById("sensor_count").value = x;
        $(this).parents('.wrap:first').remove(); //Remove field html
    });
});

$(document).ready(function() {
//delete record 
$('#confirm-delete').on('show.bs.modal', function(e) {
    $(this).find('.btn-ok').attr('href', $(e.relatedTarget).data('href'));
});

//Automatically close alert message  after 5 seconds
window.setTimeout(function() {
    $(".alert").fadeTo(1500, 0).slideUp(500, function(){
        $(this).remove(); 
    });
}, 10000);

//load homelist or onetouch depending on value of page_link set in home.php
<?php if (strtok($_SERVER["REQUEST_URI"], '?') == '/home.php'){ ?>
        var x = document.getElementById("page_link").value;
        $(document).ready(function(){
                $.get(x.concat('.php'), function(output) {
                        $('#'.concat(x)).html(output).fadeIn(50);
                });
        });
<?php } ?>

<?php if ($_SERVER['REQUEST_URI'] == '/schedule.php'){ ?>
//load schedulelist.php
$(document).ready(function(){
	$.get('schedulelist.php', function(output) {
		$('#schedulelist').html(output).fadeIn(50);
	});
 });
<?php } ?>

<?php if (strtok($_SERVER["REQUEST_URI"], '?') == '/settings.php'){
	$url_components = parse_url($_SERVER["REQUEST_URI"]);
	parse_str($url_components['query'], $params);
	$s_id = $params['s_id']; ?>
	//load settingslist.php
	$(document).ready(function(){
		$.get('settingslist.php?id=<?php echo $s_id; ?>', function(output) {
        		$('#settingslist').html(output).fadeIn(50);
       		});
	});
<?php } ?>

//load overridelist.php
$('#overridelist').load('overridelist.php');

//load boostlist.php
$('#boostlist').load('boostlist.php');

//load charttlist.php
$('#chart_dailyusage').load('chart_dailyusage.php');

//load holidayslist.php
$('#holidayslist').load('holidayslist.php');

//load holidayslist.php
$('#nightclimatelist').load('nightclimatelist.php');

} );
</script>

<script>
<?php 
if ($_SERVER['SCRIPT_NAME'] == '/scheduling.php'){
	$query = "select * from zone where status = 1;";
	$results = $conn->query($query);
/*	while ($row = mysqli_fetch_assoc($results)) { ?>
		var slider<?php echo $row["id"];?> = document.getElementById("bb<?php echo $row["id"];?>");
		var output<?php echo $row["id"];?> = document.getElementById("val<?php echo $row["id"];?>");
		output<?php echo $row["id"];?>.innerHTML = slider<?php echo $row["id"];?>.value;
		slider<?php echo $row["id"];?>.oninput = function() {
		output<?php echo $row["id"];?>.innerHTML = this.value;
		}
<?php
	}
*/
}
?>

<?php if (($_SERVER['REQUEST_URI'] == '/holiday.php') OR ($_SERVER['SCRIPT_NAME'] == '/holiday.php')){ ?>
    $(".form_datetime").datetimepicker({
        //format: "dd MM yyyy - hh:ii",
		format: "yyyy-mm-dd hh:ii",
        autoclose: true,
        todayBtn: true,
        startDate: "2019-07-09 10:00",
        minuteStep: 10
    });
<?php } ?>
</script>

<script>
<?php if (($_SERVER['SCRIPT_NAME'] == '/scheduling.php') OR ($_SERVER['SCRIPT_NAME'] == '/schedule.php')){ ?>
      // popover
      var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
      var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
      return new bootstrap.Popover(popoverTriggerEl);
    });
<?php } ?>
</script>

<script>
<?php if ($_SERVER['REQUEST_URI'] == '/chart_open.php'){ ?>
        window.location="chart.php";
<?php } ?>
</script>

<?php if ($_SERVER['REQUEST_URI'] == '/chart.php'){include("chart_load.php");} ?>

<?php
//Function to check if email address is valid
function checkEmail($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return false;
        } else {
                return true;
        }
}

//Set user id from user session variable
$user_id = $_SESSION['user_id'];
$query = "select * from user where id = '{$user_id}' LIMIT 1;";
$result = $conn->query($query);
$user_row = mysqli_fetch_array($result);
$email = $user_row['email'];
//Check if email address exit
if (!checkEmail($email)){
        echo "
                <script>
                        $(document).ready(function(){
                        $(\"#user_email_Modal\").modal('show');
                        });
                </script>";
}?>

</body>
</html>
<?php if(isset($conn)) { $conn->close();} ?>

<script>
setInterval(function(){
  $.post('refresh_session.php');
},600000); //refreshes the session every 10 minutes
</script>
