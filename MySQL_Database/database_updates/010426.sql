ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS `show_disabled` TINYINT(4) NOT NULL DEFAULT '1' AFTER `smart_off`;

