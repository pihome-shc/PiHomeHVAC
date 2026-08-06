CREATE TABLE IF NOT EXISTS `sensor_min_max_graph` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sensor_id`int(4) NOT NULL,
  `name`char(50) NOT NULL,
  `max` tinyint(4) NOT NULL,
  `min` tinyint(4),
  `date` datetime,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
