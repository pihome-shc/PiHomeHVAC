ALTER TABLE `relays` ADD COLUMN IF NOT EXISTS `group_id` INT(11) NOT NULL DEFAULT '0' AFTER `lag_time`;
ALTER TABLE `relays` ADD COLUMN IF NOT EXISTS `schedule_prev` TINYINT(4) NOT NULL DEFAULT '0' AFTER `group_id`;
ALTER TABLE `relays` ADD COLUMN IF NOT EXISTS `schedule` TINYINT(4) NOT NULL DEFAULT '0' AFTER `schedule_prev`;
ALTER TABLE `relays` ADD COLUMN IF NOT EXISTS `sch_time_id` INT(11) NOT NULL DEFAULT '0' AFTER `schedule`;
ALTER TABLE `mqtt_devices` ADD COLUMN IF NOT EXISTS `brand` INT(11) NOT NULL DEFAULT '0' AFTER `purge`;

DROP TABLE IF EXISTS `relay_group`;
CREATE TABLE IF NOT EXISTS `relay_group` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf16_bin DEFAULT NULL,
   PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

DROP TABLE IF EXISTS `schedule_daily_time_relays`;
CREATE TABLE IF NOT EXISTS`schedule_daily_time_relays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) DEFAULT NULL,
  `schedule_daily_time_id` int(11) DEFAULT NULL,
  `relay_id` int(11) DEFAULT NULL,
  `state` tinyint(4) NOT NULL,
  `holidays_id` int(11) DEFAULT NULL,
  `coop` tinyint(4) NOT NULL,
  `disabled` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `FK_schedule_daily_time_relays_schedule_daily_time` (`schedule_daily_time_id`),
  KEY `FK_schedule_daily_time_relays_relays` (`relay_id`),
  CONSTRAINT `FK_schedule_daily_time_relays_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`),
  CONSTRAINT `FK_schedule_daily_time_relays_relays` FOREIGN KEY (`relay_id`) REFERENCES `relays` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

Drop View if exists `schedule_daily_time_relays_view`;
CREATE VIEW `schedule_daily_time_relays_view`  AS
SELECT DISTINCT `sdtr`.`schedule_daily_time_id` AS `time_id`, `sdt`.`status` AS `time_status`, `sdt`.`type` AS `sch_type`, `sdt`.`start` AS `start`, `sdt`.`start_sr` AS `start_sr`,
`sdt`.`start_ss` AS `start_ss`, `sdt`.`start_offset` AS `start_offset`, `sdt`.`end` AS `end`, `sdt`.`end_ss` AS `end_ss`, `sdt`.`end_sr` AS `end_sr`, `sdt`.`end_offset` AS `end_offset`,
`sdt`.`WeekDays` AS `WeekDays`, `sdtr`.`sync` AS `tr_sync`, `sdtr`.`id` AS `tr_id`, `sdtr`.`status` AS `tr_status`, `sdtr`.`relay_id` AS `relay_id`, `r`.`index_id` AS `index_id`,
`r`.`name` AS `relay_name`, `sdtr`.`state` AS `state`, `sdtr`.`holidays_id` AS `holidays_id`, `sdtr`.`coop` AS `coop`,
`sdtr`.`disabled` AS `disabled`, `sdt`.`sch_name` AS `sch_name`
FROM ((`schedule_daily_time_relays` `sdtr`
join `relays` `r` on(`sdtr`.`relay_id` = `r`.`id`))
join `schedule_daily_time` `sdt` on(`sdt`.`id` = `sdtr`.`schedule_daily_time_id`))
WHERE `sdtr`.`purge` = 0 AND `r`.`type` = 6
ORDER BY `r`.`index_id` ASC;
