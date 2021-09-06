INSERT INTO `zone_type`(`sync`, `purge`, `type`, `category`)
SELECT * FROM (SELECT '0' AS `sync`, '0' AS `purge`, 'Switch' AS `type`, '1' AS `category`) AS tmp
WHERE NOT EXISTS (
    SELECT `type` FROM `zone_type` WHERE `type` = 'Switch'
) LIMIT 1;
INSERT INTO `sensor_type`(`sync`, `purge`, `type`, `units`)
SELECT * FROM (SELECT '0' AS `sync`, '0' AS `purge`, 'Switch' AS `type`, '' AS `units`) AS tmp
WHERE NOT EXISTS (
    SELECT `type` FROM `sensor_type` WHERE `type` = 'Switch'
) LIMIT 1;
INSERT INTO `jobs`(`job_name`, `script`, `enabled`, `log_it`, `time`, `output`, `datetime`)
SELECT * FROM (SELECT 'check_gpio_switch' AS `job_name`, '/var/www/cron/check_gpio_switch.php' AS `script`, '0' AS `enabled`, '0' AS `log_it`, '60' AS `time`, '' AS `output`, now() AS `datetime`) AS tmp
WHERE NOT EXISTS (
    SELECT `job_name` FROM `jobs` WHERE `job_name` = 'check_gpio_switch'
) LIMIT 1;
