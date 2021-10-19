DROP TABLE IF EXISTS `button_page`;
CREATE TABLE IF NOT EXISTS `button_page` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf16_bin,
  `function` char(50) COLLATE utf16_bin,
  `index_id` tinyint(4),
  `page` tinyint(4),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

INSERT INTO `button_page` (`id`, `sync`, `purge`, `name`, `function`, `index_id`, `page`) 
VALUES (1, '0', '0', 'Boost', 'boost', '1', '2'), (2, '0', '0', 'Override', 'override', '2', '2'), (3, '0', '0', 'Night Climate', 'night_climate', '3', '2'), (4, '0', '0', 'Away', 'away', '4', '2'), (5, '0', '0', 'Holidays', 'holidays', '5', '2');
