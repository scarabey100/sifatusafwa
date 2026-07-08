<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class AdminGiftCardController extends ModuleAdminController
{
    protected $position_identifier = 'id_product';

    public function __construct()
    {
        if (!$this->module) {
            $this->module = Module::getInstanceByName('giftcard');
        }
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->translator = Context::getContext()->getTranslator();
        }
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'GiftCardProduct';
        $this->lang = false;
        $currencies_array = [];
        $currencies = Currency::getCurrencies();
        foreach ($currencies as $currencyitem) {
            $currencies_array[$currencyitem['id_currency']] = $currencyitem['name'] . ' ' . $currencyitem['sign'];
        }
        $this->fields_list = [
            'id_product' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'width' => 30,
            ],
            'image' => [
                'title' => $this->l('Photo'),
                'align' => 'center',
                'width' => 70,
                'orderby' => false,
                'image' => 'p',
                'filter' => false,
                'search' => false,
            ],
            'id_currency' => [
                'title' => $this->l('Display currency'),
                'width' => 'auto',
                'align' => 'center',
                'type' => 'select',
                'list' => $currencies_array,
                'filter_key' => 'curr!id_currency',
                'filter_type' => 'int',
                'order_key' => 'currname',
                'callback' => 'printCurrency',
            ],
            'product_name' => [
                'title' => $this->l('Name'),
                'width' => 'auto',
                'filter_key' => 'pl!name',
            ],
            'amount' => [
                'title' => $this->l('Price'),
                'align' => 'center',
                'callback' => 'printPrice',
                'width' => 90,
            ],
            'cr_partial_use' => [
                'title' => $this->l('Partial Use'),
                'width' => 70,
                'search' => false,
                'callback' => 'printPartialUseIcon',
                'align' => 'center',
                'orderby' => false,
            ],
            'virtualcard' => [
                'title' => $this->l('eCard'),
                'width' => 70,
                'align' => 'center',
                'callback' => 'printVirtualCardIcon',
                'orderby' => false,
            ],
            'isdefaultgiftcard' => [
                'title' => $this->l('Default'),
                'align' => 'center',
                'width' => 70,
                'callback' => 'printDefaultIcon',
                'orderby' => false,
            ],
            'active' => [
                'title' => $this->l('Status'),
                'width' => 70,
                'active' => 'status',
                'search' => false,
                'align' => 'center',
                'type' => 'bool',
                'orderby' => false,
            ],
        ];
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->fields_list['shopname'] = [
                'title' => $this->l('Default shop'),
                'filter_key' => 'shop!name',
            ];
        }
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
            ],
            'enableSelection' => [
                'text' => $this->l('Enable selection'),
            ],
            'disableSelection' => [
                'text' => $this->l('Disable selection'),
            ],
        ];
        $this->identifier = 'id_product';
        $this->context = Context::getContext();
        $this->fieldImageSettings = [
            'name' => 'image_product',
            'dir' => 'p',
        ];
        parent::__construct();
    }

    public function setMedia($isNewtheme = false)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '<')) {
            parent::setMedia();
        } else {
            parent::setMedia($isNewtheme);
        }
        $this->addCSS([
            _MODULE_DIR_ . 'giftcard/views/css/admin-ta-common.css',
            _MODULE_DIR_ . 'giftcard/views/css/icons/flaticon.css',
        ]);
        if (version_compare(_PS_VERSION_, '1.6.0', '<') === true) {
            $this->addCSS(_MODULE_DIR_ . 'giftcard/views/css/admin-ta-commonps15.css');
        }
    }

    public function renderList()
    {
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');
        $this->_select = 'g.*,pl.`name` as product_name,a.price,a.`active`,i.id_image,curr.name as currname';
        $this->_join = 'INNER JOIN `' . _DB_PREFIX_ . 'giftcardproduct` g ON a.id_product = g.id_product
						INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON a.id_product = pl.id_product
						    AND id_lang=' . (int) $this->context->language->id . '
						LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = a.`id_product` and i.`cover` = 1)
						LEFT JOIN `' . _DB_PREFIX_ . 'currency` curr ON (curr.`id_currency` = g.`id_currency`)';
        // we add restriction for shop
        $id_shop = Shop::isFeatureActive()
        && Shop::getContext() == Shop::CONTEXT_SHOP ?
            (int) $this->context->shop->id :
            'a.id_shop_default';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_shop` sa
            ON (a.`id_product` = sa.`id_product` AND sa.id_shop = ' . $id_shop . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . $id_shop . ') ';
        $this->_group = 'GROUP BY a.id_product';
        $lists = parent::renderList();
        parent::initToolbar();
        $this->context->smarty->assign([
            'ta_gc_tab_select' => 'giftcardproduct',
            'link' => $this->context->link,
        ]);
        $html = $this->context->smarty->fetch(parent::getTemplatePath() . '/menu-top.tpl');
        $html .= $lists;
        $html .= $this->context->smarty->fetch(parent::getTemplatePath() . 'footer-module.tpl');

        return $html;
    }

    public function displayEditLink($token, $id, $name = null)
    {
        if ($this->access('edit')) {
            $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');
            if (!array_key_exists('Edit', self::$cache_lang)) {
                self::$cache_lang['Edit'] = $this->trans('Edit', [], 'Admin.Actions');
            }

            $tpl->assign([
                'href' => $this->context->link->getAdminLink('AdminGiftCard') . '&id_product=' . (int) $id . '&update' . $this->table,
                'action' => self::$cache_lang['Edit'],
                'id' => $id,
            ]);

            return $tpl->fetch();
        } else {
            return;
        }
    }

    public function initProcess()
    {
        parent::initProcess();
        
        /* check access if issue specific action */
        $access = Profile::getProfileAccess(
            $this->context->employee->id_profile,
            (int) Tab::getIdFromClassName('AdminGiftCard')
        );

        if (Tools::getIsset('duplicate' . $this->table)) {
            if ($access['edit'] === '1') {
                $this->action = 'duplicate';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }
        }
        if ($access['view'] === '1' && ($action = Tools::getValue('submitAction'))) {
            $this->action = $action;
        }
        if (Tools::isSubmit('changeDefaultVal') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_default_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
        if (Tools::isSubmit('changeVirtualCard') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_virtual_card_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
        if (Tools::isSubmit('changePartialUseVal') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_partial_use_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
        if (Tools::isSubmit('changeFreeShipVal') && $this->id_object) {
            if ($access['edit'] === '1') {
                $this->action = 'change_free_ship_val';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }
        }
               
    }

    /* Change default giftcard product */
    public function processChangeDefaultVal()
    {
        $gift_card = new GiftCardProduct($this->id_object);
        if (!Validate::isLoadedObject($gift_card)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value_default = $gift_card->isdefaultgiftcard ? 0 : 1;
        if ((int) $value_default == 0) {
            $this->errors[] = Tools::displayError('Default is required');
        } else {
            if (!$gift_card->changeToDefault()) {
                $this->errors[] = Tools::displayError('An error occurred while updating information.');
            }
        }
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function processChangePartialUseVal()
    {
        $gift_card = new GiftCardProduct($this->id_object);
        if (!Validate::isLoadedObject($gift_card)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value = $gift_card->cr_partial_use ? 0 : 1;
        $gift_card->cr_partial_use = $value;
        $gift_card->update();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function processChangeVirtualCardVal()
    {
        $gift_card = new GiftCardProduct($this->id_object);
        if (!Validate::isLoadedObject($gift_card)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value = $gift_card->virtualcard ? 0 : 1;
        $gift_card->virtualcard = $value;
        $gift_card->update();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function processChangeFreeShipVal()
    {
        $gift_card = new GiftCardProduct($this->id_object);
        if (!Validate::isLoadedObject($gift_card)) {
            $this->errors[] = Tools::displayError('An error occurred while updating rule information.');
        }
        $value = $gift_card->cr_free_shipping ? 0 : 1;
        $gift_card->cr_free_shipping = $value;
        $gift_card->update();
        Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
    }

    public function postProcess()
    {
        $this->addJqueryUI([
            'ui.core',
            'ui.widget',
        ]);
        if (!count($this->errors)) {
            parent::postProcess();
        }
    }

    public function processAdd()
    {
        if (!isset($this->className) || empty($this->className)) {
            return false;
        }
        $this->object = new $this->className();
        $this->copyFromPost($this->object, $this->table);
        /* Init value */
        $this->initAndVal();
        if (!empty($this->errors)) {
            $this->display = 'add';

            return false;
        }
        if (!$this->object->add()) {
            $this->errors[] = Tools::displayError('An error occurred while creating an object.') .
                ' <b>' . $this->table . '</b>';
        } else {
            $this->updateAssoShop($this->object->id);
            StockAvailable::setQuantity($this->object->id, 0, (int) Tools::getValue('quantity'));
            /*
             * if (version_compare(_PS_VERSION_, '1.6.0.8', '>') === true)
             * {
             * if (Configuration::get('PS_FORCE_ASM_NEW_PRODUCT') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'))
             * {
             * $this->object->advanced_stock_management = 1;
             * StockAvailable::setProductDependsOnStock($this->object->id, true, (int)$this->context->shop->id, 0);
             * $this->object->save();
             * }
             * }
             */
            $currency_default = (int) Configuration::get('PS_CURRENCY_DEFAULT');
            $currencies = Currency::getCurrencies();
            foreach ($currencies as $currencyitem) {
                if ((int) $currencyitem['id_currency'] != $currency_default
                    && ((int) $this->object->id_currency == 0
                        || (int) $currencyitem['id_currency'] == (int) $this->object->id_currency)) {
                    GiftCardTools::addFixedPrice(
                        $this->object->id,
                        (int) $currencyitem['id_currency'],
                        (float) Tools::getValue('amount')
                    );
                }
            }
            if (version_compare(_PS_VERSION_, '1.6.0.8', '>') === true) {
                if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT') != 0
                    && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                    $warehouse_location_entity = new WarehouseProductLocation();
                    $warehouse_location_entity->id_product = $this->object->id;
                    $warehouse_location_entity->id_product_attribute = 0;
                    $warehouse_location_entity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
                    $warehouse_location_entity->location = pSQL('');
                    $warehouse_location_entity->save();
                }
            }
            $gift_card_template = GiftCardTemplate::getDefault();
            if (!$gift_card_template) {
                $this->error[] = $this->l('Template default is required to create product image');
            } elseif (!$gift_card_template->generateProductImage($this->object->id)) {
                $this->error[] = $this->l('Error occuring while creating default image');
            } else {
                Hook::exec('actionProductAdd', [
                    'product' => $this->object,
                ]);
                $this->confirmations[] = $this->l('The card has successfully created');
            }
        }

        return $this->object;
    }

    private function removeFixedPrice($id_product)
    {
        SpecificPrice::deleteByProductId($id_product);
    }

    public function processUpdate()
    {
        $this->copyFromPost($this->object, $this->table);
        $this->initAndVal();
        if (!empty($this->errors)) {
            $this->display = 'edit';

            return false;
        } else {
            if ($this->object->update()) {
                $this->updateAssoShop($this->object->id);
                StockAvailable::setQuantity($this->object->id, 0, (int) Tools::getValue('quantity'));
                $this->removeFixedPrice($this->object->id);
                $currency_default = (int) Configuration::get('PS_CURRENCY_DEFAULT');
                $currencies = Currency::getCurrencies();
                foreach ($currencies as $currencyitem) {
                    if ((int) $currencyitem['id_currency'] != $currency_default) {
                        GiftCardTools::addFixedPrice(
                            $this->object->id,
                            (int) $currencyitem['id_currency'],
                            (float) Tools::getValue('amount')
                        );
                    }
                }
                $image = Image::getCover($this->object->id);
                $gift_card_template = GiftCardTemplate::getDefault();
                if (!$gift_card_template) {
                    $this->error[] = $this->l('Template default is required to create product image');
                } elseif ($gift_card_template->issvg
                    && !$gift_card_template->generateProductImage($this->object->id)) {
                    $this->error[] = $this->l('Error occuring while creating default image');
                } elseif (!$gift_card_template->issvg
                    && !$image
                    && !$gift_card_template->generateProductImage($this->object->id)) {
                    $this->error[] = $this->l('Error occuring while creating default image');
                } else {
                    $this->confirmations[] = $this->l('The card has successfully updated');
                }
            } else {
                $this->errors[] = $this->l('An error occurred while updated the object.');
            }
        }
    }

    public function initAndVal()
    {
        $languages = Language::getLanguages(false);
        /* name is required in default language */
        $default_language = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        if (!Tools::getValue('name_' . $default_language->id)) {
            $this->errors[] = $this->l('The name is empty. You will at least need to enter a name for the default language before you can save.');
        }
        foreach ($languages as $language) {
            $product_name = Tools::getValue('name_' . (int) $language['id_lang'], '');
            $this->object->name[(int) $language['id_lang']] = $product_name;
        }
        if (!Tools::getValue('amount')
            || !Validate::isPrice(Tools::getValue('amount'))
            || !(((float) Tools::getValue('amount')) > 0)) {
            $this->errors[] = $this->l('The field price is required and must be superior at 0');
        }
        if (Tools::getValue('ean13')
            && trim(Tools::getValue('ean13')) != ''
            && !Validate::isEan13(Tools::getValue('ean13'))
        ) {
            $this->errors[] = $this->l('The field ean13 is not valid, length (25) must be between 0 and 13');
        }
        if (Tools::getValue('upc')
            && trim(Tools::getValue('upc')) != ''
            && !Validate::isUpc(Tools::getValue('upc'))) {
            $this->errors[] = $this->l('The field upc is not valid');
        }
        if (trim(Tools::getValue('period_val')) == ''
            || (!(int) Tools::getValue('period_val') > 0
                || (int) Tools::getValue('period_val') > 1000)) {
            $this->errors[] =
                $this->l('The field period_val is required and must be superior at 0 and inferior at 1000');
        }
        if (trim(Tools::getValue('quantity')) == '' || !((int) Tools::getValue('quantity') > 0)) {
            $this->errors[] = $this->l('The field quantity is required and must be superior at 0');
        }
        $this->object->id_currency = (int) Tools::getValue('id_currency');
        $this->object->id_tax_rules_group = 0;
        $this->object->price = (float) Tools::getValue('amount');
        $this->object->amount = (float) Tools::getValue('amount');
        $this->object->reference = Tools::getValue('reference', '');
        $this->object->ean13 = Tools::getValue('ean13', '');
        $this->object->upc = Tools::getValue('upc', '');
        if (empty($this->errors)) {
            $currency_default = (int) Configuration::get('PS_CURRENCY_DEFAULT');
        }
    }

    public function renderForm()
    {
        if (!($obj = $this->loadObject(true))) {
            return;
        }
        $this->toolbar_btn['save-and-stay'] = [
            'href' => '#',
            'desc' => $this->l('Save and Stay'),
        ];
        $current_object = $this->loadObject(true);
        $languages = Language::getLanguages();
        $default_currency = new Currency((int) Configuration::get('PS_CURRENCY_DEFAULT'));
        $currencies = Currency::getCurrencies();
        $imageproduct = false;
        $imagick = false;
        if (extension_loaded('imagick')) {
            $imagick = true;
        }
        if ($this->object->id) {
            $image = Image::getCover($this->object->id);
            if ($image && isset($image['id_image'])) {
                $image_product = new Image($image['id_image']);
                $image = ImageManager::thumbnail(
                    _PS_IMG_DIR_ . 'p/' . $image_product->getExistingImgPath() . '.jpg',
                    'product_mini_' . (int) $this->object->id . '.jpg',
                    45,
                    'jpg'
                );
                $this->fields_value = [];
                $imageproduct = [
                    'image' => $image ? $image : false,
                    'size' => $image ? filesize(_PS_IMG_DIR_ . 'p/' . $image_product->getExistingImgPath() . '.jpg') / 1000 :
                        false,
                ];
            }
        }
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Gift Card'),
                'icon' => 'icon-credit-card',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Name:'),
                    'name' => 'name',
                    'lang' => true,
                    'required' => true,
                    'class' => 'copy2friendlyUrl',
                    'hint' => $this->l('Invalid characters:') . ' <>;=#{}',
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6.0', '>=') ? 'switch' : 'radio'),
                    'label' => $this->l('eCard:'),
                    'name' => 'virtualcard',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'virtualcard_yes',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'virtualcard_no',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Currency'),
                    'name' => 'id_currency',
                    'desc' => $this->l('Gift card only visible.'),
                    'required' => true,
                    'options' => [
                        'query' => $currencies,
                        'id' => 'id_currency',
                        'name' => 'name',
                        'default' => [
                            'label' => $this->l('All Currencies'),
                            'value' => 0,
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Price'),
                    'name' => 'amount',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Quantity'),
                    'name' => 'quantity',
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Period of validity'),
                    'name' => 'period_val',
                    'suffix' => $this->l('Month'),
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Reference'),
                    'name' => 'reference',
                    'required' => false,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Ean13'),
                    'name' => 'ean13',
                    'required' => false,
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Upc'),
                    'name' => 'upc',
                    'required' => false,
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6.0', '>=') ? 'switch' : 'radio'),
                    'label' => $this->l('Free Shipping'),
                    'name' => 'cr_free_shipping',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'freeship_yes',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'freeship_no',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6.0', '>=') ? 'switch' : 'radio'),
                    'label' => $this->l('Partial Use'),
                    'name' => 'cr_partial_use',
                    'hint' => $this->l('Only applicable if the voucher value is greater than the cart total.') . ' ' .
                        $this->l('If you do not allow partial use, the voucher value will be lowered to the total order amount. If you allow partial use, however, a new voucher will be created with the remainder.'),
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'partial_use_yes',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id' => 'partial_use_no',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
                [
                    'type' => (version_compare(_PS_VERSION_, '1.6.0', '>=') ? 'switch' : 'radio'),
                    'label' => $this->l('Status'),
                    'name' => 'active',
                    'class' => 't',
                    'is_bool' => true,
                    'values' => [
                        [
                            'id' => 'active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
                'name' => 'submitAdd' . $this->table . 'AndBackToParent',
            ],
        ];
        if (Shop::isFeatureActive()) {
            $this->fields_form['input'][] = [
                'type' => 'shop',
                'label' => $this->l('Shop association'),
                'name' => 'checkBoxShopAsso',
            ];
        }
        /*
     * $this->fields_form ['submit'] = array ( 'title' => $this->l ( 'Save ' ), 'class' => 'button' );
     */
        $this->context->smarty->assign([
            'show_toolbar' => true,
            'title' => $this->l('Gift Card'),
            '	' => $default_currency,
            'defaultCurrency' => Configuration::get('PS_CURRENCY_DEFAULT'),
            'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
            'languages' => $languages,
            'currencies' => Currency::getCurrencies(),
            'currentIndex' => self::$currentIndex,
            'currentToken' => $this->token,
            'currentObject' => $current_object,
            'ps_version' => _PS_VERSION_,
            'image_product' => $imageproduct,
            'currentTab' => $this,
            'toolbar_btn' => $this->toolbar_btn,
            'toolbar_scroll' => $this->toolbar_scroll,
            'giftcard_img_dir' => _MODULE_DIR_ . 'giftcard/img/',
            'imagick' => $imagick,
        ]);

        return parent::renderForm();
    }

    public function printCurrency($value, $giftcard)
    {
        if (version_compare(_PS_VERSION_, '1.6.0', '>=')) {
            if ((int) $giftcard['id_currency'] > 0) {
                $currency = new Currency((int) $giftcard['id_currency']);

                return '<span class="badge" style="background-color:#000;color:#FFF;">' .
                    $currency->name . ' ' . $currency->sign . '</span>';
            }

            return '<span class="badge" style="background-color:green;color:#FFF;">' . $this->l('ALL') . '</span>';
        } else {
            if ((int) $giftcard['id_currency'] > 0) {
                $currency = new Currency((int) $giftcard['id_currency']);

                return '<span class="color_field" ' .
                    'style="background-color:#000;color:#FFF;font-weight:bold;font-size:13px;">' .
                    $currency->name . ' ' . $currency->sign . '</span>';
            }

            return '<span class="color_field" ' .
                'style="background-color:green;color:#FFF;font-weight:bold;font-size:13px;">' .
                $this->l('ALL') . '</span>';
        }
    }

    public static function printImage($value, $giftcard)
    {
        $image = Image::getCover((int) $giftcard['id_product']);
        if ($image && isset($image['id_image'])) {
            $image_product = new Image($image['id_image']);
            $image = ImageManager::thumbnail(
                _PS_IMG_DIR_ . 'p/' . $image_product->getExistingImgPath() . '.jpg',
                'product_mini_' . (int) $giftcard['id_product'] . '.jpg',
                45,
                'jpg'
            );

            return _PS_IMG_DIR_ . 'p/' . $image_product->getExistingImgPath() . '.jpg';
        }

        return '';
    }

    public static function printPrice($value, $giftcard)
    {
        if ((int) $giftcard['id_currency'] == 0) {
            return (float) $giftcard['amount'];
        }

        return Tools::displayPrice((float) $giftcard['amount'], (int) $giftcard['id_currency']);
    }

    public function printDefaultIcon($value, $gift_card)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return '<a class="list-action-enable ' .
                ($value ? 'action-enabled' : 'action-disabled') .
                '" href="index.php?tab=AdminGiftCard&id_product=' . (int) $gift_card['id_product'] .
                '&changeDefaultVal&token=' . Tools::getAdminTokenLite('AdminGiftCard') . '">
                ' . ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=AdminGiftCard&id_product=' .
                (int) $gift_card['id_product'] . '&changeDefaultVal&token=' . Tools::getAdminTokenLite('AdminGiftCard') . '">
                ' . ((int) $value ? '<img src="../img/admin/enabled.gif" alt="' .
                    $this->l('Default') . '" title="' . $this->l('Default') . '"/>' :
                    '<img src="../img/admin/disabled.gif" alt="' . $this->l('Not Default') . '" title="' .
                    $this->l('Not Default') . '"/>') . '</a>';
        }
    }

    public function printPartialUseIcon($value, $gift_card)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return '<a class="list-action-enable ' .
                ($value ? 'action-enabled' : 'action-disabled') .
                '" href="index.php?tab=AdminGiftCard&id_product=' .
                (int) $gift_card['id_product'] . '&changePartialUseVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCard') . '">' .
                ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=AdminGiftCard&id_product=' .
                (int) $gift_card['id_product'] .
                '&changePartialUseVal&token=' .
                Tools::getAdminTokenLite('AdminGiftCard') . '">' .
                ((int) $value ?
                    '<img src="../img/admin/enabled.gif" alt="' .
                    $this->l('Yes') . '" title="' . $this->l('Yes') . '"/>' :
                    '<img src="../img/admin/disabled.gif" alt="' . $this->l('No') . '" title="' . $this->l('No') . '"/>') . '</a>';
        }
    }

    public function printVirtualCardIcon($value, $gift_card)
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=')) {
            return '<a class="list-action-enable ' .
                ($value ? 'action-enabled' : 'action-disabled') .
                '" href="index.php?tab=AdminGiftCard&id_product=' .
                (int) $gift_card['id_product'] . '&changeVirtualCard&token=' . Tools::getAdminTokenLite('AdminGiftCard') . '">
				' . ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>') . '</a>';
        } else {
            return '<a href="index.php?tab=AdminGiftCard&id_product=' . (int) $gift_card['id_product'] .
                '&changeVirtualCard&token=' . Tools::getAdminTokenLite('AdminGiftCard') . '">' .
                ((int) $value ? '<img src="../img/admin/enabled.gif" alt="' .
                    $this->l('Yes') . '" title="' . $this->l('Yes') . '"/>' : '<img src="../img/admin/disabled.gif" alt="' .
                    $this->l('No') . '" title="' . $this->l('No') . '"/>') . '</a>';
        }
    }

    public function printFreeShipIcon($value, $gift_card)
    {
        return '<a href="index.php?tab=AdminGiftCard&id_product=' .
            (int) $gift_card['id_product'] . '&changeFreeShipVal&token=' .
            Tools::getAdminTokenLite('AdminGiftCard') . '">' .
            ((int) $value ? '<img src="../img/admin/enabled.gif" alt="' . $this->l('Yes') .
                '" title="' . $this->l('Yes') . '"/>' : '<img src="../img/admin/disabled.gif" alt="' . $this->l('No') .
                '" title="' . $this->l('No') . '"/>') . '</a>';
    }

    public function processDuplicate()
    {
        $giftcardnew = GiftCardProduct::duplicate((int) Tools::getValue('id_product'));
        if ($giftcardnew) {
            Hook::exec('actionProductAdd', [
                'product' => $giftcardnew,
            ]);
        } else {
            $this->errors[] = $this->l('An error occurred while creating an object.');
        }
    }
}
