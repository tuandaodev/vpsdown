CREATE TABLE `vps_down`.`cache` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(255) NULL,
  `type` TINYINT NULL,
  `updated` INT NULL,
  PRIMARY KEY (`id`));


ALTER TABLE `vps_down`.`cache` 
ADD COLUMN `uid` VARCHAR(100) NOT NULL AFTER `id`,
DROP PRIMARY KEY,
ADD PRIMARY KEY (`id`, `uid`);
