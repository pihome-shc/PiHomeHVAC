DROP TABLE IF EXISTS `sensor_messages`;
CREATE TABLE IF NOT EXISTS `sensor_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message_id` decimal(10,2),
  `message` char(10) COLLATE utf8_bin,
  `status_color` char(10) COLLATE utf8_bin,
  `sub_type` tinyint(4) NOT NULL,
  `sensor_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
INSERT INTO `sensor_type`(`id`, `sync`, `purge`, `type`, `units`)
SELECT * FROM (SELECT '4' AS `id`, '0' AS `sync`, '0' AS `purge`, 'Message' AS `type`, '' AS `units`) AS tmp
WHERE NOT EXISTS (
    SELECT `id` FROM `sensor_type` WHERE `id` = 4
) LIMIT 1;
