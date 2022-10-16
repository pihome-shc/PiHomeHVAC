ALTER TABLE `zone_sensors` ADD COLUMN IF NOT EXISTS `default_m` TINYINT(1) NOT NULL AFTER `default_c`;
UPDATE `zone_sensors` SET `default_m`= 0;
