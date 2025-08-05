DROP TABLE IF EXISTS `sensor_average`;
CREATE TABLE IF NOT EXISTS `sensor_average` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `zone_id` int(11) DEFAULT NULL,
  `sensor_id` char(15) DEFAULT NULL,
  `graph_num` tinyint(4) DEFAULT NULL,
  `show_it` tinyint(1) DEFAULT NULL,
  `min_max_graph` tinyint(1) DEFAULT NULL,
  `message_in` tinyint(4) DEFAULT 1,
  `current_val_1` DECIMAL(10,2) DEFAULT NULL,
  `last_seen` timestamp NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `FK_sensor_average_zone` (`zone_id`),
  CONSTRAINT `FK_average_sensor_zone` FOREIGN KEY (`zone_id`) REFERENCES `zone` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=50 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

INSERT INTO `sensor_average` (`sync`, `purge`, `zone_id`, `sensor_id`, `graph_num`, `show_it`, `min_max_graph`, `message_in`, `current_val_1`, `last_seen`)
SELECT DISTINCT 0 AS `sync`, 0 AS `purge`, `zone_id`, CONCAT('zavg_',`zone_id`) AS sensor_id, 0 AS `graph_num`, 0 AS `show_it`, 0 AS `min_max_graph`, 1 AS `message_in`, NULL AS `current_val_1`, NULL AS `last_seen`
from (
    SELECT t.*, count(*) over(partition by zone_id) cnt
    FROM zone_sensors t
) t
WHERE cnt > 1;
