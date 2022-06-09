DROP TABLE IF EXISTS `auto_backup`;
CREATE TABLE IF NOT EXISTS `auto_backup` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `enabled` tinyint(4) NOT NULL,
  `frequency` char(50) COLLATE utf16_bin,
  `rotation` char(50) COLLATE utf16_bin,
  `destination` char(50) COLLATE utf16_bin,
  `email_backup` tinyint(4) NOT NULL,
  `email_confirmation` tinyint(4) NOT NULL,  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf16 COLLATE=utf16_bin;

INSERT INTO `auto_backup` (`id`, `sync`, `purge`, `enabled`, `frequency`, `rotation`, `destination`, `email_backup`, `email_confirmation`)
VALUES (1, '0', '0', '0', '1 DAY', '2 WEEK', '/var/www/MySQL_Database/database_backups/', '0', '0');

INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
VALUES ('auto_backup','/var/www/cron/auto_backup.py',1,0,'01:00','',now());