<?php
/**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HiPrestaFaqModule
{
    public $module;
    public $context;

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = Context::getContext();
    }

    public function searchMetaByPage($page, $id_lang)
    {
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
        SELECT *
        FROM ' . _DB_PREFIX_ . 'meta m
        LEFT JOIN ' . _DB_PREFIX_ . 'meta_lang ml ON m.id_meta = ml.id_meta
        WHERE  m.page LIKE \'' . pSQL($page) . '%\'
        AND ml.id_lang = ' . (int) $id_lang . '
        ' . Shop::addSqlRestrictionOnLang('ml'));
    }

    /**
     * isSelectedShopGroup "Check selected ShopGroup or no"
     *
     * @return bool
     */
    public function isSelectedShopGroup()
    {
        if (Shop::getContext() == Shop::CONTEXT_GROUP || Shop::getContext() == Shop::CONTEXT_ALL) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * createTabs "Create module admin tabe"
     *
     * @param array $sql_select (db select)
     * @param int $page (page number)
     * @param int $pagination (pagination number)
     *
     * @return array
     */
    public function pagination($sql_select, $page = 1, $pagination = 50)
    {
        if (count($sql_select) > $pagination) {
            $sql_select = array_slice($sql_select, $pagination * ($page - 1), $pagination);
        }

        return $sql_select;
    }

    /**
     * createTabs "Create module admin tabe"
     *
     * @param int $active (active or inactive tabe)
     * @param string $class_name (Admin class name)
     * @param string $class_title (Admin class title)
     * @param string $configuration (Configuration name)
     *
     * @return bool
     */
    public function createTabs($class_name, $class_title, $configuration, $active = 0)
    {
        $langs = Language::getLanguages();
        $tab = new Tab();
        $tab->active = $active;
        $tab->class_name = $class_name;
        $tab->id_parent = 0;
        foreach ($langs as $lang) {
            $tab->name[$lang['id_lang']] = $class_title;
        }
        $tab->id_parent = 0;
        $tab->module = $this->module->name;
        $tab->add();
        Configuration::updateValue($configuration, $tab->id);

        return true;
    }

    /**
     * deleteTabs "Delete module admin tabes"
     *
     * @param string $configuration (Configuration name)
     *
     * @return bool
     */
    public function deleteTabs($configuration)
    {
        $tab = new Tab(Configuration::get($configuration));
        $tab->delete();

        return true;
    }

    /**
     * getModuleUrl get module url
     *
     * @param string $prefix (adding in url parameter name)
     *
     * @return string url
     */
    public function getModuleUrl($prefix = '')
    {
        $module_token = $this->context->link->getAdminLink(
            'AdminModules',
            true
        ) . '&configure=' . $this->module->name . '&tab_module=' . $this->module->tab . '&module_name=' . $this->module->name . $prefix;
        if ($this->module->psv >= 1.7) {
            return $module_token;
        }
        if ($this->module->psv == 1.6) {
            $host_webpath = Tools::getAdminUrl() . $this->context->controller->admin_webpath;
        } else {
            $host_webpath = Tools::getHttpHost(true) . __PS_BASE_URI__
            . Tools::substr(str_ireplace(_PS_ROOT_DIR_, '', getcwd()), 1);
        }
        $module_url = $host_webpath . '/' . $module_token;

        return $module_url;
    }

    /**
     * createEmailLangFiles "Automatically create language files for emails"
     *
     * @return bool
     */
    public function createEmailLangFiles()
    {
        $languages = Language::getLanguages(false);
        $path = _PS_MODULE_DIR_ . $this->module->name . '/mails/en';
        foreach ($languages as $lang) {
            if (is_dir($path)) {
                $dir = opendir($path);
                $new_path = _PS_MODULE_DIR_ . $this->module->name . '/mails/' . $lang['iso_code'];
                if (!file_exists($new_path)) {
                    mkdir($new_path);
                    while (($file = readdir($dir)) !== false) {
                        if (($file != '.') && ($file != '..')) {
                            copy($path . '/' . $file, $new_path . '/' . $file);
                        }
                    }
                }
                closedir($dir);
            }
        }

        return true;
    }

    /**
     * autoRegisterHook register or un registr hooks
     *
     * @param int $module_id (Module id)
     * @param array() $custom_hooks (if you want add others hooks)
     * @param string $module_select_hook can be array (In module selected hook name)
     *
     * @return bool true or false
     */
    public function autoRegisterHook($module_id, $module_select_hook, $custom_hooks = [])
    {
        $hooks = $this->module->module_hooks;
        if (empty($hooks)) {
            return false;
        }
        if ($custom_hooks != null && is_array($custom_hooks)) {
            foreach ($custom_hooks as $c_hook) {
                array_push($hooks, $c_hook);
            }
        }
        if (is_array($module_select_hook)) {
            $selected_hooks = $module_select_hook;
        } else {
            $selected_hooks = [$module_select_hook];
        }
        foreach ($hooks as $hook) {
            $module_hook = Hook::getModulesFromHook(Hook::getIdByName($hook), $module_id);
            if (in_array($hook, $selected_hooks)) {
                if (empty($module_hook)) {
                    $this->module->registerHook($hook);
                }
            } else {
                if (!empty($module_hook)) {
                    $this->module->unregisterHook($hook);
                }
            }
        }

        return true;
    }

    /**
     * removeDirAndFile remove folder and file
     *
     * @param url $path (delete folder path)
     *
     * @return bool
     */
    public function removeDirAndFile($path)
    {
        if (is_dir($path) && file_exists($path)) {
            $directorys = array_diff(scandir($path), ['.', '..']);
            foreach ($directorys as $directory) {
                if (is_dir($path . '/' . $directory)) {
                    $this->module->removeDirAndFile($path . '/' . $directory);
                } else {
                    if (file_exists($path . '/' . $directory) && is_file($path . '/' . $directory)) {
                        unlink($path . '/' . $directory);
                    }
                }
            }
            rmdir($path);
        } else {
            if (file_exists($path) && is_file($path)) {
                unlink($path);
            }
        }

        return true;
    }

    public function addTableColumn($table, $column, $type, $after = null, $default_value = null)
    {
        $result = Db::getInstance()->executeS('DESCRIBE `' . _DB_PREFIX_ . bqSQL($table) . '`');
        $isset = false;
        foreach ($result as $col) {
            if ($col['Field'] == $column) {
                $isset = true;
            }
        }
        if (!$isset) {
            return Db::getInstance()->execute('
                ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '`
                ADD `' . bqSQL($column) . '` ' . bqSQL($type) . '
                NOT NULL
                ' . ($default_value ? ' DEFAULT `' . bqSQL($default_value) . '`' : '') . '
                ' . ($after ? 'AFTER `' . bqSQL($after) . '`' : ''));
        }

        return true;
    }

    /**
     * deleteTableColumn remove column from tabel
     *
     * @param string $tabel (table name)
     * @param string $columt (table  column name)
     *
     * @return bool
     */
    public function deleteTableColumn($table, $column)
    {
        $result = Db::getInstance()->executeS('DESCRIBE `' . _DB_PREFIX_ . bqSQL($table) . '`');
        $isset = false;
        foreach ($result as $col) {
            if ($col['Field'] == $column) {
                $isset = true;
            }
        }
        if ($isset) {
            return Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '` DROP `' . bqSQL($column) . '`');
        }

        return true;
    }

    /**
     * renameTableColumn rename column from tabel
     *
     * @param string $tabel (table name)
     * @param string $column_old_name (table  column name)
     * @param string $column_new_name (table  column new name)
     * @param string $type (table  column type)
     *
     * @return bool
     */
    public function renameTableColumn($table, $column_old_name, $column_new_name, $type)
    {
        $result = Db::getInstance()->executeS('DESCRIBE `' . _DB_PREFIX_ . bqSQL($table) . '`');
        $isset = false;
        foreach ($result as $col) {
            if ($col['Field'] == $column_old_name) {
                $isset = true;
            }
        }
        if ($isset) {
            return Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '` CHANGE  `' . bqSQL($column_old_name) . '` `' . bqSQL($column_new_name) . '` ' . bqSQL($type) . ' NOT NULL');
        }

        return true;
    }

    /**
     * getImageType get image type
     *
     * @param string $type (type)
     *
     * @return string
     */
    public function getImageType($type)
    {
        if ($this->module->psv >= 1.7) {
            $image_type = ImageType::getFormattedName($type);
        } else {
            $image_type = ImageType::getFormatedName($type);
        }

        return $image_type;
    }

    /**
     * changeTableColumnType chaneg table colum type
     *
     * @param string $tabel (table name)
     * @param string $column (table  column name)
     * @param string $type (table  column type)
     *
     * @return bool
     */
    public function changeTableColumnType($table, $column, $type)
    {
        $result = Db::getInstance()->executeS('DESCRIBE `' . _DB_PREFIX_ . bqSQL($table) . '`');
        $isset = false;
        foreach ($result as $col) {
            if ($col['Field'] == $column) {
                $isset = true;
            }
        }
        if ($isset) {
            return Db::getInstance()->execute('ALTER TABLE `' . _DB_PREFIX_ . bqSQL($table) . '` MODIFY COLUMN `' . bqSQL($column) . '` ' . bqSQL($type) . ' NOT NULL');
        }

        return true;
    }
}
