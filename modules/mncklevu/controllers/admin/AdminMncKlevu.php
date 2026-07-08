<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

use MncKlevu\PrestaShop\Adapter\Configuration;

@ini_set('max_execution_time', 0);

class AdminMncKlevuController extends ModuleAdminController
{
    /**
     * @param array $data
     */
    protected function displayAjaxResponse(array $data)
    {
        ob_end_clean();
        header('Content-Type: application/json');
        header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');

        die(json_encode($data));
    }

    /**
     * @param string $message
     */
    protected function displayAjaxError($message)
    {
        $this->displayAjaxResponse(['error' => $message]);
    }

    /**
     * @return string
     */
    protected function getLanguageErrorMessage()
    {
        return $this->module->l('Invalid language ID.', 'AdminMncKlevu');
    }

    /**
     * @return MncKlevu
     */
    protected function getModule()
    {
        return $this->module;
    }

    public function ajaxProcessInitialize()
    {
        $languageId = (int)Tools::getValue('id_lang');
        if (!$languageId) {
            $this->displayAjaxError($this->getLanguageErrorMessage());
        }

        if (!$this->getModule()->getConfiguration()->get(Configuration::KEY_REST_AUTH_KEY, $languageId) ||
            !$this->getModule()->getConfiguration()->get(Configuration::KEY_CONNECTED, $languageId)) {
            $this->displayAjaxError($this->module->l('Invalid connection.', 'AdminMncKlevu'));
        }

        if (!$this->getModule()->getProductSynchronizer()->setProductsRecordsAsNotValid($languageId)) {
            $this->displayAjaxError($this->module->l('Failed to initialize products records.', 'AdminMncKlevu'));
        }

        $this->displayAjaxResponse([
            'product_count' => $this->getModule()
                ->getProductSynchronizer()
                ->getProductCount(),
            'product_count_per_request' => (int)$this->getModule()
                ->getConfiguration()
                ->get(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST),
            'status' => $this->module->l('Products synchronization', 'AdminMncKlevu'),
        ]);
    }

    public function ajaxProcessSynchronizeProducts()
    {
        $languageId = (int)Tools::getValue('id_lang');
        if (!$languageId) {
            $this->displayAjaxError($this->getLanguageErrorMessage());
        }

        if (!$this->getModule()->getProductSynchronizer()->updateProducts(
            $languageId,
            (int)Tools::getValue('start'),
            (int)Tools::getValue('limit')
        )) {
            $this->displayAjaxError($this->module->l('Failed to synchronize products.', 'AdminMncKlevu'));
        }

        $this->displayAjaxResponse([
            'success' => 1,
        ]);
    }

    public function ajaxProcessFinalizeProductsSynchronization()
    {
        $languageId = (int)Tools::getValue('id_lang');
        if (!$languageId) {
            $this->displayAjaxError($this->getLanguageErrorMessage());
        }

        if (!$this->getModule()->getProductSynchronizer()->deleteNotValidRecords($languageId)) {
            $this->displayAjaxError($this->module->l('Failed to delete not valid records.', 'AdminMncKlevu'));
        }

        $this->getModule()->getConfiguration()->set(
            Configuration::KEY_SYNCHRONIZED,
            [$languageId => 1]
        );

        $this->displayAjaxResponse(['status' => $this->module->l('Completed', 'AdminMncKlevu')]);
    }
}
