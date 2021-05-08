/*
             __  __                             _
            |  \/  |                    /\     (_)
            | \  / |   __ _  __  __    /  \     _   _ __
            | |\/| |  / _` | \ \/ /   / /\ \   | | |  __|
            | |  | | | (_| |  >  <   / ____ \  | | | |
            |_|  |_|  \__,_| /_/\_\ /_/    \_\ |_| |_|

                    S M A R T   T H E R M O S T A T

*************************************************************************"
* PiHome is Raspberry Pi based Central Heating Control systems. It runs *"
* from web interface and it comes with ABSOLUTELY NO WARRANTY, to the   *"
* extent permitted by applicable law. I take no responsibility for any  *"
* loss or damage to you or your property.                               *"
* DO NOT MAKE ANY CHANGES TO YOUR HEATING SYSTEM UNTILL UNLESS YOU KNOW *"
* WHAT YOU ARE DOING                                                    *"
*************************************************************************"
*/

-- You Must create following View Talbes in MySQL for MaxAir Smart Thermostat to work

-- Schedule List with zone details view table version 1.x
Drop View if exists schedule_daily_time_zone_view;
CREATE VIEW schedule_daily_time_zone_view AS
select ss.id as time_id, ss.status as time_status, sstart.start, send.end, sWeekDays.WeekDays,
sdtz.sync as tz_sync, sdtz.id as tz_id, sdtz.status as tz_status,
sdtz.zone_id, zone.index_id, zone.name as zone_name, ztype.`type`, ztype.category, temperature, holidays_id , coop, ss.sch_name, sdtz.sunset, sdtz.sunset_offset, zs.max_c
from schedule_daily_time_zone sdtz
join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
join zone on sdtz.zone_id = zone.id
join zone zt on sdtz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
join zone_type ztype on zone.type_id = ztype.id
where sdtz.`purge` = '0' order by zone.index_id;

-- Zones View version 2
Drop View if exists zone_view;
CREATE VIEW zone_view AS
select zone.status, zone.zone_state, zone.sync, zone.id, zone.index_id, zone.name, ztype.type, ztype.category, ts.graph_num, zs.min_c, zs.max_c, zs.default_c, max_operation_time, zs.hysteresis_time,
zs.sp_deadband, sid.node_id as sensors_id, ts.sensor_child_id,
ctype.`type` AS relay_type, cr.relay_id as relay_id, cr.relay_child_id,
IFNULL(lasts.last_seen, lasts_2.last_seen) as last_seen, IFNULL(msv.ms_version, msv_2.ms_version) as ms_version, IFNULL(skv.sketch_version, skv_2.sketch_version) as sketch_version
from zone
LEFT join zone_sensors zs on zone.id = zs.zone_id
LEFT join sensors ts on zone.id = ts.zone_id
LEFT join zone_relays zr on zone.id = zr.zone_id
LEFT join relays cr on zr.zone_relay_id = cr.id
join zone_type ztype on zone.type_id = ztype.id
LEFT join nodes sid on ts.sensor_id = sid.id
LEFT join nodes ctype on cr.relay_id = ctype.id
LEFT join nodes lasts on ts.sensor_id = lasts.id
LEFT join nodes lasts_2 on cr.relay_id = lasts_2.id
LEFT join nodes msv on ts.sensor_id = msv.id
LEFT join nodes msv_2 on cr.relay_id = msv_2.id
LEFT join nodes skv on ts.sensor_id = skv.id
LEFT join nodes skv_2 on cr.relay_id = skv_2.id
where zone.`purge` = '0';

-- Add-On Logs views
Drop View if exists add_on_log_view;
CREATE VIEW add_on_log_view AS
select add_on_logs.id, add_on_logs.sync, add_on_logs.zone_id, zt.name, ztype.type,
add_on_logs.start_datetime, add_on_logs.stop_datetime, add_on_logs.expected_end_date_time
from add_on_logs
join zone zt on add_on_logs.zone_id = zt.id
join zone_type ztype on zt.type_id = ztype.id
order by id asc;

-- System Controller View
Drop View if exists system_controller_view;
CREATE VIEW system_controller_view AS
select system_controller.status, system_controller.sync, system_controller.`purge`, system_controller.active_status, system_controller.name, ctype.`type` AS controller_type, cr.relay_id, cr.relay_child_id, system_controller.hysteresis_time, system_controller.max_operation_time, system_controller.overrun, system_controller.heat_relay_id, system_controller.cool_relay_id, system_controller.fan_relay_id
from system_controller
join relays cr on system_controller.heat_relay_id = cr.id
join nodes ctype on cr.relay_id = ctype.id
where system_controller.`purge` = '0';

-- Schedule List with zone details view table version 1.x
Drop View if exists schedule_night_climat_zone_view;
CREATE VIEW schedule_night_climat_zone_view AS
select tnct.id as time_id, tnct.status as time_status, snct.start_time as start, enct.end_time as end, snct.WeekDays, 
nctz.sync as tz_sync, nctz.id as tz_id, nctz.status as tz_status, nctz.zone_id, zone.index_id, zone.name as zone_name, 
ztype.`type`, ztype.category, zone.status as zone_status, nctz.min_temperature, nctz.max_temperature, zs.max_c
from schedule_night_climat_zone nctz
join schedule_night_climate_time snct on nctz.schedule_night_climate_id = snct.id
join schedule_night_climate_time enct on nctz.schedule_night_climate_id = enct.id
join schedule_night_climate_time tnct on nctz.schedule_night_climate_id = tnct.id
join zone on nctz.zone_id = zone.id
join zone zt on nctz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
join zone_type ztype on zone.type_id = ztype.id
where nctz.`purge` = '0' order by zone.index_id;

-- Messages_in View for Graps
Drop View if exists messages_in_view_24h;
CREATE VIEW messages_in_view_24h AS
select node_id, child_id, datetime, payload
from messages_in
where datetime > DATE_SUB( NOW(), INTERVAL 24 HOUR);

-- Zone Logs views
Drop View if exists zone_log_view;
CREATE VIEW zone_log_view AS 
select controller_zone_logs.id, controller_zone_logs.sync, controller_zone_logs.zone_id, ztype.type,
controller_zone_logs.start_datetime, controller_zone_logs.stop_datetime, controller_zone_logs.expected_end_date_time
from controller_zone_logs
join zone zt on controller_zone_logs.zone_id = zt.id
join zone_type ztype on zt.type_id = ztype.id
order by id asc;
