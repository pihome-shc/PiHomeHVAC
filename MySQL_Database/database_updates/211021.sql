DROP TABLE IF EXISTS `db_cleanup`;
CREATE TABLE IF NOT EXISTS `db_cleanup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `messages_in` char(50) COLLATE utf16_bin,
  `nodes_battery` char(50) COLLATE utf16_bin,
  `gateway_logs` char(50) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
INSERT INTO `db_cleanup` (`id`, `sync`, `purge`, `messages_in`, `nodes_battery`, `gateway_logs`)
VALUES (1, '0', '0', '3 DAY', '3 MONTH', '3 DAY');
