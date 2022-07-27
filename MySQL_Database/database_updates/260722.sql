ALTER TABLE `sensor_messages` ADD COLUMN IF NOT EXISTS `sensor_id` INT(11) NOT NULL AFTER `purge`;
