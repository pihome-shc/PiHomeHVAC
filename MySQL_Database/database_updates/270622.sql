DROP TABLE IF EXISTS `repository`;
CREATE TABLE IF NOT EXISTS `repository` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `status` tinyint(4) NOT NULL,
  `name` char(50) COLLATE utf16_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
INSERT INTO `repository` (`sync`, `purge`, `status`, `name`)
VALUES (0, 0, 1, 'pihome-shc/PiHomeHVAC'), (0, 0, 0, 'twa127/PiHomeHVAC'), (0, 0, 0, 'dvdcut/PiHomeHVAC'), (0, 0, 0, 'JSa1987/PiHomeHVAC'), (0, 0, 0, 'mjhumphrey/PiHomeHVAC'), (0, 0, 0, 'sandreialexandru/PiHomeHVAC');
