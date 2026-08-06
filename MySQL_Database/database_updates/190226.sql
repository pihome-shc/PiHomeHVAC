DROP TABLE IF EXISTS `relay_messages`;
CREATE TABLE IF NOT EXISTS `relay_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sync` tinyint(4) NOT NULL,
  `purge` tinyint(4) NOT NULL COMMENT 'Mark For Deletion',
  `message_id` decimal(10,2),
  `message` char(10) COLLATE utf8_bin,
  `relay_id` int(11) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

ALTER TABLE `relays` ADD COLUMN IF NOT EXISTS `current_val_2` decimal(10,2) NOT NULL DEFAULT '0' AFTER `state`;
