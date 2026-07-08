<?php
/**
 * Upgrade script for version 1.0.2
 */

function upgrade_module_1_0_2($module)
{
    $sql = array();
    $sql[] = 'ALTER TABLE `'._DB_PREFIX_.'eg_banner` 
        MODIFY `start_date` DATETIME DEFAULT NULL, 
        MODIFY `end_date` DATETIME DEFAULT NULL;';
    foreach ($sql as $query) {
        if (!Db::getInstance()->execute($query)) {
            return false;
        }
    }
    return true;
}
