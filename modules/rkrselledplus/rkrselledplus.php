<?php
/**
 * @author    Rekire <info@rekire.com>
 * @copyright Rekire
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'rkrselledplus/class/RkrSelledPlusConfig.php';

class RkrSelledPlus extends Module implements WidgetInterface
{
    const INSTALL_SQL_FILE = 'tablas.sql';
    private $db;
    private $templateFile;

    public function __construct()
    {
        $this->name = 'rkrselledplus';
        $this->module_key = '30ba947a86c05c4f50bf63ff5b1341fe';
        $this->version = '1.0.5';
        $this->tab = 'front_office_features';
        $this->author = 'Rekire';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Best sellers plus');
        $this->description = $this->l('Shows the best sellers by category and date of purchase');

        $this->ps_versions_compliancy = ['min' => '1.7.3', 'max' => _PS_VERSION_];

        $this->templateFile = 'module:rkrselledplus/views/templates/hook/rkr_selledplus_front.tpl';
        $this->db = Db::getInstance(_PS_USE_SQL_SLAVE_);
    }

    public function install($keep = true)
    {
        Configuration::updateValue('RKR_SELLED_PLUS_ONLY_ORDERS', 0);

        if ($keep) {
            if (!$this->createDB_file()) {
                return false;
            }
        }

        return parent::install()
            && $this->registerHook('actionFrontControllerSetMedia')
            && $this->registerHook('filterCmsContent')
            && $this->registerHook('filterCategoryContent')
            && $this->registerHook('filterManufacturerContent')
            && $this->registerHook('filterSupplierContent');
    }

    public function uninstall($keep = true)
    {
        if (!parent::uninstall() || ($keep && !$this->deleteDB())
        ) {
            return false;
        }

        Configuration::deleteByName('RKR_SELLED_PLUS_ONLY_ORDERS');

        return true;
    }

    public function reset()
    {
        if (!$this->uninstall(false)) {
            return false;
        }
        if (!$this->install(false)) {
            return false;
        }

        return true;
    }

    public function getContent()
    {
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminRkrSelledPlusConfig'));
    }

    private function createDB_file()
    {
        if (!file_exists(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE)) {
            return false;
        } elseif (!$sql = Tools::file_get_contents(dirname(__FILE__) . '/' . self::INSTALL_SQL_FILE)) {
            return false;
        }
        $sql = str_replace(['PREFIX_', 'ENGINE_TYPE'], [_DB_PREFIX_, _MYSQL_ENGINE_], $sql);

        $sql = preg_split("/;\s*[\r\n]+/", trim($sql));

        foreach ($sql as $query) {
            if (!Db::getInstance()->execute(trim($query))) {
                return false;
            }
        }

        return true;
    }

    private function deleteDB()
    {
        return Db::getInstance()->execute(
            'DROP TABLE IF EXISTS ' . _DB_PREFIX_ . 'rkr_selled_plus, ' . _DB_PREFIX_ . 'rkr_selled_plus_lang');
    }

    public function hookActionFrontControllerSetMedia($params)
    {
        $this->context->controller->registerStylesheet(
            'module-rkrselledplus-style',
            'modules/' . $this->name . '/views/css/front.css',
            [
                'priority' => 50,
            ]
        );

        $this->context->controller->registerStylesheet(
            'module-rkrselledplus-swiper-style',
            'modules/' . $this->name . '/views/css/lib/swiper-bundle.min.css',
            []
        );

        $this->context->controller->registerJavascript(
            'module-rkrselledplus-swiper-lib',
            'modules/' . $this->name . '/views/js/lib/swiper-bundle.min.js',
            []
        );
    }

    public function hookfilterCmsContent($params)
    {
        if (Tools::getValue('id_cms') > 0) {
            $cmsHtml = $params['object']['content'];
            $params['object']['content'] = $this->getContentByShortCode($cmsHtml);

            return [
                'object' => $params['object'],
            ];
        }

        return false;
    }

    public function hookfilterCategoryContent($params)
    {
        if (!empty($params['object']['id'])) {
            $cmsHtml = $params['object']['description'];
            $params['object']['description'] = $this->getContentByShortCode($cmsHtml,
                ['id_category' => $params['object']['id']]);

            return [
                'object' => $params['object'],
            ];
        }

        return false;
    }

    public function hookfilterManufacturerContent($params)
    {
        if (Tools::getValue('id_manufacturer') > 0) {
            $cmsHtml = $params['filtered_content'];
            $params['filtered_content'] = $this->getContentByShortCode($cmsHtml,
                ['id_manufacturer' => Tools::getValue('id_manufacturer')]);
        }

        return $params['filtered_content'];
    }

    public function hookfilterSupplierContent($params)
    {
        if (!empty($params['object']['id'])) {
            $cmsHtml = $params['object']['description'];
            $params['object']['description'] = $this->getContentByShortCode($cmsHtml,
                ['id_supplier' => $params['object']['id']]);

            return [
                'object' => $params['object'],
            ];
        }

        return false;
    }

    public function renderWidget($hookName = null, array $configuration = [])
    {
        if ('quickview' === Tools::getValue('action')) {
            return false;
        }

        $multipleSliders = $this->getWidgetVariables($hookName, $configuration);
        if (empty($multipleSliders)) {
            return false;
        }

        $this->smarty->assign([
            'multipleSliders' => $multipleSliders,
            'enable_add_to_cart' => $hookName == 'displayProductAdditionalInfo' ? false : true,
            'class' => 'rkr_selledplus-type-hook' . (isset($this->context->controller->cms) ? ' rkr_selledplus-page-cms' : ''),
        ]);

        return $this->fetch($this->templateFile);
    }

    public function isCarruselVisible($configRow)
    {
        $currentPage = $this->context->controller->php_self;
        if ($currentPage == 'my-account') {
            $currentPage = 'my_account';
        }
        $page = "page_$currentPage";

        if ($configRow['filter_page_visibility']) {
            if (!isset($configRow[$page]) || (isset($configRow[$page]) && !$configRow[$page])) {
                return false;
            } else {
                $ret = true;
                switch ($page) {
                    case 'page_category':
                        if ($configRow['filter_id_categories'] != '[]') {
                            $toDisplay = json_decode($configRow['filter_id_categories']);
                            $idCategory = (int) Tools::getValue('id_category');
                            $ret = in_array($idCategory, $toDisplay);
                        } else {
                            $ret = true; // si no se especifica ninguna categoría, es visible en todas
                        }
                        break;
                    case 'page_manufacturer':
                        if ($configRow['filter_id_manufacturer'] != 0) {
                            $idManufacturer = (int) Tools::getValue('id_manufacturer');
                            $ret = $configRow['filter_id_manufacturer'] == $idManufacturer;
                        } else {
                            $ret = true; // 0 = todas
                        }
                        break;
                    default:
                        return true;
                }

                return $ret;
            }
        }

        return true;
    }

    public function getWidgetVariables($hookName = null, array $configuration = [])
    {
        $multipleSliders = [];

        if ($hookName) {
            $slidersInHook = RkrSelledPlusConfig::getRowsByHook($hookName, (int) $this->context->language->id);

            foreach ($slidersInHook as $sliderInHook) {
                if ($this->isCarruselVisible($sliderInHook)) {
                    $id = $sliderInHook['id_slider'];
                    $productsSlider = $this->getProductsSlider($sliderInHook);
                    if (!empty($productsSlider)) {
                        $multipleSliders[$id] = $productsSlider;
                    }
                }
            }
        }

        return $multipleSliders;
    }

    protected function getProductsSlider($configRow, $idAux = [])
    {
        $slider = [];

        if ($configRow) {
            $id = $configRow['id_slider'];

            $type = $configRow['type'];    // 1 = hook, 2 = shortcode
            $manufacturerOption = $configRow['manufacturer_option']; /* 1 = todos , 2 = especificar fabricante, 3 = fabricante actual */

            if ($manufacturerOption == 2
                && (isset($idAux['id_manufacturer'])
                    && $idAux['id_manufacturer'] != $configRow['id_manufacturer'])
            ) {
                return [];
            }

            $hookName = $configRow['hook_name'];
            $categoryOption = $configRow['category_option'];
            $id_categories = $configRow['id_categories'] ? json_decode($configRow['id_categories']) : [];

            if ($categoryOption == 2 && !$id_categories) {
                return [];
            } elseif ($categoryOption == 3) {
                $id_category = (int) Tools::getValue('id_category');
                if (!$id_category && ($idProduct = Tools::getValue('id_product'))) { // categoría por defecto del producto
                    $id_category = (int) Db::getInstance()->getValue('
                            SELECT product_shop.`id_category_default`
                            FROM `' . _DB_PREFIX_ . 'product` p
                        ' . Shop::addSqlAssociation('product', 'p') . '
                        WHERE p.`id_product` = ' . (int) $idProduct);
                }
                if (!$id_category) {
                    return [];
                } else {
                    $id_categories = [$id_category];
                }
            }

            $productsSlider = $this->getBestSellerProducts($configRow, $id_categories, $idAux);

            if (!empty($productsSlider)) {
                $nProducts = count($productsSlider);
                $slider = [
                    'id' => ($type == 1 ? "hook-$hookName" : 'shortcode') . "_$id",
                    'type' => $type,
                    'title' => $configRow['title'],
                    'products' => $productsSlider,
                    'imageType' => $configRow['image_type'],
                    'configRow' => $configRow,
                    'nProducts' => $nProducts,
                ];
            }
        }

        return $slider;
    }

    protected function getBestSellerProducts($configRow, $id_categories, $idAux = [])
    {
        $sliderProducts = $this->searchBestSellerProducts($configRow, $id_categories, $idAux);

        if (!empty($sliderProducts)) {
            $assembler = new ProductAssembler($this->context);
            $presenterFactory = new ProductPresenterFactory($this->context);
            $presentationSettings = $presenterFactory->getPresentationSettings();
            $presenter = new ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );

            $slider = [];

            foreach ($sliderProducts as $productData) {
                $assembledProduct = $assembler->assembleProduct($productData);

                $product = $presenter->present(
                    $presentationSettings,
                    $assembledProduct,
                    $this->context->language
                );

                $slider[] = $product;
            }

            return $slider;
        }
    }

    protected function searchBestSellerProducts($configRow, $id_categories, $idAux)
    {
        $sliderIdProducts = [];

        $query = new DbQuery();
        $query->from('product', 'p');
        $query->join(Shop::addSqlAssociation('product', 'p'));
        $query->where('product_shop.`active` = 1 AND product_shop.`visibility` IN ("both", "catalog")');
        $query->leftJoin('product_attribute_shop', 'pas',
            'pas.id_product_attribute = product_shop.cache_default_attribute AND pas.id_shop = product_shop.id_shop');
        $today = date('Y-m-d H:i:s');

        $period = $configRow['period'];
        $categoryOption = $configRow['category_option'];
        $maxProductsShow = (int) $configRow['max_products_show'];
        $manufacturerOption = $configRow['manufacturer_option']; /* 1 = todos , 2 = especificar fabricante, 3 = fabricante actual */

        if ($manufacturerOption == 3 && (isset($idAux['id_manufacturer']) && $idAux['id_manufacturer'])) {
            $idManufacturer = (int) $idAux['id_manufacturer'];
        } elseif ($manufacturerOption == 2) {
            $idManufacturer = (int) $configRow['id_manufacturer'];
        } else {
            $idManufacturer = null;
        }

        $idSupplier = (isset($idAux['id_supplier']) && ($idAux['id_supplier'] != 0)) ? $idAux['id_supplier'] : null;

        switch ($period) {
            case 'all':
                if (Configuration::get('RKR_SELLED_PLUS_ONLY_ORDERS')) {
                    $query->select('p.`id_product`, sum(od.`product_quantity`) as qty_sales, COUNT(*) as nbr, pas.minimal_quantity AS pa_minimal_quantity');
                    $query->innerJoin('order_detail', 'od', 'p.`id_product` = od.`product_id`');
                    $query->innerJoin('orders', 'o', 'od.`id_order` = o.`id_order`');
                    $query->where('o.`valid` = true');
                    $query->where('o.`current_state` IN (select `id_order_state` from ' . _DB_PREFIX_ . 'order_state where `logable` = 1)');
                    $query->groupBy('p.`id_product`');
                    $query->orderBy('qty_sales DESC, p.`id_product` ASC');
                } else {
                    $query->select('DISTINCT(p.`id_product`), ps.quantity as qty_sales, pas.minimal_quantity AS pa_minimal_quantity');
                    $query->innerJoin('product_sale', 'ps', 'p.id_product = ps.id_product');
                    $query->orderBy('qty_sales DESC, p.`id_product` ASC');
                }

                break;
            case '1 day':
            case '1 week':
            case '1 month':
            case '3 months':
            case '6 months':
            case '1 year':
                $initdate = date('Y-m-d', strtotime($today . " -$period"));
                $query->select('p.`id_product`, sum(od.`product_quantity`) as qty_sales, COUNT(*) as nbr, pas.minimal_quantity AS pa_minimal_quantity');
                if (Configuration::get('RKR_SELLED_PLUS_ONLY_ORDERS')) {
                    $query->innerJoin('order_detail', 'od', 'p.`id_product` = od.`product_id`');
                } else {
                    $query->innerJoin('product_sale', 'ps', 'p.`id_product` = ps.`id_product`');
                    $query->innerJoin('order_detail', 'od', 'ps.`id_product` = od.`product_id`');
                }
                $query->innerJoin('orders', 'o', 'od.`id_order` = o.`id_order`');
                $query->where('o.`valid` = true');
                $query->where('o.`current_state` IN (select `id_order_state` from ' . _DB_PREFIX_ . 'order_state where `logable` = 1)');
                $query->where('o.`date_add` <= ' . "'$today'");
                $query->where('o.`date_add` >= ' . "'$initdate'");
                $query->groupBy('p.`id_product`');
                $query->orderBy('qty_sales DESC, p.`id_product` ASC');

                break;
        }

        // -- 1 todos , 2 especificar categoría, 3 categoría actual
        if ($categoryOption != 1 && $id_categories) {
            $query->innerJoin('category_product', 'cp', 'cp.id_product = p.id_product');
            $query->where('cp.id_category IN (' . implode(',', $id_categories) . ')');
        }

        if ($idManufacturer) {
            $query->where("p.id_manufacturer = $idManufacturer");
        }

        if ($idSupplier) {
            $query->where("p.id_supplier = $idSupplier");
        }

        if ($maxProductsShow) {
            $query->limit($maxProductsShow);
        }

        return $this->db->executeS($query);
    }

    public function getContentByShortCode($html, $idAux = [])   // id_category => x , id_manufacturer => x
    {
        preg_match_all('/\{(rkrselledplus:)(.*?)\}/', $html, $matches);

        $idsSlider = [];
        if (isset($matches[0]) && $matches[0]) {
            foreach ($matches[0] as $key => $content) {
                $matchNoBrackets = str_replace(['{', '}'], '', $content);
                $shortCodeExploded = explode(':', $matchNoBrackets);
                if (isset($shortCodeExploded[1])) {
                    $idsSlider[$key] = (int) $shortCodeExploded[1];    // contiene el id del slider
                }
            }

            foreach ($idsSlider as $idSlider) {
                $shortCode = "{rkrselledplus:$idSlider}";
                $sliderConfig = RkrSelledPlusConfig::getRowById($idSlider, (int) $this->context->language->id);
                $productsSlider = $this->getProductsSlider($sliderConfig, $idAux);
                if ($productsSlider) {
                    $this->smarty->assign([
                        'multipleSliders' => [$idSlider => $productsSlider],
                        'link' => $this->context->link,
                        'configuration' => ['is_catalog' => Configuration::isCatalogMode()],
                        'enable_add_to_cart' => true,
                        'class' => 'rkr_selledplus-type-cms rkr_selledplus-page-cms',
                        'urls' => $this->context->controller->getTemplateVarUrls(),
                    ]);
                    $sliderContent = $this->fetch($this->templateFile);
                    $html = str_replace($shortCode, $sliderContent, $html);
                } else {
                    $html = str_replace($shortCode, '', $html);
                }
            }
        }

        return $html;
    }
}
