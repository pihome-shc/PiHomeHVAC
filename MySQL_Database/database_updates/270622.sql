ALTER TABLE `auto_backup` ADD COLUMN IF NOT EXISTS `last_backup` DATETIME NOT NULL AFTER `email_confirmation`;
UPDATE `auto_backup` SET `last_backup` = DATE_SUB(NOW(), INTERVAL 1 DAY);