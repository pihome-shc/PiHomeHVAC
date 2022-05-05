ALTER TABLE `userhistory` ADD COLUMN IF NOT EXISTS `logged_out` timestamp NULL AFTER `date`;
