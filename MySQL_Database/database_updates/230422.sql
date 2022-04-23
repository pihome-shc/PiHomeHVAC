ALTER TABLE `sensors` ADD COLUMN IF NOT EXISTS `resolution` DECIMAL(1,1) NOT NULL AFTER `timeout`;
UPDATE `sensors` SET `resolution` = 0.2;