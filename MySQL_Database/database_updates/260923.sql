ALTER TABLE `mqtt_devices` ADD COLUMN IF NOT EXISTS `last_seen` timestamp NULL ON UPDATE current_timestamp() AFTER `attribute`;
ALTER TABLE `mqtt_devices` ADD COLUMN IF NOT EXISTS `notice_interval` int(11) NOT NULL DEFAULT '0' AFTER `last_seen`;
ALTER TABLE `mqtt_devices` ADD COLUMN IF NOT EXISTS `min_value` int(11) DEFAULT '0' AFTER `notice_interval`;
