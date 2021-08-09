DROP TABLE IF EXISTS `test_table`;
CREATE TABLE IF NOT EXISTS `test_table` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4),
  `backup_name` char(50) COLLATE utf8_bin DEFAULT NULL,
  `name` char(50) COLLATE utf8_bin DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4;
