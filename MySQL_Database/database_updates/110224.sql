ALTER TABLE `graphs` ADD COLUMN IF NOT EXISTS `archive_enable` TINYINT(1) NOT NULL DEFAULT '0' AFTER `mask`;
ALTER TABLE `graphs` ADD COLUMN IF NOT EXISTS`archive_file` VARCHAR(50) NOT NULL AFTER `archive_enable`;
INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT * FROM (SELECT 'graphs_to_csv' AS `job_name`, '/var/www/cron/graph_csv.py' AS `script`, 1 AS `enabled`, 0 AS `log_it`, '00:00' AS time, '' AS `output`, '0000-00-00 00:00:00' AS `datetime`) AS tmp
WHERE NOT EXISTS (
    SELECT `job_name` FROM `jobs` WHERE `job_name` = 'graphs_to_csv'
) LIMIT 1;
