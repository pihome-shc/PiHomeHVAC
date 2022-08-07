DROP TABLE IF EXISTS `ebus_messages`;
CREATE TABLE IF NOT EXISTS `ebus_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message` char(50) COLLATE utf8_bin,
  `sensor_id` int(11) NOT NULL,
  `position` int(11) NOT NULL,
  `offset` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;
INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT * FROM (SELECT 'ebus' AS `job_name`, '/var/www/cron/check_ebus.php' AS `script`, '0' AS `enabled`, '0' AS `log_it`, '60' as `time`, '' AS `output`, now() AS `datetime`) AS tmp
WHERE NOT EXISTS (
    SELECT `job_name` FROM `jobs` WHERE `job_name` = 'ebus'
) LIMIT 1;
