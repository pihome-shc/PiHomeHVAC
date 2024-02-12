ALTER TABLE `graphs` ADD COLUMN IF NOT EXISTS `archive_pointer` INT(11) NOT NULL DEFAULT '0' AFTER `archive_file`;
