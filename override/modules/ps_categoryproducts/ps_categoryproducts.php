<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShop\PrestaShop\Adapter\Category\CategoryProductSearchProvider;
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchContext;
use PrestaShop\PrestaShop\Core\Product\Search\ProductSearchQuery;
use PrestaShop\PrestaShop\Core\Product\Search\SortOrder;
class Ps_CategoryproductsOverride extends Ps_Categoryproducts
{
    protected $html;
    protected $templateFile;

    public function __construct()
    {
        $this->name = 'ps_categoryproducts';
        $this->tab = 'pricing_promotion';
        $this->author = 'PrestaShop';
        $this->version = '1.0.7';

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->trans('Products in the same category', [], 'Modules.Categoryproducts.Admin');
        $this->description = $this->trans('Add a block on every product page that displays items from the same category.', [], 'Modules.Categoryproducts.Admin');
        $this->ps_versions_compliancy = ['min' => '1.7.0.0', 'max' => _PS_VERSION_];

        $this->templateFile = 'module:ps_categoryproducts/views/templates/hook/ps_categoryproducts.tpl';
    }
    protected function getCategoryProducts($idProduct, $idCategory)
    {
         
        $showPrice = (bool) Configuration::get('CATEGORYPRODUCTS_DISPLAY_PRICE');
 
        $context = new ProductSearchContext($this->context);

        $query = new ProductSearchQuery(); 

        $assembler = new ProductAssembler($this->context);
        $presenterFactory = new ProductPresenterFactory($this->context);
        $presentationSettings = $presenterFactory->getPresentationSettings();
        if (version_compare(_PS_VERSION_, '1.7.5', '>=')) {
            $presenter = new \PrestaShop\PrestaShop\Adapter\Presenter\Product\ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );
        } else {
            $presenter = new \PrestaShop\PrestaShop\Core\Product\ProductListingPresenter(
                new ImageRetriever(
                    $this->context->link
                ),
                $this->context->link,
                new PriceFormatter(),
                new ProductColorsRetriever(),
                $this->context->getTranslator()
            );
        }

        $productsForTemplate = [];

        $presentationSettings->showPrices = $showPrice;
 

        $sql = '
            SELECT p.*, pl.*
            FROM '._DB_PREFIX_.'category_product cp
            INNER JOIN '._DB_PREFIX_.'product p ON p.id_product = cp.id_product
            INNER JOIN '._DB_PREFIX_.'product_lang pl ON p.id_product = pl.id_product AND pl.id_lang = '.(int)$this->context->language->id.'
            INNER JOIN '._DB_PREFIX_.'stock_available sa ON sa.id_product = p.id_product
            WHERE cp.id_category = '.(int)$idCategory.'
            AND sa.quantity > 0
            AND p.id_product != '.(int)$idProduct.'
            GROUP BY p.id_product
            ORDER BY RAND()
            LIMIT '.(int)Configuration::get('CATEGORYPRODUCTS_DISPLAY_PRODUCTS')
        ;

        $products = Db::getInstance()->executeS($sql);

        foreach ($products as $rawProduct) {
            // Not duplicate current product
            if ($rawProduct['id_product'] !== $idProduct && 
                count($productsForTemplate) < (int) Configuration::get('CATEGORYPRODUCTS_DISPLAY_PRODUCTS')) {
                $productsForTemplate[] = $presenter->present(
                    $presentationSettings,
                    $assembler->assembleProduct($rawProduct),
                    $this->context->language
                );
            }
        }

        return $productsForTemplate;
    }
}
