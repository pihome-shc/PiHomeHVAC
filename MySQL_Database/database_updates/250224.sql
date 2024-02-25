INSERT INTO `button_page`(`id`, `sync`, `purge`, `name`, `function`, `index_id`, `page`)
SELECT * FROM (SELECT '7' AS `id`, '0' AS `sync`, '0' AS `purge`, 'Live Temperature' AS `name`, 'live_temp' AS `function`, '7' AS `index_id`, '2' AS `page`) AS tmp
WHERE NOT EXISTS (
    SELECT `function` FROM `button_page` WHERE `function` = 'live_temp'
) LIMIT 1;