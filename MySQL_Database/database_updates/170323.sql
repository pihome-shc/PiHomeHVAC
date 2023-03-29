ALTER TABLE `gateway` ADD COLUMN IF NOT EXISTS `heartbeat_timeout` CHAR(50) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL AFTER `timout`;
UPDATE `gateway` SET `heartbeat_timeout` = 60;
