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

function upgrade_module_1_7_0($module)
{
    // Remove all the stuff necessary for CdC module
    $module->unregisterHook('displayAfterTitle');
    $module->unregisterHook('displayAfterTitleTag');
    Configuration::deleteByName('PS_CDC_CHECKED');

    // Override PS < 1.7
    if (version_compare(_PS_VERSION_, '1.7', '<')) {
        $module->copyOverrideFolder();
        $module->addOverride('Controller');
    }

    // Ensure that we are in the first position
    $module->registerHook('actionOutputHTMLBefore');

    // There is a bug with the module cmsproductspro, which strips the code inserted by this module. So we move the cmsproductspro module to an above position
    $module->updatePosition(Hook::getIdByName('actionOutputHTMLBefore'), 0, 1);
    if ($cmsproductsproModule = Module::getInstanceByName('cmsproductspro')) {
        $cmsproductsproModule->updatePosition(Hook::getIdByName('actionOutputHTMLBefore'), 0, 1);
    }

    if (version_compare(_PS_VERSION_, '1.6', '<')) {
        $module->registerHook('actionDispatcher');
    } else {
        $module->registerHook('moduleRoutes');
    }

    return true;
}
