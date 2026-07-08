<?php 
$sql = array();

$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bms_orderpreparation_inprogress`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bms_orderpreparation_shipping_template`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bms_orderpreparation_shipping_template_type`';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'bms_orderpreparation_mime_type`';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
