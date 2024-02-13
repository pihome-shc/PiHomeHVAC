ALTER TABLE `graphs` DROP IF EXISTS `archive_pointer`;
ALTER TABLE `graphs` ADD COLUMN IF NOT EXISTS `archive_pointer` DATETIME NOT NULL AFTER `archive_file`;
UPDATE `graphs` SET `archive_pointer` = NOW() - INTERVAL 1 DAY;
