Drop View if exists `schedule_daily_time_zone_view`;
CREATE VIEW `schedule_daily_time_zone_view`  AS
SELECT DISTINCT `sdtz`.`schedule_daily_time_id` AS `time_id`, `sdt`.`status` AS `time_status`, `sdt`.`type` AS `sch_type`, `sdt`.`start` AS `start`, `sdt`.`start_sr` AS `start_sr`,
`sdt`.`start_ss` AS `start_ss`, `sdt`.`start_offset` AS `start_offset`, `sdt`.`end` AS `end`, `sdt`.`end_ss` AS `end_ss`, `sdt`.`end_sr` AS `end_sr`, `sdt`.`end_offset` AS `end_offset`,
`sdt`.`WeekDays` AS `WeekDays`, `sdtz`.`sync` AS `tz_sync`, `sdtz`.`id` AS `tz_id`, `sdtz`.`status` AS `tz_status`, `sdtz`.`zone_id` AS `zone_id`, `z`.`index_id` AS `index_id`,
`z`.`name` AS `zone_name`, `zt`.`type` AS `type`, `zt`.`category` AS `category`, `sdtz`.`temperature` AS `temperature`, `sdtz`.`holidays_id` AS `holidays_id`, `sdtz`.`coop` AS `coop`,
`sdtz`.`disabled` AS `disabled`, `sdt`.`sch_name` AS `sch_name`, `zs`.`max_c` AS `max_c`, ifnull(`s`.`sensor_type_id`,0) AS `sensor_type_id`, `st`.`type` AS `stype`
FROM ((((((`schedule_daily_time_zone` `sdtz`
join `zone` `z` on(`sdtz`.`zone_id` = `z`.`id`))
join `zone_type` `zt` on(`zt`.`id` = `z`.`type_id`))
left join `zone_sensors` `zs` on(`zs`.`zone_id` = `z`.`id`))
left join `sensors` `s` on(`s`.`id` = `zs`.`zone_sensor_id`))
left join `sensor_type` `st` on(`st`.`id` = `s`.`sensor_type_id`))
join `schedule_daily_time` `sdt` on(`sdt`.`id` = `sdtz`.`schedule_daily_time_id`))
WHERE `sdtz`.`purge` = 0
ORDER BY `z`.`index_id` ASC ;


Drop View if exists `schedule_night_climat_zone_view`;
CREATE VIEW `schedule_night_climat_zone_view`  AS
SELECT DISTINCT `tnct`.`id` AS `time_id`, `tnct`.`status` AS `time_status`, `snct`.`start_time` AS `start`, `enct`.`end_time` AS `end`, `snct`.`WeekDays` AS `WeekDays`,
`nctz`.`sync` AS `tz_sync`, `nctz`.`id` AS `tz_id`, `nctz`.`status` AS `tz_status`, `nctz`.`zone_id` AS `zone_id`, `zone`.`index_id` AS `index_id`, `zone`.`name` AS `zone_name`,
`ztype`.`type` AS `type`, `ztype`.`category` AS `category`, `zone`.`status` AS `zone_status`, `nctz`.`min_temperature` AS `min_temperature`,
`nctz`.`max_temperature` AS `max_temperature`, `zs`.`max_c` AS `max_c`, `s`.`sensor_type_id` AS `sensor_type_id`, `st`.`type` AS `stype`
FROM (((((((((`schedule_night_climat_zone` `nctz`
join `schedule_night_climate_time` `snct` on(`nctz`.`schedule_night_climate_id` = `snct`.`id`))
join `schedule_night_climate_time` `enct` on(`nctz`.`schedule_night_climate_id` = `enct`.`id`))
join `schedule_night_climate_time` `tnct` on(`nctz`.`schedule_night_climate_id` = `tnct`.`id`))
join `zone` on(`nctz`.`zone_id` = `zone`.`id`))
join `zone` `zt` on(`nctz`.`zone_id` = `zt`.`id`))
left join `zone_sensors` `zs` on(`zone`.`id` = `zs`.`zone_id`))
left join `sensors` `s` on(`zs`.`zone_sensor_id` = `s`.`id`))
left join `sensor_type` `st` on(`s`.`sensor_type_id` = `st`.`id`))
join `zone_type` `ztype` on(`zone`.`type_id` = `ztype`.`id`))
WHERE `nctz`.`purge` = '0'
ORDER BY `zone`.`index_id` ASC ;
