ALTER TABLE `ezrss_export` ADD COLUMN `language` VARCHAR(45) NULL  AFTER `url` ;
ALTER TABLE `ezrss_import` ADD COLUMN `language` VARCHAR(45) NULL  AFTER `url` ;