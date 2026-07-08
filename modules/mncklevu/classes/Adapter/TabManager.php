<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

namespace MncKlevu\PrestaShop\Adapter;

use Language;
use Module;
use Tab;

class TabManager
{
    /**
     * @var string
     */
    const ADMIN_CONTROLLER_CLASS_NAME = 'AdminMncKlevu';

    /**
     * @var Module
     */
    protected $module;

    /**
     * @param Module $module
     */
    public function __construct(Module $module)
    {
        $this->module = $module;
    }

    /**
     * @return bool
     */
    public function addTab()
    {
        $tab = new Tab();
        $tab->module = $this->module->name;
        $tab->name = [];
        $tab->class_name = self::ADMIN_CONTROLLER_CLASS_NAME;
        $tab->id_parent = -1;
        $tab->active = 1;

        foreach (Language::getLanguages(true) as $language) {
            $tab->name[$language['id_lang']] = $tab->class_name;
        }

        return (bool)$tab->add();
    }

    /**
     * @return bool
     */
    public function deleteTab()
    {
        $tabId = (int)Tab::getIdFromClassName(self::ADMIN_CONTROLLER_CLASS_NAME);
        if ($tabId) {
            return (new Tab($tabId))->delete();
        }

        return true;
    }
}
