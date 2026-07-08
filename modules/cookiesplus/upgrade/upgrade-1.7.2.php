<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_7_2($module)
{
    $C_P_CSS = Configuration::get('C_P_CSS');
    // Check if the variable already contains <style> tags
    $useHtmlPurifier = Configuration::get('PS_USE_HTMLPURIFIER');

    if (!empty($C_P_CSS) && stripos(trim($C_P_CSS), '<style>') !== 0) {
        Configuration::updateValue('PS_USE_HTMLPURIFIER', false);
        Configuration::updateValue('C_P_CSS', '<style>' . $C_P_CSS . '</style>'); // Add <style> tags
        Configuration::updateValue('PS_USE_HTMLPURIFIER', $useHtmlPurifier);
    }

    $C_P_JS = Configuration::get('C_P_JS');
    // Check if the variable already contains <script> tags
    if (!empty($C_P_JS) && stripos(trim($C_P_JS), '<script>') !== 0) {
        Configuration::updateValue('PS_USE_HTMLPURIFIER', false);
        Configuration::updateValue('C_P_JS', '<script>' . $C_P_JS . '</script>'); // Add <script> tags
        Configuration::updateValue('PS_USE_HTMLPURIFIER', $useHtmlPurifier);
    }

    if (version_compare(_PS_VERSION_, '1.6', '>=')) {
        $module->registerHook('actionFrontControllerSetMedia');
    }

    Configuration::updateValue('C_P_BUTTONS_LAYOUT', 6);
    Configuration::updateValue('C_P_IPS_DEBUG', '');

    if (method_exists('Tools', 'clearAllCache')) {
        Tools::clearAllCache();
    }

    if (method_exists('Tools', 'clearSmartyCache')) {
        Tools::clearSmartyCache();
    }

    if (method_exists('Tools', 'clearSf2Cache')) {
        Tools::clearSf2Cache();
    }

    /*if (method_exists('Tools', 'clearCache')) {
        Tools::clearCache();
    }*/

    if (method_exists('Media', 'clearCache')) {
        Media::clearCache();
    }

    return true;
}
