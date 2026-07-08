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
class AdminPacksController extends AdminController
{
    protected $moduleInstance;
    private $adminNewPackUrl;
    /** @var bool List content lines are clickable if true */
    protected $list_no_link = true;
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'Product';
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');
        $this->context = Context::getContext();
        $this->moduleInstance = (class_exists('AdvancedPack') ? AdvancedPack::getModuleInstance() : Module::getInstanceByName('pm_advancedpack'));
        $this->adminNewPackUrl = Link::getUrlSmarty(['entity' => 'sf', 'route' => 'admin_product_new']) . '&new_pack=1';
        parent::__construct();
        $this->bulk_actions = [
            'delete' => [
                'text' => $this->trans('Delete selected'),
                'confirm' => $this->trans('Delete selected items?'),
                'icon' => 'icon-trash',
            ],
        ];
        $this->fields_list = [
            'id_product' => [
                'title' => $this->trans('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'type' => 'int',
            ],
            'image' => [
                'title' => $this->trans('Image'),
                'align' => 'center',
                'image' => 'p',
                'orderby' => false,
                'filter' => false,
                'search' => false,
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->trans('Pack name'),
                'filter_key' => 'b!name',
                'callback' => 'getPackOrBundleNameFieldValue',
            ],
            'reference' => [
                'title' => $this->trans('Reference'),
                'align' => 'left',
            ],
            'name_category' => [
                'title' => $this->trans('Category'),
                'filter_key' => 'cl!name',
            ],
            'nb_products' => [
                'title' => $this->trans('Nb. products'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'type' => 'int',
            ],
            'is_bundle' => [
                'title' => $this->trans('Bundle'),
                'class' => 'fixed-width-xs',
                'align' => 'text-center',
                'callback' => 'getIsBundleActiveFieldValue',
            ],
            'classic_price' => [
                'title' => $this->trans('Classic price*'),
                'type' => 'price',
                'align' => 'text-right',
                'color' => 'red',
                'havingFilter' => false,
                'orderby' => false,
                'search' => false,
            ],
            'price_final' => [
                'title' => $this->trans('Pack price*'),
                'type' => 'price',
                'align' => 'text-right',
                'havingFilter' => false,
                'orderby' => false,
                'search' => false,
            ],
            'pack_quantity' => [
                'title' => $this->trans('Available Qty*'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
                'type' => 'int',
                'havingFilter' => false,
                'orderby' => false,
                'search' => false,
            ],
            'active' => [
                'title' => $this->trans('Status'),
                'active' => 'status',
                'filter_key' => 'sa!active',
                'align' => 'text-center',
                'type' => 'bool',
                'class' => 'fixed-width-sm',
                'orderby' => false,
            ],
        ];
        if (!class_exists('AdvancedPack')) {
            include_once _PS_ROOT_DIR_ . '/modules/pm_advancedpack/AdvancedPack.php';
            include_once _PS_ROOT_DIR_ . '/modules/pm_advancedpack/AdvancedPackCoreClass.php';
        }
        $id_shop = (Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP ? (int)$this->context->shop->id : 'a.id_shop_default');
        $this->_select .= ' app.`nb_products`, MAX(image_shop.`id_image`) AS id_image, cl.name AS `name_category`, 0 AS pack_quantity, 0 AS classic_price, 0 AS price_final, ap_shop.`is_bundle`';
        $this->_join .= ' JOIN (SELECT app.id_pack, COUNT(app.id_pack) as nb_products FROM `' . _DB_PREFIX_ . 'pm_advancedpack_products` app GROUP BY app.id_pack) app ON app.`id_pack`=a.`id_product` ';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'pm_advancedpack` ap_shop ON (a.`id_product` = ap_shop.`id_pack` AND ap_shop.`id_shop` = ' . $id_shop . ') ';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_shop` sa ON (a.`id_product` = sa.`id_product` AND sa.id_shop = ' . $id_shop . ') ';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON (sa.`id_category_default` = cl.`id_category` AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . $id_shop . ') ';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = a.`id_product`) ';
        $this->_join .= ' LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop ON (image_shop.`id_image` = i.`id_image` AND image_shop.`cover` = 1 AND image_shop.`id_shop` = ' . $id_shop . ') ';
        $this->_group = ' GROUP BY a.`id_product` ';
    }
    public function getIsBundleActiveFieldValue($value)
    {
        if (!empty($value)) {
            return $this->trans('Yes', [], 'Admin.Global');
        }
        return $this->trans('No', [], 'Admin.Global');
    }
    public function getPackOrBundleNameFieldValue($value, $data)
    {
        if (!empty($data['is_bundle'])) {
            $bundleDatas = AdvancedPack::getBundlesDatas($data['id_product']);
            if (isset($bundleDatas->name) && !empty($bundleDatas->name->{$this->context->language->id})) {
                return $bundleDatas->name->{$this->context->language->id};
            }
            return $value;
        }
        return $value;
    }
    public function l($string, $class = 'AdminPacksController', $addslashes = false, $htmlentities = true)
    {
        return $this->moduleInstance->l($string, $class);
    }
    public function initPageHeaderToolbar()
    {
        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_pack'] = [
                'href' => $this->adminNewPackUrl,
                'desc' => $this->trans('Add a new pack', [], 'Modules.pm_advancedpack.AdminPacksController'),
                'icon' => 'process-icon-new',
            ];
            $this->page_header_toolbar_btn['configure_module'] = [
                'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=pm_advancedpack',
                'desc' => $this->trans('Configure module', [], 'Modules.pm_advancedpack.AdminPacksController'),
                'icon' => 'process-icon-configure-pack-module icon-puzzle-piece',
            ];
        }
        parent::initPageHeaderToolbar();
    }
    public function initToolbar()
    {
        parent::initToolbar();
        if (empty($this->display)) {
            $this->toolbar_btn['new'] = [
                'href' => $this->adminNewPackUrl,
                'desc' => $this->trans('Add a new pack', [], 'Modules.pm_advancedpack.AdminPacksController'),
                'icon' => 'process-icon-new',
            ];
            $this->toolbar_btn['modules-list'] = [
                'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=pm_advancedpack',
                'desc' => $this->trans('Configure module', [], 'Modules.pm_advancedpack.AdminPacksController'),
                'icon' => 'process-icon-modules-list',
            ];
        }
    }
    public function initContent()
    {
        if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
            $this->errors[] = $this->trans('You must select a specific shop in order to continue.');
        }
        parent::initContent();
    }
    public function initProcess()
    {
        if (Tools::getIsset('updateproduct') && Tools::getIsset('id_product') && (int)Tools::getValue('id_product')) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts', true, ['id_product' => (int)Tools::getValue('id_product')]) . '#pm_advancedpack');
        }
        if (Tools::getIsset('duplicateproduct') && Tools::getIsset('id_product') && (int)Tools::getValue('id_product')) {
            Tools::redirectAdmin($this->moduleInstance->getContainer()->get('router')->generate('admin_product_unit_action', [
                'id' => (int)Tools::getValue('id_product'),
                'action' => 'duplicate',
            ]));
        }
        parent::initProcess();
    }
    public function getList($id_lang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $id_lang_shop = null)
    {
        parent::getList($id_lang, $orderBy, $orderWay, $start, $limit, $id_lang_shop == null ? $this->context->shop->id : $id_lang_shop);
        if (AdvancedPackCoreClass::_isFilledArray($this->_list)) {
            for ($i = 0; $i < count($this->_list); $i++) {
                $this->_list[$i]['classic_price'] = Tools::convertPrice(AdvancedPack::getPackPrice($this->_list[$i]['id_product'], true, false));
                $this->_list[$i]['price_final'] = Tools::convertPrice(AdvancedPack::getPackPrice($this->_list[$i]['id_product']));
                $this->_list[$i]['pack_quantity'] = AdvancedPack::getPackAvailableQuantity($this->_list[$i]['id_product']);
                if ($this->_list[$i]['pack_quantity'] === AdvancedPack::PACK_FAKE_STOCK) {
                    $this->_list[$i]['pack_quantity'] = $this->trans('Unlimited');
                }
            }
        }
    }
    public function renderList()
    {
        $this->actions = [];
        $this->addRowAction('edit');
        $this->addRowAction('duplicate');
        $this->addRowAction('delete');
        $r = '';
        if (!count($this->errors)) {
            $r .= parent::renderList();
            $r .= $this->moduleInstance->displayInformation('* ' . $this->trans('Columns with wildcards only reflect data for default combinations'));
        }
        return $r;
    }
    public function displayEditLink($token, $id, $name = null)
    {
        if (!AdvancedPack::isBundle($id) || !$mainProductId = AdvancedPack::getMainProductIdFromBundleId((int) $id)) {
            $mainProductId = $id;
        }
        if ($this->access('edit')) {
            $tpl = $this->createTemplate('helpers/list/list_action_edit.tpl');
            if (!array_key_exists('Edit', self::$cache_lang)) {
                self::$cache_lang['Edit'] = $this->trans('Edit', [], 'Admin.Actions');
            }
            $tpl->assign([
                'href' => $this->context->link->getAdminLink('AdminProducts', true, ['id_product' => (int) $mainProductId]),
                'action' => self::$cache_lang['Edit'],
                'id_product' => $mainProductId,
            ]);
            return $tpl->fetch();
        } else {
            return;
        }
    }
}
