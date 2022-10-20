ALTER TABLE `sw_install` ADD COLUMN IF NOT EXISTS `restart_schedule` TINYINT(1) NOT NULL AFTER `stop_datetime`;
