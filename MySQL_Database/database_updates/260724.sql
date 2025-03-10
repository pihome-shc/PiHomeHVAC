ALTER TABLE `zone_current_state` ADD COLUMN IF NOT EXISTS `mode_prev` INT(11) DEFAULT '0' AFTER `mode`;
ALTER TABLE `zone_current_state` ADD COLUMN IF NOT EXISTS `hysteresis` TINYINT(1) DEFAULT '0' AFTER `overrun`;
ALTER TABLE `zone_current_state` ADD COLUMN IF NOT EXISTS `log_it` TINYINT(1) DEFAULT '0' AFTER `add_on_toggle`;
