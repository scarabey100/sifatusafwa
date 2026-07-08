<?php
use PrestaShop\PrestaShop\Adapter\Image\ImageRetriever;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;
use PrestaShop\PrestaShop\Adapter\Product\ProductColorsRetriever;
use PrestaShop\PrestaShop\Core\Module\WidgetInterface;

if (!defined('_PS_VERSION_')) {
    exit;
}
class Ps_CrosssellingOverride extends Ps_Crossselling
{
    protected function getOrderProducts(array $productIds = [])
    {
        $q_orders = 'SELECT o.id_order
        FROM ' . _DB_PREFIX_ . 'orders o
        LEFT JOIN ' . _DB_PREFIX_ . 'order_detail od ON (od.id_order = o.id_order)
        WHERE o.valid = 1
        AND od.product_id IN (' . implode(',', $productIds) . ')
        ORDER BY o.id_order DESC
        LIMIT ' . ((int) Configuration::get('CROSSSELLING_NBR')) * static::LIMIT_FACTOR;

        $orders = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS($q_orders);

        if (0 < count($orders)) {
            $list = '';
            foreach ($orders as $order) {
                $list .= (int) $order['id_order'] . ',';
            }
            $list = rtrim($list, ',');
            $list_product_ids = join(',', $productIds);

            $sql_groups_join = $sql_groups_where = '';
            if (Group::isFeatureActive()) {
                $sql_groups_join = '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_product` cp ON (cp.`id_category` = product_shop.id_category_default AND cp.id_product = product_shop.id_product)
                LEFT JOIN `' . _DB_PREFIX_ . 'category_group` cg ON (cp.`id_category` = cg.`id_category`)';
                $groups = FrontController::getCurrentCustomerGroups();
                $sql_groups_where = 'AND cg.`id_group` ' . (count($groups) ? 'IN (' . implode(',', $groups) . ')' : '=' . (int) Group::getCurrent()->id);
            }

            // Add join with stock_available table
            $stock_join = ' LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON (sa.id_product = p.id_product AND sa.id_shop = ' . (int)Context::getContext()->shop->id . ')';

            $order_products = Db::getInstance((bool) _PS_USE_SQL_SLAVE_)->executeS('
                SELECT DISTINCT od.product_id
                FROM ' . _DB_PREFIX_ . 'order_detail od
                LEFT JOIN ' . _DB_PREFIX_ . 'product p ON (p.id_product = od.product_id)
                ' . Shop::addSqlAssociation('product', 'p') .
                $sql_groups_join . 
                $stock_join . '
                WHERE od.id_order IN (' . $list . ')
                AND od.product_id NOT IN (' . $list_product_ids . ')
                AND product_shop.visibility IN (\'both\',\'catalog\')
                AND product_shop.active = 1
                AND sa.quantity > 0
                ' . $sql_groups_where . '
                ORDER BY RAND()
                LIMIT ' . (int) Configuration::get('CROSSSELLING_NBR')
            );
        }

        if (!empty($order_products)) {
            $showPrice = (bool) Configuration::get('CROSSSELLING_DISPLAY_PRICE');

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

            if (is_array($order_products)) {
                foreach ($order_products as $productId) {
                    $productsForTemplate[] = $presenter->present(
                        $presentationSettings,
                        $assembler->assembleProduct(['id_product' => $productId['product_id']]),
                        $this->context->language
                    );
                }
            }

            return $productsForTemplate;
        }

        return false;
    }
}
