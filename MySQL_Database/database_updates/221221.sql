INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT * FROM (SELECT 'notice' AS `job_name`, '/var/www/cron/notice.py' AS `script`, '0' AS `enabled`, '0' AS `log_it`, '60' AS `time`, '' AS `output`, NOW() AS `datetime`) AS tmp
WHERE NOT EXISTS (
    SELECT `job_name` FROM `jobs` WHERE `job_name` = 'notice'
) LIMIT 1;
