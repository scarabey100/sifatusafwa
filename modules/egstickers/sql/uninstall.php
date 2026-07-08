<?php
$sql = [];

// Drop foreign key constraint on sf_egstickers_lang
$sql[] = 'ALTER TABLE `' . _DB_PREFIX_ . 'egstickers_lang` DROP FOREIGN KEY `' . _DB_PREFIX_ . 'egstickers_lang_ibfk_1`;';

// Drop egstickers_lang table
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'egstickers_lang`;';

// Drop product_sticker table
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'product_sticker`;';

// Drop egstickers table
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'egstickers`;';

foreach ($sql as $query) {
    if (!Db::getInstance()->execute($query)) {
        return false;
    }
}

return true;