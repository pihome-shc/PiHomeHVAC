ALTER TABLE `userhistory` ADD COLUMN IF NOT EXISTS `s_id` VARCHAR(255) NOT NULL AFTER `ipaddress`;
UPDATE `userhistory` SET `s_id`= 0;
