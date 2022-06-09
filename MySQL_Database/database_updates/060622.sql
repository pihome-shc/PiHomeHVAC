DROP TABLE IF EXISTS `theme`;
CREATE TABLE IF NOT EXISTS `theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf8_bin,
  `row_justification` char(50) COLLATE utf8_bin,
  `color` char(50) COLLATE utf8_bin,
  `text_color` char(50) COLLATE utf8_bin,
  `tile_size` tinyint(4) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (1,0,0,'Blue Left','left','blue','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (2,0,0,'Blue Center','center','blue','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (3,0,0,'Orange Left','left','orange','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (4,0,0,'Orange Center','center','orange','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (5,0,0,'Red Left','left','red','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (6,0,0,'Red Center','center','red','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (7,0,0,'Amber Left','left','amber','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (8,0,0,'Amber Center','center','amber','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (9,0,0,'Violet Left','left','violet','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (10,0,0,'Violet Center','center','violet','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (11,0,0,'Teal Left','left','teal','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (12,0,0,'Teal Center','center','teal','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (13,0,0,'Dark Left','left','black','text-white','0');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `color`, `text_color`, `tile_size`)
VALUES (14,0,0,'Dark Center','center','black','text-white','0');