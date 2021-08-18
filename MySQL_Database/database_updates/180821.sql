ALTER TABLE `relays` ADD `on_trigger` TINYINT(1) NOT NULL AFTER `type`;
UPDATE `relays` SET `on_trigger`=0;
