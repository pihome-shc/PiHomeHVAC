INSERT INTO `zone_type`(`sync`, `purge`, `type`, `category`)
SELECT * FROM (SELECT '0' AS `sync`, '0' AS `purge`, 'Cooling' AS `type`, '5' AS `category`) AS tmp
WHERE NOT EXISTS (
    SELECT `type` FROM `zone_type` WHERE `type` = 'Cooling'
) LIMIT 1;
