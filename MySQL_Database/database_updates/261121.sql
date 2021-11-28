INSERT INTO `zone_type`(`sync`, `purge`, `type`, `category`) 
SELECT * FROM (SELECT '0' AS `sync`, '0' AS `purge`, 'HVAC-M' AS `type`, '4' AS `category`) AS tmp
WHERE NOT EXISTS (
    SELECT `type` FROM `zone_type` WHERE `type` = 'HVAC-M'
) LIMIT 1;
ALTER TABLE `system_controller` ADD COLUMN IF NOT EXISTS `hvac_relays_state` TINYINT(4) NOT NULL AFTER `fan_relay_id`;
