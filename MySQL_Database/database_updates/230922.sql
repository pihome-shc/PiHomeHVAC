ALTER TABLE `messages_out` MODIFY node_id CHAR(50) CHARACTER SET utf16 COLLATE utf16_bin NOT NULL;
ALTER TABLE `messages_out` ADD COLUMN IF NOT EXISTS `n_id` INT(11) NOT NULL AFTER `purge`;
UPDATE `messages_out`
INNER JOIN `nodes` ON `messages_out`.node_id = `nodes`.node_id
SET `messages_out`.n_id = `nodes`.id;
