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
function upgrade_module_1_1_0(Module $module)
{
    return $module->addOverride('CategoryController');
}
