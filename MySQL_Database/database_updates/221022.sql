DROP TABLE IF EXISTS `ebus_messages`;
CREATE TABLE IF NOT EXISTS `ebus_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message` char(50) COLLATE utf8_bin,
  `sensor_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `offset` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
