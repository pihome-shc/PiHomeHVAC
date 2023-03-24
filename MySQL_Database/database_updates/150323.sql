ALTER TABLE `sensors` ADD COLUMN IF NOT EXISTS `current_val_1` DECIMAL(10,2) NOT NULL AFTER `resolution`;
ALTER TABLE `sensors` ADD COLUMN IF NOT EXISTS `current_val_2` DECIMAL(10,2) NOT NULL AFTER `current_val_1`;
