DROP TABLE IF EXISTS `hw_compensation`;
CREATE TABLE IF NOT EXISTS `hw_compensation` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT '0',
  `purge` tinyint(4) NOT NULL DEFAULT '0',
  `zone_id` int(11) NOT NULL DEFAULT '0',
  `sensor_id` int(11) NOT NULL DEFAULT '0',
  `hw_coefficient` DECIMAL(10,2) NOT NULL DEFAULT '0.6',
  `hw_threshold` INT(11) NOT NULL DEFAULT '21',
  `enabled` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4;

INSERT INTO `hw_compensation` (`id`) VALUES(1);
