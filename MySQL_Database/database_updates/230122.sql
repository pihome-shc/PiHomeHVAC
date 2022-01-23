ALTER TABLE `system_controller` ADD COLUMN IF NOT EXISTS `weather_factoring` TINYINT(1) NOT NULL AFTER `hvac_relays_state`;
UPDATE `system_controller` SET `weather_factoring`= 0;
