<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Form;

use MncKlevu;
use MncKlevu\PrestaShop\Adapter\Configuration;
use Tools;
use Validate;

class SynchronizationSettingsForm extends AbstractForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_synchronization_settings';
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
        return [
            Configuration::KEY_PRODUCT_COUNT_PER_REQUEST => Tools::getValue(
                Configuration::KEY_PRODUCT_COUNT_PER_REQUEST,
                $this->getModule()->getConfiguration()->get(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST)
            ),
            Configuration::KEY_USE_ITEM_GROUP_ID => Tools::getValue(
                Configuration::KEY_USE_ITEM_GROUP_ID,
                $this->getModule()->getConfiguration()->get(Configuration::KEY_USE_ITEM_GROUP_ID)
            ),
        ];
    }

    protected function initializeConfiguration()
    {
        $configuration = $this->getModule()->getConfiguration();
        $result = false;

        if ($configuration->get(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST) === false) {
            $configuration->set(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST, 100);
            $result = true;
        }

        if ($configuration->get(Configuration::KEY_USE_ITEM_GROUP_ID) === false) {
            $configuration->set(Configuration::KEY_USE_ITEM_GROUP_ID, 1);
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
                'title' => $this->module->l('Synchronization settings', 'SynchronizationSettingsForm'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Product count per request', 'SynchronizationSettingsForm'),
                    'name' => Configuration::KEY_PRODUCT_COUNT_PER_REQUEST,
                    'required' => true,
                    'class' => 'fixed-width-xs',
                ],
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Use item group ID', 'SynchronizationSettingsForm'),
                    'name' => Configuration::KEY_USE_ITEM_GROUP_ID,
                    'is_bool' => true,
                    'values' => [
                        [
                            'value' => 1
                        ],
                        [
                            'value' => 0
                        ],
                    ],
                    'desc' => $this->module->l('If disabled, product variants (combinations) will be presented as separated products.', 'SynchronizationSettingsForm'),
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'SynchronizationSettingsForm'),
            ],
        ];
    }

    protected function validateProductCountPerRequest()
    {
        $value = trim((string)Tools::getValue(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST));
        if (!$value || !Validate::isUnsignedInt($value)) {
            $this->errors[] = $this->module->l('Invalid product count per request.', 'StoreSettingsForm');
        }
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        $this->validateProductCountPerRequest();

        return !$this->hasErrors();
    }

    /**
     * @return bool
     */
    protected function saveFormData()
    {
        $this->getModule()->getConfiguration()
            ->set(
                Configuration::KEY_PRODUCT_COUNT_PER_REQUEST,
                trim((string)Tools::getValue(Configuration::KEY_PRODUCT_COUNT_PER_REQUEST))
            )
            ->set(
                Configuration::KEY_USE_ITEM_GROUP_ID,
                (int)((bool)Tools::getValue(Configuration::KEY_USE_ITEM_GROUP_ID))
            );

        return true;
    }
}
