<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter\Grid;

use Language;
use MncKlevu;

class StoreGrid extends AbstractGrid
{
    /**
     * @return string
     */
    protected function getTitle()
    {
        return $this->module->l('Stores', 'StoreGrid');
    }

    /**
     * @return string
     */
    public function getTable()
    {
        return 'store';
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return 'id_lang';
    }

    /**
     * @return array
     */
    protected function getActions()
    {
        return ['edit', 'synchronize'];
    }

    /**
     * @return bool
     */
    protected function getSimpleHeader()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getSynchronizeAction()
    {
        return 'synchronize' . $this->getTable();
    }

    /**
     * @return MncKlevu
     */
    protected function getModule()
    {
        return $this->module;
    }

    /**
     * @return false|string
     */
    public function displaySynchronizeLink($token, $id, $name = null)
    {
        ($template = $this->context->smarty->createTemplate(
            'module:' . $this->module->name . '/views/templates/admin/grid/grid_action_synchronize.tpl'
        ))->assign([
            'href' => $this->generateIndex([
                $this->getIdentifier() => $id,
                $this->getSynchronizeAction() => 1,
                'token' => $token ? $token : $this->getToken(),
            ]),
            'connection_status' => (int)$this->getModule()->getConnectionStatus($id),
            'action' => $this->module->l('Synchronize', 'StoreGrid'),
        ]);

        return $template->fetch();
    }

    /**
     * @return array
     */
    protected function getData()
    {
        return array_map(
            function($language) {
                $id = (int)$language['id_lang'];

                return [
                    'id_lang' => $id,
                    'language' => $language['name'],
                    'connected' => $this->getModule()->getConnectionStatus($id),
                    'synchronized' => $this->getModule()->getSynchronizationStatus($id),
                ];
            },
            Language::getLanguages(false)
        );
    }

    /**
     * @return string
     */
    public function displayStateStatus($value, $row)
    {
        ($template = $this->context->smarty->createTemplate(
            'module:' . $this->module->name . '/views/templates/admin/grid/grid_state_status.tpl'
        ))->assign('status', $value);

        return $template->fetch();
    }

    /**
     * @return array
     */
    protected function getFields()
    {
        return [
            'language' => [
                'title' => $this->module->l('Language', 'StoreGrid'),
                'orderby' => false,
                'search' => false,
            ],
            'connected' => [
                'title' => $this->module->l('Connected', 'StoreGrid'),
                'orderby' => false,
                'search' => false,
                'callback' => 'displayStateStatus',
                'callback_object' => $this,
                'align' => 'center',
            ],
            // 'synchronized' => [
            //     'title' => $this->module->l('Synchronized', 'StoreGrid'),
            //     'orderby' => false,
            //     'search' => false,
            //     'callback' => 'displayStateStatus',
            //     'callback_object' => $this,
            //     'align' => 'center',
            // ],
        ];
    }
}
