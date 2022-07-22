ALTER TABLE `http_messages` CONVERT TO CHARACTER SET utf8 COLLATE utf8_bin;
ALTER TABLE `http_messages` ADD COLUMN IF NOT EXISTS `zone_id` INT(11) NOT NULL AFTER `purge`;
UPDATE `http_messages`, `zone`
SET `http_messages`.zone_id = `zone`.`id`
WHERE `http_messages`.`zone_name` = `zone`.`name`;