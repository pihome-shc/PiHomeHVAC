ALTER TABLE `theme` ADD COLUMN IF NOT EXISTS `btn_size` TINYINT(4) NULL AFTER `btn_primary`;
UPDATE `theme` SET `btn_size`= 0;
