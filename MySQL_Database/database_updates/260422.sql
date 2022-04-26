ALTER TABLE `user` ADD COLUMN IF NOT EXISTS `persist` TINYINT(4) NOT NULL AFTER `admin_account`;
UPDATE `user` SET `persist`= 0;
