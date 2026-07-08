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

class Cart extends CartCore
{
    /**
     * Returns a list of modules that are registered for a given hook, each following this schema:
     *
     * ```
     *     [
     *         'id_hook' => $hookId,
     *         'module' => $moduleName,
     *         'id_module' => $moduleId
     *     ]
     * ```
     *
     * If no hook name is given, it returns all the hook registrations, indexed by lower cased hook name.
     *
     * @param string|null $hookName Hook name (null to return all hooks)
     *
     * @return array[]|false returns an array of hook registrations,
     *
     * or false if the provided hook name is not registered
     *
     * @throws PrestaShopDatabaseException
     *
     * @since 1.5.0
     */
    public function getPackageShippingCost(
        $id_carrier = null,
        $use_tax = true,
        Country $default_country = null,
        $product_list = null,
        $id_zone = null,
        bool $keepOrderPrices = false, /* Nueva variable en versión 1.7.7.1 */
        $id_shop = null
    ) {
        if (Module::isInstalled('lgfreeshippingzones')
            && Module::isEnabled('lgfreeshippingzones')
            && !$this->isVirtualCart()
        ) {
            if (!isset($this->apply)) {
                $this->apply = [];
            }
            $is_order_context =
                Context::getContext()->controller instanceof OrderController
                && (version_compare(_PS_VERSION_, '1.6.0', '>='))
                    ? true
                    : false;

            $id_address = (int) $this->id_address_delivery;
            if ($id_address) {
                $id_zone = Address::getZoneById((int) $id_address);
            } elseif (Configuration::get('PS_LGFSZP_DEFZONE')) {
                $id_zone = (int) Configuration::get('PS_LGFSZP_DEFZONE');
            } elseif (!empty(Context::getContext()->country->id_zone)) {
                $id_zone = (int) Context::getContext()->country->id_zone;
            }

            $aux_context = Context::getContext();
            if (is_null($id_shop) || (int) $id_shop == 0) {
                $id_shop_aux = $aux_context->shop->id;
            } else {
                $id_shop_aux = $id_shop;
            }

            $cache_id = 'Cart::getPackageShippingCost' . md5($id_carrier . $use_tax . $id_zone);
            if (!in_array($cache_id, $this->apply)) {
                if (empty($id_carrier) && !$is_order_context) {
                    if ($id_carrier_def = Db::getInstance()->getValue(
                        'SELECT c.id_carrier ' .
                        'FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones lg ' .
                        'INNER JOIN ' . _DB_PREFIX_ . 'carrier c ' .
                        'WHERE lg.id_carrier = c.id_reference ' .
                        'AND lg.active = 1 ' .
                        'AND lg.def = 1 ' .
                        'AND lg.id_zone = ' . (int) $id_zone . ' ' .
                        'AND c.active = 1 '
                    )
                    ) {
                        $id_carrier = $id_carrier_def;
                    }
                } /*else {
                    // Comprobar que el transportista se encuentra en la zona por defecto
                    if ($id_carrier_def = Db::getInstance()->getValue(
                        'SELECT lg.id_carrier ' .
                        'FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones lg ' .
                        'WHERE lg.id_carrier = ' . (int) $id_carrier . ' ' .
                        'AND lg.id_zone = ' . (int) $id_zone . ' ' .
                        'AND lg.id_shop = ' . (int) $id_shop_aux . ' ' .
                        'AND lg.active = 1 '
                    )
                    ) {
                        $id_carrier = $id_carrier_def;
                    } else {
                        $id_carrier = Db::getInstance()->getValue(
                            'SELECT lg.id_carrier ' .
                            'FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones lg ' .
                            'WHERE lg.id_zone = ' . (int) $id_zone . ' ' .
                            'AND lg.id_shop = ' . (int) $id_shop_aux . ' ' .
                            'AND lg.def = 1 ' .
                            'AND lg.active = 1 '
                        );
                    }
                }*/

                include_once _PS_MODULE_DIR_ . 'lgfreeshippingzones/lgfreeshippingzones.php';
                $lgfsz = new LGFreeShippingZones();
                $lgfs_usetax = (bool) ((int) Configuration::get('PS_LGFSZP_TAX') == 1);
                $aux_context = Context::getContext();
                if (is_null($id_shop) || (int) $id_shop == 0) {
                    $id_shop_aux = $aux_context->shop->id;
                } else {
                    $id_shop_aux = $id_shop;
                }

                /* $ignore_vouchers = (int) Configuration::get('PS_LGFSZP_VOUCHERS');
                if ($ignore_vouchers == 1) {
                    $price = $this->getOrderTotal(
                        $lgfs_usetax,
                        Cart::BOTH_WITHOUT_SHIPPING,
                        $product_list,
                        $id_carrier,
                        false
                    );
                } else {
                    $price = $this->getOrderTotal(
                        $lgfs_usetax,
                        Cart::ONLY_PRODUCTS,
                        $product_list,
                        $id_carrier,
                        false
                    );
                } */

                // CÓDIGO ALTERNATIVO
                /* $cartRules = $this->getTotalCalculationCartRules(Cart::ONLY_DISCOUNTS, Cart::BOTH);
                $discount_value = [];
                $priceT = 0;

                if (!empty($cartRules)) {
                    foreach ($cartRules as $value) {
                        $discount_value[] = $value['value_real'];
                    }

                    $price = $this->getOrderTotal(
                        $lgfs_usetax,
                        Cart::BOTH_WITHOUT_SHIPPING,
                        $product_list,
                        $id_carrier,
                        false
                    );

                    $price_without_discounts = $price - array_sum($discount_value);
                }

                if($price_without_discounts > 0) {
                    $priceT = $price_without_discounts;
                } else {
                $priceT = $this->getOrderTotal($lgfs_usetax,Cart::BOTH_WITHOUT_SHIPPING,$product_list,$id_carrier,false);
                } */
                // FIN DE CÓDIGO ALTERNATIVO

                if ($lgfsz->FSCheck(
                    $id_zone,
                    $this->getOrderTotal(
                        $lgfs_usetax,
                        Cart::BOTH_WITHOUT_SHIPPING,
                        $product_list,
                        $id_carrier,
                        false
                    ),
                    $this->getTotalWeight($product_list),
                    $id_carrier,
                    $id_shop_aux,
                    (int) $this->id_shop_group,
                    (int) $this->id_currency
                )) {
                    if (!isset(self::$_carriers[$id_carrier])) {
                        self::$_carriers[$id_carrier] = new Carrier((int) $id_carrier);
                        self::$_carriers[$id_carrier]->is_free = true;
                    }
                    $this->apply[] = $cache_id;
                    Cache::store($cache_id, 0);
                    return 0;
                }
            } else {
                return Cache::retrieve($cache_id);
            }
        }
        return parent::getPackageShippingCost($id_carrier, $use_tax, $default_country, $product_list, $id_zone);
    }
}
