ALTER TABLE `system_controller` ADD COLUMN IF NOT EXISTS `weather_sensor_id` INT(11) NOT NULL AFTER `weather_factoring`;
UPDATE `system_controller` SET `weather_sensor_id`= 0;
