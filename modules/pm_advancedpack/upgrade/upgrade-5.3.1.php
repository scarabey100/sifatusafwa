<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
function upgrade_module_5_3_1($module)
{
    $res = true;
    if (version_compare(_PS_VERSION_, '1.7.8.0', '>=')) {
        $res &= $module->unregisterHook('actionGetProductPropertiesAfter');
        $res &= $module->registerHook('actionGetProductPropertiesAfterUnitPrice');
    }
    return $res;
}
