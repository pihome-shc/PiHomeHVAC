ALTER TABLE `system` ADD COLUMN IF NOT EXISTS `max_cpu_temp` INT(11) NOT NULL AFTER `mode`;
UPDATE `system` SET `max_cpu_temp`= 50;
