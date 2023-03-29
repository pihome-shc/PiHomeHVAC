ALTER TABLE `zone_current_state` ADD COLUMN IF NOT EXISTS `sch_time_id` INT(11) NULL AFTER `schedule`;
