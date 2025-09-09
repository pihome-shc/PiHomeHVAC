ALTER TABLE `user` CHANGE IF EXISTS `admin_account` `access_level` TINYINT(4) NULL DEFAULT NULL;
UPDATE user SET `access_level` = IF(`access_level` = 0, 2, `access_level`);
UPDATE user SET `access_level` = 0 WHERE `username` = 'admin';

