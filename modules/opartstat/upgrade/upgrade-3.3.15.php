<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_3_3_15($module)
{
    $sql = 'ALTER TABLE `' . _DB_PREFIX_ . 'opartstat_sessions` CHANGE `idOrder` `idCart` INT(10) DEFAULT NULL';
    $res = Db::getInstance()->execute($sql);

    $res &= updateIdCartFromIdorder();
    return $res;
}

function updateIdCartFromIdorder()
{
    $sql = 'UPDATE `' . _DB_PREFIX_ . 'opartstat_sessions` os
        JOIN `' . _DB_PREFIX_ . 'orders` o ON os.`idCart` = o.`id_order`
        SET os.`idCart` = o.`id_cart`';
        
    return Db::getInstance()->execute($sql);
}
