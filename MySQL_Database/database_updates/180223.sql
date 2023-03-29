ALTER TABLE `system_controller` ADD COLUMN IF NOT EXISTS `pid` char(50) COLLATE utf16_bin;
ALTER TABLE `system_controller` ADD COLUMN IF NOT EXISTS `pid_running_since` char(50) COLLATE utf16_bin;
INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT * FROM (SELECT 'check_sc' AS `job_name`, '/var/www/cron/check_sc.php' AS `script`, 1 AS `enabled`, 0 AS `log_it`, '60' AS time, '' AS `output`, '0000-00-00 00:00:00' AS `datetime`) AS tmp
WHERE NOT EXISTS (
    SELECT `job_name` FROM `jobs` WHERE `job_name` = 'check_sc'
) LIMIT 1;
UPDATE `jobs` SET `enabled` = 0 WHERE `job_name`= 'controller';
