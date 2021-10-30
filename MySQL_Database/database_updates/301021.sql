DROP TABLE IF EXISTS `fork`;
CREATE TABLE IF NOT EXISTS `fork` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
INSERT INTO `fork` (`sync`, `purge`, `name`)
VALUES (0, 0, 'pihome-shc'), (0, 0, 'twa127'), (0, 0, 'dvdcut'),(0, 0, 'JSa1987'),(0, 0, 'mjhumphrey'), (0, 0, 'sandreialexandru');
