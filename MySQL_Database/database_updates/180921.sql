ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `start_sr` TINYINT(1) NOT NULL AFTER `start`;
ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `start_ss` TINYINT(1) NOT NULL AFTER `start_sr`;
ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `start_offset` INT(11) NOT NULL AFTER `start_ss`;
ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `end_sr` TINYINT(1) NOT NULL AFTER `end`;
ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `end_ss` TINYINT(1) NOT NULL AFTER `end_sr`;
ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `end_offset` INT(11) NOT NULL AFTER `end_ss`;
UPDATE `schedule_daily_time` SET `start_sr`='0';
UPDATE `schedule_daily_time` SET `start_ss`=`sunset`;
UPDATE `schedule_daily_time` SET `start_offset`=`sunset_offset`;
UPDATE `schedule_daily_time` SET `end_sr`='0';
UPDATE `schedule_daily_time` SET `end_ss`='0';
UPDATE `schedule_daily_time` SET `end_offset`='0';
ALTER TABLE `schedule_daily_time_zone` DELETE COLUMN IF EXISTS `sunrise`;
ALTER TABLE `schedule_daily_time_zone` DELETE COLUMN IF EXISTS `sunrise_offset`;
ALTER TABLE `schedule_daily_time_zone` DELETE COLUMN IF EXISTS `sunset`;
ALTER TABLE `schedule_daily_time_zone` DELETE COLUMN IF EXISTS `sunset_offset`;
Drop View if exists schedule_daily_time_zone_view;
CREATE VIEW schedule_daily_time_zone_view AS
select ss.id as time_id, ss.status as time_status, sstart.start, sstart_sr.start_sr, sstart_ss.start_ss, sstart_offset.start_offset, send.end, send_sr.end_sr, send_ss.end_ss, send_offset.end_offset, sWeekDays.WeekDays,
sdtz.sync as tz_sync, sdtz.id as tz_id, sdtz.status as tz_status,
sdtz.zone_id, zone.index_id, zone.name as zone_name, ztype.`type`, ztype.category, temperature, holidays_id , coop, ss.sch_name, zs.max_c, s.sensor_type_id, st.type as stype
from schedule_daily_time_zone sdtz
join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
join schedule_daily_time sstart_sr on sdtz.schedule_daily_time_id = sstart_sr.id
join schedule_daily_time sstart_ss on sdtz.schedule_daily_time_id = sstart_ss.id
join schedule_daily_time sstart_offset on sdtz.schedule_daily_time_id = sstart_offset.id
join schedule_daily_time send_sr on sdtz.schedule_daily_time_id = send_sr.id
join schedule_daily_time send_ss on sdtz.schedule_daily_time_id = send_ss.id
join schedule_daily_time send_offset on sdtz.schedule_daily_time_id = send_offset.id
join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
join zone on sdtz.zone_id = zone.id
join zone zt on sdtz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
LEFT JOIN sensors s ON zs.zone_sensor_id = s.id
LEFT JOIN sensor_type st ON s.sensor_type_id = st.id
join zone_type ztype on zone.type_id = ztype.id
where sdtz.`purge` = '0' order by zone.index_id;
