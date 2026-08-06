DROP TABLE IF EXISTS `frost_sensor_relays`;
CREATE TABLE `frost_sensor_relays` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `sensor_id` int(11) DEFAULT NULL,
  `relay_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

INSERT INTO frost_sensor_relays(`sync`,`purge`,`sensor_id`,`relay_id`)
SELECT 0, 0,`zs`.`zone_sensor_id`,`zr`.`zone_relay_id` 
FROM `zone`
JOIN `zone_relays` `zr` ON `zone`.`id` = `zr`.`zone_id`
JOIN `zone_sensors` `zs` ON `zone`.`id` = `zs`.`zone_id`
JOIN `sensors` `s` ON `s`.`id` = `zs`.`zone_sensor_id`
JOIN `relays` `r` ON `r`.`id` = `zr`.`zone_relay_id`
WHERE `s`.`frost_temp` <> 0
ORDER BY `s`.`id` DESC, `r`.`id` DESC;
