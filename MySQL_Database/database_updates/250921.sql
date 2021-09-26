DROP TABLE IF EXISTS `mqtt_node_child`;
CREATE TABLE `mqtt_node_child` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `child_id` tinyint(4) NOT NULL,
  `node_id` tinyint(4) NOT NULL,
  `type` tinyint(4) NOT NULL COMMENT '0 - Sensor, 1 - Relay',
  `purge` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `mqtt_topic` CHAR(50) COLLATE utf8_bin NOT NULL COMMENT 'Relay payload for on command',
  `on_payload` char(50) COLLATE utf8_bin DEFAULT NULL COMMENT 'Relay payload for on command',
  `off_payload` char(50) COLLATE utf8_bin DEFAULT NULL COMMENT 'Relay payload for on command',
  `attribute` char(50) COLLATE utf8_bin DEFAULT NULL COMMENT 'Sensor JSON attribute',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;