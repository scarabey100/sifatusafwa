<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Form;

use Language;
use MncKlevu;
use MncKlevu\PrestaShop\Adapter\Configuration;
use Tools;
use Validate;

class StoreSettingsForm extends AbstractStoreForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_store_settings';
    }

    /**
     * @return MncKlevu
     */
    protected function getModule()
    {
        return $this->module;
    }

    /**
     * @return array
     */
    protected function getFieldsValue()
    {
        $languageId = $this->getGridItemId();
        $configuration = $this->getModule()->getConfiguration();

        return [
            Configuration::KEY_REST_AUTH_KEY => Tools::getValue(
                Configuration::KEY_REST_AUTH_KEY,
                $configuration->get(Configuration::KEY_REST_AUTH_KEY, $languageId)
            ),
            Configuration::KEY_JS_API_KEY => Tools::getValue(
                Configuration::KEY_JS_API_KEY,
                $configuration->get(Configuration::KEY_JS_API_KEY, $languageId)
            ),
            Configuration::KEY_APIV2_CLOUD_SEARCH_URL => Tools::getValue(
                Configuration::KEY_APIV2_CLOUD_SEARCH_URL,
                $configuration->get(Configuration::KEY_APIV2_CLOUD_SEARCH_URL, $languageId)
            ),
        ];
    }

    protected function initializeConfiguration()
    {
        $languageId = $this->getGridItemId();
        $configuration = $this->getModule()->getConfiguration();
        $result = false;

        if ($configuration->get(Configuration::KEY_REST_AUTH_KEY, $languageId) === false) {
            $configuration->set(Configuration::KEY_REST_AUTH_KEY, [$languageId => '']);
            $result = true;
        }

        if ($configuration->get(Configuration::KEY_JS_API_KEY, $languageId) === false) {
            $configuration->set(Configuration::KEY_JS_API_KEY, [$languageId => '']);
            $result = true;
        }

        if ($configuration->get(Configuration::KEY_APIV2_CLOUD_SEARCH_URL, $languageId) === false) {
            $configuration->set(Configuration::KEY_APIV2_CLOUD_SEARCH_URL, [$languageId => '']);
            $result = true;
        }

        if ($configuration->get(Configuration::KEY_CONNECTED, $languageId) === false) {
            $configuration->set(Configuration::KEY_CONNECTED, [$languageId => 0]);
            $result = true;
        }

        if ($configuration->get(Configuration::KEY_SYNCHRONIZED, $languageId) === false) {
            $configuration->set(Configuration::KEY_SYNCHRONIZED, [$languageId => 0]);
            $result = true;
        }

        return $result;
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return [
            'legend' => [
                'title' => str_replace(
                    '%iso%',
                    Language::getIsoById($this->getGridItemId()),
                    $this->module->l('Store settings (%iso%)', 'StoreSettingsForm')
                ),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('REST AUTH Key', 'StoreSettingsForm'),
                    'name' => Configuration::KEY_REST_AUTH_KEY,
                    'required' => true,
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('JS API Key', 'StoreSettingsForm'),
                    'name' => Configuration::KEY_JS_API_KEY,
                    'required' => true,
                    'class' => 'fixed-width-xxl',
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('APIv2 Cloud Search URL', 'StoreSettingsForm'),
                    'name' => Configuration::KEY_APIV2_CLOUD_SEARCH_URL,
                    'required' => true,
                    'class' => 'fixed-width-xl',
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'StoreSettingsForm'),
            ],
            'buttons' => [
                [
                    'href' => $this->getCurrentIndex(['token' => $this->getToken()]),
                    'title' => $this->module->l('Cancel', 'StoreSettingsForm'),
                    'icon' => 'process-icon-cancel',
                ]
            ],
        ];
    }

    protected function validateRestAuthKey()
    {
        if (!trim((string)Tools::getValue(Configuration::KEY_REST_AUTH_KEY))) {
            $this->errors[] = $this->module->l('Invalid REST AUTH key.', 'StoreSettingsForm');
        }
    }

    protected function validateJsApiKey()
    {
        if (!trim((string)Tools::getValue(Configuration::KEY_JS_API_KEY))) {
            $this->errors[] = $this->module->l('Invalid JS API key.', 'StoreSettingsForm');
        }
    }

    protected function validateApiV2CloudSearchUrl()
    {
        $value = trim((string)Tools::getValue(Configuration::KEY_APIV2_CLOUD_SEARCH_URL));
        if (!$value || !Validate::isUrl($value)) {
            $this->errors[] = $this->module->l('Invalid APIv2 cloud search URL.', 'StoreSettingsForm');
        }
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        $this->validateRestAuthKey();
        $this->validateJsApiKey();
        $this->validateApiV2CloudSearchUrl();

        return !$this->hasErrors();
    }

    /**
     * @return bool
     */
    protected function saveFormData()
    {
        $languageId = $this->getGridItemId();
        $restAuthKey = trim((string)Tools::getValue(Configuration::KEY_REST_AUTH_KEY));
        $configuration = $this->getModule()->getConfiguration();

        if ($restAuthKey !== $configuration->get(Configuration::KEY_REST_AUTH_KEY, $languageId)) {
            $configuration->set(Configuration::KEY_SYNCHRONIZED, [$languageId => 0]);
        }

        $configuration
            ->set(
                Configuration::KEY_REST_AUTH_KEY,
                [$languageId => $restAuthKey]
            )
            ->set(
                Configuration::KEY_JS_API_KEY,
                [$languageId => trim((string)Tools::getValue(Configuration::KEY_JS_API_KEY))]
            )
            ->set(
                Configuration::KEY_APIV2_CLOUD_SEARCH_URL,
                [$languageId => trim((string)Tools::getValue(Configuration::KEY_APIV2_CLOUD_SEARCH_URL))]
            )
            ->set(
                Configuration::KEY_CONNECTED,
                [$languageId => (int)((bool)$this->getModule()->getClient($languageId)->getSessionId())]
            );

        return true;
    }
}
