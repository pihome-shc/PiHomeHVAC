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
$weather_c = array();
$system_c = array();

$query="select * from messages_in where datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR)";
$result = $conn->query($query);
//create array of pairs of x and y values
while ($row = mysqli_fetch_assoc($result)) {
        $datetime = $row['datetime'];
        $payload = $row['payload'];
        if ($row['node_id'] == 0) {
                $system_c[] = array(strtotime($datetime) * 1000, DispSensor($conn,$payload,1));
        } elseif ($row['node_id'] == 1) {
                $weather_c[] = array(strtotime($datetime) * 1000, DispSensor($conn,$payload,1));
        }
}

// weather table to get sunrise and sun set time
$query="select * from weather";
$result = $conn->query($query);
$weather_row = mysqli_fetch_array($result);
$sunrise = $weather_row['sunrise']* 1000 ;
$sunset = $weather_row['sunset']* 1000 ;

//date_sun_info ( int $time , float $latitude , float $longitude )
//http://php.net/manual/en/function.date-sun-info.php

//check which graphs are enabled as a 6 bit mask
$query ="SELECT mask FROM graphs LIMIT 1;";
$result = $conn->query($query);
$grow = mysqli_fetch_assoc($result);

if ($grow['mask'] & 0b1) {
	// create datasets based on all available sensors
	$querya ="SELECT * FROM sensors WHERE graph_num > 0 AND sensor_type_id = 1 ORDER BY id ASC;";
	$resulta = $conn->query($querya);
	$graph1 = '';
	$graph2 = '';
	$graph3 = '';
        $graph_water = 2; // default graph number for water zone
	while ($row = mysqli_fetch_assoc($resulta)) {
        	// grab the sensor names to be displayed in the plot legend
		$name=$row['name'];
		$id=$row['id'];
	  	$graph_id = $row['sensor_id'].".".$row['sensor_child_id'];
        	$graph_num = $row['graph_num'];
                if(strpos($name, 'Water') !== false) { $graph_water = $graph_num; }
		$query="select * from sensor_graphs where zone_id = {$id};";
        	$result = $conn->query($query);
	        // create array of pairs of x and y values for every zone
        	$graph1_temp = array();
	        $graph2_temp = array();
        	$graph3_temp = array();
	        while ($rowb = mysqli_fetch_assoc($result)) {
        	        if($graph_num == 1) {
                	        $graph1_temp[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
	                } elseif($graph_num == 2) {
        	                $graph2_temp[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
                	} elseif($graph_num == 3) {
                        	$graph3_temp[] = array(strtotime($rowb['datetime']) * 1000, $rowb['payload']);
			}
        	}
	        // create dataset entry using distinct color based on zone index(to have the same color everytime chart is opened)
        	if($graph_num == 1) {
                	$graph1 = $graph1. "{label: \"".$name."\", data: ".json_encode($graph1_temp).", color: '".$sensor_color[$graph_id]."'}, \n";
	        } elseif($graph_num == 2) {
        	        $graph2 = $graph2. "{label: \"".$name."\", data: ".json_encode($graph2_temp).", color: '".$sensor_color[$graph_id]."'}, \n";
	        } elseif($graph_num == 3) {
        	        $graph3 = $graph3. "{label: \"".$name."\", data: ".json_encode($graph3_temp).", color: '".$sensor_color[$graph_id]."'}, \n";
		}
	}
	// add outside weather temperature
	$graph2 = $graph2."{label: \"".$lang['graph_outsie']."\", data: ".json_encode($weather_c).", color: '".graph_color($count, ++$counter)."'}, \n";

	// add CPU temperature
	$graph3 = $graph3."{label: \"".$lang['cpu']."\", data: ".json_encode($system_c).", color: '".graph_color($count, ++$counter)."'}, \n";

	//background-color for system controller on time
	$query="select start_datetime, stop_datetime, type from zone_log_view where status= '1' AND start_datetime > current_timestamp() - interval 24 hour;";
	$query="SELECT start_datetime, stop_datetime, type FROM zone_log_view WHERE start_datetime > current_timestamp() - interval 24 hour;";
	$results = $conn->query($query);
	$count=mysqli_num_rows($results);
	$warn1 = '';
	$warn2 = '';
	while ($row = mysqli_fetch_assoc($results)) {
        	if((--$count)==-1) break;
	        $zone_type=$row['type'];
        	$system_controller_start = strtotime($row['start_datetime']) * 1000;
	        if (is_null($row['stop_datetime'])) {
        	        $system_controller_stop = strtotime("now") * 1000;
	        } else {
        	        $system_controller_stop = strtotime($row['stop_datetime']) * 1000;
	        }
                if(strpos($zone_type, 'Heating') !== false) {
                        if ($graph_water == 2) {
                                $warn1 = $warn1."{ xaxis: { from: ".$system_controller_start.", to: ".$system_controller_stop." }, color: \"#ffe9dc\" },  \n" ;
                        } else {
                                $warn2 = $warn2."{ xaxis: { from: ".$system_controller_start.", to: ".$system_controller_stop." }, color: \"#ffe9dc\" },  \n" ;
                        }
                } elseif((strpos($zone_type, 'Water') !== false) || (strpos($zone_type, 'Immersion') !== false)) {
                        if ($graph_water == 2) {
                                $warn2 = $warn2."{ xaxis: { from: ".$system_controller_start.", to: ".$system_controller_stop." }, color: \"#ffe9dc\" },  \n" ;
                        } else {
                                $warn1 = $warn1."{ xaxis: { from: ".$system_controller_start.", to: ".$system_controller_stop." }, color: \"#ffe9dc\" },  \n" ;
                        }
                }
	}
}

//only show on chart page footer  ?>
<!--[if lte IE 8]><script src="js/plugins/flot/excanvas.min.js"></script><![endif]-->
<!--[if lte IE 8]><script language="javascript" type="text/javascript" src="/js/flot/excanvas.min.js"></script><![endif]-->
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.min.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jshashtable-2.1.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.numberformatter-1.2.3.min.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.time.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.symbol.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.tickrotor.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.axislabels.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.resize.js"></script>
    <script type="text/javascript" src="js/plugins/flot/jquery.flot.tooltip.min.js"></script>
	<script type="text/javascript" src="js/plugins/flot/curvedLines.js"></script>

<script type="text/javascript">

// Create datasets for graphs, and graph markings
<?php if($grow['mask'] & 0b1) { ?>
  var dataset = [ <?php echo $graph1 ?>];
  var wdataset = [ <?php echo $graph2 ?>];
  var hdataset = [ <?php echo $graph3 ?>];
  var markings = [ <?php echo $warn1 ?> ];
  var wmarkings = [ <?php echo $warn2 ?> ];
  var markings_system_controller = [ <?php echo $warn1.$warn2 ?> ];

  // Create Graph 1
  var options_one = {
    xaxis: { mode: "time", timezone: "browser", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839", markings: markings,},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {
	$.plot($("#placeholder"), dataset, options_one);
    $("#placeholder").UseTooltip();
  });

  // Create Graphs 2
  var options_two = {
    xaxis: { mode: "time", timezone: "browser", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839", markings: wmarkings,},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };
  $(document).ready(function () {
	$.plot($("#graph2"), wdataset, options_two);
	$("#graph2").UseTooltip();
  });

  // Create Graphs 3
  var options_three = {
    xaxis: { mode: "time", timezone: "browser", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839", markings: markings_system_controller, },
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#graph3"), hdataset, options_three);$("#graph3").UseTooltip();});
  var previousPoint = null, previousLabel = null;

  $.fn.UseTooltip = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong><?php echo $lang['temp']; ?>  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "&deg;</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };

  function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
  }
<?php } ?>

// Create Graph Humidity
<?php if($grow['mask'] & 0b10) { ?>
  var options_humidity = {
    xaxis: { mode: "time", timezone: "browser", timeformat: "%H:%M"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839",},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#humidity_level"), humidity_level_dataset, options_humidity);$("#humidity_level").UseTooltiphu();});
  var previousPoint = null, previousLabel = null;

  $.fn.UseTooltiphu = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
                showTooltiphu(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong><?php echo $lang['humid']; ?>  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "%rh</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };

  function showTooltiphu(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
  }
<?php } ?>

<?php if($grow['mask'] & 0b100) { ?>
// Create Graphs Add-On State
  var options_addon = {
    xaxis: { mode: "time", timezone: "browser", timeformat: "%H:%M"},
    yaxis: { font:{ size:8, weight: "bold", family: "sans-serif", variant: "small-caps", color: "#545454" }, ticks: tick_dataset },
    series: { lines: { show: true, lineWidth: 2, fill: false}, straightLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839", },
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#addon_state"), addon_state_dataset, options_addon);$("#addon_state").UseTooltipao();});
  var previousPoint = null, previousLabel = null;


  $.fn.UseTooltipao = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };
<?php } ?>

// Create Monthly Usage Graphs
<?php if($grow['mask'] & 0b10000) { ?>
  function getMonthName(numericMonth) {
    var monthArray = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var alphaMonth = monthArray[numericMonth];
    return alphaMonth;
  }

  function convertToDate(timestamp) {
    var newDate = new Date(timestamp);
    var dateString = newDate.getMonth();
    var monthName = getMonthName(dateString);
    return monthName;
  }

/*
Timeformat specifiers
%a: weekday name (customizable)
%b: month name (customizable)
%d: day of month, zero-padded (01-31)
%e: day of month, space-padded ( 1-31)
%H: hours, 24-hour time, zero-padded (00-23)
%I: hours, 12-hour time, zero-padded (01-12)
%m: month, zero-padded (01-12)
%M: minutes, zero-padded (00-59)
%q: quarter (1-4)
%S: seconds, zero-padded (00-59)
%y: year (two digits)
%Y: year (four digits)
%p: am/pm
%P: AM/PM (uppercase version of %p)
%w: weekday as number (0-6, 0 being Sunday)
*/
  var options_four = {
    xaxis: { mode: "time", timeformat: "%b %Y"},
	//yaxis: {axisLabel: 'Hours', axisLabelPadding: 15 },
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf7f4"] }, borderColor: "#ff8839" },
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#month_usage"), dataset_mu, options_four);$("#month_usage").UseTooltipu();});

  var previousPoint = null, previousLabel = null;
  $.fn.UseTooltipu = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) || (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var z = convertToDate(item.datapoint[0]);
				var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
                showTooltipu(item.pageX, item.pageY, color,
                "<strong>" + item.series.label + " in " + z +" <strong><br><?php echo $lang['hours']; ?> : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };

  function showTooltipu(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
  }
<?php } ?>

<?php if($grow['mask'] & 0b100000) { ?>
// Create Battery Usage Graphs
  var options_bat = {
	xaxis: { mode: "time", timeformat: "%b %Y"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839",},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#battery_level"), bat_level_dataset, options_bat);$("#battery_level").UseTooltipl();});
  var previousPoint = null, previousLabel = null;
  var weekday = new Array(7);
  weekday[0] = "Sunday";
  weekday[1] = "Monday";
  weekday[2] = "Tuesday";
  weekday[3] = "Wednesday";
  weekday[4] = "Thursday";
  weekday[5] = "Friday";
  weekday[6] = "Saturday";

  $.fn.UseTooltipl = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + weekday[new Date(x).getDay()] + " " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong><?php echo $lang['battery_level']; ?>  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + "%</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };

  function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
  }
<?php } ?>

<?php if($grow['mask'] & 0b1000000) { ?>
// Create Min Graphs
  var options_min = {
	xaxis: { mode: "time", timeformat: "%b %Y"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839",},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#min_readings"), min_dataset, options_min);$("#min_readings").UseTooltip2();});
  var previousPoint = null, previousLabel = null;
  var weekday = new Array(7);
  weekday[0] = "Sunday";
  weekday[1] = "Monday";
  weekday[2] = "Tuesday";
  weekday[3] = "Wednesday";
  weekday[4] = "Thursday";
  weekday[5] = "Friday";
  weekday[6] = "Saturday";

  $.fn.UseTooltip2 = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
                if (item.series.stype == "1") {
                        var stype = "Temperature";
                        var units = "&deg";
                } else {
                        var stype = "Humidity";
                        var units = "%";
                }
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + weekday[new Date(x).getDay()] + " " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong>" + stype + "  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + units + "</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };

  function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
  }

// Create max Graphs
  var options_max = {
        xaxis: { mode: "time", timeformat: "%b %Y"},
    series: { lines: { show: true, lineWidth: 1, fill: false}, curvedLines: { apply: true,  active: true,  monotonicFit: true } },
    grid: { hoverable: true, borderWidth: 1,  backgroundColor: { colors: ["#ffffff", "#fdf9f9"] }, borderColor: "#ff8839",},
    legend: { noColumns: 3, labelBoxBorderColor: "#ffff", position: "nw" }
  };

  $(document).ready(function () {$.plot($("#max_readings"), max_dataset, options_max);$("#max_readings").UseTooltip2();});
  var previousPoint = null, previousLabel = null;
  var weekday = new Array(7);
  weekday[0] = "Sunday";
  weekday[1] = "Monday";
  weekday[2] = "Tuesday";
  weekday[3] = "Wednesday";
  weekday[4] = "Thursday";
  weekday[5] = "Friday";
  weekday[6] = "Saturday";

  $.fn.UseTooltip2 = function () {
    $(this).bind("plothover", function (event, pos, item) {
        if (item) {
            if ((previousLabel != item.series.label) ||
                 (previousPoint != item.dataIndex)) {
                previousPoint = item.dataIndex;
                previousLabel = item.series.label;
                $("#tooltip").remove();
                var x = item.datapoint[0];
                var y = item.datapoint[1];
                var color = item.series.color;
		if (item.series.stype == '1') {
                        var stype = "Temperature";
			var units = "&deg";
		} else {
			var stype = "Humidity";
			var units = "%";
		}
                showTooltip(item.pageX,
                        item.pageY,
                        color,
                        "<strong>" + item.series.label + "</strong> At: " + weekday[new Date(x).getDay()] + " " + (new Date(x).getHours()<10?'0':'') + new Date(x).getHours() + ":"  + (new Date(x).getMinutes()<10?'0':'') + new Date(x).getMinutes() +"<br> <strong>" + stype + "  : " + $.formatNumber(y, { format: "#,###", locale: "us" }) + units + "</strong> ");
            }
        } else {
            $("#tooltip").remove();
            previousPoint = null;
        }
    });
  };

  function showTooltip(x, y, color, contents) {
    $('<div id="tooltip">' + contents + '</div>').css({
        position: 'absolute',
        display: 'none',
        top: y - 10,
        left: x + 10,
        border: '1px solid ' + color,
        padding: '3px',
        'font-size': '9px',
        'border-radius': '5px',
        'background-color': '#fff',
        'font-family': 'Verdana, Arial, Helvetica, Tahoma, sans-serif',
        opacity: 0.7
    }).appendTo("body").fadeIn(200);
  }
<?php } ?>
</script>

