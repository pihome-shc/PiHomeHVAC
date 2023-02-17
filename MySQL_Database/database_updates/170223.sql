INSERT INTO `repository`(`sync`, `purge`, `status`, `name`)
SELECT * FROM (SELECT 0 AS `sync`, 0 AS `purge`, 0 AS `status`, 'twa127/PiHomeHVAC-V3' AS `name`) AS tmp
WHERE NOT EXISTS (
    SELECT `name` FROM `repository` WHERE `name` = 'twa127/PiHomeHVAC-V3'
) LIMIT 1;
