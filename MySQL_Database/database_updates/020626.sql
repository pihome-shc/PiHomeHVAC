INSERT INTO jobs (`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT 'check_pvvx_bridge', '/var/www/cron/check_pvvx_bridge.php', 0, 0, 60, '', now()
FROM DUAL
WHERE NOT EXISTS (
   SELECT 1
   FROM jobs
   WHERE script = '/var/www/cron/check_pvvx_bridge.php'
);
