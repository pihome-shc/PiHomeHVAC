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

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

require_once(__DIR__.'../../st_inc/connection.php');
require_once(__DIR__.'../../st_inc/functions.php');

// --- Helper: fetch min/max for a zone (today) ---
function getMinMax($conn, $where_zone = "") {
        $query = "SELECT id, name,
                         ROUND(MIN(avg_payload), 2) AS min_temp,
                         ROUND(MAX(avg_payload), 2) AS max_temp
                  FROM (
                      SELECT z.id, z.name, m.datetime,
                             AVG(m.payload) AS avg_payload
                      FROM zone_sensors
                      JOIN zone z ON z.id = zone_sensors.zone_id
                      JOIN sensors s ON s.id = zone_sensors.zone_sensor_id
                      JOIN nodes n ON n.id = s.sensor_id
                      JOIN messages_in m ON m.node_id = n.node_id AND m.child_id = s.sensor_child_id
                      WHERE DATE(m.datetime) = CURDATE()
                      AND s.sensor_type_id = 1
                      AND z.type_id
                      {$where_zone}
                      GROUP BY z.id, z.name, m.datetime
                  ) AS averaged
                  GROUP BY id, name
                  ORDER BY id ASC;";
        $result = $conn->query($query);
        $rows = array();
        if($result) {
                while($row = mysqli_fetch_assoc($result)) {
                        $rows[$row['id']] = array(
                                "min" => $row['min_temp'],
                                "max" => $row['max_temp']
                        );
                }
        }
        return $rows;
}

// --- Helper: fetch delta for a zone (last hour) ---
function getDelta($conn, $where_zone = "") {
        // get the first record from 1 hour ago
        $query = "SELECT id, name, avg_payload_l AS last
                  FROM (
                      SELECT z.id, z.name, m.datetime,
                             AVG(m.payload) AS avg_payload_l
                      FROM zone_sensors
                      JOIN zone z ON z.id = zone_sensors.zone_id
                      JOIN sensors s ON s.id = zone_sensors.zone_sensor_id
                      JOIN nodes n ON n.id = s.sensor_id
                      JOIN messages_in m ON m.node_id = n.node_id AND m.child_id = s.sensor_child_id
                      WHERE m.datetime >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                      AND s.sensor_type_id = 1
                      AND z.type_id
                      {$where_zone}
                      GROUP BY z.id, z.name, m.datetime
                      ORDER BY m.datetime DESC
                  ) AS averaged
                  GROUP BY id, name
                  ORDER BY id ASC;";
        $result = $conn->query($query);
        $rows_l = array();
        if($result) {
                while($row = mysqli_fetch_assoc($result)) {
                        $rows_l[$row['id']] = $row['last'];
                }
        }

        // get the first record from 1 hour ago
        $query = "SELECT id, name, avg_payload_f AS first
                  FROM (
                      SELECT z.id, z.name, m.datetime,
                             AVG(m.payload) AS avg_payload_f
                      FROM zone_sensors
                      JOIN zone z ON z.id = zone_sensors.zone_id
                      JOIN sensors s ON s.id = zone_sensors.zone_sensor_id
                      JOIN nodes n ON n.id = s.sensor_id
                      JOIN messages_in m ON m.node_id = n.node_id AND m.child_id = s.sensor_child_id
                      WHERE m.datetime >= DATE_SUB(NOW(), INTERVAL 1 HOUR)
                      AND s.sensor_type_id = 1
                      AND z.type_id
                      {$where_zone}
                      GROUP BY z.id, z.name, m.datetime
                      ORDER BY m.datetime ASC
                  ) AS averaged
                  GROUP BY id, name
                  ORDER BY id ASC;";
        $result = $conn->query($query);
        $rows_f = array();
        if($result) {
                while($row = mysqli_fetch_assoc($result)) {
                        $rows_f[$row['id']] = $row['first'];
                }
        }

        // build array of zone_id and change between first and last values over the preceeding 1 hour
        $rows_d = array();
        foreach ($rows_l as $key => $value) {
                $delta = round($value - $rows_f[$key], 2);
                $rows_d[$key] = $delta;
        }
        return $rows_d;
}

// return active state
function check_active($mode) {
        // ------------------------------------------------------------------------
        // Zone Main Mode
        // ------------------------------------------------------------------------
        // 0 - idle
        // 10 - fault
        // 20 - frost
        // 30 - overtemperature
        // 40 - holiday
        // 50 - nightclimate
        // 60 - boost
        // 70 - override
        // 80 - sheduled
        // 90 - away
        // 100 - hysteresis
        // 110 - Add-On
        // 120 - HVAC
        // 130 - undertemperature
        // 140 - manual*/

        // ------------------------------------------------------------------------
        // Zone sub mode - running/ stopped different types
        // ------------------------------------------------------------------------
        // 0 - stopped (above cut out setpoint or not running in this mode)
        // 1 - heating running
        // 2 - stopped (within deadband)
        // 3 - stopped (coop start waiting for the system_controller)
        // 4 - manual operation ON
        // 5 - manual operation OFF
        // 6 - cooling running
        // 7 - HVAC Fan Only
        // 8 - Max Running Time Exceeded - Hysteresis active*/

        $mode_main=floor($mode/10)*10;
        $mode_sub=floor($mode%10);
        switch ($mode_main) {
                case 20:
                case 30:
                case 50:
                case 60:
                case 70:
                case 80:
                case 100:
                case 140:
                        $active = 1;
                        break;
                default:
                        $active = 0;
        }
        return $active;
}

if(isset($_GET['zonename'])) {
        // --- Single zone mode ---
        // Returns current averaged temp, today's min/max, and last-hour delta
        // for the named zone.
        $zonename = mysqli_real_escape_string($conn, $_GET['zonename']);

        // Current averaged temperature
        $query = "SELECT z.id, z.name, zcs.mode, zcs.temp_target,
                         ROUND(AVG(m.payload), 2) AS temp_actual,
                         MAX(m.datetime) AS datetime
                  FROM zone z
                  JOIN zone_current_state zcs ON zcs.zone_id = z.id
                  JOIN sensors s ON s.zone_id = z.id
                  JOIN nodes n ON n.id = s.sensor_id AND n.status IS NOT NULL
                  JOIN messages_in m ON m.node_id = n.node_id
                       AND m.child_id = s.sensor_child_id
                       AND m.datetime = (
                           SELECT MAX(m2.datetime)
                           FROM messages_in m2
                           WHERE m2.node_id = n.node_id
                           AND m2.child_id = s.sensor_child_id
                           ORDER BY m2.datetime DESC
                           LIMIT 1
                       )
                  WHERE z.name = '{$zonename}'
                  GROUP BY z.id, z.name, zcs.temp_target
                  LIMIT 1;";
        $result = $conn->query($query);
        if(! $result) {
                http_response_code(500);
                echo json_encode(array("success" => False, "state" => "Query failed: " . $conn->error));
        } else {
                $row = mysqli_fetch_assoc($result);
                if(! $row) {
                        http_response_code(400);
                        echo json_encode(array("success" => False, "state" => "No zone with that name found, or no sensor data available."));
                } else {
                        $zone_id      = $row['id'];
                        $minmax       = getMinMax($conn, "AND z.name = '{$zonename}'");
                        $delta        = getDelta($conn,  "AND z.name = '{$zonename}'");
                        http_response_code(200);
                        echo json_encode(array(
                                "success"       => True,
                                "id"            => $row['id'],
                                "name"          => $row['name'],
                                "active"        => check_active($row['mode']),
                                "temp_target"   => $row['temp_target'],
                                "temp_actual"   => $row['temp_actual'],
                                "temp_datetime" => $row['datetime'],
                                "min"           => isset($minmax[$zone_id]) ? $minmax[$zone_id]['min'] : null,
                                "max"           => isset($minmax[$zone_id]) ? $minmax[$zone_id]['max'] : null,
                                "delta"         => isset($delta[$zone_id])  ? $delta[$zone_id]         : null
                        ));
                }
        }
} else {
        // --- All zones mode (default when no zonename provided) ---
        // Returns current averaged temp, today's min/max, and last-hour delta
        // for every zone, ordered by zone id for a stable consistent order.

        // Current averaged temperature across all zones
        $query = "SELECT z.id, z.name, zcs.mode, zcs.temp_target,
                         ROUND(AVG(m.payload), 2) AS temp_actual,
                         MAX(m.datetime) AS datetime
                  FROM zone z
                  JOIN zone_current_state zcs ON zcs.zone_id = z.id
                  JOIN sensors s ON s.zone_id = z.id
                  JOIN nodes n ON n.id = s.sensor_id AND n.status IS NOT NULL
                  JOIN messages_in m ON m.node_id = n.node_id
                       AND m.child_id = s.sensor_child_id
                       AND m.datetime = (
                           SELECT MAX(m2.datetime)
                           FROM messages_in m2
                           WHERE m2.node_id = n.node_id
                           AND m2.child_id = s.sensor_child_id
                           ORDER BY m2.datetime DESC
                           LIMIT 1
                       )
                  GROUP BY z.id, z.name, zcs.temp_target
                  ORDER BY z.id ASC;";
        $result = $conn->query($query);
        if(! $result) {
                http_response_code(500);
                echo json_encode(array("success" => False, "state" => "Query failed: " . $conn->error));
        } else {
                $row = mysqli_fetch_assoc($result);
                if(! $row) {
                        http_response_code(400);
                        echo json_encode(array("success" => False, "state" => "No zone data found."));
                } else {
                        // Fetch min/max and delta for all zones up front
                        $minmax = getMinMax($conn);
                        $delta  = getDelta($conn);

                        $latest_datetime = null;
                        $zones = array();
                        do {
                                $zone_id = $row['id'];
                                if($latest_datetime === null || $row['datetime'] > $latest_datetime) {
                                        $latest_datetime = $row['datetime'];
                                }
                                $zones[] = array(
                                        "id"            => $zone_id,
                                        "name"          => $row['name'],
                                        "active"        => check_active($row['mode']),
                                        "temp_target"   => $row['temp_target'],
                                        "temp_actual"   => $row['temp_actual'],
                                        "temp_datetime" => $row['datetime'],
                                        "min"           => isset($minmax[$zone_id]) ? $minmax[$zone_id]['min'] : null,
                                        "max"           => isset($minmax[$zone_id]) ? $minmax[$zone_id]['max'] : null,
                                        "delta"         => isset($delta[$zone_id])  ? $delta[$zone_id]         : null                                );
                        } while($row = mysqli_fetch_assoc($result));
                        http_response_code(200);
                        echo json_encode(array("success" => True, "zones" => $zones));
                }
        }
}
?>
