ALTER TABLE `sensor_type` ADD `units` CHAR(11) NOT NULL AFTER `type`;
UPDATE `sensor_type` SET `units`='&deg;' WHERE `type`='Temperature';
UPDATE `sensor_type` SET `units`='%' WHERE `type`='Humidity';
