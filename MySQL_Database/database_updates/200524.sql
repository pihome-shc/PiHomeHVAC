ALTER TABLE `relays` ADD COLUMN IF NOT EXISTS `state` tinyint(1) DEFAULT '0' AFTER `user_display`;
