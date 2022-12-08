DROP TABLE IF EXISTS `relay_logs`;
CREATE TABLE IF NOT EXISTS `relay_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `relay_id' int(11) NOT NULL,
  `relay_name` char(50) COLLATE utf8_bin,
  `message` varchar(100) CHARACTER SET utf8 NOT NULL COMMENT 'Sent To Relay',
  `zone_name` char(50) COLLATE utf8_bin,
  `zone_mode' int(11) NOT NULL,
  `datetime` timestamp NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;

ALTER TABLE `db_cleanup` ADD `relay_logs` CHAR(50) CHARACTER SET utf16 COLLATE utf16_bin NULL DEFAULT NULL AFTER `gateway_logs`;

UPDATE `db_cleanup` SET `relay_logs`='2 DAY';
