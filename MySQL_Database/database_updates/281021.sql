ALTER TABLE `system` CHANGE update_location repository char(100);
UPDATE `system` SET `repository`='https://github.com/pihome-shc/PiHomeHVAC.git';
ALTER TABLE `system` DROP IF EXISTS `update_file`;
ALTER TABLE `system` DROP IF EXISTS `update_alias`;
