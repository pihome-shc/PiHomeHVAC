Drop View if exists schedule_daily_time_zone_view;
CREATE VIEW schedule_daily_time_zone_view AS
SELECT sdtz.schedule_daily_time_id as time_id, sdt.status as time_status, sdt.type as sch_type, sdt.start, sdt.start_ss, sdt.start_sr, sdt.start_offset,
sdt.end, sdt.end_ss, sdt.end_sr, sdt.end_offset, sdt.WeekDays, sdtz.sync as tz_sync, sdtz.id as tz_id, 
sdtz.status as tz_status, sdtz.zone_id, z.index_id, z.name as zone_name, zt.type, zt.category, sdtz.temperature, sdtz.holidays_id, sdtz.coop, 
sdt.sch_name, zs.max_c, IFNULL(s.sensor_type_id,0) as sensor_type_id, st.type as stype
FROM `schedule_daily_time_zone` sdtz
JOIN zone z ON sdtz.zone_id = z.id
JOIN zone_type zt ON zt.id = z.type_id
LEFT JOIN zone_sensors zs ON zs.zone_id = z.id
LEFT JOIN sensors s ON s.id = zs.zone_sensor_id
LEFT JOIN sensor_type st ON st.id = s.sensor_type_id
JOIN schedule_daily_time sdt ON sdt.id = sdtz.schedule_daily_time_id
WHERE sdtz.purge = 0
ORDER BY z.index_id;
