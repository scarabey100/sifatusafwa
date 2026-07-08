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

class ProductPageSettingsForm extends AbstractForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_product_page_settings';
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
        $result = [Configuration::KEY_PRODUCT_PAGE_CONTENT => []];

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            $result[Configuration::KEY_PRODUCT_PAGE_CONTENT][$languageId] = Tools::getValue(
                Configuration::KEY_PRODUCT_PAGE_CONTENT . '_' . $languageId,
                $this->getModule()->getConfiguration()->get(Configuration::KEY_PRODUCT_PAGE_CONTENT, $languageId)
            );
        }

        // Add categories field (not multilingual)
        $result[Configuration::KEY_PRODUCT_PAGE_CATEGORIES] = Tools::getValue(
            Configuration::KEY_PRODUCT_PAGE_CATEGORIES,
            $this->getModule()->getConfiguration()->get(Configuration::KEY_PRODUCT_PAGE_CATEGORIES)
        );

        return $result;
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return [
            'legend' => [
                'title' => $this->module->l('Product page settings', 'ProductPageSettingsForm'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'textarea',
                    'label' => $this->module->l('Content', 'ProductPageSettingsForm'),
                    'name' => Configuration::KEY_PRODUCT_PAGE_CONTENT,
                    'lang' => true,
                    'autoload_rte' => true
                ],
                [
                    'type' => 'categories',
                    'label' => $this->module->l('Categories to exlude', 'ProductPageSettingsForm'),
                    'name' => Configuration::KEY_PRODUCT_PAGE_CATEGORIES,
                    'tree' => [
                        'id' => Configuration::KEY_PRODUCT_PAGE_CATEGORIES . '_tree',
                        'selected_categories' => explode(',', $this->getFieldsValue()[Configuration::KEY_PRODUCT_PAGE_CATEGORIES]),
                        'use_search' => true,
                        'use_checkbox' => true,
                        'disabled_categories' => [],
                        'root_category' => 2,
                    ],
                    'desc' => $this->module->l('Select one or more categories for the product page.', 'ProductPageSettingsForm'),
                ]
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'ProductPageSettingsForm')
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
        // Save content (multilang)
        foreach (Language::getLanguages(false, false, true) as $languageId) {
            $this->getModule()->getConfiguration()->set(
                Configuration::KEY_PRODUCT_PAGE_CONTENT,
                [$languageId => trim((string)Tools::getValue(Configuration::KEY_PRODUCT_PAGE_CONTENT .
                    '_' . $languageId))],
                true
            );
        }

        // Save categories (comma separated)
        $categories = Tools::getValue(Configuration::KEY_PRODUCT_PAGE_CATEGORIES, []);
        if (is_array($categories)) {
            $categories = implode(',', array_filter($categories));
        }
        $this->getModule()->getConfiguration()->set(
            Configuration::KEY_PRODUCT_PAGE_CATEGORIES,
            $categories
        );

        return true;
    }
}
