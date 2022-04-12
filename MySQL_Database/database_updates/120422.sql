ALTER TABLE `system` ADD COLUMN IF NOT EXISTS `page_refresh` TINYINT(4) NOT NULL AFTER `max_cpu_temp`;
UPDATE `system` SET `page_refresh`= 1;
