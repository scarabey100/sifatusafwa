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
function upgrade_module_5_3_6($module)
{
    $res = true;
    $res &= $module->registerHook('actionFrontControllerSetVariables');
    // We need to register on both to handle the old and new product pages on PS8
    $res &= $module->registerHook('actionAdminProductsListingResultsModifier');
    if (version_compare(_PS_VERSION_, '8.0.0', '>=')) {
        $res &= $module->registerHook('actionProductGridDataModifier');
    }
    return $res;
}
