<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * @param Module $module
 */
function upgrade_module_1_4_2(Module $module)
{
    return Db::getInstance()->execute('
        ALTER TABLE `' . _DB_PREFIX_ . 'mncklevu_order_data` ADD `klevu_shopper_ip` VARCHAR(16) NOT NULL
    ');
}
