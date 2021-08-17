ALTER TABLE `nodes` ADD `sub_type` INT(11) NOT NULL AFTER `max_child_id`;
UPDATE `nodes` SET `sub_type`=0;
