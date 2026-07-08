<?php
/**
 * DISCLAIMER.
 *
 * Do not edit or add to this file if you wish to upgrade this Module to
 * newer versions in the future. If you wish to customize the Module for
 * your needs please use "themes/" or/and "override/modules" directories
 * or refer to http://www.prestashop.com for more information.
 *
 *  .--.
 *  |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
 *  |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
 *  '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
 *       w w w . d r e a m - m e - u p . f r       '
 *
 * @author    Dream me up <prestashop@dream-me-up.fr>
 * @copyright 2007 - 2024 Dream me up
 * @license   All Rights Reserved
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminDmuPromosController extends ModuleAdminController
{
    public $campagne;

    public $show_campaign = false;
    public $filter_id_category;
    public $filter_id_manufacturer;
    public $filter_keywords;
    public $filter_orderby = 'id_product';
    public $filter_orderway = 'ASC';
    public $filter_stock = 0;

    public function __construct()
    {
        require_once dirname(__FILE__) . '/../../classes/CampagnesPromos.php';

        $this->module = 'dmupromos';
        $this->context = Context::getContext();
        $this->bootstrap = true;

        $this->token = Tools::getAdminToken(
            $this->module . (int) Tab::getIdFromClassName($this->module) . (int) Context::getContext()->cookie->id_employee
        );

        parent::__construct();

        $this->table = 'promos_campagnes';
        $this->identifier = 'id_promos_campagnes';
        $this->className = 'CampagnesPromos';

        CampagnesPromos::verifyDeletedSpecificPrices();

        $this->fields_list = [
            'id_promos_campagnes' => [
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'width' => '20',
            ],
            'name' => [
                'title' => $this->l('Name'),
                'filter_key' => 'a!name',
                'width' => 'auto',
                'callback' => 'callbackListName',
            ],
            'information' => [
                'title' => $this->l('Information'),
                'width' => 'auto',
                'search' => false,
                'orderby' => false,
                'callback' => 'callbackListInfo',
            ],
            'date_debut' => [
                'title' => $this->l('Start date'),
                'align' => 'right',
                'type' => 'datetime',
                'width' => '130',
            ],
            'date_fin' => [
                'title' => $this->l('End date'),
                'align' => 'right',
                'type' => 'datetime',
                'width' => '130',
            ],
            'status' => [
                'title' => $this->l('Status'),
                'width' => 'auto',
                'search' => false,
                'orderby' => false,
                'callback' => 'callbackListStatus',
            ],
        ];
        if (Shop::getTotalShops() > 1 && !Shop::getContextShopID()) {
            $this->fields_list['id_shop'] = [
                'title' => $this->l('Shop'),
                'width' => 'auto',
                'search' => false,
                'orderby' => false,
                'callback' => 'callbackShop',
            ];
        }

        $this->_defaultOrderBy = 'date_fin';
        $this->_defaultOrderWay = 'DESC';
        $orderby = 'dmupromos' . $this->table . 'Orderby';
        $orderway = 'dmupromos' . $this->table . 'Orderway';
        if (!isset($this->context->cookie->$orderby) && !isset($this->context->cookie->$orderway)) {
            $this->context->cookie->$orderby = 'date_fin';
            $this->context->cookie->$orderway = 'DESC';
        }

        // Rajout des colonnes « Informations » & « Statut »
        $this->_select .= 'a.id_promos_campagnes as information,
            IF (date_fin > NOW(), IF (date_debut < NOW(), 2, 1), 0) as status';

        // Affichage des campagnes du magasin sélectionné uniquement
        if ($id_shop = Shop::getContextShopID()) {
            $this->_where .= ' AND a.id_shop = ' . (int) $id_shop;
        }

        // Si possibilité Instanciation de la Campagne
        if (!$this->campagne && Tools::getIsset('id_promos_campagnes')) {
            $id_promos_campagnes = (int) Tools::getValue('id_promos_campagnes');
            $this->campagne = new CampagnesPromos($id_promos_campagnes);
        }
    }

    public function postProcess()
    {
        if (Tools::getIsset('submit_bulk_update_form')) {
            $products = Tools::getValue('products');
            $discount = Tools::getValue('discount_input') . Tools::getValue('discount_select');
            $on_sale = Tools::getValue('onsale_checkbox');
            $filterOrderway = Tools::getValue('filter_orderway');
            $filterOrderby = Tools::getValue('filter_orderby');
            $paginationPage = $_GET['paginationPage'] > 0 ? $_GET['paginationPage'] : 1; //Tools::getValue('submitFilterproductslist');
            $perPage = $_GET['perPage'] > 0 ? $_GET['perPage'] : 50;
            $on_sale = ('on' == $on_sale ? 1 : 0);
            $array_prods = explode(',', $products);
            foreach ($array_prods as $id_product) {
                $this->setDiscount($id_product, 0, $discount);
                $this->setOnSale($id_product, $on_sale);
            }


            $this->filter_orderway = Tools::getValue('filter_orderway');
            $this->filter_orderby = Tools::getValue('filter_orderby');

            Tools::redirectAdmin(
                self::$currentIndex . '&submitFilterproductslist='.$paginationPage.'&paginationPage='.$paginationPage.'&perPage='.$perPage.'&filter_orderway='.$filterOrderway.'&filter_orderby='.$filterOrderby.'&token=' . Tools::getAdminTokenLite('AdminDmuPromos') .
                '&conf=4&viewpromos_campagnes&id_promos_campagnes=' . (int) Tools::getValue('id_promos_campagnes')
            );
        }

        parent::postProcess();

        // Suppression multiple de campagne
        if (Tools::getIsset('submitBulkdeletepromos_campagnes')) {
            if (Tools::getIsset('promos_campagnesBox')) {
                $promos_campagnes_ids_list = Tools::getValue('promos_campagnesBox');
                foreach ($promos_campagnes_ids_list as $id_promos_campagnes) {
                    $campagne = new CampagnesPromos($id_promos_campagnes);
                    if (!$campagne->delete()) {
                        // Erreur !
                    }
                }
                Tools::redirectAdmin(
                    self::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminDmuPromos') . '&conf=2'
                );
            }
        }

        // Duplication de campagne
        if (Tools::getIsset('duplicatepromos_campagnes')) {
            if (Validate::isLoadedObject($campagne = $this->campagne)) {
                $old_id_promos_campagnes = $campagne->id;

                $date_end = time() - 1;
                $date_start = $date_end - 1;

                unset($campagne->id);
                unset($campagne->id_promos_campagnes);
                $campagne->name .= ' (' . $this->l('copy') . ')';
                $campagne->date_debut = date('Y-m-d H:i:s', $date_start);
                $campagne->date_fin = date('Y-m-d H:i:s', $date_end);
                if ($campagne->add()) {
                    $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                            WHERE id_promos_campagnes = ' . $old_id_promos_campagnes;
                    foreach (Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql) as $product) {
                        $specific_price = new SpecificPrice($product['id_specific_price']);
                        if (!empty($product['id_product'])) {
                            $specific_price = new SpecificPrice($product['id_specific_price']);
                            // on vérifie qu'il existe bien
                            // s'il n'existe pas on ne fait rien
                            // car on ne peut pas le recréer n'ayant pas la donnée de réduction
                            if ($specific_price->id) {
                                unset($specific_price->id);
                                unset($specific_price->id_specific_price);
                                $specific_price->from = date('Y-m-d H:i:s', $date_start);
                                $specific_price->to = date('Y-m-d H:i:s', $date_end);
                                if ($specific_price->add()) {
                                    $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'promos_produits` (id_promos_campagnes,
                                            id_product, id_product_attribute, id_specific_price, solde)
                                            VALUES 
                                            (' . (int) $campagne->id . ', ' . (int) $product['id_product'] . ', ' .
                                            (int) $product['id_product_attribute'] . ', ' . (int) $specific_price->id . ', ' .
                                            (int) $product['solde'] . ')';
                                    Db::getInstance()->execute($sql);
                                }
                            }
                        }
                    }
                    Tools::redirectAdmin(
                        self::$currentIndex . '&token=' . Tools::getAdminTokenLite('AdminDmuPromos') .
                        '&conf=19&updatepromos_campagnes&id_promos_campagnes=' . $campagne->id
                    );
                }
            }
        }
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        $module_dir = _PS_MODULE_DIR_;
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $module_dir = __PS_BASE_URI__ . 'modules';
        }
        $this->addJquery();
        $this->addCSS($module_dir . '/dmupromos/views/css/dmupromos.css');
        $this->addJS($module_dir . '/dmupromos/views/js/dmupromos.js');
    }

    public function renderForm()
    {
        if (Shop::getTotalShops() > 1) {
            $shop_options = [];
            foreach (Shop::getShops() as $shop) {
                $shop_options[] = [
                    'id' => $shop['id_shop'],
                    'name' => $shop['name'],
                ];
            }
        }
        $group_options = [['id' => 0, 'name' => $this->l('All groups')]];
        foreach (Group::getGroups((int) $this->context->language->id) as $group) {
            $group_options[] = [
                'id' => $group['id_group'],
                'name' => $group['name'],
            ];
        }
        $country_options = [['id' => 0, 'name' => $this->l('All countries')]];
        foreach (Country::getCountries((int) $this->context->language->id) as $country) {
            $country_options[] = [
                'id' => $country['id_country'],
                'name' => $country['name'],
            ];
        }
        $currency_options = [['id' => 0, 'name' => $this->l('All currencies')]];
        foreach (Currency::getCurrencies((int) $this->context->language->id) as $currency) {
            $currency_options[] = [
                'id' => $currency->id,
                'name' => $currency->sign . ' - ' . $currency->name,
            ];
        }

        $date_type = version_compare(_PS_VERSION_, '1.6', '<') ? 'date' : 'datetime';
        $this->fields_form = [
            'legend' => [
                'title' => $this->l('Discount campaign parameters'),
                'icon' => 'icon-cog',
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->l('Name'),
                    'name' => 'name',
                    'maxlength' => 32,
                    'required' => true,
                    'hint' => $this->l('Forbidden characters') . ' <>;=#{}',
                ],
                [
                    'type' => $date_type,
                    'label' => $this->l('Start date'),
                    'name' => 'date_debut',
                    'required' => true,
                ],
                [
                    'type' => $date_type,
                    'label' => $this->l('End date'),
                    'name' => 'date_fin',
                    'required' => true,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        if (Shop::getTotalShops() > 1) {
            $this->fields_form['input'] = array_merge(
                $this->fields_form['input'],
                [
                    [
                        'type' => 'select',
                        'label' => $this->l('Shop'),
                        'desc' => $this->l(
                            'This option allows you to choose the concerned shop for your campaign.',
                            'AdminDmuPromos'
                        ),
                        'name' => 'id_shop',
                        'options' => [
                            'query' => $shop_options,
                            'id' => 'id',
                            'name' => 'name',
                        ],
                    ],
                ]
            );
        }

        $this->fields_form['input'] = array_merge(
            $this->fields_form['input'],
            [
                [
                    'type' => 'select',
                    'label' => $this->l('Group'),
                    'desc' => $this->l(
                        'This option allows you to restrict the campaign to the concerned group.',
                        'AdminDmuPromos'
                    ),
                    'name' => 'id_group',
                    'options' => [
                        'query' => $group_options,
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Country'),
                    'desc' => $this->l(
                        'This option allows you to restrict the campaign to the concerned country.',
                        'AdminDmuPromos'
                    ),
                    'name' => 'id_country',
                    'options' => [
                        'query' => $country_options,
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'select',
                    'label' => $this->l('Currency'),
                    'desc' => $this->l(
                        'This option allows you to restrict the campaign to the concerned currency.',
                        'AdminDmuPromos'
                    ),
                    'name' => 'id_currency',
                    'options' => [
                        'query' => $currency_options,
                        'id' => 'id',
                        'name' => 'name',
                    ],
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Only show active products'),
                    'name' => 'search_only_active',
                    'required' => false,
                    'is_bool' => true,
                    'hint' => $this->l('Set to "NO" to view all products !'),
                    'values' => [
                        [
                            'id' => 'search_only_active_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'search_only_active_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Minimum age of products'),
                    'desc' => $this->l('Allows you to hide new products. (optional)'),
                    'name' => 'search_new_age',
                    'maxlength' => 4,
                    'suffix' => $this->l('days'),
                ],
                [
                    'type' => 'text',
                    'label' => $this->l('Minimum delay since last restock'),
                    'desc' => $this->l('Allows you to hide products newly restock. (optional)'),
                    'name' => 'search_stock_age',
                    'maxlength' => 4,
                    'suffix' => $this->l('days'),
                ],
                [
                    'type' => 'switch',
                    'label' => $this->l('Only apply promotions to products in stock'),
                    'name' => 'apply_on_stock_only',
                    'required' => false,
                    'is_bool' => true,
                    'hint' => $this->l(
                        'The promotion will be definitively removed from any products that are not in stock anymore',
                        'AdminDmuPromos'
                    ),
                    'values' => [
                        [
                            'id' => 'apply_on_stock_only_on',
                            'value' => 1,
                            'label' => $this->l('Enabled'),
                        ],
                        [
                            'id' => 'apply_on_stock_only_off',
                            'value' => 0,
                            'label' => $this->l('Disabled'),
                        ],
                    ],
                ],
            ]
        );

        return parent::renderForm();
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');

        $this->bulk_actions = [
            'delete' => [
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
        ];

        // Création de Table temporaire ... -###TEMP###-
        $this->generateStockTable();

        $txt_margincalculation = $this->l('Margin calculation in progress ! Please wait...');
        $content = '<script type="text/javascript">
                    var dmup_txt_margincalculation = "' . str_replace('"', '\\"', $txt_margincalculation) . '";
                    </script>';
        $content .= parent::renderList();

        return $content;
    }

    public function renderView()
    {
        $id_promos_campagnes = (int) Tools::getValue('id_promos_campagnes');
        $this->campagne = new CampagnesPromos($id_promos_campagnes);

        // vérification que nous sommes sur le bon Magasin !
        $id_shop = Shop::getContextShopID();
        if ($id_shop && $this->campagne->id_shop != $id_shop) {
            Tools::redirectAdmin(self::$currentIndex . '&token=' . $this->token);
        }
        // Création du titre
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $this->page_header_toolbar_title = '« ' . $this->campagne->name . ' » - ' . $this->l('from')
                . ' ' . Tools::displayDate($this->campagne->date_debut, $this->context->language->id)
                . ' (' . $this->l('at') . ' ' . date('G:i', strtotime($this->campagne->date_debut)) . ')'
                . ' ' . $this->l(' to')
                . ' ' . Tools::displayDate($this->campagne->date_fin, $this->context->language->id)
                . ' (' . $this->l('at') . ' ' . date('G:i', strtotime($this->campagne->date_fin)) . ')';
        } else {
            $this->page_header_toolbar_title = '« ' . $this->campagne->name . ' » - ' . $this->l('from')
                . ' ' . Tools::displayDate($this->campagne->date_debut)
                . ' (' . $this->l('at') . ' ' . date('G:i', strtotime($this->campagne->date_debut)) . ')'
                . ' ' . $this->l(' to')
                . ' ' . Tools::displayDate($this->campagne->date_fin)
                . ' (' . $this->l('at') . ' ' . date('G:i', strtotime($this->campagne->date_fin)) . ')';
        }

        // Création de Table temporaire ... -###TEMP###-
        $this->generateStockTable();
        $this->generateMarginTable($this->campagne);

        // Affichage exclusif des produits de la campagne au démarrage (si produits)
        //nalezny - Disabled check "Only show products of this campaign" automatically
//        if ($this->campagne->getSpecificPricesIds()) {
//            $this->show_campaign = true;
//        }

        // Affichage de l'interface
        $this->renderCheckUpdates();
        $retour = '<ul class="cc_button"><li><a class="toolbar_btn" href="' .
            $this->context->link->getAdminLink('AdminDmuPromos', true) .
            '"><span class="process-icon-back "></span><div>' .
            $this->l('Back to list') . '</div></a></li></ul>';
        $content_for_ps15 = '<div class="ps15_bootstrap"><div class="toolbarBox toolbarHead">' . $retour .
            '<div class="pageTitle"><h3>' . $this->page_header_toolbar_title . '</h3></div></div>';
        $content = '<script type="text/javascript">
                    var dmup_txt_discount = "' . str_replace('"', '\\"', $this->l('Discount')) . '";
                    var dmup_txt_currency = "' . $this->context->currency->sign . '";
                    var dmup_txt_onsale = "' .
                    str_replace('"', '\\"', $this->l('Display the "on sale" icon')) . '";
                    var dmup_txt_cancel = "' . str_replace('"', '\\"', $this->l('Cancel')) . '";
                    var dmup_txt_save = "' . str_replace('"', '\\"', $this->l('Save')) . '";
                    </script>';
        $content .= version_compare(_PS_VERSION_, '1.6', '<') ? $content_for_ps15 : '<div>';
        $content .= $this->renderProductsFilters();
        $content .= '<div id="dmup_reloadable">';
        $content .= $this->renderProductsList();
        $content .= '</div></div>';

        return $content;
    }

    public function renderCheckUpdates()
    {
        // Amélioration du Helper 1.5
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $is_good = Configuration::get('PS15_HELPERS_LIST_TR_CLASS');
            if (!$is_good) {
                $uri = $_SERVER['REQUEST_URI'] ? $_SERVER['REQUEST_URI'] : $_SERVER['PHP_SELF'];
                $uri = $uri ? $uri : $_SERVER['SCRIPT_NAME'];
                $admin_dir = explode('index.php', $uri);
                $file = dirname(__FILE__) . '/../../../..' . $admin_dir[0] .
                    'themes/default/template/helpers/list/list_content.tpl';
                if (file_exists($file)) {
                    $new_pattern = 'class="{if isset($tr.class)}{$tr.class} {/if}{if $index is odd}';
                    $pattern = '/class="\{if \$index is odd\}/';
                    $content = Tools::file_get_contents($file);
                    $content = preg_replace($pattern, $new_pattern, $content);
                    $f = fopen($file, 'w');
                    fwrite($f, $content);
                    fclose($f);
                    Configuration::updateValue('PS15_HELPERS_LIST_TR_CLASS', true);
                }
            }
        }
    }

    public function renderProductsFilters()
    {
        // Récupération des catégories
        $categories = Category::getCategories($this->context->language->id, false);
        $current = current($categories);
        $current = $current[key($current)];

        $options_categories = [];
        $this->campagne->recurseCategoryNbProds(
            $options_categories,
            $categories,
            $current,
            $current['infos']['id_category'],
            Tools::getValue('id_selected')
        );

        // Récupération des marques
        $id_shop = (int) $this->context->shop->id;
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'manufacturer` m
               ' . ($id_shop ? ' INNER JOIN `' . _DB_PREFIX_ . 'manufacturer_shop` ms
                                ON ms.id_manufacturer = m.id_manufacturer
                                    AND ms.id_shop = ' . $id_shop : '') . '
                GROUP BY m.id_manufacturer
                ORDER BY m.name';
        $options_marques = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        // Smarty & Template
        $this->context->smarty->assign([
            'toolbar_title' => $this->page_header_toolbar_title,
            'advanced_stock_management' => Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT'),
            'show_campaign' => $this->show_campaign,
            'campagne' => $this->campagne,
            'options_categories' => $options_categories,
            'options_marques' => $options_marques,
        ]);

        return $this->context->smarty->fetch(dirname(__FILE__) . '/../../views/templates/admin/filters.tpl');
    }

    public function renderProductsList($id_product = null)
    {
        $helper = new HelperList();
        // Add 500 between 300 and 1000 (PrestaShop default is [20, 50, 100, 300, 1000]).
        // Lets admins work on mid-sized batches without jumping to 1000 (which hits
        // the upstream proxy 100s timeout on this catalog size).
        $helper->_pagination = [20, 50, 100, 300, 500, 1000];

        $fields_list = [
            'id_product' => [
                'title' => $this->l('ID'),
                'search' => false,
                'filter_key' => 'id_product',
            ],
            'reference' => [
                'title' => $this->l('Reference'),
                'search' => true,
                'filter_key' => 'reference',
            ],
            'image' => [
                'title' => $this->l('Image'),
                'align' => 'td_image left',
                'class' => 'td_image',
                'callback' => 'callbackImage',
                'search' => false,
            ],
            'name' => [
                'title' => $this->l('Product name'),
                'align' => 'td_name attributes_swap_button left',
                'class' => 'td_name attributes_swap_button',
                'callback' => 'callbackName',
                'search' => false,
                'filter_key' => 'pl.name',
            ],
            'last_sale' => [
                'title' => $this->l('Last sale'),
                'align' => 'td_last_sale attributes_swap_button left',
                'class' => 'td_last_sale attributes_swap_button',
                'callback' => 'callbackLastSale',
                'filter_key' => 'last_sale',
            ],
            'stock' => [
                'title' => $this->l('Stock'),
                'align' => 'td_stock center',
                'class' => 'td_stock',
                'callback' => 'callbackStock',
                'search' => false,
                'filter_key' => 'stock',
            ],
            'wholesale_price' => [
                'title' => $this->l('Wholesale price'),
                'class' => 'td_wholesale_price',
                'align' => 'td_wholesale_price left',
                'callback' => 'callbackWholesale',
                'search' => false,
                'filter_key' => 'wholesale_price',
            ],
            'price' => [
                'title' => '<span class="label-tooltip" data-toggle="tooltip" data-original-title="' .
                    str_replace('"', '\\"', $this->l('Price without discounts')) . '">' .
                    $this->l('Price tax excl. / tax incl.') . '</label>',
                'align' => 'td_price center',
                'class' => 'td_price',
                'callback' => 'callbackPrice',
                'search' => false,
                'filter_key' => 'price',
            ],
            'on_sale' => [
                'title' => '<span class="label-tooltip" data-toggle="tooltip" data-original-title="' .
                    str_replace(
                        '"',
                        '\\"',
                        $this->l('Display the "on sale" icon on the product page')
                    ) . '">' .
                    $this->l('"on sale" icon') . '</label>',
                'no_link' => true,
                'align' => 'td_on_sale center',
                'class' => 'td_on_sale',
                'callback' => 'callbackOnSale',
                'search' => false,
                'orderby' => false,
            ],
            'discount' => [
                'title' => '<span class="label-tooltip" data-toggle="tooltip" data-original-title="' .
                    str_replace('"', '\\"', $this->l('Amount or percentage')) . '">' .
                    $this->l('Discount') . '</label>',
                'align' => 'td_discount text-right',
                'class' => 'td_discount',
                'callback' => 'callbackDiscount',
                'search' => false,
                'orderby' => false,
            ],
            'final_price' => [
                'title' => $this->l('Price with discount tax excl. / tax incl.'),
                'align' => 'td_final_price center',
                'class' => 'td_final_price',
                'callback' => 'callbackFinalPrice',
                'search' => false,
                'orderby' => false,
            ],
            'margin' => [
                'title' => '<span class="label-tooltip" data-toggle="tooltip" data-original-title="' .
                    str_replace('"', '\\"', $this->l('Margin after discount')) . '">' .
                    $this->l('Margin') . '</label>',
                'align' => 'td_margin left',
                'class' => 'td_margin',
                'callback' => 'callbackMargin',
                'search' => false,
                'filter_key' => 'margin',
            ],
        ];

        $results = $id_product ? $this->getCombinationsList($id_product) : $this->getProductsList();
        $fields_values = $results['products'];

        $helper->identifier = 'id_product';
        $helper->list_id = 'productslist';
        $helper->table = 'productslist';
        $helper->simple_header = false;
        $helper->token = Tools::getAdminTokenLite('AdminDmuPromos');
        $helper->currentIndex = AdminController::$currentIndex;
        $helper->shopLinkType = $this->shopLinkType;
        $helper->colorOnBackground = true;
        $helper->no_link = true;

        $helper->listTotal = $results['listTotal'];
        $helper->bulk_actions = [
            'setDiscounts' => [
                'text' => ' ' . $this->l('Set discounts for the selection'),
                'icon' => 'icon-certificate',
            ],
            'deleteDiscounts' => [
                'text' => ' ' . $this->l('Delete the selected discounts'),
                'icon' => 'icon-trash',
            ],
        ];

        return $helper->generateList($fields_values, $fields_list);
    }

    public function getProductsList()
    {
        $id_lang = (int) $this->context->language->id;
        $id_shop = (int) $this->context->shop->id;
        $results = [
            'products' => [],
            'listTotal' => 0,
        ];

        $page = 1;
        // Gestion de la pagination
        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            $limit = $this->_pagination[0];
            if (isset($this->context->cookie->_pagination)
                && $this->context->cookie->_pagination) {
                $limit = (int) $this->context->cookie->_pagination;
            }
            if (Tools::getIsset('pagination')
                && (int) Tools::getValue('pagination')) {
                $limit = (int) Tools::getValue('pagination');
                $this->context->cookie->_pagination = $limit;
                $this->context->cookie->write();
            }

            if (Tools::getIsset('submitFilter')
                && (int) Tools::getValue('submitFilter')) {
                $page = (int) Tools::getValue('submitFilter');
                $this->context->cookie->submitFilter = $page;
            }
        } else {
            $limit = $this->_default_pagination;
            if (isset($this->context->cookie->productslist_pagination)
                && $this->context->cookie->productslist_pagination) {
                $limit = (int) $this->context->cookie->productslist_pagination;
            }
            if (Tools::getIsset('productslist_pagination')
                && (int) Tools::getValue('productslist_pagination')) {
                $limit = (int) Tools::getValue('productslist_pagination');
                $this->context->cookie->productslist_pagination = $limit;
                $this->context->cookie->write();
            }
            if (Tools::getIsset('submitFilterproductslist')
                && (int) Tools::getValue('submitFilterproductslist')) {
                $page = (int) Tools::getValue('submitFilterproductslist');
                $this->context->cookie->submitFilterproductslist = $page;
            }

//            if (isset($_GET['paginationPage']) && !empty($_GET['paginationPage'])) {
//                $this->context->cookie->submitFilter = $_GET['paginationPage'];
//                $this->context->cookie->write();
//
//            }
//
//            if (isset($_GET['perPage']) && !empty($_GET['perPage'])) {
//                $this->context->cookie->submitFilterproductslist = $_GET['perPage'];
//                $this->context->cookie->productslist_pagination = $_GET['perPage'];
//                $this->context->cookie->write();
//                $page = (int) $this->context->cookie->productslist_pagination;
////                $limit =  (int) $this->context->cookie->submitFilterproductslist;
//            }

//            if (isset($_GET['paginationPage']) && !empty($_GET['paginationPage'])) {
//                $page = $_GET['paginationPage'] > 0 ? $_GET['paginationPage'] : 1;
//            }
//
//            if (isset($_GET['perPage']) && !empty($_GET['perPage'])) {
//                $start = ($_GET['perPage'] * ($page - 1));
//            }
        }
        $page = $page > 0 ? $page : 1;
        $start = $limit * ($page - 1);
//        echo $page.'-'.$start;
        $join = [];
        $where = [];
        // Seulement les produits actif ?
        if ($this->campagne->search_only_active) {
            $where[] = 'AND (p.active = 1 OR ps.active = 1) ';
        }
        // Seulement les produits de la campagne ?
        if ($this->show_campaign) {
            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'promos_produits` pp
                            ON pp.id_product = p.id_product
                                AND pp.id_promos_campagnes = ' . (int) $this->campagne->id;
        }
        // Filtre : Age minimum des produits
        if ($this->campagne->search_new_age) {
            $where[] = 'AND p.date_add <= DATE_SUB(CURDATE(),INTERVAL ' . $this->campagne->search_new_age . ' DAY)';
        }
        // Filtre : Délai minimum depuis le dernier réassort
        if ($this->campagne->search_stock_age && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
            $join[] = 'LEFT JOIN (
                        SELECT MAX(sm.date_add) as max_date, s.id_product
                        FROM `' . _DB_PREFIX_ . 'stock_mvt` sm
                        INNER JOIN `' . _DB_PREFIX_ . 'stock` s
                            ON s.id_stock = sm.id_stock
                        ) as max_mvt
                            ON max_mvt.id_product = p.id_product';
            $where[] = 'AND (max_mvt.max_date IS NULL OR max_mvt.max_date <= DATE_SUB(CURDATE(),INTERVAL ' .
                        $this->campagne->search_stock_age . ' DAY))';
            // Rajout de la date de creation en tant que date de reassort maxi du produit
            $where[] = 'AND p.date_add <= DATE_SUB(CURDATE(),INTERVAL ' . $this->campagne->search_stock_age . ' DAY)';
        }
        // Filtre Catégorie
        if ($this->filter_id_category) {
            $join[] = 'INNER JOIN `' . _DB_PREFIX_ . 'category_product` cp
                            ON cp.id_product = p.id_product
                                AND cp.id_category = ' . (int) $this->filter_id_category;
        }
        // Filtre Marque
        if ($this->filter_id_manufacturer) {
            $where[] = 'AND p.id_manufacturer = ' . (int) $this->filter_id_manufacturer;
        }
        // Filtre Mots clés
        if ($this->filter_keywords) {
            $words = explode(' ', $this->filter_keywords);
            $where[] = 'AND (pl.name LIKE \'%' . implode('%\' OR pl.name LIKE \'%', $words) .
                '%\' OR p.reference LIKE \'%' . implode('%\' OR p.reference LIKE \'%', $words) . '%\')';
        }
        // Filtre Stock min
        if ($this->filter_stock) {
            $where[] = 'AND stock >= ' . (int) $this->filter_stock;
        }

        // OrderBy & OrderWay

        if (!empty($_GET['filter_orderby'])) {
            $filter_orderby = $_GET['filter_orderby'];
        } else {
            $filter_orderby = $this->filter_orderby;
        }

        if (!empty($_GET['filter_orderway'])) {
            $filter_orderway = $_GET['filter_orderway'];
        } else {
            $filter_orderway = $this->filter_orderway;
        }

        if ('last_sale' == $filter_orderby) {
            $filter_orderway = ('ASC' == $filter_orderway ? 'DESC' : 'ASC');
        }

        // REQUÊTE PRINCIPALE
        $sql = 'SELECT SQL_CALC_FOUND_ROWS DISTINCT p.id_product,
                    p.reference as reference,
                    0 as id_product_attribute,
                    "" as image,
                    pl.name as name,
                    IF (ls.date_add, ls.date_add, 0) as last_sale,
                    IF (tps.stock, tps.stock, 0) as stock,
                    ps.wholesale_price as wholesale_price,
                    p.price as price,
                    0 as on_sale,
                    0 as discount,
                    0 as final_price,
                    IF (tpm.margin, tpm.margin,0) as margin,
                    m.name as manufacturer,
                    CONCAT("tr_", p.id_product, "_0 product") as class
                FROM `' . _DB_PREFIX_ . 'product` p
                INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
                    ON ps.id_product = p.id_product
                    ' . ($id_shop ? 'AND ps.id_shop = ' . (int) $id_shop : '') . '
                INNER JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON pl.id_product = p.id_product
                    ' . ($id_shop ? 'AND pl.id_shop = ' . (int) $id_shop : '') . '
                    ' . ($id_lang ? 'AND pl.id_lang = ' . (int) $id_lang : '') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
                    ON m.id_manufacturer = p.id_manufacturer
                ' . (count($join) ? implode(' ', $join) : '') . '
                LEFT JOIN (
                    SELECT od.product_id as id_product, MAX(o.date_add) as date_add
                    FROM ' . _DB_PREFIX_ . 'orders o
                    INNER JOIN ' . _DB_PREFIX_ . 'order_detail od
                        ON o.id_order = od.id_order
                    WHERE o.valid = 1
                    GROUP BY od.product_id
                    ) ls
                    ON ls.id_product = p.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'temporary_product_stock` tps
                    ON tps.id_product = p.id_product
                LEFT JOIN `' . _DB_PREFIX_ . 'temporary_product_margin` tpm
                    ON tpm.id_product = p.id_product AND tpm.id_promos_campagnes = ' . (int) $this->campagne->id . '
                WHERE 1 ' . (count($where) ? implode(' ', $where) : '') . '
                ORDER BY ' . $filter_orderby . ' ' . $filter_orderway . '
                LIMIT ' . $start . ', ' . $limit;
//        die($sql);
        $products = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        $results['sql'] = $sql;
        $results['listTotal'] = Db::getInstance()->getValue('SELECT FOUND_ROWS() AS listTotal');
        $results['products'] = $products;

        return $results;
    }

    public function getCombinationsList($id_product)
    {


        $id_shop = (int) $this->context->shop->id;
        $default_wholesale_price = $this->getPriceBase($id_product);
        $default_wholesale_price = $default_wholesale_price['wholesale'];

        // on récupère le produit de base pour savoir le fournisseur par défault
        $product = new Product((int) $id_product, true);

        $sql = 'SELECT pa.id_product,
                    pa.reference as reference,
                    pa.id_product_attribute,
                    "" as image,
                    "" as name,
                    IF (ls.date_add, ls.date_add, 0) as last_sale,
                    "?" as stock,
                    CASE
                            WHEN pps.product_supplier_price_te > 0 THEN pps.product_supplier_price_te
                            WHEN pas.wholesale_price > 0 then pas.wholesale_price
                            ELSE \'' .
                        $default_wholesale_price . '\'
                    END 
                    AS wholesale_price, 
                    0 as price,
                    0 as on_sale,
                    0 as discount,
                    0 as final_price,
                    0 as margin,
                    0 as attributes_nb,
                    CONCAT("tr_' . (int) $id_product . '_", pa.id_product_attribute, " tr_' .
                        (int) $id_product . ' combination open") as class
                FROM `' . _DB_PREFIX_ . 'product_attribute` pa
                INNER JOIN `' . _DB_PREFIX_ . 'product_attribute_shop` pas
                    ON pas.id_product_attribute = pa.id_product_attribute
                    ' . ($id_shop ? 'AND pas.id_shop = ' . (int) $id_shop : '') . '
                LEFT JOIN ' . _DB_PREFIX_ . 'product_supplier pps
                ON pps.id_product_attribute = pa.id_product_attribute
                AND pps.id_product = pa.id_product
                LEFT JOIN (
                    SELECT od.product_id as id_product, od.product_attribute_id as id_product_attribute,
                        MAX(o.date_add) as date_add
                    FROM ' . _DB_PREFIX_ . 'orders o
                    INNER JOIN ' . _DB_PREFIX_ . 'order_detail od
                        ON o.id_order = od.id_order
                    WHERE o.valid = 1
                    GROUP BY od.product_id, od.product_attribute_id
                    ) ls
                    ON ls.id_product = pa.id_product
                        AND ls.id_product_attribute = pa.id_product_attribute
                WHERE pa.id_product = ' . (int) $id_product . '
                GROUP BY pa.id_product_attribute';

        if ($product->id_supplier) {
            $sql .= ' ORDER BY id_supplier, id_product_attribute';
        }

        $attributes = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return [
            'listTotal' => count($attributes),
            'products' => $attributes,
        ];
    }

    public function callbackListName($name, $tr)
    {
        $now = date('Y-m-d H:i:s');
        if ($tr['date_fin'] >= $now) {
            return '<b>' . $name . '</b>';
        }

        return $name;
    }

    public function callbackListInfo($id_promos_campagnes)
    {
        $information = '--';
        $sql = 'SELECT COUNT(DISTINCT pp.id_product) as number
                FROM `' . _DB_PREFIX_ . 'promos_produits` pp
                INNER JOIN `' . _DB_PREFIX_ . 'specific_price` sp ON pp.id_specific_price = sp.id_specific_price
                WHERE pp.id_promos_campagnes = ' . $id_promos_campagnes;
        if ($info = Db::getInstance()->getRow($sql)) {
            $information = $info['number'] . ' ' . $this->l('discounted products');
        }

        return $information;
    }

    public function callbackListStatus($status)
    {
        switch ($status) {
            case 2:
                return '<div class="badge badge-success"> ' . $this->l('In progress') . ' </div>';
            case 1:
                return '<div class="badge badge-info"> ' . $this->l('Scheduled') . ' </div>';
            case 0:
                return '<div class="badge" style="background-color:#ccc;"> ' .
                $this->l('Finished') . ' </div>';
        }

        return '--';
    }

    public function callbackShop($id_shop)
    {
        if ($id_shop) {
            $shop = new Shop((int) $id_shop);

            return $shop->name;
        }

        return '--';
    }

    public function callbackImage($echo, $tr)
    {
        if ($tr['id_product_attribute']) {
            return '';
        }

        $image_type = ImageType::getFormattedName('small');

        if ($cover = Product::getCover($tr['id_product'])) {
            $src = $this->context->link->getImageLink(
                $this->l('Preview'),
                $cover['id_image'],
                $image_type
            );
        } else {
            $iso = $this->context->language->iso_code;
            $src = $this->context->link->getImageLink(
                $this->l('Preview'),
                $iso . '-default',
                $image_type
            );
        }

        return '<img class="imgm img-thumbnail checkable" src="' . $src . '" width="50" height="50" border="0" />';
    }

    public function callbackName($name, $tr)
    {
        $res = ($tr['reference'] ? '<span class="cp_info_ref">' . $this->l('Reference') . ' : ' .
            $tr['reference'] . '</span>' : '');
        $id_product = $tr['id_product'];
        $id_product_attribute = $tr['id_product_attribute'] ? $tr['id_product_attribute'] : null;

        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'specific_price`
                WHERE (`from` = \'0000-00-00 00:00:00\'
                    OR (`from` <= \'' . pSQL($this->campagne->date_debut) . '\'
                        AND `to` >= \'' . pSQL($this->campagne->date_fin) . '\'))
                    AND id_product = ' . (int) $id_product .
                    ($id_product_attribute ? ' AND id_product_attribute = ' . (int) $id_product_attribute : '') . '
                    ' . (!$this->campagne->getSpecificPricesIds() ? '' :
                        ' AND id_specific_price NOT IN (' . implode(',', $this->campagne->getSpecificPricesIds()) . ')') .
                    ' AND id_shop IN (0' . ($this->context->shop->id ? ',' . (int) $this->context->shop->id : '') . ')
                    AND id_cart = 0
                    AND id_customer = 0
                ORDER BY id_product_attribute ASC';
        if ($reduc = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $is_comb = !$id_product_attribute && $reduc['id_product_attribute'] ? true : false;
            $message_no_comb = 'Warning ! This product is already under the effect of other promotion, ' .
                                'or it will be during the period of the campaign !';
            $message_is_comb = 'Warning ! A combination of this product is already under the effect ' .
                                'of other promotion, or it will be during the period of the campaign !';
            if ($is_comb) {
                $message = $this->l($message_is_comb);
            } else {
                $message = $this->l($message_no_comb);
            }
            $message = str_replace('"', '\\"', $message);
            $res .= '<img src="../img/admin/error.png" width=16 height=16 border=0 alt="' . $message . '" title="' . $message
                . '" style="' . ($is_comb ? 'opacity:.3;' : '') . 'float:right;margin-right:20px;cursor:help;" />';
        }

        if (!$tr['id_product_attribute']) {
            // Calcul du nombre de combinaisons
            $id_shop = (int) $this->context->shop->id;
            $sql = 'SELECT COUNT(*) as attributes_nb FROM `' . _DB_PREFIX_ . 'product_attribute` pa' .
                    ($id_shop ?
                        ' INNER JOIN `' . _DB_PREFIX_ .
                        'product_attribute_shop` pas ON pas.id_product_attribute = pa.id_product_attribute '
                        : '') .
                    ' WHERE pa.id_product = ' . (int) $tr['id_product'] .
                        ($id_shop ? ' AND pas.id_shop = ' . (int) $id_shop : '');
            $tr['attributes_nb'] = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
            // Affichage du Nom du produit et des infos
            $res .= '<b title="' . str_replace('"', '', $tr['manufacturer']) . '" class="checkable">' . $name . '</b><br />';
            if (!$tr['attributes_nb']) {
                if ($tr['manufacturer']) {
                    $res .= '<span class="small_info" title="' .
                    str_replace('"', '', $tr['manufacturer']) .
                    '">« ' . $tr['manufacturer'] . ' »</span>';
                } else {
                    $res .= '<i style="opacity:.5;" class="small_info" title="' .
                    str_replace('"', '', $tr['manufacturer']) .
                    '">' . $this->l('No combination') . '</i>';
                }
            } else {
                $res .= '<a href="javascript:;" class="small_info" title="' .
                    str_replace('"', '', $tr['manufacturer']) .
                    '"><i class="icon icon-plus-square attributes-swap"></i> ' .
                    str_replace(
                        '%1',
                        $tr['attributes_nb'],
                        $this->l('This product has %1 combinations')
                    ) . '</a>';
            }
        } else {
            // Affichage du Détail de la déclinaison
            $product_name = self::getProductNameOverride((int) $id_product, (int) $id_product_attribute);
            $simple = explode(' : ', $product_name);
            if (count($simple) > 1) {
                $res .= '<span class="checkable">' . $simple[1] . '</span>';
            } else {
                $res .= '<span class="checkable">' .
                self::getProductNameOverride((int) $id_product, (int) $id_product_attribute) . '</span>';
            }
        }

        return $res;
    }

    public function callbackLastSale($last_sale, $tr)
    {
        if (Validate::isDate($last_sale)) {
            if ($tr['id_product_attribute']) {
                return $this->getTextDelayByDate($last_sale);
            } else {
                return '<b>' . $this->getTextDelayByDate($last_sale) . '</b>';
            }
        }

        return '<b style="color:#f06c25">' . $this->l('No sale') . '</b>';
    }

    public function callbackStock($echo, $tr)
    {
        return StockAvailable::getQuantityAvailableByProduct($tr['id_product'], $tr['id_product_attribute']);
    }

    public function callbackWholesale($echo)
    {
        return Tools::displayPrice($echo, $this->context->currency->id);
    }

    public function callbackPrice($echo, $tr)
    {
        $id_product = $tr['id_product'];
        $id_product_attribute = $tr['id_product_attribute'] ? $tr['id_product_attribute'] : null;
        $price = $this->getPriceBase($id_product, $id_product_attribute);

        return str_replace(' ', '&nbsp;', Tools::displayPrice($price['ht'], $this->context->currency->id)) .
            ' &nbsp;-&nbsp; <b>' .
            str_replace(' ', '&nbsp;', Tools::displayPrice($price['ttc'], $this->context->currency->id)) . '</b>';
    }

    public function callbackOnSale($echo, $tr)
    {
        if (!$tr['id_product_attribute']) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                    WHERE id_promos_campagnes = ' . $this->campagne->id . '
                        AND id_product = ' . $tr['id_product'];
            if ($promos_produit = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                if ($promos_produit['solde']) {
                    return '<a class="list-action-enable action-enabled" href="javascript:;" title="' .
                            $this->l('Enabled') . '"><i class="icon-check"></i></a>';
                } else {
                    return '<a class="list-action-enable action-disabled" href="javascript:;" title="' .
                            $this->l('disabled') . '"><i class="icon-remove"></i></a>';
                }
            }
        }

        return '<span></span>';
    }

    public function callbackDiscount($echo, $tr)
    {
        $id_product_attribute = $tr['id_product_attribute'] ? $tr['id_product_attribute'] : null;

        $specific_price = $this->campagne->getSpecificPrice((int) $tr['id_product'], (int) $tr['id_product_attribute']);
        if ($specific_price) {
            if ('amount' == $specific_price['reduction_type']) {
                $reduction = $specific_price['reduction'];

                return '<span rel="' . Tools::displayPrice($reduction) . '"><b>' .
                    Tools::displayPrice($reduction, $this->context->currency) . '</b><div class="delete">X</div></span>';
            } else {
                $reduction = $specific_price['reduction'] * 100;

                return '<span rel="' . Tools::ps_round($reduction, 1) . '%"><b>' .
                    Tools::ps_round($reduction, 1) . ' %</b><div class="delete">X</div></span>';
            }
        } elseif ($id_product_attribute) {
            $specific_price = $this->campagne->getSpecificPrice((int) $tr['id_product']);
            if ($specific_price) {
                if ('amount' == $specific_price['reduction_type']) {
                    $reduction = $specific_price['reduction'];

                    return '<span rel="0"><b style="opacity:.3;">' .
                        Tools::displayPrice($reduction, $this->context->currency) . '</b></span>';
                } else {
                    $reduction = $specific_price['reduction'] * 100;

                    return '<span rel="0"><b style="opacity:.3;">' .
                        Tools::ps_round($reduction, 1) . ' %</b></span>';
                }
            }
        }

        return '<span rel="0">&nbsp;</span>';
    }

    public function callbackFinalPrice($echo, $tr)
    {
        $id_product = $tr['id_product'];
        $id_product_attribute = $tr['id_product_attribute'] ? $tr['id_product_attribute'] : null;
        $price = $this->getPriceBase($id_product, $id_product_attribute);

        $specific_price = $this->campagne->getSpecificPrice((int) $tr['id_product'], (int) $tr['id_product_attribute']);
        if ($specific_price) {
            if ($price['ht'] > 0) {
                $is_percentage = 'percentage' == $specific_price['reduction_type'] ? true : false;
                $reduction = $specific_price['reduction'];
                $final_price_ttc = $is_percentage ? $price['ttc'] * (1 - $reduction) : $price['ttc'] - $reduction;
                $final_price_ht = $final_price_ttc * $price['ht'] / $price['ttc'];

                return '<span>' .
                    str_replace(' ', '&nbsp;', Tools::displayPrice($final_price_ht, $this->context->currency->id))
                    . ' &nbsp;-&nbsp; <b>' .
                    str_replace(' ', '&nbsp;', Tools::displayPrice($final_price_ttc, $this->context->currency->id))
                    . '</b></span>';
            }
        } elseif ($id_product_attribute) {
            $specific_price = $this->campagne->getSpecificPrice((int) $tr['id_product']);
            if ($specific_price) {
                if ($price['ht'] > 0) {
                    $is_percentage = 'percentage' == $specific_price['reduction_type'] ? true : false;
                    $reduction = $specific_price['reduction'];
                    $final_price_ttc = $is_percentage ?
                        $price['ttc'] * (1 - $reduction) : $price['ttc'] - $reduction;
                    $final_price_ht = $final_price_ttc * $price['ht'] / $price['ttc'];

                    return '<span>' .
                        str_replace(' ', '&nbsp;', Tools::displayPrice($final_price_ht, $this->context->currency->id))
                        . ' &nbsp;-&nbsp; <b>' .
                        str_replace(' ', '&nbsp;', Tools::displayPrice($final_price_ttc, $this->context->currency->id))
                        . '</b></span>';
                }
            }
        }

        return '<span style="opacity:.3;">' .
                str_replace(' ', '&nbsp;', Tools::displayPrice($price['ht'], $this->context->currency->id))
                . ' &nbsp;-&nbsp; <b>' .
                str_replace(' ', '&nbsp;', Tools::displayPrice($price['ttc'], $this->context->currency->id))
                . '</b></span>';
    }

    public function callbackMargin($echo, $tr)
    {
        $id_product = $tr['id_product'];
        $id_product_attribute = $tr['id_product_attribute'] ? $tr['id_product_attribute'] : null;
        $price = $this->getPriceBase($id_product, $id_product_attribute);

        $specific_price = $this->campagne->getSpecificPrice((int) $tr['id_product'], (int) $tr['id_product_attribute']);
        if ($specific_price) {
            if ($price['ht'] > 0) {
                $is_percentage = 'percentage' == $specific_price['reduction_type'] ? true : false;
                $reduction = $specific_price['reduction'];
                $final_price_ht = $is_percentage ? $price['ht'] * (1 - $reduction) : $price['ht'] - $reduction;

                if ($tr['wholesale_price'] > 0) {
                    $margin = ($final_price_ht - $tr['wholesale_price']) / $tr['wholesale_price'] * 100;
                    if (Tools::ps_round($margin, 1) <= 50) {
                        if (Tools::ps_round($margin, 1) < 25) {
                            return '<span class="badge badge-danger"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                        } else {
                            return '<span class="badge badge-warning"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                        }
                    } else {
                        return '<span class="badge badge-success"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                    }
                }
            }
        } elseif ($id_product_attribute) {
            $specific_price = $this->campagne->getSpecificPrice((int) $tr['id_product']);
            if ($specific_price) {
                if ($price['ht'] > 0) {
                    $is_percentage = 'percentage' == $specific_price['reduction_type'] ? true : false;
                    $reduction = $specific_price['reduction'];
                    $final_price_ht = $is_percentage ? $price['ht'] * (1 - $reduction) : $price['ht'] - $reduction;

                    if ($tr['wholesale_price'] > 0) {
                        $margin = ($final_price_ht - $tr['wholesale_price']) / $tr['wholesale_price'] * 100;
                        if (Tools::ps_round($margin, 1) <= 50) {
                            if (Tools::ps_round($margin, 1) < 25) {
                                return '<span class="badge badge-danger"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                            } else {
                                return '<span class="badge badge-warning"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                            }
                        } else {
                            return '<span class="badge badge-success"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                        }
                    }
                }
            }
        }

        if ($tr['wholesale_price'] > 0) {
            $margin = ($price['ht'] - $tr['wholesale_price']) / $tr['wholesale_price'] * 100;
            if (Tools::ps_round($margin, 1) <= 50) {
                if (Tools::ps_round($margin, 1) < 25) {
                    return '<span class="badge badge-danger"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                } else {
                    return '<span class="badge badge-warning"> ' . Tools::ps_round($margin, 1) . ' % </span>';
                }
            } else {
                return '<span class="badge badge-success"> ' . Tools::ps_round($margin, 1) . ' % </span>';
            }
        }

        return '';
    }

    public function getPriceBase($id_product, $id_product_attribute = null)
    {
        $cache_key = 'base_price_id_product_' . (int) $id_product . '_' . (int) $id_product_attribute;
        if (!Cache::isStored($cache_key)) {
            if ($id_product_attribute) {
                // récupération du prix de base du produit
                // (ne jamais mettre l'id_product_attribute, ça créerait une boucle infinie)
                $default_wholesale_price = $this->getPriceBase($id_product);
                $sql = 'SELECT (ps.price + pas.price) as price_HT, 
                        CASE
                            WHEN pps.product_supplier_price_te > 0 THEN pps.product_supplier_price_te
                            WHEN pas.wholesale_price > 0 then pas.wholesale_price
                            ELSE \'' .
                            $default_wholesale_price['wholesale'] . '\'
                        END 
                        AS wholesale_price,
                        IF(tr.id_tax IS NULL, (ps.price + pas.price), ((ps.price + pas.price)*(1+(t.rate/100))))
                            as price_TTC
                        FROM `' . _DB_PREFIX_ . 'product_attribute` pas
                        INNER JOIN `' . _DB_PREFIX_ . 'product_shop` ps
                            ON ps.id_product = pas.id_product
                            AND ps.id_shop = ' . (int) $this->context->shop->id . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr
                            ON tr.id_tax_rules_group = ps.id_tax_rules_group
                            AND id_country = ' . (int) $this->context->country->id . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'tax` t
                            ON t.id_tax = tr.id_tax
                        LEFT JOIN ' . _DB_PREFIX_ . 'product_supplier pps
                            ON pps.id_product_attribute = pas.id_product_attribute
                            AND pps.id_product = pas.id_product
                        WHERE pas.id_product = ' . (int) $id_product . '
                            AND pas.id_product_attribute = ' . (int) $id_product_attribute;
                $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
                $price_HT = $res['price_HT'];
                $price_TTC = $res['price_TTC'];
                $wholesale_price = $res['wholesale_price'];
            } else {
                $sql = 'SELECT ps.price as price_HT, ps.wholesale_price as wholesale_price, 
                        IF(tr.id_tax IS NULL, ps.price, (ps.price*(1+(t.rate/100))))
                             as price_TTC
                        FROM `' . _DB_PREFIX_ . 'product_shop` ps
                        LEFT JOIN `' . _DB_PREFIX_ . 'tax_rule` tr
                            ON tr.id_tax_rules_group = ps.id_tax_rules_group
                            AND id_country = ' . (int) $this->context->country->id . '
                        LEFT JOIN `' . _DB_PREFIX_ . 'tax` t
                            ON t.id_tax = tr.id_tax
                        WHERE id_product = ' . (int) $id_product . '
                            AND id_shop = ' . (int) $this->context->shop->id;
                $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

                $price_HT = $res['price_HT'];
                $price_TTC = $res['price_TTC'];
                $wholesale_price = $res['wholesale_price'];
            }

            $price_base = [
                'ht' => $price_HT,
                'ttc' => $price_TTC,
                'wholesale' => $wholesale_price,
            ];

            Cache::store($cache_key, $price_base);
        } else {
            $price_base = Cache::retrieve($cache_key);
        }

        return $price_base;
    }

    public function getTextDelayByDate($date)
    {
        $delay_seconds = time() - strtotime($date);
        $delay_days = $delay_seconds / 3600 / 24;
        $delay_hours = $delay_seconds / 3600;

        $years = floor($delay_days / 30 / 12);
        $month = floor($delay_days / 30) - ($years * 12);
        $days = ($delay_days - ($years * 12 * 30) - $month * 30);
        $days = floor($days);
        $hours = ($delay_hours - ($days * 24) - ($years * 12 * 30 * 24) - ($month * 30 * 24));
        $hours = ceil($hours);

        if ($years) {
            $res = $years . ' ' . ($years > 1 ? $this->l('years') : $this->l('year'));
        } else {
            $res = '';
        }
        if ($month) {
            $res .= ' ' . $month . ' ' .
            ($month > 1 ? $this->l('months') : $this->l('month'));
        } else {
            $res .= '';
        }
        if (!$years && $days) {
            $res .= ' ' . $days . ' ' . ($days > 1 ? $this->l('days') : $this->l('day'));
        } else {
            $res .= '';
        }
        if ($years || $month) {
            $res .= '';
        } else {
            $res .= ' ' . $hours . ' ' .
            ($hours > 1 ? $this->l('hours') : $this->l('hour'));
        }

        return $res;
    }

    public function generateStockTable()
    {
        $table = 'temporary_product_stock';
        $last_access = (Configuration::hasKey($table) ? Configuration::getGlobalValue($table) : 0);
        if ($last_access < (time() - 3600)) { // MAJ si module non utilisé depuis plus d'une Heure !
            Db::getInstance()->execute('DROP  TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`'); // TEMPORARY
        }
        Configuration::updateGlobalValue($table, time());
        $sql = 'CREATE  TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $table . '` (
                    `id_product` INT( 10 ) NOT NULL PRIMARY KEY,
                    `stock` INT( 10 ) NOT NULL DEFAULT 0
                    ) ENGINE = ' . _MYSQL_ENGINE_; // TEMPORARY
        Db::getInstance()->execute($sql);
        // use_cache=false: PrestaShop's Db query cache returns a STALE COUNT after the
        // DROP+CREATE above when CacheMemcached is active (cache invalidation is keyed
        // by SQL hash, not by schema changes). Without this flag the cached non-zero
        // would make the bulk-INSERT branch skip and leave the table empty forever.
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . $table . '`';
        $is_temporary_table = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql, false);
        if (!$is_temporary_table) {
            // Bulk replacement of the per-product loop that called
            // StockAvailable::getQuantityAvailableByProduct() one product at a time.
            // The original loop generated N+1 queries; on a 4513-product catalog with
            // Memcached enabled, each call also pays cache-map maintenance overhead
            // (≈5 ms × 4513 ≈ 22 s). The single INSERT…SELECT below mirrors the same
            // semantics: SUM(quantity) per product where id_product_attribute = 0,
            // restricted to the current shop (matches StockAvailable::addSqlShopRestriction
            // for non-shared-stock setups: id_shop = current AND id_shop_group = 0).
            // NOTE: Multi-shop with $shop_group->share_stock = 1 would need the alternate
            // branch (id_shop = 0 AND id_shop_group = N). This site is single-shop with
            // share_stock = 0; the fall-through here is intentional.
            $id_shop = (int) $this->context->shop->id;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . $table . '` (id_product, stock)
                    SELECT p.id_product, COALESCE(SUM(sa.quantity), 0) AS stock
                    FROM `' . _DB_PREFIX_ . 'product` p
                    LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sa
                        ON sa.id_product = p.id_product
                        AND sa.id_product_attribute = 0
                        AND sa.id_shop = ' . $id_shop . '
                        AND sa.id_shop_group = 0
                    GROUP BY p.id_product';
            Db::getInstance()->execute($sql);
        }
    }

    public function generateMarginTable($campagne)
    {
        $table = 'temporary_product_margin';
        $last_access = (Configuration::hasKey($table) ? Configuration::getGlobalValue($table) : 0);
        if ($last_access < (time() - 3600)) { // MAJ si module non utilisé depuis plus d'une Heure !
            Db::getInstance()->execute('DROP  TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`'); // TEMPORARY
        }
        Configuration::updateGlobalValue($table, time());
        $sql = 'CREATE  TABLE IF NOT EXISTS `' . _DB_PREFIX_ . $table . '` (
                    `id_product` INT( 10 ) NOT NULL,
                    `id_promos_campagnes` INT( 10 ) NOT NULL,
                    `margin` FLOAT( 6,1 ) NOT NULL DEFAULT 0,
                    PRIMARY KEY (`id_product`, `id_promos_campagnes`)
                    ) ENGINE = ' . _MYSQL_ENGINE_; // TEMPORARY
        Db::getInstance()->execute($sql);
        // use_cache=false: same Memcached-stale-COUNT issue as generateStockTable above.
        $sql = 'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . $table . '`
                WHERE id_promos_campagnes = ' . (int) $campagne->id;
        $is_temporary_table = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql, false);
        if (!$is_temporary_table) {
            // Bulk replacement of the per-product loop that called getPriceBase() and
            // $campagne->getSpecificPrice() one product at a time. On a 4513-product
            // catalog this previously issued ~9000 cached Db::getRow calls per campaign
            // (~5 ms each with Memcached cache-map maintenance ≈ 45 s). The single
            // INSERT…SELECT below collapses it to one query by joining product_shop
            // with promos_produits + specific_price (LEFT JOIN to keep products that
            // have no campaign-specific price). Margin formula mirrors the PHP branch:
            //   wholesale ≤ 0           → 0
            //   no specific_price       → (price - wholesale) / wholesale * 100
            //   price ≤ 0 (w/ sp)       → 0
            //   specific_price %        → ((price * (1 - reduction)) - wholesale) / wholesale * 100
            //   specific_price amount   → ((price - reduction) - wholesale) / wholesale * 100
            // DEDUP: sf_promos_produits has no UNIQUE constraint on
            // (id_promos_campagnes, id_product, id_product_attribute); duplicate rows
            // would fan out the LEFT JOIN and break the PRIMARY KEY on the temp table.
            // The inner SELECT collapses duplicates by picking MIN(id_specific_price)
            // per (campaign, product, attribute=0) — matches getSpecificPrice's
            // deterministic-arbitrary getRow() semantics for the de-duplicated case.
            $id_shop = (int) $this->context->shop->id;
            $id_campagne = (int) $campagne->id;
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . $table . '` (id_promos_campagnes, id_product, margin)
                    SELECT
                        ' . $id_campagne . ' AS id_promos_campagnes,
                        ps.id_product,
                        CASE
                            WHEN ps.wholesale_price <= 0 THEN 0
                            WHEN sp.id_specific_price IS NULL THEN
                                ((ps.price - ps.wholesale_price) / ps.wholesale_price) * 100
                            WHEN ps.price <= 0 THEN 0
                            WHEN sp.reduction_type = "percentage" THEN
                                (((ps.price * (1 - sp.reduction)) - ps.wholesale_price) / ps.wholesale_price) * 100
                            ELSE
                                (((ps.price - sp.reduction) - ps.wholesale_price) / ps.wholesale_price) * 100
                        END AS margin
                    FROM `' . _DB_PREFIX_ . 'product_shop` ps
                    LEFT JOIN (
                        SELECT id_product, MIN(id_specific_price) AS id_specific_price
                        FROM `' . _DB_PREFIX_ . 'promos_produits`
                        WHERE id_promos_campagnes = ' . $id_campagne . '
                            AND id_product_attribute = 0
                        GROUP BY id_product
                    ) pp ON pp.id_product = ps.id_product
                    LEFT JOIN `' . _DB_PREFIX_ . 'specific_price` sp
                        ON sp.id_specific_price = pp.id_specific_price
                    WHERE ps.id_shop = ' . $id_shop;
            Db::getInstance()->execute($sql);
        }
    }

    public function displayAjaxGetCombinations()
    {
        $ajax = ['success' => false];
        if (Tools::getIsset('id_product')) {
            $id_product = (int) Tools::getValue('id_product');
            $ajax = [
                'success' => true,
                'id_product' => (int) $id_product,
                'html' => $this->renderProductsList((int) $id_product),
            ];
        }
        exit(json_encode($ajax));
    }

    public function displayAjaxGetProducts()
    {
        // $time_start = microtime(true);
        $ajax = ['success' => false];
        if (Tools::getIsset('filters')) {
            $filters = json_decode(Tools::getValue('filters'));
            $this->campagne->search_only_active = (int) $filters->show_active;
            $this->campagne->search_new_age = (int) $filters->filter_newage;
            $this->campagne->search_stock_age = (int) $filters->filter_lastrestock;
            $this->campagne->update();
            $this->show_campaign = (int) $filters->show_campaign;
            $this->filter_id_category = (int) $filters->filter_id_category;
            $this->filter_id_manufacturer = (int) $filters->filter_id_manufacturer;
            $this->filter_keywords = $filters->filter_keywords;
            $this->filter_orderway = $filters->filter_orderway;
            $this->filter_orderby = $filters->filter_orderby;
            $this->filter_stock = $filters->filter_stock;
            $ajax = [
                'success' => true,
                'html' => $this->renderProductsList(),
            ];
        }
        /*$time_elapsed_secs = microtime(true) - $time_start;
        var_dump($time_elapsed_secs);*/
        exit(json_encode($ajax));
    }

    public function displayAjaxSetDiscount()
    {
        $ajax = ['success' => false];
        if (Tools::getIsset('id_product')) {
            $id_product = (int) Tools::getValue('id_product');
            $id_product_attribute = (int) Tools::getValue('id_product_attribute');
            $val = Tools::getValue('val');

            $ajax = $this->setDiscount($id_product, $id_product_attribute, $val);
        }
        exit(json_encode($ajax));
    }

    public function displayAjaxSetOnSale()
    {
        $ajax = ['success' => false];
        if (Tools::getIsset('id_product')) {
            $id_product = (int) Tools::getValue('id_product');
            $onsale = (int) Tools::getValue('onsale');
            $onsale = $onsale ? 1 : 0;

            $ajax = $this->setOnSale($id_product, $onsale);
        }
        exit(json_encode($ajax));
    }

    public function setOnSale($id_product, $onsale)
    {
        $ajax = [];
        $ajax['id_product'] = $id_product;
        $ajax['onsale'] = $onsale;

        $sql = 'UPDATE `' . _DB_PREFIX_ . 'promos_produits`
                SET solde = ' . (int) $onsale . '
                WHERE id_product = ' . $id_product;
        if (Db::getInstance()->execute($sql)) {
            if ($this->campagne->is_onsale) {
                $this->campagne->is_onsale = false;
                $this->campagne->checkIsOnSale();
            }
            $ajax['success'] = true;
        }

        return $ajax;
    }

    public function setDiscount($id_product, $id_product_attribute, $val)
    {
        $ajax = [];
        $price = $this->getPriceBase((int) $id_product, (int) $id_product_attribute);

        $ajax['id_product'] = $id_product;
        $ajax['id_product_attribute'] = $id_product_attribute;
        $ajax['val'] = $val;

        // Nouvelle option, on regarde si on ne doit appliquer les promos que sur les produits en stock
        if (1 == (int) $this->campagne->apply_on_stock_only) {
            // Vérification du stock de ce produit / déclinaison
            $stock = StockAvailable::getQuantityAvailableByProduct($id_product, $id_product_attribute);
            if ($stock <= 0) {
                $ajax['val'] = 'no stock';
                $ajax['wholesale_price'] = Tools::displayPrice($price['wholesale']);
                $ajax['price_ht'] = Tools::displayPrice($price['ht']);
                $ajax['price_ttc'] = Tools::displayPrice($price['ttc']);
                $ajax['final_price_ht'] = Tools::displayPrice($price['ht']);
                $ajax['final_price_ttc'] = Tools::displayPrice($price['ttc']);
                $ajax['success'] = true;

                return $ajax;
            }
        }

        $is_percentage = false === strpos($val, $this->context->currency->sign) ? true : false;

        $reduction = abs((float) str_replace(',', '.', $val));
        if ($is_percentage) {
            $ajax['val'] = Tools::ps_round($reduction, 1) . ' %';
            $reduction /= 100;
        } else {
            $ajax['val'] = Tools::displayPrice($reduction);
        }
        $reduction = Tools::ps_round($reduction, 6);

        // *** Tests & Calcul du prix final HT, TTC, et de la marge selon reduc (ou reduc parente)
        $real_reduction = $reduction;
        if ($id_product_attribute && !$real_reduction) { // *** Déclinaison sans réduction ?
            $specific_price = $this->campagne->getSpecificPrice((int) $id_product);
            if ($specific_price) { // Pas de reduction spécifique à la déclinaison, on prend la réduc parente
                $is_percentage = 'amount' == $specific_price['reduction_type'] ? false : true;
                $real_reduction = $specific_price['reduction'];
                if ($is_percentage) {
                    $ajax['val_temp'] = $specific_price['reduction'] * 100;
                    $ajax['val_temp'] = Tools::ps_round($ajax['val_temp'], 1) . ' %';
                } else {
                    $ajax['val_temp'] = $specific_price['reduction'];
                    $ajax['val_temp'] = Tools::displayPrice($ajax['val_temp']);
                }
            }
        }
        $final_price_ttc = $is_percentage ?
            $price['ttc'] * (1 - $real_reduction) : $price['ttc'] - $real_reduction;
        $final_price_ht = $price['ttc'] && 0 != $price['ttc'] ? $final_price_ttc * $price['ht'] / $price['ttc'] : 0;
        $margin = false;
        if ($price['wholesale'] && $price['wholesale'] > 0) {
            $margin = ($final_price_ht - $price['wholesale']) / $price['wholesale'] * 100;
            $margin = Tools::ps_round($margin, 1);
        }

        // *** Mise à jour de la Table temporaire : Marges
        if (!$id_product_attribute) {
            $table = 'temporary_product_margin';
            $sql = 'UPDATE `' . _DB_PREFIX_ . $table . '`
                    SET margin = ' . (int) $margin . '
                    WHERE id_promos_campagnes = ' . (int) $this->campagne->id . '
                        AND id_product = ' . (int) $id_product;
            Db::getInstance()->execute($sql);
        }

        // **** Enregistrement
        if (!empty($id_product)) {
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                    WHERE id_promos_campagnes = ' . (int) $this->campagne->id . '
                        AND id_product = ' . (int) $id_product . '
                        AND id_product_attribute = ' . (int) $id_product_attribute;
            if ($promos_produit = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
                if ($reduction) {
                    // Modification du Prix spécifique
                    $specific_price = new SpecificPrice((int) $promos_produit['id_specific_price']);
                    if ($specific_price->id) { // il existe bien
                        $specific_price->reduction = (float) $reduction;
                        $specific_price->reduction_tax = 1; // 0
                        $specific_price->reduction_type = $is_percentage ? 'percentage' : 'amount';
                        if ($specific_price->update()) {
                            $ajax['success'] = true;
                        }
                    } else { // il n'existe plus !? on le recréé...
                        $specific_price = new SpecificPrice();
                        $specific_price->id_shop = (int) $this->campagne->id_shop;
                        $specific_price->id_cart = 0;
                        $specific_price->id_product = (int) $id_product;
                        $specific_price->id_product_attribute = (int) $id_product_attribute;
                        $specific_price->id_currency = (int) $this->campagne->id_currency;
                        $specific_price->id_country = (int) $this->campagne->id_country;
                        $specific_price->id_group = (int) $this->campagne->id_group;
                        $specific_price->id_customer = 0;
                        $specific_price->price = -1;
                        $specific_price->from_quantity = 1;
                        $specific_price->reduction = (float) $reduction;
                        $specific_price->reduction_tax = 1; // 0
                        $specific_price->reduction_type = $is_percentage ? 'percentage' : 'amount';
                        $specific_price->from = $this->campagne->date_debut;
                        $specific_price->to = $this->campagne->date_fin;
                        if ($specific_price->add()) {
                            $sql = 'UPDATE `' . _DB_PREFIX_ . 'promos_produits`
                                    SET id_specific_price = ' . (int) $specific_price->id . '
                                    WHERE id_promos_campagnes = ' . (int) $this->campagne->id . '
                                        AND id_product = ' . (int) $id_product . '
                                        AND id_product_attribute = ' . (int) $id_product_attribute;
                            if (Db::getInstance()->execute($sql)) {
                                $ajax['success'] = true;
                            }
                        }
                    }
                } else {
                    // Suppression du Prix spécifique
                    $specific_price = new SpecificPrice($promos_produit['id_specific_price']);
                    if ($specific_price->delete()) {
                        $sql = 'DELETE FROM `' . _DB_PREFIX_ . 'promos_produits`
                                WHERE id_promos_campagnes = ' . (int) $this->campagne->id . '
                                    AND id_product = ' . (int) $id_product . '
                                    AND id_product_attribute = ' . (int) $id_product_attribute;
                        if (Db::getInstance()->execute($sql)) {
                            $ajax['val'] = '';
                            $ajax['success'] = true;
                        }
                    }
                }
            } else {
                if ($reduction) {
                    // Création du Prix spécifique
                    $specific_price = new SpecificPrice();
                    $specific_price->id_shop = (int) $this->campagne->id_shop;
                    $specific_price->id_cart = 0;
                    $specific_price->id_product = (int) $id_product;
                    $specific_price->id_product_attribute = (int) $id_product_attribute;
                    $specific_price->id_currency = (int) $this->campagne->id_currency;
                    $specific_price->id_country = (int) $this->campagne->id_country;
                    $specific_price->id_group = (int) $this->campagne->id_group;
                    $specific_price->id_customer = 0;
                    $specific_price->price = -1;
                    $specific_price->from_quantity = 1;
                    $specific_price->reduction = (float) $reduction;
                    $specific_price->reduction_tax = 1; // 0
                    $specific_price->reduction_type = $is_percentage ? 'percentage' : 'amount';
                    $specific_price->from = $this->campagne->date_debut;
                    $specific_price->to = $this->campagne->date_fin;
                    if ($specific_price->add()) {
                        $on_sale = 0;
                        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                                WHERE id_promos_campagnes = ' . (int) $this->campagne->id . '
                                    AND id_product = ' . (int) $id_product . '
                                ORDER BY id_product_attribute';
                        if ($promos_produits = Db::getInstance()->getRow($sql)) {
                            $on_sale = $promos_produits['solde'] ? 1 : 0;
                        }
                        $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'promos_produits`
                                    (id_promos_campagnes, id_product, id_product_attribute, id_specific_price, solde)
                                VALUES (' . (int) $this->campagne->id . ', ' . (int) $id_product . ', ' .
                                    (int) $id_product_attribute . ', ' . (int) $specific_price->id . ', ' . $on_sale . ')';
                        if (Db::getInstance()->execute($sql)) {
                            $ajax['success'] = true;
                        }
                    }
                } else {
                    $ajax['val'] = '';
                    $ajax['success'] = true;
                }
            }
        }

        // En solde !?
        $on_sale = 2;
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'promos_produits`
                WHERE id_promos_campagnes = ' . (int) $this->campagne->id . '
                    AND id_product = ' . (int) $id_product . '
                ORDER BY id_product_attribute';
        if ($promos_produits = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql)) {
            $on_sale = $promos_produits['solde'] ? 1 : 0;
        }

        // *** Valeurs de sorties
        $ajax['wholesale_price'] = Tools::displayPrice($price['wholesale']);
        $ajax['price_ht'] = Tools::displayPrice($price['ht']);
        $ajax['price_ttc'] = Tools::displayPrice($price['ttc']);
        $ajax['final_price_ht'] = Tools::displayPrice($final_price_ht);
        $ajax['final_price_ttc'] = Tools::displayPrice($final_price_ttc);
        $ajax['margin'] = $margin;
        $ajax['on_sale'] = $on_sale;

        return $ajax;
    }

    public static function getProductNameOverride($id_product, $id_product_attribute = null, $id_lang = null)
    {
        // use the lang in the context if $id_lang is not defined
        if (!$id_lang) {
            $id_lang = (int) Context::getContext()->language->id;
        }

        // creates the query object
        $query = new DbQuery();

        // selects different names, if it is a combination
        if ($id_product_attribute) {
            $select = "IFNULL(
                CONCAT(
                    pl.name, ' : ',
                    GROUP_CONCAT(DISTINCT agl.name, ' - ', al.name SEPARATOR ',')
                ),
                pl.name
            ) AS name";

            $query->select($select);
        } else {
            $query->select('DISTINCT pl.name as name');
        }

        // adds joins & where clauses for combinations
        if ($id_product_attribute) {
            $query->from('product_attribute', 'pa');
            $query->join(Shop::addSqlAssociation('product_attribute', 'pa'));
            $query->innerJoin(
                'product_lang',
                'pl',
                'pl.id_product = pa.id_product AND pl.id_lang = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl')
            );
            $query->leftJoin(
                'product_attribute_combination',
                'pac',
                'pac.id_product_attribute = pa.id_product_attribute'
            );
            $query->leftJoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
            $query->leftJoin(
                'attribute_lang',
                'al',
                'al.id_attribute = atr.id_attribute AND al.id_lang = ' . (int) $id_lang
            );
            $query->leftJoin(
                'attribute_group_lang',
                'agl',
                'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = ' . (int) $id_lang
            );
            $query->where(
                'pa.id_product = ' . (int) $id_product . ' AND pa.id_product_attribute = ' . (int) $id_product_attribute
            );
        } else {
            // or just adds a 'where' clause for a simple product

            $query->from('product_lang', 'pl');
            $query->where('pl.id_product = ' . (int) $id_product);
            $query->where('pl.id_lang = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl'));
        }

        return Db::getInstance()->getValue($query);
    }
}
