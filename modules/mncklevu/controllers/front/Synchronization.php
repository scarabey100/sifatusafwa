<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use MncKlevu\PrestaShop\Adapter\Configuration;

@ini_set('max_execution_time', 0);

class MncKlevuSynchronizationModuleFrontController extends ModuleFrontController
{
    /**
     * @return MncKlevu
     */
    protected function getModule()
    {
        return $this->module;
    }

    public function postProcess()
    {
        if (strcmp(Tools::getValue('token'), $this->getModule()->getToken()) !== 0) {
            die($this->module->l('Invalid token.', 'Synchronization'));
        }
        
        if (!empty(Tools::getValue('products')) ){
            $passed_title = Tools::getValue('passed_title');
            $products = Tools::getValue('products');
            $token = Tools::getValue('token');
            $action = Tools::getValue('action');
            $slider = 0 ;
            if ($action == 'getResult') {
                $slider = 0 ;
            } else {
                $slider = 1 ;
            }
            $theme =  $this->getModule()->ThemingProducts($products,$slider,$passed_title) ;
            $this->ajaxDie(json_encode([
            'token' => $token,
            'success' => true,
            'products' => $theme,
            ]));
            die;
        }

        $productCount = $this->getModule()
            ->getProductSynchronizer()
            ->getProductCount();

        $productCountPerRequest = (int)$this->getModule()
            ->getConfiguration()
            ->get(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST);

        if ($productCountPerRequest == 0) {
            $productCountPerRequest = $productCount;
        }

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            if (!$this->getModule()->getConnectionStatus($languageId)) {
                continue;
            }

            // First, delete inactive products that are currently synced to Klevu
            if (!$this->getModule()->getProductSynchronizer()->deleteInactiveProducts($languageId)) {
                die($this->module->l('Failed to delete inactive products.', 'Synchronization'));
            }

            if (!$this->getModule()->getProductSynchronizer()->setProductsRecordsAsNotValid($languageId)) {
                die($this->module->l('Failed to initialize products records.', 'Synchronization'));
            }

            $start = 0;

            while ($start < $productCount) {
                if (!$this->getModule()->getProductSynchronizer()->updateProducts(
                    $languageId,
                    $start,
                    $productCountPerRequest
                )) {
                    die($this->module->l('Failed to synchronize products.', 'Synchronization'));
                }

                $start += $productCountPerRequest;
            }

            if (!$this->getModule()->getProductSynchronizer()->deleteNotValidRecords($languageId)) {
                die($this->module->l('Failed to delete not valid records.', 'Synchronization'));
            }

            $this->getModule()->getConfiguration()->set(
                Configuration::KEY_SYNCHRONIZED,
                [$languageId => 1]
            );
        }

        die('Done!');
    }
}