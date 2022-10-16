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
require_once(__DIR__.'/st_inc/connection.php');
require_once(__DIR__.'/st_inc/functions.php');

// get current day number
$dow = idate('w');
// get previous day number, used when end time is less than start time
$prev_dow = $dow - 1;

$end_time = strtotime(date("G:i:s"));

//query to check away status
$query = "SELECT * FROM away LIMIT 1";
$result = $conn->query($query);
$away = mysqli_fetch_array($result);
$away_status = $away['status'];

//query to check holidays status
$query = "SELECT * FROM holidays WHERE '".$date_time."' between start_date_time AND end_date_time AND status = '1' LIMIT 1";
$result = $conn->query($query);
$rowcount=mysqli_num_rows($result);
if ($rowcount > 0) {
        $holidays = mysqli_fetch_array($result);
        $holidays_status = $holidays['status'];
}else {
        $holidays_status = 0;
}

$query = "SELECT id, name FROM zone";
$zresults = $conn->query($query);
while ($zrow = mysqli_fetch_assoc($zresults)) {
        $zone_id = $zrow["id"];
        // get raw data
        $query = "SELECT schedule_daily_time.id AS time_id, schedule_daily_time.start, schedule_daily_time.start_sr, schedule_daily_time.start_ss, schedule_daily_time.start_offset,
        schedule_daily_time.end, schedule_daily_time.end_sr, schedule_daily_time.end_ss, schedule_daily_time.end_offset,
        schedule_daily_time.WeekDays, schedule_daily_time.status AS time_status, schedule_daily_time.sch_name, schedule_daily_time.type AS sch_type
        FROM `schedule_daily_time`, `schedule_daily_time_zone`
        WHERE (schedule_daily_time.id = schedule_daily_time_zone.schedule_daily_time_id) AND schedule_daily_time_zone.status = 1
        AND schedule_daily_time.status = 1 AND zone_id = {$zone_id}";
        if ($away_status == 1) { $query = $query." AND schedule_daily_time.type = 1"; } else { $query = $query." AND schedule_daily_time.type = 0"; }
        if ($holidays_status == 0) {
                $query = $query." AND holidays_id = 0;";
        } else {
                $query = $query." AND holidays_id > 0;";
        }
        $results = $conn->query($query);
        $sch_count=mysqli_num_rows($results);
        if ($sch_count > 0) {
                $sch_status = 0;
                $away_sch = 0;
                while ($row = mysqli_fetch_assoc($results)) {
                        // check each schedule for this zone
                        $time = strtotime(date("G:i:s"));
                        $time_id = $row['time_id'];
                        $start_time = strtotime($row['start']);
                        $end_time = strtotime($row['end']);
                        echo "-----------------------------------------------\n";
                        echo "Zone Name: ".$zrow["name"]."\n";
                        echo "Time: ".date('m/d/Y H:i:s', $time)."\n";
                        echo "Start Time: ".date('m/d/Y H:i:s', $start_time)."\n";
                        echo "End Time: ".date('m/d/Y H:i:s', $end_time)."\n";
                }
        }
}
echo "-----------------------------------------------\n";
?>
