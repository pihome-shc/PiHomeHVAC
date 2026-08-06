DROP TABLE IF EXISTS `summer`;
CREATE TABLE IF NOT EXISTS `summer` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL DEFAULT 0,
  `purge` tinyint(4) NOT NULL DEFAULT 0 COMMENT 'Mark For Deletion',
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `start_date` date NOT NULL DEFAULT '2026-06-01',
  `end_date` date NOT NULL DEFAULT '2026-08-31',
  `summer_winter` tinyint(4) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;
INSERT INTO `summer` (`id`) VALUES(1);

ALTER TABLE `system` ADD COLUMN IF NOT EXISTS `page_timeout` int(11) NOT NULL DEFAULT '60' AFTER `page_refresh`;

ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `disable_in_summer` TINYINT(4) NOT NULL DEFAULT '0' AFTER `show_disabled`;

DELETE FROM `button_page` WHERE `index_id` > 6;
INSERT INTO `button_page`(`id`, `sync`, `purge`, `name`, `function`, `index_id`, `page`) VALUES (7,0,0,'Disable Summer','disable_summer',7,2);
INSERT INTO `button_page`(`id`, `sync`, `purge`, `name`, `function`, `index_id`, `page`) VALUES (8,0,0,'Live Temperature','live_temp',8,2);

UPDATE `jobs` SET `script`='/var/www/cron/db_cleanup.py' WHERE `script` = '/var/www/cron/db_cleanup.php';
UPDATE `jobs` SET `script`='nmcli_reboot_wifi.sh' WHERE `script` = '/var/www/cron/reboot_wifi.sh';

