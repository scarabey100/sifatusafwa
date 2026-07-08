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

class SearchSettingsForm extends AbstractForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return 'submit_search_settings';
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
        $configuration = $this->getModule()->getConfiguration();

        $result = [
            Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT => Tools::getValue(
                Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT,
                $configuration->get(Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT)
            ),
            Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL => [],
        ];

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            $result[Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL][$languageId] = Tools::getValue(
                Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL . '_' . $languageId,
                $configuration->get(Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL, $languageId)
            );
        }

        return $result;
    }

    protected function initializeConfiguration()
    {
        $configuration = $this->getModule()->getConfiguration();
        $result = false;

        if ($configuration->get(Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT) === false) {
            $configuration->set(Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT, 3);
            $result = true;
        }

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            if ($configuration->get(Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL, $languageId) === false) {
                $configuration->set(
                    Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL,
                    [$languageId => 'search-results']
                );

                $result = true;
            }
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
                'title' => $this->module->l('Search settings', 'SearchSettingsForm'),
                'icon' => 'icon-cogs'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => $this->module->l('Search box minimal character count', 'SearchSettingsForm'),
                    'name' => Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT,
                    'required' => true,
                    'class' => 'fixed-width-xs',
                ],
                [
                    'type' => 'text',
                    'label' => $this->module->l('Search results page friendly URL', 'SearchSettingsForm'),
                    'name' => Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL,
                    'required' => true,
                    'lang' => true,
                ],
            ],
            'submit' => [
                'title' => $this->module->l('Save', 'SearchSettingsForm'),
            ],
        ];
    }

    protected function validateSearchBoxMinimalCharacterCount()
    {
        $value = trim((string)Tools::getValue(Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT));
        if (!Validate::isUnsignedInt($value)) {
            $this->errors[] = $this->module->l('Invalid search box minimal character count.', 'SearchSettingsForm');
        }
    }

    protected function validateSearchResultsPageFriendlyUrl()
    {
        foreach (Language::getLanguages(false) as $language) {
            $languageId = (int)$language['id_lang'];

            $value = trim((string)Tools::getValue(
                Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL . '_' . $languageId
            ));

            if (!$value || !Validate::isLinkRewrite($value)) {
                $this->errors[] = str_replace(
                    '%language%',
                    $language['name'],
                    $this->module->l('Invalid search results page friendly URL (language: %language%).', 'SearchSettingsForm')
                );
            }
        }
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        $this->validateSearchBoxMinimalCharacterCount();
        $this->validateSearchResultsPageFriendlyUrl();

        return !$this->hasErrors();
    }

    /**
     * @return bool
     */
    protected function saveFormData()
    {
        $configuration = $this->getModule()->getConfiguration()->set(
            Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT,
            trim((string)Tools::getValue(Configuration::KEY_SEARCH_BOX_MINIMAL_CHARACTER_COUNT))
        );

        foreach (Language::getLanguages(false, false, true) as $languageId) {
            $configuration->set(
                Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL,
                [$languageId => trim((string)Tools::getValue(Configuration::KEY_SEARCH_RESULTS_PAGE_FRIENDLY_URL .
                    '_' . $languageId))]
            );
        }

        return true;
    }
}
