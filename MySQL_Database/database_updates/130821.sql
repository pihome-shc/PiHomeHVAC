ALTER TABLE `sensors` ADD COLUMN IF NOT EXISTS `correction_factor` decimal(10,2) AFTER `sensor_child_id`;
UPDATE `sensors` SET `correction_factor`=0;
