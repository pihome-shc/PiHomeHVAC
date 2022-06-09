DROP TABLE IF EXISTS `theme`;
CREATE TABLE IF NOT EXISTS `theme` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `name` char(50) COLLATE utf8_bin,
  `row_justification` char(50) COLLATE utf8_bin,
  `background_color` char(50) COLLATE utf8_bin,
  `text_color` char(50) COLLATE utf8_bin,
  `border_color` char(50) COLLATE utf8_bin,
  `footer_color` char(50) COLLATE utf8_bin,
  `btn_style` char(50) COLLATE utf8_bin,
  `btn_primary` char(50) COLLATE utf8_bin,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (1,0,0,'Blue Left','left','bg-blue','text-white','border-blue','card-footer-blue','btn-bm-blue','btn-primary-blue');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (2,0,0,'Blue Center','center','bg-blue','text-white','border-blue','card-footer-blue','btn-bm-blue','btn-primary-blue');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (3,0,0,'Orange Left','left','bg-orange','text-white','border-orange','card-footer-orange','btn-bm-orange','btn-primary-orange');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (4,0,0,'Orange Center','center','bg-orange','text-white','border-orange','card-footer-orange','btn-bm-orange','btn-primary-orange');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (5,0,0,'Red Left','left','bg-red','text-white','border-red','card-footer-red','btn-bm-red','btn-primary-red');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (6,0,0,'Red Center','center','bg-red','text-white','border-red','card-footer-red','btn-bm-red','btn-primary-red');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (7,0,0,'Amber Left','left','bg-amber','text-white','border-amber','card-footer-amber','btn-bm-amber','btn-primary-amber');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (8,0,0,'Amber Center','center','bg-amber','text-white','border-amber','card-footer-amber','btn-bm-amber','btn-primary-amber');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (9,0,0,'Violet Left','left','bg-violet','text-white','border-violet','card-footer-violet','btn-bm-violet','btn-primary-violet');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (10,0,0,'Violet Center','center','bg-violet','text-white','border-violet','card-footer-violet','btn-bm-violet','btn-primary-violet');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (11,0,0,'Teal Left','left','bg-teal','text-white','border-teal','card-footer-teal','btn-bm-teal','btn-primary-teal');
INSERT INTO `theme`(`id`, `sync`, `purge`, `name`, `row_justification`, `background_color`, `text_color`, `border_color`, `footer_color`, `btn_style`, `btn_primary`)
VALUES (12,0,0,'Teal Center','center','bg-teal','text-white','border-teal','card-footer-teal','btn-bm-teal','btn-primary-teal');
