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
function upgrade_module_5_3_0($module)
{
    $res = true;
    // Get module configuration
    $config = pm_advancedpack::getModuleConfigurationStatic();
    if (isset($config['addPrefixToOrderDetail'])) {
        if (!empty($config['addPrefixToOrderDetail'])) {
            $config['addDatasToOrderDetail'] = 'id';
        } else {
            $config['addDatasToOrderDetail'] = 'none';
        }
        unset($config['addPrefixToOrderDetail']);
    }
    pm_advancedpack::setModuleConfigurationStatic($config);
    $module->registerHook('actionPresentProduct');
    $module->registerHook('actionFrontControllerInitBefore');
    $module->registerHook('displayAdminProductsMainStepLeftColumnBottom');
    $module->unregisterHook('actionBeforeCartUpdateQty');
    $module->registerHook('actionCartUpdateQuantityBefore');
    $module->addCustomBundleBadgesFeature();
    return $res;
}
