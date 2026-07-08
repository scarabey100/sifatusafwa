<?php
$sql = [];

// Create egstickers table
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'egstickers` (
    `id_sticker` INT(11) NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `color` VARCHAR(7) NOT NULL,
    `rate` TINYINT(1) NOT NULL,
    `position` INT(11) NOT NULL,  
    `active` TINYINT(1) NOT NULL,  
    `sticker_position` INT(11) NOT NULL,  
    PRIMARY KEY (`id_sticker`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Create product_sticker table
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'product_sticker` (
    `id_product` INT(11) NOT NULL,
    `id_sticker` INT(11) NOT NULL,
    PRIMARY KEY (`id_product`, `id_sticker`),
    FOREIGN KEY (`id_sticker`) REFERENCES `' . _DB_PREFIX_ . 'egstickers`(`id_sticker`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Create egstickers_lang table
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'egstickers_lang` (
    `id_sticker` INT(11) NOT NULL,
    `id_lang` INT(11) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_sticker`, `id_lang`),
    FOREIGN KEY (`id_sticker`) REFERENCES `' . _DB_PREFIX_ . 'egstickers`(`id_sticker`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Create egstickers_flags table
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'egstickers_flags` (
    `id_flag` INT(11) NOT NULL AUTO_INCREMENT,
    `native_flag` VARCHAR(255) NOT NULL, 
    `sticker_position` INT(11) NOT NULL,
    `color` VARCHAR(7) NOT NULL,
    `active` TINYINT(1) NOT NULL,
    PRIMARY KEY (`id_flag`)
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

// Create egstickers_flags_lang table
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'egstickers_flags_lang` (
    `id_flag` INT(11) NOT NULL,
    `id_lang` INT(11) NOT NULL,
    `parallel_value` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_flag`, `id_lang`),
    FOREIGN KEY (`id_flag`) REFERENCES `' . _DB_PREFIX_ . 'egstickers_flags`(`id_flag`) ON DELETE CASCADE
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

return true;