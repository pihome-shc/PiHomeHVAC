ALTER TABLE `schedule_daily_time` ADD COLUMN IF NOT EXISTS  `smart_off` INT(11) NOT NULL DEFAULT '0' AFTER `type`;
