ALTER TABLE `zone_current_state` ADD COLUMN IF NOT EXISTS `add_on_toggle` tinyint(1) DEFAULT '0' AFTER `overrun`;
