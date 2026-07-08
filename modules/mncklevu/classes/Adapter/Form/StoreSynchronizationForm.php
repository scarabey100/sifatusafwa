<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Form;

use Language;
use MncKlevu\PrestaShop\Adapter\TabManager;

class StoreSynchronizationForm extends AbstractStoreForm
{
    /**
     * @return string
     */
    public function getSubmitAction()
    {
        return '';
    }

    /**
     * @return array
     */
    protected function getFieldsValue()
    {
        return [
            'ajax_url' => $this->context->link->getAdminLink(TabManager::ADMIN_CONTROLLER_CLASS_NAME, true, [],
                ['id_lang' => $this->getGridItemId()]),
            'return_url' => $this->getCurrentIndex([
                'conf' => 4,
                'token' => $this->getToken()
            ]),
        ];
    }

    /**
     * @return string
     */
    protected function getStatusHtmlContent()
    {
        return $this->context->smarty
            ->createTemplate(
                'module:' . $this->module->name . '/views/templates/admin/form/form_html_content_status.tpl'
            )
            ->fetch();
    }

    /**
     * @return array
     */
    protected function getSettings()
    {
        return [
            'id_form' => 'store_synchronization_form',
            'legend' => [
                'title' => str_replace(
                    '%iso%',
                    Language::getIsoById($this->getGridItemId()),
                    $this->module->l('Store synchronization (%iso%)', 'StoreSynchronizationForm')
                ),
                'icon' => 'icon-refresh'
            ],
            'input' => [
                [
                    'type' => 'hidden',
                    'name' => 'ajax_url',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'return_url',
                ],
                [
                    'type' => 'html',
                    'label' => $this->module->l('Status:', 'StoreSynchronizationForm'),
                    'name' => 'status',
                    'html_content' => $this->getStatusHtmlContent(),
                ],
            ],
            'buttons' => [
                [
                    'href' => $this->getCurrentIndex(['token' => $this->getToken()]),
                    'icon' => 'process-icon-cancel',
                    'title' => $this->module->l('Cancel', 'StoreSynchronizationForm'),
                ]
            ],
        ];
    }

    /**
     * @return bool
     */
    protected function validate()
    {
        return false;
    }

    /**
     * @return bool
     */
    protected function saveFormData()
    {
        return false;
    }
}
