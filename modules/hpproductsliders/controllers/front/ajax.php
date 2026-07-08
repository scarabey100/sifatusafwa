<?php

/**
 * 2007-2020 PrestaShop and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */
class HpproductslidersAjaxModuleFrontController extends ModuleFrontController {

    public $ssl = true;

    public function initContent() {
        parent::initContent();

        if ($this->module->secret === Tools::getValue('secret')) {
            $numberOfProducts = Tools::getValue('numberOfProducts');
            $formattedProducts = [];

            if (Tools::getValue('page') == 'new') {
                $sql = 'SELECT p.id_product, 
                CONCAT(p.reference, " ", pl.name) AS product_name 
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product 
                LEFT JOIN ' . _DB_PREFIX_ . 'product_shop pss ON pss.id_product = pl.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                WHERE pl.id_lang = ' . (int) Context::getContext()->language->id . ' 
                AND p.active = 1 AND pss.active = 1
                ORDER BY p.date_add DESC LIMIT ' . $numberOfProducts;

                $products = Db::getInstance()->executeS($sql);

                $formattedProducts = [];

                foreach ($products as $product) {
                    $formattedProducts[] = [
                        'id_product' => $product['id_product'],
                        'name' => $product['product_name']
                    ];
                }

                usort($formattedProducts, function ($a, $b) {
                    return $a['id_product'] <=> $b['id_product'];
                });
            }

            if (Tools::getValue('page') == 'brands') {
                $sql = 'SELECT DISTINCT p.id_product, 
                CONCAT(p.reference, " ", pl.name) AS product_name 
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product 
                LEFT JOIN ' . _DB_PREFIX_ . 'product_shop pss ON pss.id_product = pl.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                WHERE pl.id_lang = ' . (int) Context::getContext()->language->id . ' 
                AND p.id_manufacturer = ' . Tools::getValue('idManufacturer') . '
                AND p.active = 1 AND pss.active = 1
                ORDER BY p.date_add DESC LIMIT ' . $numberOfProducts;

                $products = Db::getInstance()->executeS($sql);

                $formattedProducts = [];

                foreach ($products as $product) {
                    $formattedProducts[] = [
                        'id_product' => $product['id_product'],
                        'name' => $product['product_name']
                    ];
                }

                usort($formattedProducts, function ($a, $b) {
                    return $a['id_product'] <=> $b['id_product'];
                });
            }

            if (Tools::getValue('page') == 'category') {
                $sql = 'SELECT DISTINCT p.id_product, 
                CONCAT(p.reference, " ", pl.name) AS product_name 
                FROM ' . _DB_PREFIX_ . 'product p
                LEFT JOIN ' . _DB_PREFIX_ . 'product_lang pl ON p.id_product = pl.id_product 
                LEFT JOIN ' . _DB_PREFIX_ . 'product_shop pss ON pss.id_product = pl.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'stock_available sa ON p.id_product = sa.id_product
                LEFT JOIN ' . _DB_PREFIX_ . 'category_product cp ON p.id_product = cp.id_product
                WHERE pl.id_lang = ' . (int) Context::getContext()->language->id . ' 
                AND cp.id_category = ' . Tools::getValue('idCategory') . '
                AND p.active = 1 AND pss.active = 1
                ORDER BY p.date_add DESC LIMIT ' . $numberOfProducts;

                $products = Db::getInstance()->executeS($sql);

                $formattedProducts = [];

                foreach ($products as $product) {
                    $formattedProducts[] = [
                        'id_product' => $product['id_product'],
                        'name' => $product['product_name']
                    ];
                }

                usort($formattedProducts, function ($a, $b) {
                    return $a['id_product'] <=> $b['id_product'];
                });
            }

            if (Tools::getValue('page') == 'sales') {
                $sql = 'SELECT DISTINCT ssp.`id_product`, sp.reference,spl.meta_title, sp.active, ssp.reduction_type, ssa.quantity FROM sf_specific_price ssp
                        LEFT JOIN ' . _DB_PREFIX_ . 'product sp ON (sp.id_product = ssp.id_product)
                        LEFT JOIN (
                            SELECT id_product, MAX(quantity) AS quantity
                            FROM ' . _DB_PREFIX_ . 'stock_available
                            GROUP BY id_product
                        ) ssa ON (ssa.id_product = ssp.id_product)
                        LEFT JOIN ' . _DB_PREFIX_ . 'product_lang spl ON (spl.id_product = sp.id_product AND spl.id_lang = 1)
                        WHERE `to` = "0000-00-00 00:00:00" AND sp.active = 1 AND ssa.quantity > 0 AND sp.visibility != "none"
                        ';

                $products = Db::getInstance()->executeS($sql);

                $formattedProducts = [];

                foreach ($products as $product) {
                    $formattedProducts[] = [
                        'id_product' => $product['id_product'],
                        'name' => $product['reference'].' '.$product['meta_title']
                    ];
                }

                usort($formattedProducts, function ($a, $b) {
                    return $a['id_product'] <=> $b['id_product'];
                });
            }

            ob_end_clean();
            header('Content-Type: application/json');
            exit(json_encode([
                'products' => $formattedProducts,
            ]));
        }

        ob_end_clean();
        header('Content-Type: application/json');
        exit(json_encode([
            'data' => false,
        ]));
    }
}
