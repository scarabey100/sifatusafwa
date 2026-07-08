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

class GeneralSettingsForm extends AbstractForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_general_settings';
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
            Configuration::KEY_ALLOWED_IN_FRONTEND => Tools::getValue(
                Configuration::KEY_ALLOWED_IN_FRONTEND,
                $this->getModule()->getConfiguration()->get(Configuration::KEY_ALLOWED_IN_FRONTEND)
            ),
        ];
    }

    protected function initializeConfiguration()
    {
        $configuration = $this->getModule()->getConfiguration();

        if ($configuration->get(Configuration::KEY_ALLOWED_IN_FRONTEND) === false) {
            $configuration->set(Configuration::KEY_ALLOWED_IN_FRONTEND, 0);

            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return [
            'legend' => [
                'title' => $this->module->l('General Settings', 'GeneralSettingsForm'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'switch',
                    'label' => $this->module->l('Allowed in the frontend', 'GeneralSettingsForm'),
                    'name' => Configuration::KEY_ALLOWED_IN_FRONTEND,
                    'is_bool' => true,
                    'values' => [
                        [
                            'value' => 1
                        ],
                        [
                            'value' => 0
                        ],
                    ],
                    'desc' => $this->module->l('If disabled, the module will not be displayed in the frontend.', 'GeneralSettingsForm'),
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'GeneralSettingsForm'),
            ],
        ];
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        return !$this->hasErrors();
    }

    /**
     * @return bool
     */
    protected function saveFormData()
    {
        $this->getModule()->getConfiguration()->set(
            Configuration::KEY_ALLOWED_IN_FRONTEND,
            (int)((bool)Tools::getValue(Configuration::KEY_ALLOWED_IN_FRONTEND))
        );

        return true;
    }
}
