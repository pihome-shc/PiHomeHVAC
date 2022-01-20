DROP TABLE IF EXISTS `sensor_limits`;
CREATE TABLE IF NOT EXISTS `sensor_limits` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL,
  `sensor_id` int(11),
  `min` decimal(10,2),
  `max` decimal(10,2),
  `status` tinyint(1),
  PRIMARY KEY (`id`),
  KEY `FK_sensors_limits_sensors` (`sensor_id`),
  CONSTRAINT `FK_sensor_limits_sensors` FOREIGN KEY (`sensor_id`) REFERENCES `sensors` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
