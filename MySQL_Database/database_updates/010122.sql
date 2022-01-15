DROP TABLE IF EXISTS `schedule_time_temp_offset`;
CREATE TABLE IF NOT EXISTS `schedule_time_temp_offset` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `schedule_daily_time_id` int(11),
  `low_temperature` float NOT NULL,
  `high_temperature` float NOT NULL,
  `start_time_offset` int(11),
  `sensors_id` int(11),
  PRIMARY KEY (`id`),
  KEY `FK_schedule_time_temp_offset_schedule_daily_time` (`schedule_daily_time_id`),
  CONSTRAINT `FK_schedule_time_temp_offset_schedule_daily_time` FOREIGN KEY (`schedule_daily_time_id`) REFERENCES `schedule_daily_time` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

DELETE FROM `button_page`;

INSERT INTO `button_page` (`id`, `sync`, `purge`, `name`, `function`, `index_id`, `page`)
VALUES (1, '0', '0', 'Boost', 'boost', '1', '2'), (2, '0', '0', 'Override', 'override', '2', '2'),(3, '0', '0', 'Offset', 'offset', '3', '2'), (4, '0', '0', 'Night Climate', 'night_climate', '4', '2'), (5, '0', '0', 'Away', 'away', '5', '2'), (6, '0', '0', 'Holidays', 'holidays', '6', '2');
