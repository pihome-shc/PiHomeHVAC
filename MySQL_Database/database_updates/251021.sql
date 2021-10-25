DROP TABLE IF EXISTS `graphs`;
CREATE TABLE IF NOT EXISTS `graphs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `mask` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
INSERT INTO `graphs` (`sync`, `purge`, `mask`)
VALUES (0, 0, 57);
