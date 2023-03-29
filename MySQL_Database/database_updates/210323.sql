INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT * FROM (SELECT 'shutdown_reboot' AS `job_name`, '/var/www/cron/shutdown_reboot.py' AS `script`, 1 AS `enabled`, 0 AS `log_it`, '15' AS time, '' AS `output`, '0000-00-00 00:00:00' AS `datetime`) AS tmp
WHERE NOT EXISTS (
    SELECT `job_name` FROM `jobs` WHERE `job_name` = 'shutdown_reboot'
) LIMIT 1;
