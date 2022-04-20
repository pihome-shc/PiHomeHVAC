ALTER TABLE `sensors` ADD COLUMN IF NOT EXISTS `mode` TINYINT(4) NOT NULL AFTER `frost_controller`;
ALTER TABLE `sensors` ADD COLUMN IF NOT EXISTS `timeout` INT(11) NOT NULL AFTER `mode`;
UPDATE `sensors` SET `mode` = 0, `timeout`= 0;
