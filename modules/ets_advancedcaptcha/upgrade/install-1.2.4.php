<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }


function upgrade_module_1_2_4($object)
{
    /*
    * 4. modules/ps_emailalerts/views/templates/hook/product.tpl
    * - modules/mailalerts/views/templates/hook/product.tpl
    */

    $tpl_product = ($object->is17 ? 'ps_emailalerts' : 'mailalerts') . '/views/templates/hook/product.tpl';
    $tpl_product = file_exists(_PS_THEME_DIR_ . 'modules/' . $tpl_product) ? _PS_THEME_DIR_ . 'modules/' . $tpl_product : (file_exists(_PS_PARENT_THEME_DIR_ . 'modules/' . $tpl_product) ? _PS_PARENT_THEME_DIR_ . 'modules/' . $tpl_product : _PS_MODULE_DIR_ . $tpl_product);
    if (file_exists($tpl_product)) {
        $tpl_content = Tools::file_get_contents($tpl_product);
        $tpl_content = preg_replace('/\bjs-mailalert-add\b/', 'js-mailalert-add-custom', $tpl_content);
        file_put_contents($tpl_product, $tpl_content);
    }

    return true;
}