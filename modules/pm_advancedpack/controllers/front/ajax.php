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
class pm_advancedpackAjaxModuleFrontController extends ModuleFrontController
{
    public $ssl = true;
    public $ajax = true;
    public $display_header = false;
    public $display_footer = false;
    public $display_column_left = false;
    public $display_column_right = false;
    protected $product;
    private function getFactory()
    {
        return new ProductPresenterFactory($this->context, new TaxConfiguration());
    }
    protected function getProductPresentationSettings()
    {
        return $this->getFactory()->getPresentationSettings();
    }
    protected function getProductPresenter()
    {
        return $this->getFactory()->getPresenter();
    }
    public function getTemplateVarProduct()
    {
        $productSettings = $this->getProductPresentationSettings();
        $product = $this->objectPresenter->present($this->product);
        $product['id_product'] = (int) $this->product->id;
        $product['out_of_stock'] = (int) $this->product->out_of_stock;
        $product_full = Product::getProductProperties($this->context->language->id, $product, $this->context);
        if ($product_full['unit_price_ratio'] > 0) {
            $unitPrice = ($productSettings->include_taxes) ? $product_full['price'] : $product_full['price_tax_exc'];
            $product_full['unit_price'] = $unitPrice / $product_full['unit_price_ratio'];
        }
        $group_reduction = GroupReduction::getValueForProduct($this->product->id, (int) Group::getCurrent()->id);
        if ($group_reduction === false) {
            $group_reduction = Group::getReduction((int) $this->context->cookie->id_customer) / 100;
        }
        $product_full['customer_group_discount'] = $group_reduction;
        $product_full['rounded_display_price'] = Tools::ps_round(
            $product_full['price'],
            Context::getContext()->currency->precision
        );
        $presenter = $this->getProductPresenter();
        return $presenter->present(
            $productSettings,
            $product_full,
            $this->context->language
        );
    }
    public function postProcess()
    {
        if (!$this->isTokenValid()) {
            Tools::redirect('index.php');
        }
    }
    public function displayAjaxGetBundlePrices()
    {
        $productId = (int)Tools::getValue('productId');
        if (empty($productId) || !AdvancedPack::productHasBundles($productId)) {
            header('HTTP/1.0 403 Forbidden', true, 403);
            $this->ajaxRender(json_encode([
                'errors' => $this->l('There is no bundle for this product'),
            ]));
            exit;
        }
        $bundleId = (int)Tools::getValue('bundleId');
        if (empty($bundleId) || !AdvancedPack::isBundle($bundleId)) {
            header('HTTP/1.0 403 Forbidden', true, 403);
            $this->ajaxRender(json_encode([
                'errors' => $this->l('Invalid bundle ID'),
            ]));
            exit;
        }
        $mainProduct = new Product($productId, false, $this->context->language->id);
        $this->product = new Product($bundleId, false, $this->context->language->id);
        $bundleDatas = AdvancedPack::getBundlesDatas($bundleId);
        $priceDisplay = Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer);
        $regularPrice = Tools::displayPrice($mainProduct->getPrice((bool) !$priceDisplay, null, 6, null, false, false) * $bundleDatas->quantity);
        $product = $this->getTemplateVarProduct();
        $product['regular_price'] = $regularPrice;
        if (isset($bundleDatas->reduction->amount) && isset($bundleDatas->reduction->type) && $bundleDatas->reduction->amount) {
            $product['has_discount'] = true;
            if ($bundleDatas->reduction->type == 'percentage') {
                $product['discount_type'] = 'percentage';
                $product['discount_percentage_absolute'] = Tools::displayNumber($bundleDatas->reduction->amount) . '%';
                $product['discount_to_display'] = Tools::displayNumber($bundleDatas->reduction->amount) . '%';
            } else {
                $product['discount_type'] = 'amount';
                $product['discount_to_display'] = Tools::displayPrice($bundleDatas->reduction->amount);
            }
        } else {
            $product['has_discount'] = false;
            $product['discount_type'] = null;
            $product['discount_to_display'] = null;
        }
        $this->context->smarty->assign([
            'product' => $product,
            'displayUnitPrice' => false,
            'displayPackPrice' => false,
            'priceDisplay' => $priceDisplay,
        ]);
        $this->ajaxRender(json_encode([
            'product_prices' => $this->render('catalog/_partials/product-prices'),
            'product_title' => !empty($bundleDatas->name->{$this->context->language->id}) ? $bundleDatas->name->{$this->context->language->id} : $this->product->name,
        ]));
    }
}
