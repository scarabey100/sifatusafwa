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

class HomepageSettingsForm extends AbstractForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_homepage_settings';
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
        $result = [Configuration::KEY_HOMEPAGE_CONTENT => []];

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            $result[Configuration::KEY_HOMEPAGE_CONTENT][$languageId] = Tools::getValue(
                Configuration::KEY_HOMEPAGE_CONTENT . '_' . $languageId,
                $this->getModule()->getConfiguration()->get(Configuration::KEY_HOMEPAGE_CONTENT, $languageId)
            );
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
                'title' => $this->module->l('Homepage settings', 'HomepageSettingsForm'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'textarea',
                    'label' => $this->module->l('Content', 'HomepageSettingsForm'),
                    'name' => Configuration::KEY_HOMEPAGE_CONTENT,
                    'lang' => true,
                    'autoload_rte' => true
                ]
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'HomepageSettingsForm')
            ]
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
        foreach (Language::getLanguages(false, false, true) as $languageId) {
            $this->getModule()->getConfiguration()->set(
                Configuration::KEY_HOMEPAGE_CONTENT,
                [$languageId => trim((string)Tools::getValue(Configuration::KEY_HOMEPAGE_CONTENT .
                    '_' . $languageId))],
                true
            );
        }

        return true;
    }
}
