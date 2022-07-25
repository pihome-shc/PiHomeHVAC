DROP TABLE IF EXISTS `sensor_messages`;
CREATE TABLE IF NOT EXISTS `sensor_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message_id` decimal(10,2),
  `message` char(10) COLLATE utf8_bin,
  `status_color` char(10) COLLATE utf8_bin,
  `sub_type` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
INSERT INTO `sensor_type` VALUES (4,0,0,'Message','');
