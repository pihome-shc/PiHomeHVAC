INSERT INTO `zone_type`(`sync`, `purge`, `type`, `category`) VALUES ('0','0','Humidity','1');
Drop View if exists schedule_daily_time_zone_view;
CREATE VIEW schedule_daily_time_zone_view AS
select ss.id as time_id, ss.status as time_status, sstart.start, send.end, sWeekDays.WeekDays,
sdtz.sync as tz_sync, sdtz.id as tz_id, sdtz.status as tz_status,
sdtz.zone_id, zone.index_id, zone.name as zone_name, ztype.`type`, ztype.category, temperature, holidays_id , coop, ss.sch_name, sdtz.sunset, sdtz.sunset_offset, zs.max_c, s.sensor_type_id, st.type as stype
from schedule_daily_time_zone sdtz
join schedule_daily_time ss on sdtz.schedule_daily_time_id = ss.id
join schedule_daily_time sstart on sdtz.schedule_daily_time_id = sstart.id
join schedule_daily_time send on sdtz.schedule_daily_time_id = send.id
join schedule_daily_time sWeekDays on sdtz.schedule_daily_time_id = sWeekDays.id
join zone on sdtz.zone_id = zone.id
join zone zt on sdtz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
LEFT JOIN sensors s ON zs.zone_sensor_id = s.id
LEFT JOIN sensor_type st ON s.sensor_type_id = st.id
join zone_type ztype on zone.type_id = ztype.id
where sdtz.`purge` = '0' order by zone.index_id;
Drop View if exists schedule_night_climat_zone_view;
CREATE VIEW schedule_night_climat_zone_view AS
select tnct.id as time_id, tnct.status as time_status, snct.start_time as start, enct.end_time as end, snct.WeekDays, 
nctz.sync as tz_sync, nctz.id as tz_id, nctz.status as tz_status, nctz.zone_id, zone.index_id, zone.name as zone_name, 
ztype.`type`, ztype.category, zone.status as zone_status, nctz.min_temperature, nctz.max_temperature, zs.max_c, s.sensor_type_id, st.type as stype
from schedule_night_climat_zone nctz
join schedule_night_climate_time snct on nctz.schedule_night_climate_id = snct.id
join schedule_night_climate_time enct on nctz.schedule_night_climate_id = enct.id
join schedule_night_climate_time tnct on nctz.schedule_night_climate_id = tnct.id
join zone on nctz.zone_id = zone.id
join zone zt on nctz.zone_id = zt.id
LEFT join zone_sensors zs on zone.id = zs.zone_id
LEFT JOIN sensors s ON zs.zone_sensor_id = s.id
LEFT JOIN sensor_type st ON s.sensor_type_id = st.id
join zone_type ztype on zone.type_id = ztype.id
where nctz.`purge` = '0' order by zone.index_id;
