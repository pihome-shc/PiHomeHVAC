<?php
/*
-- ------------------------------------------------------------------------
--     __  __                             _
--    |  \/  |                    /\     (_)
--    | \  / |   __ _  __  __    /  \     _   _ __
--    | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
--    | |  | | | (_| |  >  <   / ____ \  | | | |
--    |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|
--
--          S M A R T   T H E R M O S T A T
--
-- *************************************************************************
-- * MaxAir is a Linux based Central Heating Control systems. It runs from *
-- * a web interface and it comes with ABSOLUTELY NO WARRANTY, to the      *
-- * extent permitted by applicable law. I take no responsibility for any  *
-- * loss or damage to you or your property.                               *
-- * DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *
-- * WHAT YOU ARE DOING                                                    *
-- *************************************************************************

getFrostStatus.php
When called returns the current Frost Control status, the configured frost
set-point (Frost_set) and the weather-compensated frost set-point
(Frost_auto).

Frost_auto is calculated as:
    Frost_auto = Frost_set + ((Frost_set - control_temp) * 0.5)

E.g.
http://192.168.1.2/api/getFrostStatus

{"success":true,"state":"Frost Control NOT Active.","Frost_set":"5.00","Frost_auto":"8.50","control_temp":"-2.00"}
*/

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

// ---------------------------------------------------------------------------
// 1) Frost Control Active / NOT Active
// ---------------------------------------------------------------------------
$query = "SELECT * FROM zone_current_state WHERE mode DIV 10 = 2;";
$results = $conn->query($query);
if (mysqli_num_rows($results) > 0) {
    $state = "Frost Control Active.";
} else {
    $state = "Frost Control NOT Active.";
}

// ---------------------------------------------------------------------------
// 2) getFrostOutside - latest outside / hw-compensation control temperature
// ---------------------------------------------------------------------------
$query_outside = "
    SELECT TRUNCATE(payload, 2) AS control_temp
    FROM messages_in
    WHERE node_id IN (
            SELECT IF(hwc.sensor_id = 0, '1',
                      (SELECT node_id FROM nodes WHERE nodes.id = s.sensor_id)
                  ) AS node_id
            FROM hw_compensation hwc
            LEFT JOIN sensors s ON s.id = hwc.sensor_id
        )
      AND child_id IN (
            SELECT IF(hwc.sensor_id = 0, 0,
                      (SELECT sensor_child_id FROM sensors WHERE sensors.id = hwc.sensor_id)
                  ) AS child_id
            FROM hw_compensation hwc
            LEFT JOIN sensors s ON s.id = hwc.sensor_id
        )
    ORDER BY id DESC
    LIMIT 1;";
$res_outside = $conn->query($query_outside);
$control_temp = null;
if ($res_outside && mysqli_num_rows($res_outside) > 0) {
    $row = $res_outside->fetch_assoc();
    $control_temp = $row['control_temp'];
}

// ---------------------------------------------------------------------------
// 3) getFrost_Set - configured frost set-point for the Ground Floor zone
// ---------------------------------------------------------------------------
$query_set = "
    SELECT s.id AS sensor_id, s.name, s.frost_temp,
           MIN(current_val_1) AS current_val_1,
           zs.sp_deadband, z.id
    FROM zone z
    JOIN zone_sensors zs ON zs.zone_id = z.id
    JOIN sensors s ON s.id = zs.zone_sensor_id
    WHERE s.frost_temp <> 0
      AND ((ABS(TIME_TO_SEC(TIMEDIFF(NOW(), s.last_seen)) DIV 60) < s.fail_timeout)
           OR s.fail_timeout = 0)
      AND z.name = 'Ground Floor';";
$res_set = $conn->query($query_set);
$frost_set = null;
if ($res_set && mysqli_num_rows($res_set) > 0) {
    $row = $res_set->fetch_assoc();
    $frost_set = $row['frost_temp'];
}

// ---------------------------------------------------------------------------
// 4) Calculate Frost_auto
//    Frost_auto = Frost_set + ((Frost_set - control_temp) * 0.5)
// ---------------------------------------------------------------------------
$frost_auto = null;
if ($frost_set !== null && $control_temp !== null) {
    $diff       = (float)$frost_set - (float)$control_temp;
    $frost_auto = (float)$frost_set + ($diff * 0.5);
}

// ---------------------------------------------------------------------------
// Build response
// ---------------------------------------------------------------------------
echo json_encode(array(
    "success"      => true,
    "state"        => $state,
    "Frost_set"    => $frost_set !== null ? number_format((float)$frost_set, 2, '.', '')   : null,
    "control_temp" => $control_temp !== null ? number_format((float)$control_temp, 2, '.', '') : null,
    "Frost_auto"   => $frost_auto !== null ? number_format($frost_auto, 2, '.', '')         : null,
));
?>
