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

class LGFreeShippingZones extends Module
{
    protected $default_currency;
    protected $compute_precision;
    protected $id_shop = false;
    protected $id_group = false;
    protected $shops = [];
    protected $html = '';
    protected $ps_version = '16';

    private $publi = [];
    private $third_party_modules = ['idxropc'];

    public function __construct()
    {
        $this->name = 'lgfreeshippingzones';
        $this->tab = 'shipping_logistics';
        $this->version = '1.4.8';
        $this->author = 'Línea Gráfica';
        $this->module_key = '89ac4fd54e6cdb93595643fb986be768';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Free Shipping / Delivery by Zone, Carrier, Price and Weight');
        $this->description = $this->l('Set free shipping for different zones, carriers, price and weight interval.');

        $this->ps_versions_compliancy = [
            'min' => '1.6',
            'max' => _PS_VERSION_,
        ];

        $shop_context = $this->context->cookie->shopContext;

        if (strpos($shop_context, 's-') !== false) {
            $this->shops[] = $this->id_shop = (int) Tools::str_replace_once('s-', '', $shop_context);
        }

        if (strpos($shop_context, 'g-') !== false) {
            $this->id_group = (int) Tools::str_replace_once('g-', '', $shop_context);
            $this->id_shop = false;
            $groups_shops = ShopGroup::getShopsFromGroup((int) $this->id_group);
            foreach ($groups_shops as $shop) {
                $this->shops[] = $shop['id_shop'];
            }
        }

        if (empty($this->shops)) {
            $this->id_shop = false;
            $shops_groups = Shop::getShops();
            foreach ($shops_groups as $shop) {
                $this->shops[] = $shop['id_shop'];
            }
        }

        $this->default_currency = Configuration::get('PS_CURRENCY_DEFAULT');
        $this->compute_precision = Configuration::get('_PS_PRICE_COMPUTE_PRECISION_');

        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            $this->ps_version = '17';
        }
    }

    public function install()
    {
        $res = parent::install();

        $res &= $this->registerHook('displayBackofficeHeader');
        $res &= $this->registerHook('displayHeader');

        $res &= version_compare(_PS_VERSION_, '1.7.0', '>=') ?
            $this->registerHook('displayShoppingCartFooter') :
            $this->registerHook('displayBeforeShoppingCartBlock');

        if (!$res) {
            return false;
        }

        $queries = [
            'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'lgfreeshippingzones` (
              `id_zone` int(11) NOT NULL,
              `id_shop` int(11) NOT NULL,
              `id_shop_group` int(11) NOT NULL,
              `id_carrier` int(11) NOT NULL,
              `price` decimal(10,2) NOT NULL,
              `weight` decimal(10,3) NOT NULL,
              `price2` decimal(10,2) NOT NULL,
              `weight2` decimal(10,3) NOT NULL,
              `def` tinyint(1) NOT NULL DEFAULT 0,
              `active` tinyint(1) NOT NULL DEFAULT 0,
              KEY `id_zone` (`id_zone`),
              KEY `id_carrier` (`id_carrier`)
              ) ENGINE=' . (defined('ENGINE_TYPE') ? ENGINE_TYPE : 'Innodb'),
        ];

        foreach ($queries as $query) {
            if (!Db::getInstance()->Execute($query)) {
                parent::uninstall();
                return false;
            }
        }

        $def_carrier = Configuration::get('PS_CARRIER_DEFAULT');
        $shops = Shop::getShops();
        foreach ($shops as $shop) {
            // Init values
            $zonas = ZoneCore::getZones();
            foreach ($zonas as $zona) {
                $carriers = $this->getCarriers($zona['id_zone']);
                foreach ($carriers as $carrier) {
                    Db::getInstance()->Execute(
                        'INSERT INTO ' . _DB_PREFIX_ . 'lgfreeshippingzones ' .
                        'VALUES (
                            \'' . $zona['id_zone'] . '\',
                            \'' . (int) $shop['id_shop'] . '\',
                            \'0\',
                            \'' . (int) $carrier['id_reference'] . '\',
                            0,
                            0,
                            0,
                            0,
                            0,
                            0
                        )'
                    );
                }
                Configuration::updateValue('PS_LGFSZP_DEFCARRIER_ZONE' . $zona['id_zone'] . '', $def_carrier);
            }
        }
        $shop_groups = ShopGroup::getShopGroups();
        foreach ($shop_groups as $shop_group) {
            $shop_g = (array) $shop_group;
            // Init values
            $zonas = ZoneCore::getZones();
            foreach ($zonas as $zona) {
                $carriers = $this->getCarriers($zona['id_zone']);
                foreach ($carriers as $carrier) {
                    Db::getInstance()->Execute(
                        'INSERT INTO ' . _DB_PREFIX_ . 'lgfreeshippingzones ' .
                        'VALUES (
                            \'' . $zona['id_zone'] . '\',
                            \'0\',
                            \'' . (int) $shop_g['id'] . '\',
                            \'' . $carrier['id_reference'] . '\',
                            0,
                            0,
                            0,
                            0,
                            0,
                            0
                        )'
                    );
                }
                Configuration::updateValue('PS_LGFSZP_DEFCARRIER_ZONE' . $zona['id_zone'] . '', $def_carrier);
            }
        }
        $id_country = (int) Configuration::get('PS_COUNTRY_DEFAULT');
        $id_zone = CountryCore::getIdZone((int) $id_country);
        if (!Configuration::updateValue('PS_LGFSZP_TAX', '1')
            || !Configuration::updateValue('PS_LGFSZP_MESSAGE', '1')
            || !Configuration::updateValue('PS_LGFSZP_VOUCHERS', '0')
            || !Configuration::updateValue('PS_LGFSZP_COLOR1', '55c65e')
            || !Configuration::updateValue('PS_LGFSZP_COLOR2', 'fe9126')
            || !Configuration::updateValue('PS_LGFSZP_DEBUG', '0')
            || !Configuration::updateValue('PS_LGFSZP_DEBUGIP', '' . $_SERVER['REMOTE_ADDR'] . '')
            || !Configuration::updateValue('PS_LGFSZP_DEFZONE', $id_zone)
        ) {
            return false;
        }
        return true;
    }

    public function uninstall()
    {
        Db::getInstance()->Execute('DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'lgfreeshippingzones`');
        return parent::uninstall();
    }

    public function installOverrides()
    {
        $path = _PS_MODULE_DIR_ . $this->name .
            DIRECTORY_SEPARATOR . 'override' .
            DIRECTORY_SEPARATOR . 'classes' .
            DIRECTORY_SEPARATOR;

        if (version_compare(_PS_VERSION_, '1.7.7.1', '>=')) {
            copy($path . 'Cart1771.php', $path . 'Cart.php');
        } else {
            copy($path . 'Cart.php', $path . 'Cart.php');
        }

        return parent::installOverrides();
    }

    public function getContent()
    {
        $this->updateConfiguration();
        $this->checkConfiguration();

        $this->initPubli();
        $this->getP();

        $zonas = $this->getZones();

        foreach ($zonas as $index_zona => $zona) {
            $id_zone = (int) $zona['id_zone'];
            $zonas[$index_zona]['carriers'] = [];
            $zonas[$index_zona]['carriers'] = $this->getCarriers($id_zone, $this->id_shop);
            $zonas[$index_zona]['name'] = $this->getZoneName($id_zone);

            if (!empty($zonas[$index_zona]['carriers'])) {
                $zonas[$index_zona]['mi_array'] = $this->getActiveZoneCarriers(
                    (int) $id_zone,
                    (int) $this->id_shop,
                    (int) $this->id_group
                );
                foreach ($zonas[$index_zona]['carriers'] as $index_carrier => $carrier) {
                    $id_carrier = $carrier['id_reference'];
                    $active = in_array($carrier['id_reference'], $zonas[$index_zona]['mi_array']);
                    $price_aux_1 = $this->getZoCaValue(
                        (int) $id_zone,
                        (int) $id_carrier,
                        'price',
                        (int) $this->id_shop,
                        (int) $this->id_group
                    );
                    $price_aux_2 = $this->getZoCaValue(
                        (int) $id_zone,
                        (int) $id_carrier,
                        'price2',
                        (int) $this->id_shop,
                        (int) $this->id_group
                    );
                    $weight_aux_1 = $this->getZoCaValue(
                        (int) $id_zone,
                        (int) $id_carrier,
                        'weight',
                        (int) $this->id_shop,
                        (int) $this->id_group
                    );
                    $weight_aux_2 = $this->getZoCaValue(
                        (int) $id_zone,
                        (int) $id_carrier,
                        'weight2',
                        (int) $this->id_shop,
                        (int) $this->id_group
                    );
                    $zonas[$index_zona]['carriers'][$index_carrier]['price_aux_1'] = $price_aux_1;
                    $zonas[$index_zona]['carriers'][$index_carrier]['price_aux_2'] = $price_aux_2;
                    $zonas[$index_zona]['carriers'][$index_carrier]['weight_aux_1'] = $weight_aux_1;
                    $zonas[$index_zona]['carriers'][$index_carrier]['weight_aux_2'] = $weight_aux_2;
                    $zonas[$index_zona]['carriers'][$index_carrier]['active'] = (int) $active;
                }
            }
        }

        $currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $currency_sign = Validate::isLoadedObject($currency) ? $currency->getSign() : '€';

        $weight_unit = Configuration::get('PS_WEIGHT_UNIT');

        $class_alert = 'alert alert-info';

        $this->context->smarty->assign([
            'module_name' => $this->name,
            'class_alert' => $class_alert,
            'zonas' => $zonas,
            'currency' => $currency_sign,
            'weight_unit' => $weight_unit,
            'PS_LGFSZP_TAX' => Configuration::get('PS_LGFSZP_TAX'),
            'PS_LGFSZP_MESSAGE' => Configuration::get('PS_LGFSZP_MESSAGE'),
            // 'PS_LGFSZP_VOUCHERS' => Configuration::get('PS_LGFSZP_VOUCHERS'),
            'PS_LGFSZP_COLOR1' => Configuration::get('PS_LGFSZP_COLOR1'),
            'PS_LGFSZP_COLOR2' => Configuration::get('PS_LGFSZP_COLOR2'),
            'PS_LGFSZP_DEBUG' => Configuration::get('PS_LGFSZP_DEBUG'),
            'PS_LGFSZP_DEBUGIP' => Configuration::get('PS_LGFSZP_DEBUGIP'),
            'bootstrap' => $this->bootstrap,
        ]);

        $this->html .= $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . $this->name .
            DIRECTORY_SEPARATOR . 'views' .
            DIRECTORY_SEPARATOR . 'templates' .
            DIRECTORY_SEPARATOR . 'admin' .
            DIRECTORY_SEPARATOR . '_configure' .
            DIRECTORY_SEPARATOR . 'configuration.tpl'
        );

        return $this->html;
    }

    protected function updateConfiguration()
    {
        $zonas = $this->getZones();
        // Update free shipping
        if (Tools::isSubmit('updateConf')) {
            Configuration::updateValue('PS_LGFSZP_DEFZONE', (int) Tools::getValue('def_zone'));
            foreach ($zonas as $zona) {
                $id_zone = $zona['id_zone'];
                $carriers = $this->getCarriers($id_zone);
                foreach ($carriers as $carrier) {
                    $id_carrier = $carrier['id_reference'];
                    $nombre_array = 'act_carrier_' . $id_zone;
                    $mi_array = Tools::getValue($nombre_array);
                    $activo = empty($mi_array) ? 0 : (int) in_array($id_carrier, $mi_array);
                    $default = Tools::getValue('def_carrier_' . $id_zone) == $id_carrier ? 1 : 0;
                    $this->updateFreeshipping(
                        (int) $id_zone,
                        (int) $id_carrier,
                        (int) $this->id_shop,
                        (int) $this->id_group,
                        (int) $default,
                        (int) $activo
                    );
                }
            }
            $this->html .= Module::DisplayConfirmation($this->l('Configuration updated'));
        }
        // Update additional configuration
        if (Tools::isSubmit('updateConf2')) {
            Configuration::updateValue('PS_LGFSZP_TAX', (int) Tools::getValue('price_tax'));
            Configuration::updateValue('PS_LGFSZP_MESSAGE', (int) Tools::getValue('shipping_message'));
            // Configuration::updateValue('PS_LGFSZP_VOUCHERS', (int) Tools::getValue('vouchers'));
            Configuration::updateValue('PS_LGFSZP_COLOR1', Tools::getValue('message_color1'));
            Configuration::updateValue('PS_LGFSZP_COLOR2', Tools::getValue('message_color2'));
            Configuration::updateValue('PS_LGFSZP_DEBUG', (int) Tools::getValue('debug_mode'));
            Configuration::updateValue('PS_LGFSZP_DEBUGIP', Tools::getValue('debugip'));
            $this->html .= Module::DisplayConfirmation($this->l('Configuration updated'));
        }
    }

    protected function updateFreeshipping($id_zone, $id_carrier, $id_shop, $id_shop_group, $default, $activo)
    {
        if ($this->getFreeShipping((int) $id_zone, (int) $id_shop, (int) $id_shop_group, (int) $id_carrier)) {
            Db::getInstance()->Execute(
                'UPDATE `' . _DB_PREFIX_ . 'lgfreeshippingzones` ' .
                'SET ' .
                '`price` = "' . (float) Tools::getValue('price_' . $id_zone . '_' . $id_carrier) . '", ' .
                '`weight` = "' . (float) Tools::getValue('weight_' . $id_zone . '_' . $id_carrier) . '", ' .
                '`price2` = "' . (float) Tools::getValue('price2_' . $id_zone . '_' . $id_carrier) . '", ' .
                '`weight2` = "' . (float) Tools::getValue('weight2_' . $id_zone . '_' . $id_carrier) . '", ' .
                '`def` = "' . (int) $default . '", ' .
                '`active` = "' . (int) $activo . '" ' .
                'WHERE `id_zone` = ' . (int) $id_zone . ' ' .
                'AND `id_shop` = ' . (int) $id_shop . ' ' .
                'AND `id_shop_group` = ' . (int) $id_shop_group . ' ' .
                'AND `id_carrier` = ' . (int) $id_carrier
            );
        } else {
            Db::getInstance()->Execute(
                'INSERT INTO `' . _DB_PREFIX_ . 'lgfreeshippingzones` ' .
                'VALUES (
                    ' . (int) $id_zone . ',
                    ' . (int) $id_shop . ',
                    ' . (int) $id_shop_group . ',
                    ' . (int) $id_carrier . ',
                    ' . (float) Tools::getValue('price_' . $id_zone . '_' . $id_carrier) . ',
                    ' . (float) Tools::getValue('weight_' . $id_zone . '_' . $id_carrier) . ',
                    ' . (float) Tools::getValue('price2_' . $id_zone . '_' . $id_carrier) . ',
                    ' . (float) Tools::getValue('weight2_' . $id_zone . '_' . $id_carrier) . ',
                    ' . (int) $default . ',
                    ' . (int) $activo . '
                )'
            );
        }
    }

    protected function checkConfiguration()
    {
        $shopid = (int) $this->context->shop->id;
        $default_id_zone = (int) Configuration::get('PS_LGFSZP_DEFZONE');
        $default_id_carrier = (int) Configuration::get('PS_LGFSZP_DEFCARRIER_ZONE' . $default_id_zone . '');

        if ($default_id_carrier < 1) {
            $default_carrier = $this->l('undefined');
        } else {
            $default_carrier = $this->getCarrierName((int) $default_id_carrier);
            $default_carrier_free = $this->getCarrierIsFree((int) $default_id_carrier);
        }

        if ($default_id_zone < 1) {
            $default_zone = $this->l('undefined');
        } else {
            $default_zone = $this->getZoneName($default_id_zone);
        }

        if ($default_id_carrier > 0 and $default_id_zone > 0) {
            $carrier_id = $this->getCarrierId((int) $default_id_carrier);
            $carrier_zone = $this->getCarrierZone((int) $default_id_zone, (int) $carrier_id);
            $free_shipping = $this->getFreeShipping((int) $default_id_zone, (int) $shopid, (int) $default_id_carrier);
        }

        if (!file_exists(_PS_ROOT_DIR_ . '/override/classes/Cart.php')) {
            $this->html .= $this->displayError(
                $this->l('The Cart.php override is missing.') . '&nbsp;' .
                $this->l('Please reset the module or copy the override manually on your FTP.')
            );
        }

        if ((int) Configuration::get('PS_DISABLE_OVERRIDES') > 0) {
            $this->html .= $this->displayError(
                $this->l('The overrides are currently disabled on your store. Please change the configuration')
                . ' <a href="index.php?tab=AdminPerformance&token=' . Tools::getAdminTokenLite('AdminPerformance')
                . '" target="_blank">' . $this->l('here') . '</a>'
            );
        }

        if ((int) Configuration::get('PS_DISABLE_NON_NATIVE_MODULE') > 0) {
            $this->html .= $this->displayError(
                $this->l('Non PrestaShop modules are currently disabled on your store. Please change the configuration')
                . ' <a href="index.php?tab=AdminPerformance&token=' . Tools::getAdminTokenLite('AdminPerformance')
                . '" target="_blank">' . $this->l('here') . '</a>'
            );
        }

        if ((int) Configuration::get('PS_SHIPPING_FREE_PRICE') > 0
            or (int) Configuration::get('PS_SHIPPING_FREE_WEIGHT') > 0
        ) {
            $this->html .= $this->displayError(
                $this->l('Your general free shipping configuration is not set at 0. Please change the configuration')
                . ' <a href="index.php?tab=AdminShipping&token=' . Tools::getAdminTokenLite('AdminShipping')
                . '" target="_blank">' . $this->l('here') . '</a>'
            );
        }

        if ((int) $default_id_zone < 1) {
            $this->html .= $this->displayError(
                $this->l('You have not selected a default zone') . '&nbsp;' .
                $this->l('(free shipping will not be visible if users are not logged in).') . '&nbsp;' .
                $this->l('Please select one in the column "Zone" below.')
            );
        }

        if ((int) $default_id_carrier < 1) {
            $this->html .= $this->displayError(
                $this->l('You have not selected a default carrier for the default zone') . '&nbsp;' .
                $this->l('(free shipping will not be visible if users are not logged in).') . '&nbsp;' .
                $this->l('Please select one in the column "Carrier" below.')
            );
        }

        if ((int) $default_id_carrier > 0 and (int) $default_carrier_free > 0) {
            $this->html .= $this->displayError(
                $this->l('Free shipping is permanently enabled for the default carrier') . '&nbsp;' .
                $default_carrier . '. ' .
                $this->l('Please change the configuration')
                . ' <a href="index.php?tab=AdminCarriers&token=' . Tools::getAdminTokenLite('AdminCarriers')
                . '" target="_blank">' . $this->l('here') . '</a>'
            );
        }

        if ((int) $default_id_carrier > 0 and (int) $default_id_zone > 0) {
            if ((int) $carrier_zone < 1) {
                $this->html .= $this->displayError(
                    $this->l('The default carrier') . '&nbsp;' .
                    $default_carrier . '&nbsp;' .
                    $this->l('is not enabled for the default zone') . '&nbsp;' .
                    $default_zone . '.&nbsp;' .
                    $this->l('Please change the configuration')
                    . ' <a href="index.php?tab=AdminCarriers&token=' . Tools::getAdminTokenLite('AdminCarriers')
                    . '" target="_blank">' . $this->l('here') . '</a>'
                );
            }

            if ($free_shipping
                && (int) $free_shipping['price'] == 0
                && (int) $free_shipping['price2'] == 0
                && (int) $free_shipping['weight'] == 0
                && (int) $free_shipping['weight2'] == 0
            ) {
                $this->html .= ModuleCore::displayWarning(
                    $this->l('Free shipping is not configured yet for the default zone') .
                    $default_zone . $this->l('and default carrier') . $default_carrier .
                    $this->l('Please configure below the line ') . $default_zone . ' - ' . $default_carrier .
                    $this->l('in order to get free shipping when users are not logged-in.')
                );
            }
        }
    }

    // Methods for process Hooks
    public function hookDisplayHeader()
    {
        if ($this->context->controller instanceof CartController
            || $this->context->controller instanceof OrderController
            || $this->context->controller instanceof ParentOrderController
            || (
                isset($this->context->controller->module->name)
                && in_array($this->context->controller->module->name, $this->third_party_modules)
            )
        ) {
            if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
                $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/lgfreeshippingzones.css');
                $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/freeshipping.js');
            } else {
                $this->context->controller->addCSS(
                    _MODULE_DIR_ .
                    $this->name .
                    '/views/css/lgfreeshippingzones_17.css'
                );

                $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/freeshipping_17.js');
            }
        }
    }

    public function hookDisplayBackofficeHeader()
    {
        if ($this->context->controller instanceof AdminModulesController
            && (
                (strcmp(Tools::getValue('configure'), $this->name) === 0)
                || (strcmp(Tools::getValue('module_name'), $this->name) === 0)
            )
        ) {
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/admin.js');
            $this->context->controller->addJS(_MODULE_DIR_ . $this->name . '/views/js/jscolor.js');
            $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/lgfreeshippingzones.css');
        }
    }

    public function hookDisplayShoppingCartFooter($params)
    {
        return $this->hookDisplayBeforeShoppingCartBlock($params);
    }

    public function hookDisplayBeforeShoppingCartBlock($params)
    {
        return $this->render($params);
    }

    public function render($params)
    {
        $paquetes = $params['cart']->getPackageList();
        $taxCalculationMethod = Group::getPriceDisplayMethod((int) Group::getCurrent()->id);
        $useTax = !($taxCalculationMethod == PS_TAX_EXC);
        $lgfs_usetax = (bool) ((int) Configuration::get('PS_LGFSZP_TAX') == 1);

        $general_config = (bool) (Configuration::get('PS_SHIPPING_FREE_PRICE') > 0
            || (int) Configuration::get('PS_SHIPPING_FREE_WEIGHT') > 0);

        $customer_debug_info = [];
        $customer_debug_info['Shop']['id'] = (int) $this->context->shop->id;
        $customer_debug_info['Shop']['id_group'] = (int) $this->id_group;
        $customer_debug_info['Shop']['name'] = $this->context->shop->name;
        $customer_debug_info['NumberOfShippingAddress'] = count($paquetes);
        $customer_debug_info['GeneralConfig'] = $general_config;
        $customer_debug_info['undeliverable'] = false;

        $selected_carriers = $params['cart']->getDeliveryOption();

        $free_shipping_voucher = false;
        $vouchers = $params['cart']->getCartRules();

        $customer_debug_info['free_shipping_voucher_code'] = 0;
        foreach ($vouchers as $voucher) {
            if ($voucher['free_shipping']) {
                $free_shipping_voucher = true;
                $customer_debug_info['free_shipping_voucher_code'] = $voucher['code'];
                break;
            }
        }
        $customer_debug_info['free_shipping_voucher'] = $free_shipping_voucher;

        $nbTotalProducts = 0;
        foreach ($paquetes as $a => $direccion_envio) {
            $shipping_address = new Address($a);

            // Contendrá los carriers seleccionados para el carro, cuando hay paquetes hay varios
            $address_carriers = [];
            if (isset($selected_carriers[$a])) {
                $address_carriers = explode(',', $selected_carriers[$a]);
            }
            // Limpiamos este array porque puede traer un elemento como la cadena vacía
            foreach ($address_carriers as $cindex => $carrier) {
                if (is_null($carrier) || trim($carrier) == '') {
                    unset($address_carriers[$cindex]);
                }
            }

            $customer_debug_info['Addresses'][$a]['id'] = $a;
            $customer_debug_info['Addresses'][$a]['address'] = AddressFormatCore::generateAddress($shipping_address);
            $customer_debug_info['Addresses'][$a]['NumberOfPackages'] = count($direccion_envio);
            if ($a) {
                $customer_debug_info['Addresses'][$a]['ZoneID'] = Address::getZoneById((int) $a);
            } elseif (Configuration::get('PS_LGFSZP_DEFZONE')) {
                $customer_debug_info['Addresses'][$a]['ZoneID'] = (int) Configuration::get('PS_LGFSZP_DEFZONE');
            } elseif (!empty(Context::getContext()->country->id_zone)) {
                $customer_debug_info['Addresses'][$a]['ZoneID'] = Context::getContext()->country->id_zone;
            }
            $address_zone = new Zone($customer_debug_info['Addresses'][$a]['ZoneID']);
            $customer_debug_info['Addresses'][$a]['ZoneName'] = $address_zone->name;
            foreach ($direccion_envio as $b => $paquete) {
                $final_carriers = [];
                $undeliverable = false;
                foreach ($paquete['carrier_list'] as $carrier_aux) {
                    if ($carrier_aux) {
                        $final_carriers[] = $carrier_aux;
                    }
                }
                if (empty($final_carriers)) {
                    $undeliverable = true;
                    // Really if there is a package undeliverable, whole cart will be undeliverable
                    $customer_debug_info['undeliverable'] = true;
                }
                $customer_debug_info['Addresses'][$a]['Packages'][$b]['undeliverable'] = $undeliverable;

                $customer_debug_info['Addresses'][$a]['Packages'][$b]['id'] = $b;
                $customer_debug_info['Addresses'][$a]['Packages'][$b]['NumberOfCarriers'] = count($final_carriers);

                /*
                    $ignore_vouchers = (int) Configuration::get('PS_LGFSZP_VOUCHERS');
                    if ($ignore_vouchers == 1) {
                        $price = $params['cart']->getOrderTotal(
                            $lgfs_usetax,
                            Cart::ONLY_PRODUCTS,
                            $paquete['product_list'],
                            null,
                            false
                        );
                    } else {
                        $price = $params['cart']->getOrderTotal(
                            $lgfs_usetax,
                            Cart::BOTH_WITHOUT_SHIPPING,
                            $paquete['product_list'],
                            null,
                            false
                        );
                    }

                    $customer_debug_info['Addresses'][$a]['Packages'][$b]['TotalCost'] = $price;
                */
                $customer_debug_info['Addresses'][$a]['Packages'][$b]['TotalCost'] = $params['cart']->getOrderTotal(
                    $lgfs_usetax,
                    Cart::BOTH_WITHOUT_SHIPPING,
                    $paquete['product_list'],
                    null,
                    false
                );
                $customer_debug_info['Addresses'][$a]['Packages'][$b]['TotalWeight'] = $params['cart']->getTotalWeight(
                    $paquete['product_list']
                );
                $packageNumberOfProduct = 0;
                foreach ($paquete['product_list'] as $product) {
                    $packageNumberOfProduct += (int) $product['cart_quantity'];
                }
                $nbTotalProducts += $packageNumberOfProduct;
                $customer_debug_info['Addresses'][$a]['Packages'][$b]['NumberOfProducts'] = $packageNumberOfProduct;
                foreach ($paquete['carrier_list'] as $transportista) {
                    if (in_array($transportista, $address_carriers)) {
                        $id_zone = empty($customer_debug_info['Addresses'][$a]['id'])
                            ? ((int) Configuration::get('PS_LGFSZP_DEFZONE') !== 0
                                ? (int) Configuration::get('PS_LGFSZP_DEFZONE')
                                : (!empty(Context::getContext()->country->id_zone) ? (int) Context::getContext()->country->id_zone : 1)
                            )
                            : $customer_debug_info['Addresses'][$a]['ZoneID'];

                        /* if ($carrier_def = Db::getInstance()->getValue(
                            'SELECT lg.id_carrier ' .
                            'FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones lg ' .
                            'WHERE lg.id_carrier = ' . (int) $transportista . ' ' .
                            'AND lg.id_zone = ' . (int) $id_zone . ' ' .
                            'AND lg.id_shop = ' . (int) $this->context->shop->id . ' ' .
                            'AND lg.active = 1 '
                        )
                        ) {
                            $transportista = $carrier_def;
                        } else {
                            $transportista = Db::getInstance()->getValue(
                                'SELECT lg.id_carrier ' .
                                'FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones lg ' .
                                'WHERE lg.id_zone = ' . (int) $id_zone . ' ' .
                                'AND lg.id_shop = ' . (int) $this->context->shop->id . ' ' .
                                'AND lg.def = 1 ' .
                                'AND lg.active = 1 '
                            );
                        } */
                        $trans = new Carrier($transportista);
                        $carrierInfo = $this->getZoCaValueAll(
                            $id_zone,
                            $trans->id_reference,
                            $customer_debug_info['Shop']['id'],
                            (int) $params['cart']->id_shop_group,
                            (int) $params['cart']->id_currency
                        );
                        $aux_gastos_envio = $params['cart']->getOrderTotal(
                            $lgfs_usetax,
                            Cart::ONLY_SHIPPING,
                            $paquete['product_list'],
                            $transportista,
                            false
                        );

                        $carrier_check = 0;
                        $carrier_zone = $this->getCarrierZone(
                            (int) $customer_debug_info['Addresses'][$a]['ZoneID'],
                            (int) $transportista
                        );
                        if (!$carrier_zone) {
                            $carrier_check = 1;
                        }
                        // Calculamos nuestros gastos de envío
                        /* $ignore_vouchers = (int) Configuration::get('PS_LGFSZP_VOUCHERS');
                        if ($ignore_vouchers == 1) {
                            $price = $params['cart']->getOrderTotal(
                                $lgfs_usetax,
                                Cart::ONLY_PRODUCTS,
                                $paquete['product_list']
                            );
                        } else {
                            $price = $params['cart']->getOrderTotal(
                                $lgfs_usetax,
                                Cart::BOTH_WITHOUT_SHIPPING,
                                $paquete['product_list']
                            );
                        } */

                        $our_shipping_costs_are_free = self::FSCheck(
                            $carrier_zone,
                            $params['cart']->getOrderTotal(
                                $lgfs_usetax,
                                Cart::BOTH_WITHOUT_SHIPPING,
                                $paquete['product_list']
                            ),
                            $params['cart']->getTotalWeight($paquete['product_list']),
                            (int) $trans->id_reference,
                            $customer_debug_info['Shop']['id'],
                            (int) $params['cart']->id_shop_group,
                            (int) $params['cart']->id_currency
                        );

                        $auxarray = [
                            'id' => $transportista,
                            'name' => $trans->name,
                            'PriceMin' => (isset($carrierInfo) && $carrierInfo['price']) ? $carrierInfo['price'] : 0,
                            'PriceMax' => (isset($carrierInfo) && $carrierInfo['price2']) ? $carrierInfo['price2'] : 0,
                            'WeightMin' => (isset($carrierInfo) && $carrierInfo['weight']) ? $carrierInfo['weight'] : 0,
                            'WeightMax' => (isset($carrierInfo) && $carrierInfo['weight2']) ? $carrierInfo['weight2'] : 0,
                            'reference' => $trans->id_reference,
                            'shippingCost' => $aux_gastos_envio,
                            'isFree' => $trans->is_free,
                            'carrierCheck' => $carrier_check,
                            'our_shipping_costs_are_free' => $our_shipping_costs_are_free,
                        ];

                        $customer_debug_info['Addresses'][$a]['Packages'][$b]['Carriers'][$transportista] = $auxarray;
                    }
                }
            }
        }

        $customer_debug_info['Totals']['NumberOfProducts'] = (int) $nbTotalProducts;
        /* $ignore_vouchers = (int) Configuration::get('PS_LGFSZP_VOUCHERS');
        if ($ignore_vouchers == 1) {
            $price = $params['cart']->getOrderTotal(
                $useTax,
                Cart::ONLY_PRODUCTS
            );
        } else {
            $price = $params['cart']->getOrderTotal(
                $useTax,
                Cart::BOTH_WITHOUT_SHIPPING
            );
        }

        $customer_debug_info['Totals']['ProductsCost'] = $price; */
        $customer_debug_info['Totals']['ProductsCost'] = $params['cart']->getOrderTotal(
            $useTax,
            Cart::BOTH_WITHOUT_SHIPPING
        );
        $customer_debug_info['Totals']['ShippingCost'] = $params['cart']->getOrderTotal(
            $useTax,
            Cart::ONLY_SHIPPING
        );
        $customer_debug_info['Totals']['Weight'] = $params['cart']->getTotalWeight();
        $weight_unit = Configuration::get('PS_WEIGHT_UNIT');

        $show_debug = (bool) ((int) Configuration::get('PS_LGFSZP_DEBUG') > 0
            && Configuration::get('PS_LGFSZP_DEBUGIP') == $_SERVER['REMOTE_ADDR']);

        $variables = [
            'debugInfo' => $customer_debug_info,
            'weight_unit' => $weight_unit,
            'left_message' => Configuration::get('PS_LGFSZP_MESSAGE'),
            // 'vouchers' => Configuration::get('PS_LGFSZP_VOUCHERS'),
            'message_color1' => Configuration::get('PS_LGFSZP_COLOR1'),
            'message_color2' => Configuration::get('PS_LGFSZP_COLOR2'),
            'lgfs_usetax' => $lgfs_usetax,
            'show_debug' => $show_debug,
            'lgfreeshipping_ps_version' => $this->ps_version,
            'lgfreshippingzones_is_ajax' => isset($params['lgfreshippingzones_is_ajax']),
            'lg_fs_url' => $this->context->link->getModuleLink($this->name, 'freeshipping', [], true),
        ];

        $this->context->smarty->assign($variables);

        // return $this->display(__FILE__, '/views/templates/front/shoppingCart.tpl');
        return $this->context->smarty->fetch($this->getTemplatePath('views/templates/front/shoppingCart.tpl'));
    }

    // Methods for process Module Backoffice Ajax Calls
    // Useful methods
    public function getActiveZoneCarriers($id_zone, $id_shop, $id_shop_group)
    {
        $sql = 'SELECT `id_carrier` ' .
            'FROM `' . _DB_PREFIX_ . 'lgfreeshippingzones` ' .
            'WHERE `id_zone` = ' . (int) $id_zone .
            '  AND `id_shop` = ' . (int) $id_shop .
            '  AND `id_shop_group` = ' . (int) $id_shop_group .
            '  AND `active` = 1';
        $result = Db::getInstance()->executeS($sql);
        $array_final = [];
        if (!empty($result)) {
            foreach ($result as $row) {
                $array_final[] = $row['id_carrier'];
            }
        }
        return $array_final;
    }

    private function getP()
    {
        $this->context->controller->addCSS(_MODULE_DIR_ . $this->name . '/views/css/publi/style.css');

        $base = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ?
            'https://' . $this->context->shop->domain_ssl :
            'http://' . $this->context->shop->domain);
        if (version_compare(_PS_VERSION_, '1.5.0', '>')) {
            $uri = $base . $this->context->shop->getBaseURI();
        } else {
            $uri = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ?
                'https://' . _PS_SHOP_DOMAIN_SSL_DOMAIN_ :
                'http://' . _PS_SHOP_DOMAIN_) . __PS_BASE_URI__;
        }

        $this->context->smarty->assign(
            [
                'publi' => $this->publi,
                'uri' => $uri,
                'lang_iso' => Language::getIsoById($this->context->language->id),
            ]
        );
    }

    private function initPubli()
    {
        $this->publi = [
            'default_language' => 'en',
            'addons_domain' => 'http://addons.prestashop.com',
            'modules' => [
                [
                    'name' => 'lgdropshipping',
                    'icon' => 'dropshipping.jpg',
                    'languages' => [
                        'en' => [
                            'module_name' => 'Emails Dropshipping',
                            'module_url' => '17943-dropshipping-automatic-emails-to-suppliers-carriers.html',
                        ],
                        'es' => [
                            'module_name' => 'Emails Dropshipping',
                            'module_url' => '17943-dropshipping-emails-a-proveedores-y-transportistas.html',
                        ],
                        'fr' => [
                            'module_name' => 'Emails Dropshipping',
                            'module_url' => '17943-dropshipping-emails-fournisseurs-et-transporteurs.html',
                        ],
                    ],
                ],
                [
                    'name' => 'lgcookieslaw',
                    'icon' => 'cookies.jpg',
                    'languages' => [
                        'en' => [
                            'module_name' => 'EU Cookie Law',
                            'module_url' => '8734-eu-cookie-law-notification-banner-cookie-blocker.html',
                        ],
                        'es' => [
                            'module_name' => 'Ley de Cookies',
                            'module_url' => '8734-ley-europea-de-cookies-aviso-bloqueador-de-cookie.html',
                        ],
                        'fr' => [
                            'module_name' => 'Directive sur les Cookies',
                            'module_url' => '8734-directive-europeenne-des-cookies-bandeau-bloqueur.html',
                        ],
                    ],
                ],
                [
                    'name' => 'lgcomments',
                    'icon' => 'opinion.jpg',
                    'languages' => [
                        'en' => [
                            'module_name' => 'Store & Product Reviews',
                            'module_url' => '17896-store-product-reviews-google-rich-snippets.html',
                        ],
                        'es' => [
                            'module_name' => 'Opiniones Tienda y Productos',
                            'module_url' => '17896-opiniones-tienda-y-productos-google-rich-snippets.html',
                        ],
                        'fr' => [
                            'module_name' => 'Avis Boutique & Produits',
                            'module_url' => '17896-avis-clients-boutique-produits-google-rich-snippets.html',
                        ],
                    ],
                ],
            ],
        ];
    }

    private function getCarriers($id_zone, $id_shop = null)
    {
        $carriers = Carrier::getCarriers($this->context->language->id, true, false, $id_zone, null, ALL_CARRIERS);
        foreach ($carriers as $k => $carrier) {
            if (!empty($id_shop)) {
                $carriers[$k]['default'] = (int) Db::getInstance()->getValue(
                    'SELECT `def` FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones ' .
                    'WHERE id_zone = ' . (int) $id_zone . ' ' .
                    'AND id_carrier = ' . $carrier['id_reference'] . ' ' .
                    'AND id_shop = ' . $id_shop
                );
            } else {
                $carriers[$k]['default'] = (int) Db::getInstance()->getValue(
                    'SELECT `def` FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones ' .
                    'WHERE id_zone = ' . (int) $id_zone . ' ' .
                    'AND id_carrier = ' . $carrier['id_reference']
                );
            }
        }
        return $carriers;
    }

    private function getZones()
    {
        $zones = Zone::getZones(true);
        return $zones;
    }

    private function getZoCaValue($id_zone, $id_carrier, $value, $id_shop = 0, $id_shop_group = 0, $to_currency = null)
    {
        $val = Db::getInstance()->getValue(
            'SELECT lg.`' . pSQL($value) . '` ' .
            'FROM `' . _DB_PREFIX_ . 'lgfreeshippingzones` lg ' .
            'INNER JOIN `' . _DB_PREFIX_ . 'carrier` c ' .
            'WHERE lg.`id_carrier` = c.`id_reference` ' .
            'AND lg.`id_zone` = ' . (int) $id_zone . ' ' .
            'AND lg.`id_shop` = ' . (int) $id_shop . ' ' .
            'AND lg.`id_shop_group` = ' . (int) $id_shop_group . ' ' .
            'AND lg.`id_carrier` = ' . (int) $id_carrier . ' ' .
            ($this->context->controller instanceof AdminModulesController ? '' : 'AND lg.`active` = 1 ')
        );

        if (!empty($to_currency) && $to_currency != $this->default_currency) {
            $val = Tools::convertPrice(
                Tools::ps_round(
                    $val,
                    $this->compute_precision
                ),
                $to_currency
            );
        }
        return $val;
    }

    private function getZoCaValueAll($id_zone, $id_carrier, $id_shop = 0, $id_shop_group = 0, $to_currency = null)
    {
        if (empty($id_shop)) {
            $id_shop = $this->context->shop->id;
        }
        if ($ancestry = $this->checkAncestry($id_zone, $id_shop, $id_shop_group, $id_carrier)) {
            $val = Db::getInstance()->getRow(
                'SELECT * FROM `' . _DB_PREFIX_ . 'lgfreeshippingzones` lg ' .
                'INNER JOIN `' . _DB_PREFIX_ . 'carrier` c ' .
                'WHERE lg.`id_carrier` = c.`id_reference` ' .
                'AND lg.`active` = 1 ' .
                'AND lg.`id_zone` = ' . (int) $id_zone . ' ' .
                'AND lg.`id_shop` = ' . (int) $ancestry['id_shop'] . ' ' .
                'AND lg.`id_shop_group` = ' . (int) $ancestry['id_shop_group'] . ' ' .
                'AND lg.`id_carrier` = ' . (int) $id_carrier
            );
        }
        if (!empty($to_currency) && $to_currency != $this->default_currency) {
            $val['price'] = Tools::convertPrice(
                Tools::ps_round(
                    $val['price'],
                    $this->compute_precision
                ),
                $to_currency
            );
            $val['price2'] = Tools::convertPrice(
                Tools::ps_round(
                    $val['price2'],
                    $this->compute_precision
                ),
                $to_currency
            );
        }
        return empty($val) ? null : $val;
    }

    private function getZoneName($id_zone)
    {
        $zoneName = Db::getInstance()->getValue(
            'SELECT name ' .
            'FROM ' . _DB_PREFIX_ . 'zone ' .
            'WHERE id_zone = ' . (int) $id_zone
        );
        return $zoneName;
    }

    private function getZoneId($id_country)
    {
        $zoneId = Db::getInstance()->getValue(
            'SELECT id_zone ' .
            'FROM ' . _DB_PREFIX_ . 'country ' .
            'WHERE id_country = ' . (int) $id_country
        );
        return $zoneId;
    }

    private function getCarrierName($id_carrier)
    {
        $carrierName = Db::getInstance()->getValue(
            'SELECT name ' .
            'FROM ' . _DB_PREFIX_ . 'carrier ' .
            'WHERE id_reference = ' . (int) $id_carrier .
            ' ORDER BY id_carrier DESC'
        );
        return $carrierName;
    }

    private function getCarrierId($id_carrier)
    {
        $carrierId = Db::getInstance()->getValue(
            'SELECT id_carrier ' .
            'FROM ' . _DB_PREFIX_ . 'carrier ' .
            'WHERE id_reference = ' . (int) $id_carrier .
            ' ORDER BY id_carrier DESC'
        );
        return $carrierId;
    }

    private function getCarrierIsFree($id_carrier)
    {
        $isFree = Db::getInstance()->getValue(
            'SELECT is_free ' .
            'FROM ' . _DB_PREFIX_ . 'carrier ' .
            'WHERE id_reference = ' . (int) $id_carrier .
            ' ORDER BY id_carrier DESC'
        );
        return $isFree;
    }

    private function getCarrierZone($id_zone, $id_carrier)
    {
        $carrierZone = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'carrier_zone ' .
            'WHERE id_zone = ' . (int) $id_zone . ' ' .
            'AND id_carrier = ' . (int) $id_carrier
        );
        return $carrierZone;
    }

    private function getFreeShipping($id_zone = null, $id_shop = null, $id_shop_group = null, $id_carrier = null)
    {
        $freeShipping = Db::getInstance()->getRow(
            'SELECT * FROM ' . _DB_PREFIX_ . 'lgfreeshippingzones lg ' .
            'INNER JOIN `' . _DB_PREFIX_ . 'carrier` c ON lg.`id_carrier` = c.`id_reference` ' .
            'WHERE lg.`id_zone` = ' . (int) $id_zone . ' ' .
            'AND lg.`id_shop` = ' . (int) $id_shop . ' ' .
            'AND lg.`id_shop_group` = ' . (int) $id_shop_group . ' ' .
            'AND lg.`id_carrier` = ' . (int) $id_carrier
        );
        return $freeShipping;
    }

    protected function checkAncestry($id_zone, $id_shop, $id_shop_group, $id_carrier)
    {
        return Db::getInstance()->getRow(
            'SELECT `id_shop`,`id_shop_group` FROM `' . _DB_PREFIX_ . 'lgfreeshippingzones` lg ' .
            'INNER JOIN `' . _DB_PREFIX_ . 'carrier` c ON lg.`id_carrier` = c.`id_reference` ' .
            'WHERE lg.`active` = 1 ' .
            'AND lg.`id_zone` = ' . (int) $id_zone . ' ' .
            'AND lg.`id_carrier` = ' . (int) $id_carrier . ' ' .
            'AND ( lg.`id_shop` = ' . (int) $id_shop . ' OR ' .
            '(lg.`id_shop_group` = ' . (int) $id_shop_group . ' AND lg.`id_shop` = 0) OR ' .
            '(lg.`id_shop_group` = 0 AND lg.`id_shop` = 0) )'
        );
    }

    public function FSCheck($id_zone, $price, $weight, $id_carrier, $id_shop, $id_shop_group, $id_currency = null)
    {
        foreach ($this->getZones() as $zona) {
            if ($zona['id_zone'] == $id_zone) {
                foreach ($this->getCarriers($zona['id_zone']) as $carrier) {
                    if ($id_carrier == $carrier['id_carrier']
                        && $ancestry = $this->checkAncestry($id_zone, $id_shop, $id_shop_group, $carrier['id_reference'])
                    ) {
                        $price1 = $this->getZoCaValue(
                            $zona['id_zone'],
                            $carrier['id_reference'],
                            'price',
                            (int) $ancestry['id_shop'],
                            (int) $ancestry['id_shop_group'],
                            $id_currency
                        );
                        $price2 = $this->getZoCaValue(
                            $zona['id_zone'],
                            $carrier['id_reference'],
                            'price2',
                            (int) $ancestry['id_shop'],
                            (int) $ancestry['id_shop_group'],
                            $id_currency
                        );
                        $weight1 = $this->getZoCaValue(
                            $zona['id_zone'],
                            $carrier['id_reference'],
                            'weight',
                            (int) $ancestry['id_shop'],
                            (int) $ancestry['id_shop_group'],
                            $id_currency
                        );
                        $weight2 = $this->getZoCaValue(
                            $zona['id_zone'],
                            $carrier['id_reference'],
                            'weight2',
                            (int) $ancestry['id_shop'],
                            (int) $ancestry['id_shop_group'],
                            $id_currency
                        );
                        // 15 different possibilities
                        if ($price1 > 0 and $price2 > 0 and $weight1 > 0 and $weight2 > 0) {
                            if (($price >= $price1 or $weight >= $weight1)
                                and ($price <= $price2 and $weight <= $weight2)
                            ) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 > 0 and $weight1 > 0 and $weight2 == 0) {
                            if (($price >= $price1 or $weight >= $weight1) and $price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 > 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($price >= $price1 and ($price <= $price2 and $weight <= $weight2)) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 > 0 and $weight1 == 0 and $weight2 == 0) {
                            if ($price >= $price1 and $price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 > 0 and $weight2 > 0) {
                            if (($price >= $price1 or $weight >= $weight1) and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 > 0 and $weight2 == 0) {
                            if ($price >= $price1 or $weight >= $weight1) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($price >= $price1 and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 > 0 and $price2 == 0 and $weight1 == 0 and $weight2 == 0) {
                            if ($price >= $price1) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 > 0 and $weight2 > 0) {
                            if ($weight >= $weight1 and ($price <= $price2 and $weight <= $weight2)) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 > 0 and $weight2 == 0) {
                            if ($weight >= $weight1 and $price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($price <= $price2 and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 > 0 and $weight1 == 0 and $weight2 == 0) {
                            if ($price <= $price2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 == 0 and $weight1 > 0 and $weight2 > 0) {
                            if ($weight >= $weight1 and $weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 == 0 and $weight1 > 0 and $weight2 == 0) {
                            if ($weight >= $weight1) {
                                return true;
                            } else {
                                return false;
                            }
                        } elseif ($price1 == 0 and $price2 == 0 and $weight1 == 0 and $weight2 > 0) {
                            if ($weight <= $weight2) {
                                return true;
                            } else {
                                return false;
                            }
                        }
                    }
                }
            }
        }
        return false;
    }
}
