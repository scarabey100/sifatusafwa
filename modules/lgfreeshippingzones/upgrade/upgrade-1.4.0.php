<?php
/**
 * Copyright 2024 LÍNEA GRÁFICA E.C.E S.L.
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_1_4_0($module)
{
    $result = true;
    if (!Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'lgfreeshippingzones` LIKE \'def\'')) {
        if (!Db::getInstance()->Execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'lgfreeshippingzones` ' .
            'ADD `def` tinyint(1) NOT NULL DEFAULT 0 AFTER `weight2`'
        )) {
            $result &= false;
        }
    }
    if (!Db::getInstance()->executeS('SHOW COLUMNS FROM `' . _DB_PREFIX_ . 'lgfreeshippingzones` LIKE \'active\'')) {
        if (!Db::getInstance()->Execute(
            'ALTER TABLE `' . _DB_PREFIX_ . 'lgfreeshippingzones` ' .
            'ADD `active` tinyint(1) NOT NULL DEFAULT 0 AFTER `def`'
        )) {
            $result &= false;
        }
    }

    if (!$module->registerHook('displayBackofficeHeader')) {
        $result &= false;
    }

    if (!$module->registerHook('displayShoppingCartFooter')) {
        $result &= false;
    }

    return $result;
}
