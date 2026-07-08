<?php
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\ProductListingPresenter;

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'rkrselledplus/class/RkrSelledPlusConfig.php';
class RkrSelledPlusOverride extends RkrSelledPlus
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
    protected function searchBestSellerProducts($configRow, $id_categories, $idAux)
    {
        $sliderIdProducts = [];

        $query = new DbQuery();
        $query->from('product', 'p');
        $query->join(Shop::addSqlAssociation('product', 'p'));
        $query->where('product_shop.`active` = 1 AND product_shop.`visibility` IN ("both", "catalog")');
        $query->leftJoin('product_attribute_shop', 'pas',
            'pas.id_product_attribute = product_shop.cache_default_attribute AND pas.id_shop = product_shop.id_shop');
        
        // Add stock table joins
        $query->leftJoin('stock_available', 'sa', 'sa.id_product = p.id_product AND sa.id_shop = product_shop.id_shop');
        $query->leftJoin('stock_available', 'sa_attr', 'sa_attr.id_product = p.id_product AND sa_attr.id_product_attribute = pas.id_product_attribute AND sa_attr.id_shop = product_shop.id_shop');
        
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

        // Add quantity filter condition
        // For simple products (no combinations): check stock_available where id_product_attribute = 0
        // For products with combinations: check if any combination has quantity > 0
        $query->where('(
            (product_shop.cache_default_attribute = 0 AND sa.id_product_attribute = 0 AND sa.quantity > 0) 
            OR 
            (product_shop.cache_default_attribute > 0 AND sa_attr.quantity > 0)
        )');

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

}
