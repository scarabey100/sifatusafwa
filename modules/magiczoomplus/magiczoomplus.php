<?php
/**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

if (!isset($GLOBALS['magictoolbox'])) {
    $GLOBALS['magictoolbox'] = array();
    $GLOBALS['magictoolbox']['filters'] = array();
    $GLOBALS['magictoolbox']['isProductScriptIncluded'] = false;
    $GLOBALS['magictoolbox']['standardTool'] = '';
    $GLOBALS['magictoolbox']['selectorImageType'] = '';
    $GLOBALS['magictoolbox']['isProductBlockProcessed'] = false;
}

if (!isset($GLOBALS['magictoolbox']['magiczoomplus'])) {
    $GLOBALS['magictoolbox']['magiczoomplus'] = array();
    $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = false;
    $GLOBALS['magictoolbox']['magiczoomplus']['scripts'] = '';
}

class MagicZoomPlus extends Module
{

    /* PrestaShop v1.5 or above */
    public $isPrestaShop15x = false;

    /* PrestaShop v1.5.5.0 or above */
    public $isPrestaShop155x = false;

    /* PrestaShop v1.6 or above */
    public $isPrestaShop16x = false;

    /* PrestaShop v1.7 or above */
    public $isPrestaShop17x = false;

    /* Smarty v3 template engine */
    public $isSmarty3 = false;

    /* Smarty 'getTemplateVars' function name */
    public $getTemplateVars = 'getTemplateVars';

    /* Suffix was added to default images types since version 1.5.1.0 */
    public $imageTypeSuffix = '';

    /* To display 'product.js' file inline */
    public $displayInlineProductJs = false;

    /* Ajax request flag */
    public $isAjaxRequest = false;

    /* Featured Products module name */
    public $featuredProductsModule = 'homefeatured';

    /* Top-sellers block module name */
    public $topSellersModule = 'blockbestsellers';

    /* New Products module name */
    public $newProductsModule = 'blocknewproducts';

    /* Specials Products module name */
    public $specialsProductsModule = 'blockspecials';

    /* NOTE: identifying PrestaShop version class */
    public $psVersionClass = 'mt-ps-old';

    public function __construct()
    {
        $this->name = 'magiczoomplus';
        $this->tab = 'Tools';
        $this->version = '5.10.3';
        $this->author = 'Magic Toolbox';

        $this->module_key = '23501dac81e578fbb7212ba65e8b6950';


        //NOTE: to link bootstrap css for settings page in v1.6
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'Magic Zoom Plus';
        $this->description = "Beautiful zoom and enlarge effect for your product images.";

        $this->confirmUninstall = 'All magiczoomplus settings would be deleted. Do you really want to uninstall this module ?';

        $this->isPrestaShop15x = version_compare(_PS_VERSION_, '1.5', '>=');
        $this->isPrestaShop155x = version_compare(_PS_VERSION_, '1.5.5', '>=');
        $this->isPrestaShop16x = version_compare(_PS_VERSION_, '1.6', '>=');
        $this->isPrestaShop17x = version_compare(_PS_VERSION_, '1.7', '>=');

        $this->displayInlineProductJs = version_compare(_PS_VERSION_, '1.6.0.3', '>=') && version_compare(_PS_VERSION_, '1.6.0.7', '<');

        if ($this->isPrestaShop16x) {
            $this->tab = 'others';
        }

        $this->isSmarty3 = $this->isPrestaShop15x || Configuration::get('PS_FORCE_SMARTY_2') === '0';
        if ($this->isSmarty3) {
            //Smarty v3 template engine
            $this->getTemplateVars = 'getTemplateVars';
        } else {
            //Smarty v2 template engine
            $this->getTemplateVars = 'get_template_vars';
        }

        $this->imageTypeSuffix = version_compare(_PS_VERSION_, '1.5.1.0', '>=') ? '_default' : '';

        $this->isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');

        if ($this->isPrestaShop17x) {
            $this->featuredProductsModule = 'ps_featuredproducts';
            $this->topSellersModule = 'ps_bestsellers';
            $this->newProductsModule = 'ps_newproducts';
            $this->specialsProductsModule = 'ps_specials';
        }

        if (version_compare(_PS_VERSION_, '1.4.5.1', '>=')) {
            $this->psVersionClass = 'mt-ps-1451x';
            if ($this->isPrestaShop15x) {
                $this->psVersionClass = 'mt-ps-15x';
                if ($this->isPrestaShop16x) {
                    $this->psVersionClass = 'mt-ps-16x';
                    if ($this->isPrestaShop17x) {
                        $this->psVersionClass = 'mt-ps-17x';
                    }
                }
            }
        }
    }

    protected function _generateConfigXml($need_instance = 1)
    {
        //NOTE: this fix an issue with description in PrestaShop 1.4
        $description = htmlentities($this->description, ENT_COMPAT, 'UTF-8');
        $this->description = htmlentities($description);
        return parent::_generateConfigXml($need_instance);
    }

    public function install()
    {
        $headerHookID = $this->isPrestaShop15x ? Hook::getIdByName('displayHeader') : Hook::get('header');

        $updateHookName = false;
        $isPrestaShop1451 = version_compare(_PS_VERSION_, '1.4.5.1', '>=');
        if ($isPrestaShop1451) {
            $updateHookName = 'afterSaveProduct';
            if ($this->isPrestaShop15x) {
                $updateHookName = 'actionProductSave';
                if ($this->isPrestaShop16x) {
                    $updateHookName = 'actionProductUpdate';
                    if ($this->isPrestaShop17x) {
                        $updateHookName = 'actionProductSave';
                    }
                }
            }
        }

        if (!parent::install()
            || !$this->registerHook($this->isPrestaShop15x ? 'displayHeader' : 'header')
            || $this->isPrestaShop17x && !$this->registerHook('actionDispatcher')
            || !$this->registerHook($this->isPrestaShop15x ? 'displayFooterProduct' : 'productFooter')
            || !$this->registerHook($this->isPrestaShop15x ? 'displayFooter' : 'footer')
            || !$this->registerHook($this->isPrestaShop15x ? 'displayAdminProductsExtra' : 'backOfficeFooter')
            || !($updateHookName && $this->registerHook($updateHookName))
            || !$this->installDB()
            || !$this->fixCSS()
            //NOTICE: this function can return false if the module is the only one in this position
            || !($this->updatePosition($headerHookID, 0, 1) || true)
            /**/) {
            return false;
        }

        return true;
    }

    private function installDB()
    {
        if (!Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'magiczoomplus_settings` (
                                        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                                        `block` VARCHAR(32) NOT NULL,
                                        `name` VARCHAR(32) NOT NULL,
                                        `value` TEXT,
                                        `default_value` TEXT,
                                        `enabled` TINYINT(1) UNSIGNED NOT NULL,
                                        `default_enabled` TINYINT(1) UNSIGNED NOT NULL,
                                        PRIMARY KEY (`id`)
                                        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;')
            || !$this->fillDB()
            || !$this->fixDefaultValues()
            || !Db::getInstance()->Execute('CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'magictoolbox_video` (
                                            `id_product` INT(10) UNSIGNED NOT NULL,
                                            `data` MEDIUMTEXT,
                                            PRIMARY KEY (`id_product`)
                                            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;')
        /**/) {
            return false;
        }

        return true;
    }

    private function fixCSS()
    {
        //fix url's in css files
        $path = dirname(__FILE__);
        $list = glob($path.'/*');
        $files = array();
        if (is_array($list)) {
            $listLength = count($list);
            for ($i = 0; $i < $listLength; $i++) {
                if (is_dir($list[$i])) {
                    if (!in_array(basename($list[$i]), array('.svn', '.git'))) {
                        $add = glob($list[$i].'/*');
                        if (is_array($add)) {
                            $list = array_merge($list, $add);
                            $listLength += count($add);
                        }
                    }
                } elseif (preg_match('#\.css$#i', $list[$i])) {
                    $files[] = $list[$i];
                }
            }
        }
        foreach ($files as $file) {
            $cssPath = dirname($file);
            $cssRelPath = str_replace($path, '', $cssPath);
            $toolPath = _MODULE_DIR_.'magiczoomplus'.$cssRelPath;
            $pattern = '#url\(\s*(\'|")?(?!data:|mhtml:|http(?:s)?:|/)([^\)\s\'"]+?)(?(1)\1)\s*\)#is';
            $replace = 'url($1'.$toolPath.'/$2$1)';
            $fileContents = Tools::file_get_contents($file);
            $fixedFileContents = preg_replace($pattern, $replace, $fileContents);
            //preg_match_all($pattern, $fileContents, $matches, PREG_SET_ORDER);
            //debug_log($matches);
            if ($fixedFileContents != $fileContents) {
                $fp = fopen($file, 'w+');
                if ($fp) {
                    fwrite($fp, $fixedFileContents);
                    fclose($fp);
                }
            }
        }

        return true;
    }


    public function fixDefaultValues()
    {
        $result = true;
        if (version_compare(_PS_VERSION_, '1.5.1.0', '>=')) {
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `value`=CONCAT(value, \'_default\'), `default_value`=CONCAT(default_value, \'_default\') WHERE (`name`=\'thumb-image\' OR `name`=\'selector-image\' OR `name`=\'large-image\') AND `value`!=\'original\'';
            $result = Db::getInstance()->Execute($sql);
        }
        if ($this->isPrestaShop16x) {
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `value`=\'small_default\', `default_value`=\'small_default\', `enabled`=1 WHERE `name`=\'thumb-image\' AND (`block`=\'blocknewproducts\' OR `block`=\'blockbestsellers\' OR `block`=\'blockspecials\' OR `block`=\'blockviewed\')';
            $result = Db::getInstance()->Execute($sql);
        }
        if ($this->isPrestaShop17x) {
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `enabled`=1, `value`=\'large_default\', `default_value`=\'large_default\' WHERE `name`=\'large-image\'';
            $result = Db::getInstance()->Execute($sql);
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `enabled`=1, `value`=\'large_default\', `default_value`=\'large_default\' WHERE `name`=\'thumb-image\' AND `block`=\'product\'';
            $result = Db::getInstance()->Execute($sql);
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `enabled`=1, `value`=\'home_default\', `default_value`=\'home_default\' WHERE `name`=\'thumb-image\' AND (`block`=\'category\' OR `block`=\'manufacturer\' OR `block`=\'newproductpage\' OR `block`=\'bestsellerspage\' OR `block`=\'specialspage\')';
            $result = Db::getInstance()->Execute($sql);
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `enabled`=1, `value`=\'home_default\', `default_value`=\'home_default\' WHERE `name`=\'thumb-image\' AND (`block`=\'blocknewproducts_home\' OR `block`=\'blockbestsellers_home\' OR `block`=\'blockspecials_home\' OR `block`=\'homefeatured\')';
            $result = Db::getInstance()->Execute($sql);
            $sql = 'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `enabled`=1, `value`=\'small_default\', `default_value`=\'small_default\' WHERE `name`=\'thumb-image\' AND (`block`=\'blocknewproducts\' OR `block`=\'blockbestsellers\' OR `block`=\'blockspecials\' OR `block`=\'blockviewed\')';
            $result = Db::getInstance()->Execute($sql);
        }
        return $result;
    }

    public function uninstall()
    {
        if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            $this->_clearCache('*');
        }
        if (!parent::uninstall() || !$this->uninstallDB()) {
            return false;
        }
        return true;
    }

    private function uninstallDB()
    {
        return Db::getInstance()->Execute('DROP TABLE IF EXISTS `'._DB_PREFIX_.'magiczoomplus_settings`;');
    }

    public function disable($forceAll = false)
    {
        if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            $this->_clearCache('*');
        }
        return parent::disable($forceAll);
    }

    public function enable($forceAll = false)
    {
        if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
            $this->_clearCache('*');
        }
        return parent::enable($forceAll);
    }

    public function _clearCache($template, $cache_id = null, $compile_id = null)
    {
        if ($this->isPrestaShop17x) {
            $this->name = 'ps_featuredproducts';
            parent::_clearCache('ps_featuredproducts.tpl', 'ps_featuredproducts');
            parent::_clearCache('module:ps_featuredproducts/views/templates/hook/ps_featuredproducts.tpl');

            $this->name = 'ps_bestsellers';
            parent::_clearCache('module:ps_bestsellers/views/templates/hook/ps_bestsellers.tpl');

            $this->name = 'ps_newproducts';
            parent::_clearCache('module:ps_newproducts/views/templates/hook/ps_newproducts.tpl');

            $this->name = 'ps_specials';
            parent::_clearCache('module:ps_specials/views/templates/hook/ps_specials.tpl');

            $this->name = 'magiczoomplus';
            return;
        }

        $this->name = 'homefeatured';//NOTE: spike to clear cache for 'homefeatured.tpl'
        parent::_clearCache('homefeatured.tpl');
        parent::_clearCache('tab.tpl', 'homefeatured-tab');

        $this->name = 'blockbestsellers';
        parent::_clearCache('blockbestsellers.tpl');
        parent::_clearCache('blockbestsellers-home.tpl', 'blockbestsellers-home');
        parent::_clearCache('blockbestsellers.tpl', 'blockbestsellers_col');
        parent::_clearCache('tab.tpl', 'blockbestsellers-tab');

        $this->name = 'blocknewproducts';
        parent::_clearCache('blocknewproducts.tpl');
        parent::_clearCache('blocknewproducts_home.tpl', 'blocknewproducts-home');
        parent::_clearCache('tab.tpl', 'blocknewproducts-tab');

        $this->name = 'blockspecials';
        parent::_clearCache('blockspecials.tpl');
        parent::_clearCache('blockspecials-home.tpl', 'blockspecials-home');
        parent::_clearCache('tab.tpl', 'blockspecials-tab');

        $this->name = 'blockspecials';
        parent::_clearCache('blockspecials.tpl');

        $this->name = 'magiczoomplus';
    }

    public function getImagesTypes()
    {
        if (!isset($GLOBALS['magictoolbox']['imagesTypes'])) {
            $GLOBALS['magictoolbox']['imagesTypes'] = array('original');
            //NOTE: get image type values
            $sql = 'SELECT name FROM `'._DB_PREFIX_.'image_type` ORDER BY `id_image_type` ASC';
            $result = Db::getInstance()->ExecuteS($sql);
            foreach ($result as $row) {
                $GLOBALS['magictoolbox']['imagesTypes'][] = $row['name'];
            }
        }
        return $GLOBALS['magictoolbox']['imagesTypes'];
    }

    public function getContent()
    {
        if ($this->needUpdateDb()) {
            Db::getInstance()->Execute('DELETE FROM `'._DB_PREFIX_.'magiczoomplus_settings`');
            $this->fillDB();
            $this->fixDefaultValues();
        }

        $action = Tools::getValue('magiczoomplus-submit-action', false);
        $activeTab = Tools::getValue('magiczoomplus-active-tab', false);

        if ($action == 'reset' && $activeTab) {
            Db::getInstance()->Execute(
                'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `value`=`default_value`, `enabled`=`default_enabled` WHERE `block`=\''.pSQL($activeTab).'\''
            );
        }

        $tool = $this->loadTool();
        $paramsMap = $this->getParamsMap();

        $_imagesTypes = array(
            'selector',
            'large',
            'thumb'
        );

        foreach ($_imagesTypes as $name) {
            foreach ($this->getBlocks() as $blockId => $blockLabel) {
                if ($tool->params->paramExists($name.'-image', $blockId)) {
                    $tool->params->setValues($name.'-image', $this->getImagesTypes(), $blockId);
                }
            }
        }


        $paramData = $tool->params->getParam('magicscroll', 'product', false);
        $paramData['description'] = '<img id="magicscroll_icon" src="'._MODULE_DIR_.'magiczoomplus/views/img/magicscroll.png" />'.$paramData['description'];
        $tool->params->appendParams(array('magicscroll' => $paramData), 'product');

        //debug_log($_GET);
        //debug_log($_POST);

        $params = Tools::getValue('magiczoomplus', false);

        //NOTE: save settings
        if ($action == 'save' && $params) {
            foreach ($paramsMap as $blockId => $groups) {
                foreach ($groups as $group) {
                    foreach ($group as $param => $required) {
                        if (isset($params[$blockId][$param])) {
                            $valueToSave = $value = trim($params[$blockId][$param]);
                            switch ($tool->params->getType($param)) {
                                case 'num':
                                    $valueToSave = $value = (int)$value;
                                    break;
                                case 'array':
                                    if (!in_array($value, $tool->params->getValues($param))) {
                                        $valueToSave = $value = $tool->params->getDefaultValue($param);
                                    }
                                    $valueToSave = pSQL($valueToSave);
                                    break;
                                case 'text':
                                    $valueToSave = $value = str_replace('"', '&quot;', $value);//NOTE: fixed issue with "
                                    $valueToSave = pSQL($value);
                                    break;
                            }
                            Db::getInstance()->Execute(
                                'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `value`=\''.$valueToSave.'\', `enabled`=1 WHERE `block`=\''.pSQL($blockId).'\' AND `name`=\''.pSQL($param).'\''
                            );
                            $tool->params->setValue($param, $value, $blockId);
                        } else {
                            Db::getInstance()->Execute(
                                'UPDATE `'._DB_PREFIX_.'magiczoomplus_settings` SET `enabled`=0 WHERE `block`=\''.pSQL($blockId).'\' AND `name`=\''.pSQL($param).'\''
                            );
                            if ($tool->params->paramExists($param, $blockId)) {
                                $tool->params->removeParam($param, $blockId);
                            }
                        }
                    }
                }
            }
            if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
                $this->_clearCache('*');
            }
        }


        include(dirname(__FILE__).'/admin/magictoolbox.settings.editor.class.php');
        $settings = new MagictoolboxSettingsEditorClass(dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'js');
        $settings->paramsMap = $this->getParamsMap();
        $settings->core = $this->loadTool();
        $settings->profiles = $this->getBlocks();
        $settings->pathToJS = dirname(__FILE__).DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.'js';
        $settings->action = htmlentities($_SERVER['REQUEST_URI']);
        $settings->setResourcesURL(_MODULE_DIR_.'magiczoomplus/admin/resources/');
        $settings->setResourcesURL(_MODULE_DIR_.'magiczoomplus/views/js/', 'js');
        $settings->setResourcesURL(_MODULE_DIR_.'magiczoomplus/views/css/', 'css');
        $settings->namePrefix = 'magiczoomplus';

        $settings->pageTitle .= '&nbsp;<a target="_blank" title="Watch tutorial" href="http://www.youtube.com/watch?v=yAix6lXqyAw&t=0m54s" style="float: right;">Watch tutorial</a>';

        $settings->languagesData = Db::getInstance()->ExecuteS('SELECT id_lang as id, iso_code as code, active FROM `'._DB_PREFIX_.'lang` ORDER BY `id_lang` ASC');

        if ($activeTab) {
            $settings->activeTab = $activeTab;
        }


        $settings->addJSFile(_MODULE_DIR_.'magiczoomplus/views/js/options.js');


        $html = $settings->getHTML();
        $html .= '
<script type="text/javascript">
    //<![CDATA[
    initOptionsValidation(\''.$settings->getName('product', 'template').'\', \''.$settings->getName('product', 'magicscroll').'\');
    //]]>
</script>';
        return $html;
    }

    public function needUpdateDb()
    {
        //NOTE: check if all new params are present in DB
        $params = array();
        $sql = 'SELECT `name`, `value`, `block` FROM `'._DB_PREFIX_.'magiczoomplus_settings`';
        $result = Db::getInstance()->ExecuteS($sql);
        foreach ($result as $row) {
            if (!isset($params[$row['block']])) {
                $params[$row['block']] = array();
            }
            $params[$row['block']][$row['name']] = $row['value'];
        }

        $needUpdate = false;
        $paramsMap = $this->getParamsMap();
        foreach ($paramsMap as $blockId => $groups) {
            foreach ($groups as $group) {
                foreach ($group as $param => $required) {
                    if (!isset($params[$blockId][$param])) {
                        $needUpdate = true;
                        break 3;
                    }
                }
            }
        }

        return $needUpdate;
    }

    public function loadTool($profile = false, $force = false)
    {
        if (!isset($GLOBALS['magictoolbox']['magiczoomplus']['class']) || $force) {
            require_once(dirname(__FILE__).'/magiczoomplus.module.core.class.php');
            $GLOBALS['magictoolbox']['magiczoomplus']['class'] = new MagicZoomPlusModuleCoreClass();
            $tool = &$GLOBALS['magictoolbox']['magiczoomplus']['class'];
            // load current params
            $sql = 'SELECT `name`, `value`, `block` FROM `'._DB_PREFIX_.'magiczoomplus_settings` WHERE `enabled`=1';
            $result = Db::getInstance()->ExecuteS($sql);
            //NOTE: get data without cache
            //$result = Db::getInstance()->ExecuteS($sql, true, false);
            foreach ($result as $row) {
                $tool->params->setValue($row['name'], $row['value'], $row['block']);
            }

            // load translates
            $GLOBALS['magictoolbox']['magiczoomplus']['translates'] = $this->getMessages();
            $translates = & $GLOBALS['magictoolbox']['magiczoomplus']['translates'];
            foreach ($this->getBlocks() as $block => $label) {
                if ($translates[$block]['message']['title'] != $translates[$block]['message']['translate']) {
                    $tool->params->setValue('message', $translates[$block]['message']['translate'], $block);
                }
                if ($translates[$block]['textHoverZoomHint']['title'] != $translates[$block]['textHoverZoomHint']['translate']) {
                    $tool->params->setValue('textHoverZoomHint', $translates[$block]['textHoverZoomHint']['translate'], $block);
                }
                if ($translates[$block]['textClickZoomHint']['title'] != $translates[$block]['textClickZoomHint']['translate']) {
                    $tool->params->setValue('textClickZoomHint', $translates[$block]['textClickZoomHint']['translate'], $block);
                }
                if ($translates[$block]['textHoverZoomHintForMobile']['title'] != $translates[$block]['textHoverZoomHintForMobile']['translate']) {
                    $tool->params->setValue('textHoverZoomHintForMobile', $translates[$block]['textHoverZoomHintForMobile']['translate'], $block);
                }
                if ($translates[$block]['textClickZoomHintForMobile']['title'] != $translates[$block]['textClickZoomHintForMobile']['translate']) {
                    $tool->params->setValue('textClickZoomHintForMobile', $translates[$block]['textClickZoomHintForMobile']['translate'], $block);
                }
                if ($translates[$block]['textExpandHint']['title'] != $translates[$block]['textExpandHint']['translate']) {
                    $tool->params->setValue('textExpandHint', $translates[$block]['textExpandHint']['translate'], $block);
                }
                if ($translates[$block]['textBtnClose']['title'] != $translates[$block]['textBtnClose']['translate']) {
                    $tool->params->setValue('textBtnClose', $translates[$block]['textBtnClose']['translate'], $block);
                }
                if ($translates[$block]['textBtnNext']['title'] != $translates[$block]['textBtnNext']['translate']) {
                    $tool->params->setValue('textBtnNext', $translates[$block]['textBtnNext']['translate'], $block);
                }
                if ($translates[$block]['textBtnPrev']['title'] != $translates[$block]['textBtnPrev']['translate']) {
                    $tool->params->setValue('textBtnPrev', $translates[$block]['textBtnPrev']['translate'], $block);
                }
                if ($translates[$block]['textExpandHintForMobile']['title'] != $translates[$block]['textExpandHintForMobile']['translate']) {
                    $tool->params->setValue('textExpandHintForMobile', $translates[$block]['textExpandHintForMobile']['translate'], $block);
                }
                //NOTE: prepare image types
                foreach (array('large', 'selector', 'thumb') as $name) {
                    if ($tool->params->checkValue($name.'-image', 'original', $block)) {
                        $tool->params->setValue($name.'-image', false, $block);
                    }
                }
            }

            if ($tool->params->checkValue('magicscroll', 'Yes', 'product')) {
                require_once(dirname(__FILE__).'/magicscroll.module.core.class.php');
                $GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'] = new MagicScrollModuleCoreClass(false);
                $scroll = &$GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'];
                //NOTE: load params in a separate profile, in order not to overwrite the options of MagicScroll module
                $scroll->params->appendParams($tool->params->getParams('product'), 'product-magicscroll-options');
                $scroll->params->setValue('orientation', ($tool->params->checkValue('template', array('left', 'right'), 'product') ? 'vertical' : 'horizontal'), 'product-magicscroll-options');
                //NOTE: if Magic Scroll module installed we need to load settings before displaying custom options
                if (parent::isInstalled('magicscroll')) {
                    $magicscrollModule = parent::getInstanceByName('magicscroll');
                    if ($magicscrollModule->active) {
                        $magicscrollModule->loadTool();
                    }
                }
            }

        }

        $tool = &$GLOBALS['magictoolbox']['magiczoomplus']['class'];

        if ($profile) {
            $tool->params->setProfile($profile);
        }

        return $tool;

    }

    public function loadProductVideoData($id_product)
    {
        $data = Db::getInstance()->getValue('SELECT data FROM `'._DB_PREFIX_.'magictoolbox_video` WHERE id_product='.(int)$id_product);
        if (empty($data)) {
            return array();
        }
        return unserialize($data);
    }

    public function saveProductVideoData($id_product, $value)
    {
        $value = $this->prepareProductVideosDataForSave($value);
        $value = serialize($value);
        $data = Db::getInstance()->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'magictoolbox_video` WHERE id_product='.(int)$id_product);
        if (empty($data)) {
            $sql = 'INSERT INTO `'._DB_PREFIX_.'magictoolbox_video` (`id_product`, `data`) VALUES ('.(int)$id_product.', \''.pSQL($value).'\')';
        } else {
            $sql = 'UPDATE `'._DB_PREFIX_.'magictoolbox_video` SET `data`=\''.pSQL($value).'\' WHERE `id_product`='.(int)$id_product;
        }
        $result = Db::getInstance()->Execute($sql);
    }

    public function prepareProductVideosDataForSave($value)
    {

        $data = array();

        if (empty($value)) {
            return $data;
        }

        $urls = preg_split('#\n++|\s++#', $value, -1, PREG_SPLIT_NO_EMPTY);

        foreach ($urls as $key => $_url) {

            $url = parse_url($_url);
            if (!$url) {
                $data[$_url] = array();
                continue;
            }

            $isVimeo = false;
            $videoCode = null;
            if (preg_match('#youtube\.com|youtu\.be#', $url['host'])) {
                if (isset($url['query']) && preg_match('#\bv=([^&]+)(?:&|$)#', $url['query'], $matches)) {
                    $videoCode = $matches[1];
                } elseif (isset($url['path']) && preg_match('#^/(?:embed/|v/)?([^/\?]+)(?:/|\?|$)#', $url['path'], $matches)) {
                    $videoCode = $matches[1];
                }
            } elseif (preg_match('#(?:www\.|player\.)?vimeo\.com#', $url['host'])) {
                $isVimeo = true;
                if (isset($url['path']) && preg_match('#/(?:channels/[^/]+/|groups/[^/]+/videos/|album/[^/]+/video/|video/|)(\d+)(?:/|\?|$)#', $url['path'], $matches)) {
                    $videoCode = $matches[1];
                }
            }

            if (!$videoCode) {
                $data[$_url] = array();
                continue;
            }

            if ($isVimeo) {
                $hash = unserialize(Tools::file_get_contents('http://vimeo.com/api/v2/video/'.$videoCode.'.php'));
                $thumb = $hash[0]['thumbnail_small'];
            } else {
                $thumb = 'https://i1.ytimg.com/vi/'.$videoCode.'/1.jpg';
            }

            $data[$_url] = array(
                'code' => $videoCode,
                'thumb' => $thumb,
                'vimeo' => $isVimeo,
                'youtube' => !$isVimeo,
            );
        }

        return $data;
    }

    public function hookBackOfficeFooter($params)
    {
        $id_product = Tools::getValue('id_product');
        $smarty = &$GLOBALS['smarty'];
        $smarty->assign('legacy_template', true);
        $data = $this->loadProductVideoData($id_product);
        if (!empty($data)) {
            $urls = array_keys($data);
            $urls = implode("\n", $urls)."\n";
            $smarty->assign('magiczoomplus_textarea', $urls);
            $invalid_urls = array();
            foreach ($data as $url => $_data) {
                if (empty($_data)) {
                    $invalid_urls[] = $url;
                }
            }
            if (!empty($invalid_urls)) {
                $smarty->assign('magiczoomplus_invalid_urls', $invalid_urls);
            }
        }
        return $this->display(__FILE__, 'views/templates/admin/product_videos.tpl');
    }

    public function hookDisplayAdminProductsExtra($params)
    {
        if ($this->isPrestaShop17x) {
            $id_product = $params['id_product'];
        } else {
            $id_product = Tools::getValue('id_product');
        }
        $this->context->smarty->assign(array(
            'legacy_template' => false,
            'p16x_template' => $this->isPrestaShop16x,
        ));
        $data = $this->loadProductVideoData($id_product);
        if (!empty($data)) {
            $urls = array_keys($data);
            $urls = implode("\n", $urls)."\n";
            $invalid_urls = array();
            foreach ($data as $url => $_data) {
                if (empty($_data)) {
                    $invalid_urls[] = $url;
                }
            }
            $this->context->smarty->assign(array(
                'magiczoomplus_textarea' => $urls,
                'magiczoomplus_invalid_urls' => empty($invalid_urls) ? null : $invalid_urls
            ));
        }
        if ($this->isPrestaShop17x) {
            return $this->display(__FILE__, 'views/templates/admin/product_videos_ps17.tpl');
        }
        return $this->display(__FILE__, 'views/templates/admin/product_videos.tpl');
    }

    public function hookAfterSaveProduct($params)
    {
        $id_product = Tools::getValue('id_product');
        $productVideos = Tools::getValue('magiczoomplus_video');
        $this->saveProductVideoData($id_product, $productVideos);
    }

    public function hookActionProductUpdate($params)
    {
        $id_product = Tools::getValue('id_product');
        $productVideos = Tools::getValue('magiczoomplus_video');
        $this->saveProductVideoData($id_product, $productVideos);
    }

    public function hookActionProductSave($params)
    {
        $id_product = Tools::getValue('id_product');
        $productVideos = Tools::getValue('magiczoomplus_video');
        $this->saveProductVideoData($id_product, $productVideos);
    }
    public function hookHeader($params)
    {
        //global $smarty;
        $smarty = &$GLOBALS['smarty'];

        if (!$this->isPrestaShop15x) {
            ob_start();
        }

        $headers = '';
        $tool = $this->loadTool();
        $tool->params->resetProfile();

        if ($this->isPrestaShop17x) {
            $page = $smarty->{$this->getTemplateVars}('page');
            if (is_array($page) && isset($page['page_name'])) {
                $page = $page['page_name'];
            }
        } else {
            $page = $smarty->{$this->getTemplateVars}('page_name');
        }

        switch ($page) {
            case 'product':
            case 'index':
            case 'category':
            case 'manufacturer':
            case 'search':
                break;
            case 'best-sales':
                $page = 'bestsellerspage';
                break;
            case 'new-products':
                $page = 'newproductpage';
                break;
            case 'prices-drop':
                $page = 'specialspage';
                break;
            default:
                $page = '';
        }

        if ($tool->params->checkValue('include-headers-on-all-pages', 'Yes', 'default') && ($GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true)
            || $tool->params->profileExists($page) && !$tool->params->checkValue('enable-effect', 'No', $page)
            || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'homefeatured') && parent::isInstalled($this->featuredProductsModule) && parent::getInstanceByName($this->featuredProductsModule)->active
            || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blocknewproducts_home') && parent::isInstalled($this->newProductsModule) && parent::getInstanceByName($this->newProductsModule)->active
            || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blockbestsellers_home') && parent::isInstalled($this->topSellersModule) && parent::getInstanceByName($this->topSellersModule)->active
            || $page == 'index' && !$tool->params->checkValue('enable-effect', 'No', 'blockspecials_home') && parent::isInstalled($this->specialsProductsModule) && parent::getInstanceByName($this->specialsProductsModule)->active
            || !$tool->params->checkValue('enable-effect', 'No', 'blockviewed') && parent::isInstalled('blockviewed') && parent::getInstanceByName('blockviewed')->active
            || !$tool->params->checkValue('enable-effect', 'No', 'blockspecials') && parent::isInstalled($this->specialsProductsModule) && parent::getInstanceByName($this->specialsProductsModule)->active
            || !$tool->params->checkValue('enable-effect', 'No', 'blocknewproducts') && parent::isInstalled($this->newProductsModule) && parent::getInstanceByName($this->newProductsModule)->active
            || !$tool->params->checkValue('enable-effect', 'No', 'blockbestsellers') && parent::isInstalled($this->topSellersModule) && parent::getInstanceByName($this->topSellersModule)->active
        /**/) {
            // include headers
            $headers = $tool->getHeadersTemplate(_MODULE_DIR_.'magiczoomplus/views/js', _MODULE_DIR_.'magiczoomplus/views/css');

            if (!$this->isPrestaShop17x) {
                //NOTE: if we need this on product page!?
                $headers .= '<script type="text/javascript" src="'._MODULE_DIR_.'magiczoomplus/views/js/common.js"></script>';
            }

            if ($page == 'product' && !$tool->params->checkValue('enable-effect', 'No', 'product')) {
                $useScroll = $tool->params->checkValue('magicscroll', 'Yes', 'product');
                if (/*$page == 'product' && */$useScroll) {
                    $scroll = &$GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'];
                    $scroll->params->resetProfile();
                    $headers = $scroll->getHeadersTemplate(_MODULE_DIR_.'magiczoomplus/views/js', _MODULE_DIR_.'magiczoomplus/views/css', false).$headers;
                }
                $mouseEvent = $tool->params->getValue('selectorTrigger', 'product');
                if ($mouseEvent == 'hover') {
                    $mouseEvent = 'mouseover';
                }
                $items = $tool->params->getValue('items', 'product');//auto | fit | integer | array
                $items = is_numeric($items) ? (int)$items : 0;
                $headers .= '
<script type="text/javascript">
    var isPrestaShop15x = '.($this->isPrestaShop15x ? 'true' : 'false').';
    var isPrestaShop1541 = '.(version_compare(_PS_VERSION_, '1.5.4.1', '>=') ? 'true' : 'false').';
    var isPrestaShop156x = '.(version_compare(_PS_VERSION_, '1.5.6', '>=') ? 'true' : 'false').';
    var isPrestaShop16x = '.($this->isPrestaShop16x ? 'true' : 'false').';
    var isPrestaShop17x = '.($this->isPrestaShop17x ? 'true' : 'false').';
    var mEvent = \''.$mouseEvent.'\';
    var originalLayout = '.($tool->params->checkValue('template', 'original', 'product') ? 'true' : 'false').';
    var m360AsPrimaryImage = '.($tool->params->checkValue('360-as-primary-image', 'Yes', 'product') ? 'true' : 'false').' && (typeof(window[\'Magic360\']) != \'undefined\');
    var useMagicScroll = '.($useScroll ? 'true' : 'false').';
    var scrollItems = '.$items.';'.
                ($useScroll ? '
    var isProductMagicScrollStopped = true;
    var doWaitForMagicScrollToStart = false;
    MagicScrollOptions[\'onReady\'] = function(id) {
        //console.log(\'MagicScroll onReady: \', id);
        if (id == \'MagicToolboxSelectors'.(int)Tools::getValue('id_product').'\') {
            isProductMagicScrollStopped = false;
            doWaitForMagicScrollToStart = false;
        }
    }
    MagicScrollOptions[\'onStop\'] = function(id) {
        //console.log(\'MagicScroll onStop: \', id);
        if (id == \'MagicToolboxSelectors'.(int)Tools::getValue('id_product').'\') {
            isProductMagicScrollStopped = true;
        }
    }
'   :'').'
    var isProductMagicZoomReady = false;
    var allowHighlightActiveSelectorOnUpdate = true;
    mzOptions[\'onZoomReady\'] = function(id) {
        //console.log(\'MagicZoomPlus onZoomReady: \', id);
        if (id == \'MagicZoomPlusImageMainImage\') {
            isProductMagicZoomReady = true;
        }
    }
    mzOptions[\'onUpdate\'] = function(id, oldA, newA) {
        //console.log(\'MagicZoomPlus onUpdate: \', id);
        if (allowHighlightActiveSelectorOnUpdate) {
            mtHighlightActiveSelector(newA);
        }
        allowHighlightActiveSelectorOnUpdate = true;
    }


</script>';
                if ($this->isPrestaShop17x) {
                    $headers .= "\n".'<script type="text/javascript" src="'._MODULE_DIR_.'magiczoomplus/views/js/product17.js"></script>'."\n";
                }
                if (!$this->isPrestaShop17x && !$GLOBALS['magictoolbox']['isProductScriptIncluded']) {
                    if ($this->displayInlineProductJs || (bool)Configuration::get('PS_JS_DEFER')) {
                        //NOTE: include product.js as inline because it has to be called after previous inline scripts
                        $productJsCContents = Tools::file_get_contents(_PS_ROOT_DIR_.'/modules/magiczoomplus/views/js/product.js');
                        $headers .= "\n".'<script type="text/javascript">'.$productJsCContents.'</script>'."\n";
                    } else {
                        $headers .= "\n".'<script type="text/javascript" src="'._MODULE_DIR_.'magiczoomplus/views/js/product.js"></script>'."\n";
                    }

                    $GLOBALS['magictoolbox']['isProductScriptIncluded'] = true;
                }
                $headers .= "\n".'<script type="text/javascript" src="'._MODULE_DIR_.'magiczoomplus/views/js/switch.js"></script>'."\n";
            }

            $domNotAvailable = extension_loaded('dom') ? false : true;
            if ($this->displayInlineProductJs && $domNotAvailable) {
                $scriptsPattern = '#(?:\s*+<script\b[^>]*+>.*?<\s*+/script\b[^>]*+>)++#Uims';
                if (preg_match($scriptsPattern, $headers, $scripts)) {
                    $GLOBALS['magictoolbox']['magiczoomplus']['scripts'] =
                        '<!-- MAGICZOOMPLUS HEADERS START -->'.$scripts[0].'<!-- MAGICZOOMPLUS HEADERS END -->';
                    $headers = preg_replace($scriptsPattern, '', $headers);
                }
            }

            if ($this->isSmarty3) {
                //Smarty v3 template engine
                if (isset($GLOBALS['magictoolbox']['filters']['magic360'])) {
                    $smarty->unregisterFilter('output', array(Module::getInstanceByName('magic360'), 'parseTemplateCategory'));
                }

                $smarty->registerFilter('output', array(Module::getInstanceByName('magiczoomplus'), 'parseTemplateStandard'));

                if (isset($GLOBALS['magictoolbox']['filters']['magic360'])) {
                    $smarty->registerFilter('output', array(Module::getInstanceByName('magic360'), 'parseTemplateCategory'));
                }
            } else {
                //Smarty v2 template engine
                if (isset($GLOBALS['magictoolbox']['filters']['magic360'])) {
                    $smarty->unregister_outputfilter(array(Module::getInstanceByName('magic360'), 'parseTemplateCategory'));
                }
                $smarty->register_outputfilter(array(Module::getInstanceByName('magiczoomplus'), 'parseTemplateStandard'));
                if (isset($GLOBALS['magictoolbox']['filters']['magic360'])) {
                    $smarty->register_outputfilter(array(Module::getInstanceByName('magic360'), 'parseTemplateCategory'));
                }
            }
            $GLOBALS['magictoolbox']['filters']['magiczoomplus'] = 'parseTemplateStandard';

            // presta create new class every time when hook called
            // so we need save our data in the GLOBALS
            $GLOBALS['magictoolbox']['magiczoomplus']['cookie'] = $params['cookie'];
            $GLOBALS['magictoolbox']['magiczoomplus']['productsViewedIds'] = (isset($params['cookie']->viewed) && !empty($params['cookie']->viewed)) ? explode(',', $params['cookie']->viewed) : array();

            $headers = '<!-- MAGICZOOMPLUS HEADERS START -->'.$headers.'<!-- MAGICZOOMPLUS HEADERS END -->';

        }

        return $headers;

    }

    public function hookActionDispatcher($params)
    {
        //NOTE: registered for 1.7.x
        if (!$this->isAjaxRequest) {
            return;
        }

        switch ($params['controller_class']) {
            case 'CategoryController':
                $page = 'category';
                break;
            case 'SearchController':
                $page = 'search';
                break;
            default:
                return;
        }

        $smarty = &$GLOBALS['smarty'];
        $smarty->assign('page', array(
            'page_name' => $page
        ));

        $this->hookHeader($params);
    }

    public function hookProductFooter($params)
    {
        //NOTE: we need save this data in the GLOBALS for compatible with some Prestashop modules which reset the $product smarty variable
        if ($this->isPrestaShop17x && is_array($params['product'])) {
            $GLOBALS['magictoolbox']['magiczoomplus']['product'] = array(
                'id' => $params['product']['id'],
                'name' => $params['product']['name'],
                'link_rewrite' => $params['product']['link_rewrite']
            );
        } else {
            $GLOBALS['magictoolbox']['magiczoomplus']['product'] = array(
                'id' => $params['product']->id,
                'name' => $params['product']->name,
                'link_rewrite' => $params['product']->link_rewrite
            );
        }
        return '';
    }

    public function hookFooter($params)
    {
        if (!$this->isPrestaShop15x) {

            $contents = ob_get_contents();
            ob_end_clean();


            if ($GLOBALS['magictoolbox']['magiczoomplus']['headers'] == false) {
                $contents = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS START -->.*?<\!-- MAGICZOOMPLUS HEADERS END -->/is', '', $contents);
            } else {
                $contents = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS (START|END) -->/is', '', $contents);
                //NOTE: add class for identifying PrestaShop version
                if (preg_match('#(<body\b[^>]*?\sclass\s*+=\s*+"[^"]*+)("[^>]*+>)#is', $contents)) {
                    $contents = preg_replace('#(<body\b[^>]*?\sclass\s*+=\s*+"[^"]*+)("[^>]*+>)#is', '$1 '.$this->psVersionClass.'$2', $contents);
                } else {
                    $contents = preg_replace('#(<body\s[^>]*+)>#is', '$1 class="'.$this->psVersionClass.'">', $contents);
                }
            }

            echo $contents;

        }

        return '';

    }


    private static $outputMatches = array();

    public function prepareOutput($output, $index = 'DEFAULT')
    {
        if (!isset(self::$outputMatches[$index])) {
            $regExp = '<div\b[^>]*?\sclass\s*+=\s*+"[^"]*?(?<=\s|")MagicToolboxContainer(?=\s|")[^"]*+"[^>]*+>'.
                        '('.
                        '(?:'.
                            '[^<]++'.
                            '|'.
                            '<(?!/?div\b|!--)'.
                            '|'.
                            '<!--.*?-->'.
                            '|'.
                            '<div\b[^>]*+>'.
                                '(?1)'.
                            '</div\s*+>'.
                        ')*+'.
                        ')'.
                        '</div\s*+>';
            preg_match_all('#'.$regExp.'#is', $output, self::$outputMatches[$index]);
            foreach (self::$outputMatches[$index][0] as $key => $match) {
                $output = str_replace($match, 'MAGICZOOMPLUS_MATCH_'.$index.'_'.$key.'_', $output);
            }
        } else {
            foreach (self::$outputMatches[$index][0] as $key => $match) {
                $output = str_replace('MAGICZOOMPLUS_MATCH_'.$index.'_'.$key.'_', $match, $output);
            }
            unset(self::$outputMatches[$index]);
        }
        return $output;

    }

    public function parseTemplateStandard($output, $smarty)
    {
        if ($this->isSmarty3) {
            //Smarty v3 template engine
            $currentTemplate = Tools::substr(basename($smarty->template_resource), 0, -4);
            if ($currentTemplate == 'breadcrumb') {
                $currentTemplate = 'product';
            } elseif ($currentTemplate == 'pagination') {
                $currentTemplate = 'category';
            }
        } else {
            //Smarty v2 template engine
            $currentTemplate = $smarty->currentTemplate;
        }

        if ($this->isPrestaShop17x && ($currentTemplate == 'index' || $currentTemplate == 'page') ||
            $this->isPrestaShop15x && $currentTemplate == 'layout') {
            if (version_compare(_PS_VERSION_, '1.5.5.0', '>=')) {
                //NOTE: because we do not know whether the effect is applied to the blocks in the cache
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
            }

            //NOTE: full contents in prestashop 1.5.x
            if ($GLOBALS['magictoolbox']['magiczoomplus']['headers'] == false) {
                $output = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS START -->.*?<\!-- MAGICZOOMPLUS HEADERS END -->/is', '', $output);
            } else {
                $output = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS (START|END) -->/is', '', $output);
                //NOTE: add class for identifying PrestaShop version
                if (preg_match('#(<body\b[^>]*?\sclass\s*+=\s*+"[^"]*+)("[^>]*+>)#is', $output)) {
                    $output = preg_replace('#(<body\b[^>]*?\sclass\s*+=\s*+"[^"]*+)("[^>]*+>)#is', '$1 '.$this->psVersionClass.'$2', $output);
                } else {
                    $output = preg_replace('#(<body\s[^>]*+)>#is', '$1 class="'.$this->psVersionClass.'">', $output);
                }
            }

            return $output;
        }

        switch ($currentTemplate) {
            case 'search':
            case 'manufacturer':
                //$currentTemplate = 'manufacturer';
                break;
            case 'best-sales':
                $currentTemplate = 'bestsellerspage';
                break;
            case 'new-products':
                $currentTemplate = 'newproductpage';
                break;
            case 'prices-drop':
                $currentTemplate = 'specialspage';
                break;
            case 'blockbestsellers-home':
                $currentTemplate = 'blockbestsellers_home';
                break;
            case 'blockspecials-home':
                $currentTemplate = 'blockspecials_home';
                break;
            case 'product-list'://for 'Layered navigation block'
                if (strpos($_SERVER['REQUEST_URI'], 'blocklayered-ajax.php') !== false) {
                    $currentTemplate = 'category';
                }
                break;
            case 'javascript':
                if ($GLOBALS['magictoolbox']['magiczoomplus']['scripts']) {
                    $output .= $GLOBALS['magictoolbox']['magiczoomplus']['scripts'];
                }
                break;
            //NOTE: just in case (issue 88975)
            case 'ProductController':
                $currentTemplate = 'product';
                break;
            case 'products':
                if ($this->isPrestaShop17x && $this->isAjaxRequest) {
                    $page = $smarty->{$this->getTemplateVars}('page');
                    if (is_array($page) && isset($page['page_name'])) {
                        $currentTemplate = $page['page_name'];
                    }
                }
                break;
            case 'ps_featuredproducts':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'homefeatured';
                }
                break;
            case 'ps_bestsellers':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'blockbestsellers_home';
                }
                break;
            case 'ps_newproducts':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'blocknewproducts_home';
                }
                break;
            case 'ps_specials':
                if ($this->isPrestaShop17x) {
                    $currentTemplate = 'blockspecials_home';
                }
                break;
        }

        $tool = $this->loadTool();
        if (!$tool->params->profileExists($currentTemplate) || $tool->params->checkValue('enable-effect', 'No', $currentTemplate)) {
            return $output;
        }
        $tool->params->setProfile($currentTemplate);

        //global $link;
        $link = $this->context->link;
        $cookie = &$GLOBALS['magictoolbox']['magiczoomplus']['cookie'];
        if (method_exists($link, 'getImageLink')) {
            $_link = &$link;
        } else {
            /* for Prestashop ver 1.1 */
            $_link = &$this;
        }

        $output = self::prepareOutput($output);

        switch ($currentTemplate) {
            case 'homefeatured':
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;

                $categoryID = $this->isPrestaShop15x ? Context::getContext()->shop->getCategory() : 1;
                $category = new Category($categoryID);
                $nb = (int)Configuration::get('HOME_FEATURED_NBR');//Number of product displayed
                $products = $category->getProducts((int)$cookie->id_lang, 1, ($nb ? $nb : 10));

                if (!is_array($products)) {
                    break;
                }
                foreach ($products as $product) {
                    $lrw = $product['link_rewrite'];
                    if (!$tool->params->checkValue('link-to-product-page', 'No')) {
                        $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                    } else {
                        $lnk = false;
                    }
                    $thumb = $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image'));
                    $image = $tool->getMainTemplate(array(
                        'id' => 'homefeatured'.$product['id_image'],
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                        'thumb' => $thumb,
                        'title' => $product['name'],
                        'group' => 'homefeatured',
                    ));
                    if (!$this->isPrestaShop17x) {
                        //NOTE: need a.product_image > img for blockcart module
                        $image = '<div class="MagicToolboxContainer">'.
                                    '<div style="width:0px;height:1px;overflow:hidden;visibility:hidden;">'.
                                        '<a class="product_image" href="#">'.
                                            '<img src="'.$thumb.'" />'.
                                        '</a>'.
                                    '</div>'.
                                    $image.
                                '</div>';
                    }
                    //$image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                    $image_pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], 'home'.$this->imageTypeSuffix), '/');
                    $image_pattern = str_replace('\-home'.$this->imageTypeSuffix, '\-[^"]*?', $image_pattern);
                    $image_pattern = '<img\b[^>]*?\bsrc\s*=\s*"[^"]*?'.$image_pattern.'"[^>]*>';
                    $pattern = $image_pattern.'[^<]*(<span[^>]*?class="new"[^>]*>[^<]*<\/span>)?';
                    $pattern = '<a[^>]*?href="[^"]*?"[^>]*>[^<]*'.$pattern.'[^<]*<\/a>|'.$image_pattern;
                    $output = preg_replace('/'.$pattern.'/is', $image, $output);
                }
                break;
            case 'category':
            case 'manufacturer':
            case 'newproductpage':
            case 'bestsellerspage':
            case 'specialspage':
            case 'search':
                //global $p, $n, $orderBy, $orderWay;
                //$category = new Category((int)Tools::getValue('id_category'), (int)$cookie->id_lang);
                //$products = $category->getProducts((int)$cookie->id_lang, (int)$p, (int)$n, $orderBy, $orderWay);
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                $products = $smarty->{$this->getTemplateVars}('products');

                if (!$products && $this->isPrestaShop17x) {
                    $listing = $smarty->{$this->getTemplateVars}('listing');
                    $products = $listing['products'];
                }

                if (!is_array($products)) {
                    break;
                }
                if ($this->isPrestaShop17x) {
                    //NOTE: to prevent replacing sidebar contents
                    $splitter =	'(<section\b[^>]*?\bid\s*+=\s*+"products"[^>]*+>)'.
                                '('.
                                '(?:'.
                                    '[^<]++'.
                                    '|'.
                                    '<(?!/?section\b|!--)'.
                                    '|'.
                                    '<!--.*?-->'.
                                    '|'.
                                    '<section\b[^>]*+>'.
                                        '(?2)'.
                                    '</section\s*+>'.
                                ')*+'.
                                ')'.
                                '(</section\s*+>)';
                    $parts = preg_split('#'.$splitter.'#i', $output, -1, PREG_SPLIT_DELIM_CAPTURE);
                    if (isset($parts[2])) {
                        $output = $parts[2];
                    }
                }
                foreach ($products as $product) {
                    $lrw = $product['link_rewrite'];
                    if (!$tool->params->checkValue('link-to-product-page', 'No')) {
                        $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                    } else {
                        $lnk = false;
                    }
                    $thumb = $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image'));
                    $html = $tool->getMainTemplate(array(
                        'id' => 'category'.$product['id_image'],
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                        'thumb' => $thumb,
                        'title' => $product['name'],
                        'group' => 'category',
                    ));
                    if (!$this->isPrestaShop16x) {
                        $html = '<div class="MagicToolboxContainer">'.
                                //NOTE: need a.product_img_link > img for blockcart module
                                '<div style="width:0px;height:1px;overflow:hidden;visibility:hidden;"><a class="product_img_link" href="#"><img src="'.$thumb.'" /></a></div>'.
                                $html.
                                '</div>';
                    }

                    $image_pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], 'home'.$this->imageTypeSuffix), '/');
                    $image_pattern = str_replace('\-home'.$this->imageTypeSuffix, '\-[^"]*?', $image_pattern);
                    $image_pattern = '<img[^>]*?src\s*=\s*"[^"]*?'.$image_pattern.'"[^>]*>';
                    if ($this->isPrestaShop17x) {
                        $pattern = $image_pattern;
                    } else {
                        $pattern = $image_pattern.'[^<]*(<span[^>]*?class="new"[^>]*>[^<]*<\/span>)?';
                    }
                    $pattern = '<a[^>]*?href="[^"]*?"[^>]*>[^<]*'.$pattern.'[^<]*<\/a>|'.$image_pattern;
                    //$matches = array();
                    //preg_match_all('/'.$pattern.'/is', $output, $matches, PREG_SET_ORDER);

                    if (!$this->isPrestaShop16x) {
                        //NOTE: for span.new banners
                        if (preg_match('/'.$pattern.'/is', $output, $matches)) {
                            if (isset($matches[1])) {
                                $html = preg_replace('/<\/div>$/is', $matches[1].'</div>', $html);
                            }
                        }
                    }
                    $output = preg_replace('/'.$pattern.'/is', $html, $output);
                }

                if ($this->isAjaxRequest) {
                    $output .= '
                        <script type="text/javascript">
                            //<![CDATA[
                            $(\'#products .MagicZoom\').each(function(i, el) {
                                MagicZoom.refresh(el);
                            });
                            //]]>
                        </script>';
                }

                if ($this->isPrestaShop17x && isset($parts[2])) {
                    $parts[2] = $output;
                    $output = implode('', $parts);
                }

                break;
            case 'product':
                //debug_log('MagicZoomPlus parseTemplateStandard product');

                $product_id = isset($GLOBALS['magictoolbox']['magicthumb']['product']['id'])?$GLOBALS['magictoolbox']['magicthumb']['product']['id']:(int)Tools::getValue('id_product');

                //$product = new Product((int)$smarty->$tpl_vars['product']->id, true, (int)$cookie->id_lang);
                //get some data from $GLOBALS for compatible with Prestashop modules which reset the $product smarty variable

                //$product = new Product((int)$smarty->$tpl_vars['product']->id, true, (int)$cookie->id_lang);
                //get some data from $GLOBALS for compatible with Prestashop modules which reset the $product smarty variable
                $product = new Product((int)$product_id, true, (int)$cookie->id_lang);

                $lrw = $product->link_rewrite;
                $pid = (int)$product->id;

                $productImages = $product->getImages((int)$cookie->id_lang);
                //NOTE: not all product images
                //$productImages = $smarty->{$this->getTemplateVars}('product')['images'];
                if (!is_array($productImages)) {
                    $productImages = array();
                }

                $productVideos = $this->loadProductVideoData($pid);

                if (empty($productImages) && empty($productVideos)) {
                    break;
                }

                $sProductData = $smarty->{$this->getTemplateVars}('product');
                if ($this->isPrestaShop17x) {
                    //NOTE: $cover variable contains the data of the current cover image
                    //      which depends on the selected combination
                    //      $cover['cover'] flag indicates that this is the product cover image
                    $cover = isset($sProductData['cover']) ? $sProductData['cover'] : array();
                } else {
                    $cover = $smarty->{$this->getTemplateVars}('cover');
                }

                if (!isset($cover['id_image'])) {
                    break;
                }


                $coverImageIds = is_numeric($cover['id_image']) ? $pid.'-'.$cover['id_image'] : $cover['id_image']; // Product::getCover($product_id)['id_image'];

                //NOTE: to use magic360 module with magiczoomplus
                $used360 = false;
                if (isset($GLOBALS['magictoolbox']['magic360'])) {
                    $images = Db::getInstance()->ExecuteS('SELECT id_image FROM `'._DB_PREFIX_.'magic360_images` WHERE id_product='.$pid.' LIMIT 1');
                    if (count($images) && !$GLOBALS['magictoolbox']['magic360']['class']->params->checkValue('enable-effect', 'No', 'product')) {
                        $used360 = true;
                        $GLOBALS['magictoolbox']['standardTool'] = 'magiczoomplus';
                        $GLOBALS['magictoolbox']['selectorImageType'] = $tool->params->getValue('selector-image');
                    }
                }

                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;

                $defaultContainerId = 'zoom';
                $containersData = array(
                    'zoom' => '',
                    '360' => '',
                    //'video' => '',
                );
                $html = '';
                $m360AsPrimaryImage = $tool->params->checkValue('360-as-primary-image', 'Yes');

                $containersData['zoom'] = $tool->getMainTemplate(array(
                    'id' => 'MainImage',
                    'img' => $_link->getImageLink($lrw, $coverImageIds, $tool->params->getValue('large-image')),
                    'thumb' => $_link->getImageLink($lrw, $coverImageIds, $tool->params->getValue('thumb-image')),
                    'title' => $product->name,
                    'alt' => $cover['legend'],
                ));

                $selectors = array();
                $selectorIDs = array();
                $originalLayout = $tool->params->checkValue('template', 'original');
                $coverId = '';
                foreach ($productImages as $i) {

                    //NOTE: to prevent dublicates
                    if (isset($selectorIDs[$i['id_image']])) {
                        continue;
                    }

                    $aHtml = $tool->getSelectorTemplate(array(
                        'id' => 'MainImage',
                        'img' => $_link->getImageLink($lrw, $pid.'-'.$i['id_image'], $tool->params->getValue('large-image')),
                        'medium' => $_link->getImageLink($lrw, $pid.'-'.$i['id_image'], $tool->params->getValue('thumb-image')),
                        'thumb' => $_link->getImageLink($lrw, $pid.'-'.$i['id_image'], $tool->params->getValue('selector-image')),
                        'title' => $i['legend'],
                        'alt' => $i['legend']
                    ));

                    $selectorIDs[$i['id_image']] = $i['id_image'];

                    $aHtml = str_replace('<a ', '<a data-magic-slide-id="zoom" ', $aHtml);
                    $selectorClass = 'magictoolbox-selector';

                    if ($this->isPrestaShop17x) {
                        if ($originalLayout) {
                            $selectorClass .= ' thumb';
                        }
                    }

                    if (!$m360AsPrimaryImage) {
                        if ($i['id_image'] == $cover['id_image']) {
                            $selectorClass .= ' active-selector';
                            if ($originalLayout) {
                                if ($this->isPrestaShop17x) {
                                    $selectorClass .= ' selected';
                                } else {
                                    $selectorClass .= ' shown';
                                }
                            }
                        }
                    }

                    if ($used360) {
                        $selectorClass .= ' zoom-with-360';
                    }
                    //NOTE: onclick for prevent click on selector before it is initialized
                    $aHtml = str_replace('<a ', '<a class="'.$selectorClass.'" data-mt-selector-id="'.$i['id_image'].'" onclick="return false;" ', $aHtml);

                    if ($originalLayout && !$this->isPrestaShop17x) {
                        $aHtml = str_replace('<img ', '<img id="thumb_'.$i['id_image'].'" ', $aHtml);
                        $pattern = preg_quote($_link->getImageLink($lrw, $pid.'-'.$i['id_image'], 'medium'.$this->imageTypeSuffix), '#');
                        $pattern = '<img\b[^>]*?\bsrc="[^"]*?'.$pattern.'"[^>]*+>';
                        $pattern = '(?:<img\b[^>]*?\bid="thumb_'.$i['id_image'].'"[^>]*+>|'.$pattern.')';
                        $pattern = '<a\b[^>]*+>[^<]*+'.$pattern.'[^<]*+</a>|'.$pattern;
                        //NOTE: append selector in their preserved place
                        $output = preg_replace('#'.$pattern.'#is', $aHtml, $output, 1);
                    } else {
                        $selectors[$i['id_image']] = $aHtml;
                    }

                    if ($i['cover']) {
                        $coverId = $i['id_image'];
                    }
                }

                if ($this->isPrestaShop17x) {
                    $attributeId = $smarty->{$this->getTemplateVars}('product');
                    $attributeId = isset($attributeId['id_product_attribute']) ? $attributeId['id_product_attribute'] : null;
                    //$combinations = $smarty->{$this->getTemplateVars}('combinations');
                    $combinationImages = $smarty->{$this->getTemplateVars}('combinationImages');
                    $combinationData = array(
                        'selectors' => $selectors,
                        'attributes' => array(),
                        'toolId' => 'MagicZoomPlus',
                        'toolClass' => 'MagicZoom',
                        'm360Selector' => '',
                        'videoSelectors' => array(),
                        'coverId' => $coverId,
                    );
                    if (!empty($combinationImages)) {
                        $selectors = array();
                        if (is_array($combinationImages)) {
                            foreach ($combinationImages as $attrId => $combImages) {
                                $combinationData['attributes'][$attrId] = array();
                                foreach ($combImages as $combImage) {
                                    $combinationData['attributes'][$attrId][] = $combImage['id_image'];
                                    if ($attributeId == $attrId) {
                                        if (empty($mainImageOveridden)) {
                                            $containersData['zoom'] = $tool->getMainTemplate(array(
                                                'id' => 'MainImage',
                                                'img' => $_link->getImageLink($lrw, $combImage['id_image'], $tool->params->getValue('large-image')),
                                                'thumb' => $_link->getImageLink($lrw, $combImage['id_image'], $tool->params->getValue('thumb-image')),
                                                'title' => $product->name,
                                                'alt' => $cover['legend'],
                                            ));
                                            $mainImageOveridden = true;
                                            $selectors = array();
                                        }
                                        $selectors[$combImage['id_image']] = $combinationData['selectors'][$combImage['id_image']];
                                    }
                                }
                            }
                        }
                    }
                }

                //NOTE: product videos
                $videoSelectors = array();
                $combinationScript = '';
                $videoIndex = 1;
                //NOTE: need this sizes for video selectors
                $this->setImageSizes();
                $sMaxHeight = $tool->params->getValue('selector-max-height', 'product');
                $sMaxHeight = is_numeric($sMaxHeight) ? $sMaxHeight.'px' : 'auto';
                $sMaxWidth = $tool->params->getValue('selector-max-width', 'product');
                $sMaxWidth = is_numeric($sMaxWidth) ? $sMaxWidth.'px' : 'auto';
                //NOTE: style for video thumbnails
                //      in order to display them with the same size as the product thumbnails
                //NOTICE: cannot be used with the original template because the picture size may become larger than the size of the <li>
                $html .= '<style>
div.MagicToolboxSelectorsContainer .selector-max-height {
    max-height: '.$sMaxHeight.' !important;
    max-width: '.$sMaxWidth.' !important;
}
</style>';
                $videoSelectorClass = 'video-selector magictoolbox-selector';
                if ($this->isPrestaShop17x && $originalLayout) {
                    $videoSelectorClass .= ' thumb';
                }

                foreach ($productVideos as $videoUrl => $videoData) {
                    if (empty($videoData)) {
                        continue;
                    }
                    if ($videoData['youtube']) {
                        $dataVideoType = 'youtube';
                        $containersData['video-'.$videoIndex] = '<iframe src="https://www.youtube.com/embed/'.$videoData['code'].'?enablejsapi=1"';
                    } else {
                        $dataVideoType = 'vimeo';
                        $containersData['video-'.$videoIndex] = '<iframe src="https://player.vimeo.com/video/'.$videoData['code'].'?byline=0&portrait=0"';
                    }
                    $containersData['video-'.$videoIndex] .=
                        ' frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen data-video-type="'.$dataVideoType.'"></iframe>';

                    $vsId = 9999999000+$videoIndex;

                    $videoSelector =
                        '<a data-mt-selector-id="'.$vsId.'" data-magic-slide-id="video-'.$videoIndex.'" data-video-type="'.$dataVideoType.'" class="'.$videoSelectorClass.'" href="#" onclick="return false">'.
                        '<span><b></b></span>'.
                        '<img class="selector-max-height" src="'.$videoData['thumb'].'" alt="video"/>'.
                        '</a>';

                    if (!$originalLayout || $this->isPrestaShop17x) {
                        $selectors[] = $videoSelector;
                    }

                    if (!$this->isPrestaShop17x) {
                        $videoSelectors[$vsId] = '<li id="thumbnail_'.$vsId.'">'.
                            str_replace('<img ', '<img id="thumb_'.$vsId.'" ', $videoSelector).
                            '</li>';
                        $combinationScript .= 'combinationImages[combId][combinationImages[combId].length] = '.$vsId.';';
                    }

                    if ($this->isPrestaShop17x) {
                        $combinationData['videoSelectors'][] = $videoSelector;
                    }

                    $videoIndex++;
                }

                if (!$this->isPrestaShop17x) {
                    if (!empty($combinationScript)) {
                        $combinationScript = '
<script type="text/javascript">
    //NOTE: to display video thumbnails
    var videoThumbIDs = ['.implode(',', array_keys($videoSelectors)).'];
    if (typeof(combinationImages) != "undefined") {
        for (var combId in combinationImages) {
            '.$combinationScript.'
        }
    }
</script>';
                    }
                    if ($originalLayout) {
                        $thumbsListPattern =   '(<ul\b[^>]*?\bid\s*+=\s*+"thumbs_list_frame"[^>]*+>)'.
                                                '('.
                                                '(?:'.
                                                    '[^<]++'.
                                                    '|'.
                                                    '<(?!/?ul\b|!--)'.
                                                    '|'.
                                                    '<!--.*?-->'.
                                                    '|'.
                                                    '<ul\b[^>]*+>'.
                                                        '(?2)'.
                                                    '</ul\s*+>'.
                                                ')*+'.
                                                ')'.
                                                '</ul\s*+>';
                        // $matches = array();
                        // preg_match_all('#'.$thumbsListPattern.'#is', $output, $matches, PREG_SET_ORDER);
                        // debug_log($matches);
                        $output = preg_replace(
                            '#'.$thumbsListPattern.'#is',
                            '$1$2'.implode('', $videoSelectors).'</ul>'.$combinationScript,
                            $output
                        );
                    } else {
                        $html .= $combinationScript;
                    }
                }

                //NOTE: to use magic360 module with magiczoomplus
                if ($used360) {
                    $containersData['360'] = '<!-- MAGIC360 -->';
                    $defaultContainerId = $m360AsPrimaryImage ? '360' : 'zoom';
                    if ($originalLayout && !$this->isPrestaShop17x) {
                        $output = preg_replace(
                            '/(<ul\b[^>]*?id="thumbs_list_frame"[^>]*>)/is',
                            '$1<li id="thumbnail_9999999999"><!-- MAGIC360SELECTOR --></li>',
                            $output
                        );
                    } else {
                        array_unshift($selectors, '<!-- MAGIC360SELECTOR -->');
                        if ($this->isPrestaShop17x) {
                            $combinationData['m360Selector'] = '<!-- MAGIC360SELECTOR_ESCAPED -->';
                        }
                    }
                }

                $templateParamValue = '';
                if (!$this->isPrestaShop17x) {
                    if ($originalLayout) {
                        $templateParamValue = $tool->params->getValue('template');
                        $tool->params->setValue('template', 'bottom');
                        //NOTE: make views_block visible (it is hidden when product has only one image) when magic360 icon is added
                        if ($GLOBALS['magictoolbox']['standardTool'] && count($productImages) == 1) {
                            $output = preg_replace('/(<div\s[^>]*?id="views_block"[^>]*?class="[^"]*?)hidden([^"]*"[^>]*>)/is', '$1$2', $output);
                            //NOTE: pattern breaks down a bit without this p.clear
                            $output = preg_replace('/(<ul\b[^>]*?id="usefull_link_block"[^>]*>)/is', '<p class="clear"></p>$1', $output);
                        }
                    } else {
                        //NOTE: hide selectors from contents

                        //NOTE: 'image-additional' added to support custom theme #53897
                        //NOTE: div#views_block is parent for div#thumbs_list
                        $thumbsPattern =	'(<div\b[^>]*?(?:\bid\s*+=\s*+"(?:views_block|thumbs_list)"|\bclass\s*+=\s*+"[^"]*?\bimage-additional\b[^"]*+")[^>]*+>)'.
                                            '('.
                                            '(?:'.
                                                '[^<]++'.
                                                '|'.
                                                '<(?!/?div\b|!--)'.
                                                '|'.
                                                '<!--.*?-->'.
                                                '|'.
                                                '<div\b[^>]*+>'.
                                                    '(?2)'.
                                                '</div\s*+>'.
                                            ')*+'.
                                            ')'.
                                            '</div\s*+>';

                        $matches = array();
                        if (preg_match("#{$thumbsPattern}#is", $output, $matches)) {
                            if (strpos($matches[1], 'class')) {
                                $replace = preg_replace('#\bclass\s*+=\s*+"#i', '$0hidden-important ', $matches[1]);
                            } else {
                                $replace = preg_replace('#<div\b#i', '$0 class="hidden-important"', $matches[1]);
                            }
                            $output = str_replace($matches[1], $replace, $output);
                        }

                        //NOTE: remove "View full size" link in old PrestaShop
                        $output = preg_replace('/<li[^>]*+>[^<]*+<span[^>]*?id="view_full_size"[^>]*+>[^<]*<\/span>[^<]*+<\/li>/is', '', $output);

                        //NOTE: hide span#wrapResetImages
                        $matches = array();
                        if (preg_match('#(?:<span\b[^>]*?\bid\s*+=\s*+"wrapResetImages"[^>]*+>|<a\b[^>]*?\bid\s*+=\s*+"resetImages"[^>]*+>)#is', $output, $matches)) {
                            if (strpos($matches[0], 'class')) {
                                $replace = preg_replace('#\bclass\s*+=\s*+"#i', '$0hidden-important ', $matches[0]);
                            } else {
                                $replace = preg_replace('#<span\b#i', '$0 class="hidden-important"', $matches[0]);
                            }

                            $output = str_replace($matches[0], $replace, $output);
                        }

                    }
                }

                //NOTE: we need this sizes for template renderer
                $this->setImageSizes();

                foreach ($containersData as $containerId => $containerHTML) {
                    $activeClass = $defaultContainerId == $containerId ? ' mt-active' : '';
                    $html .= "<div class=\"magic-slide{$activeClass}\" data-magic-slide=\"{$containerId}\">{$containerHTML}</div>";
                }

                require_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'magictoolbox.templatehelper.class.php');
                MagicToolboxTemplateHelperClass::setPath(dirname(__FILE__).DIRECTORY_SEPARATOR.'templates');
                MagicToolboxTemplateHelperClass::setOptions($tool->params);
                $scrollTool = null;
                $scrollOptions = '';
                if (isset($GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'])) {
                    $scrollTool = &$GLOBALS['magictoolbox']['magiczoomplus']['magicscroll'];
                }
                $scrollOptions = $scrollTool ? $scrollTool->params->serialize(false, '', 'product-magicscroll-options') : '';
                $html = MagicToolboxTemplateHelperClass::render(array(
                    'main' => $html,
                    'thumbs' => $selectors,
                    'magicscrollOptions' => $scrollOptions,
                    'pid' => $pid,
                ));
                if ($templateParamValue) {
                    //NOTE: in some cases, the wrong template is processed first
                    //      so we need to restore the old option value for the next time
                    $tool->params->setValue('template', $templateParamValue);
                }

                if (!$this->isPrestaShop17x && !$originalLayout) {
                    //NOTE: disable MagicScroll on page load (to start manually)
                    if ($tool->params->checkValue('magicscroll', 'Yes')) {
                        $matches = array();
                        if (preg_match('#<div\b[^>]*?\bclass\s*+=\s*+"[^"]*?\bMagicScroll\b[^"]*+"[^>]*+>#is', $html, $matches)) {
                            $replace = preg_replace('#(class="[^"]*?\bMagicScroll\b)([^"]*+")#i', '$1 hidden-important$2', $matches[0]);
                            if (preg_match('#\sdata-options(\s|=)#is', $replace)) {
                                $replace = preg_replace('#(\sdata-options\s*+=\s*+"[^"]*+)"#is', '$1autostart:false;"', $replace);
                            } else {
                                $replace = preg_replace('#>$#', ' data-options="autostart:false;">', $replace);
                            }
                            $html = str_replace($matches[0], $replace, $html);
                        }
                    }

                    //NOTE: for combinations and magicscroll=yes
                    $html .= '
<div id="MagicToolboxHiddenSelectors" class="hidden-important"></div>
<script type="text/javascript">
    //<![CDATA[
    magictoolboxImagesOrder = ['.implode(',', $selectorIDs).'];
    mtProductCoverImageId = '.$coverId.';
    //]]>
</script>
';
                }

                //NOTE: append main container
                if ($this->isPrestaShop17x) {
                    $html .= '
<script type="text/javascript">
    //<![CDATA[
    var mtCombinationData = '.json_encode($combinationData).';
    var mtScrollEnabled = '.($tool->params->checkValue('magicscroll', 'Yes', 'product') ? 'true' : 'false').';
    var mtScrollOptions = \''.$scrollOptions.'\';
    var mtScrollItems = \''.$tool->params->getValue('items', 'product').'\';
    var mtLayout = \''.$tool->params->getValue('template', 'product').'\';
    //]]>
</script>
';
                    $mainImagePattern = '<div\b[^>]*?\bclass\s*+=\s*+"[^"]*?\bimages-container\b[^"]*+"[^>]*+>'.
                                        '('.
                                        '(?:'.
                                            '[^<]++'.
                                            '|'.
                                            '<(?!/?div\b|!--)'.
                                            '|'.
                                            '<!--.*?-->'.
                                            '|'.
                                            '<div\b[^>]*+>'.
                                                '(?1)'.
                                            '</div\s*+>'.
                                        ')*+'.
                                        ')'.
                                        '</div\s*+>';
                    $matches = array();
                    //preg_match_all('#'.$mainImagePattern.'#is', $output, $matches, PREG_SET_ORDER);
                    //debug_log($matches);

                    if (!preg_match('#'.$mainImagePattern.'#is', $output, $matches)) {
                        break;
                    }

                    //NOTE: for proper show/hide arrows in original template
                    $replace = str_replace('js-qv-product-images', 'js-qv-product-images-disabled', $matches[0]);

                    //NOTE: div.hidden-important can be replaced with ajax contents
                    $output = str_replace(
                        $matches[0],
                        '<div class="hidden-important">'.$replace.'</div>'.$html,
                        $output
                    );

                    if (!$originalLayout) {
                        //NOTE: cut arrows
                        $arrowsPattern = '<div\b[^>]*?\bclass\s*+=\s*+"[^"]*?\bscroll-box-arrows\b[^"]*+"[^>]*+>'.
                                            '('.
                                            '(?:'.
                                                '[^<]++'.
                                                '|'.
                                                '<(?!/?div\b|!--)'.
                                                '|'.
                                                '<!--.*?-->'.
                                                '|'.
                                                '<div\b[^>]*+>'.
                                                    '(?1)'.
                                                '</div\s*+>'.
                                            ')*+'.
                                            ')'.
                                            '</div\s*+>';
                        $output = preg_replace('#'.$arrowsPattern.'#', '', $output);
                    }

                    $output = preg_replace('/<\!-- MAGICZOOMPLUS HEADERS (START|END) -->/is', '', $output);
                } else {
                    //NOTE: 'image' class added to support custom theme #53897
                    $mainImagePattern = '(<div\b[^>]*?(?:\bid\s*+=\s*+"image-block"|\bclass\s*+=\s*+"[^"]*?\bimage\b[^"]*+")[^>]*+>)'.
                                        '('.
                                        '(?:'.
                                            '[^<]++'.
                                            '|'.
                                            '<(?!/?div\b|!--)'.
                                            '|'.
                                            '<!--.*?-->'.
                                            '|'.
                                            '<div\b[^>]*+>'.
                                                '(?2)'.
                                            '</div\s*+>'.
                                        ')*+'.
                                        ')'.
                                        '</div\s*+>';
                    $matches = array();
                    //preg_match_all('#'.$mainImagePattern.'#is', $output, $matches, PREG_SET_ORDER);

                    if (!preg_match('%'.$mainImagePattern.'%is', $output, $matches)) {
                        break;
                    }

                    $iconsPattern = '<span\b[^>]*?\bclass\s*+=\s*+"[^"]*?\b(?:new-box|sale-box|discount)\b[^"]*+"[^>]*+>'.
                                    '('.
                                    '(?:'.
                                        '[^<]++'.
                                        '|'.
                                        '<(?!/?span\b|!--)'.
                                        '|'.
                                        '<!--.*?-->'.
                                        '|'.
                                        '<span\b[^>]*+>'.
                                            '(?1)'.
                                        '</span\s*+>'.
                                    ')*+'.
                                    ')'.
                                    '</span\s*+>';
                    $iconMatches = array();
                    if (preg_match_all('%'.$iconsPattern.'%is', $matches[2], $iconMatches, PREG_SET_ORDER)) {
                        foreach ($iconMatches as $key => $iconMatch) {
                            $matches[2] = str_replace($iconMatch[0], '', $matches[2]);
                            $iconMatches[$key] = $iconMatch[0];
                        }
                    }
                    $icons = implode('', $iconMatches);

                    $output = str_replace($matches[0], "{$matches[1]}{$icons}<div class=\"hidden-important\">{$matches[2]}</div>{$html}</div>", $output);
                }

                $GLOBALS['magictoolbox']['isProductBlockProcessed'] = true;
                break;
            case 'blockspecials':
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                $product = $smarty->{$this->getTemplateVars}('special');
                if (!is_array($product)) {
                    break;
                }
                $lrw = $product['link_rewrite'];
                if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $product['id_product']))) {
                    $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                } else {
                    $lnk = false;
                }

                $image = $tool->getMainTemplate(array(
                    'id' => 'blockspecials'.$product['id_image'],
                    'link' => $lnk,
                    'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                    'thumb' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image')),
                    'title' => $product['name'],
                    'group' => 'blockspecials',
                ));
                $image = '<div class="MagicToolboxContainer">'.$image.'</div>';

                $type = ($this->isPrestaShop16x ? 'small': 'medium').$this->imageTypeSuffix;
                $pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], $type), '/');
                $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                $pattern = '<img[^>]*?src="[^"]*?'.$pattern.'"[^>]*>';
                $pattern = '(<a[^>]*?href="[^"]*?"[^>]*>[^<]*)?'.$pattern.'([^<]*<\/a>)?';
                $output = preg_replace('/'.$pattern.'/is', $image, $output);

                break;
            case 'blockspecials_home':
                if ($this->isPrestaShop17x) {
                    $products = $smarty->{$this->getTemplateVars}('products');
                } else {
                    $products = $smarty->{$this->getTemplateVars}('specials');
                }
                if (!is_array($products)) {
                    break;
                }
                $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                foreach ($products as $product) {
                    $lrw = $product['link_rewrite'];
                    if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $product['id_product']))) {
                        $lnk = $link->getProductLink($product['id_product'], $lrw, isset($product['category']) ? $product['category'] : null);
                    } else {
                        $lnk = false;
                    }

                    $image = $tool->getMainTemplate(array(
                        'id' => 'blockspecials'.$product['id_image'],
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('large-image')),
                        'thumb' => $_link->getImageLink($lrw, $product['id_image'], $tool->params->getValue('thumb-image')),
                        'title' => $product['name'],
                        'group' => 'blockspecials_home',
                    ));
                    if (!$this->isPrestaShop17x) {
                        $image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                    }

                    $type = 'home'.$this->imageTypeSuffix;
                    $pattern = preg_quote($_link->getImageLink($lrw, $product['id_image'], $type), '#');
                    $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                    $pattern = '<img[^>]*?src\s*+=\s*+"[^"]*?'.$pattern.'"[^>]*+>';
                    $pattern = '(<a\b[^>]*?href="[^"]*+"[^>]*+>[^<]*+)?'.$pattern.'([^<]*+</a>)?';
                    $output = preg_replace('#'.$pattern.'#is', $image, $output);
                }
                break;
            case 'blockviewed':
                $productIDs = $GLOBALS['magictoolbox']['magiczoomplus']['productsViewedIds'];
                if ($this->isPrestaShop155x) {
                    $productIDs = array_reverse($productIDs);
                }

                $productIDs = array_slice($productIDs, 0, Configuration::get('PRODUCTS_VIEWED_NBR'));
                foreach ($productIDs as $id_product) {
                    $productViewedObj = new Product((int)$id_product, false, (int)$cookie->id_lang);
                    if (!Validate::isLoadedObject($productViewedObj) || !$productViewedObj->active) {
                        continue;
                    }

                    $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                    $images = $productViewedObj->getImages((int)$cookie->id_lang);
                    foreach ($images as $image) {
                        if ($image['cover']) {
                            $productViewedObj->cover = $productViewedObj->id.'-'.$image['id_image'];
                            $productViewedObj->legend = $image['legend'];
                            break;
                        }
                    }
                    if (!isset($productViewedObj->cover)) {
                        $productViewedObj->cover = Language::getIsoById($cookie->id_lang).'-default';
                        $productViewedObj->legend = '';
                    }
                    $lrw = $productViewedObj->link_rewrite;
                    if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $id_product))) {
                        $lnk = $link->getProductLink($id_product, $lrw, $productViewedObj->category);
                    } else {
                        $lnk = false;
                    }

                    $image = $tool->getMainTemplate(array(
                        'id' => 'blockviewed'.$id_product,
                        'link' => $lnk,
                        'img' => $_link->getImageLink($lrw, $productViewedObj->cover, $tool->params->getValue('large-image')),
                        'thumb' => $_link->getImageLink($lrw, $productViewedObj->cover, $tool->params->getValue('thumb-image')),
                        'title' => $productViewedObj->name,
                        'group' => 'blockviewed',
                    ));
                    $image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                    $type = ($this->isPrestaShop16x ? 'small': 'medium').$this->imageTypeSuffix;
                    $pattern = preg_quote($_link->getImageLink($lrw, $productViewedObj->cover, $type), '/');
                    $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                    $pattern = '<img[^>]*?src="[^"]*?'.$pattern.'"[^>]*>';
                    $pattern = '(<a[^>]*?href="[^"]*?"[^>]*>[^<]*)?'.$pattern.'([^<]*<\/a>)?';
                    $output = preg_replace('/'.$pattern.'/is', $image, $output);
                }
                break;
            case 'blockbestsellers':
            case 'blockbestsellers_home':
            case 'blocknewproducts':
            case 'blocknewproducts_home':
                if (in_array($currentTemplate, array('blockbestsellers', 'blockbestsellers_home'))) {
                    //$products = $smarty->{$this->getTemplateVars}('best_sellers');
                    //to get with description etc.
                    //$products = ProductSale::getBestSales((int)$cookie->id_lang, 0, version_compare(_PS_VERSION_, '1.5.1.0', '>=') ? 5 : 4);
                    //NOTE: blockbestsellers module uses a 'getBestSalesLight' function (the result may be different from 'getBestSales')
                    //      description we get a little further (with 'getProductDescription' function)
                    $pCount = $this->isPrestaShop16x ? 8 : (version_compare(_PS_VERSION_, '1.5.1.0', '>=') ? 5 : 4);
                    $products = ProductSale::getBestSalesLight((int)$cookie->id_lang, 0, $pCount);
                } else {
                    if ($this->isPrestaShop17x) {
                        $products = $smarty->{$this->getTemplateVars}('products');
                    } else {
                        $products = $smarty->{$this->getTemplateVars}('new_products');
                    }
                }

                if (!is_array($products)) {
                    break;
                }
                $pCount = count($products);
                if ($pCount) {
                    $GLOBALS['magictoolbox']['magiczoomplus']['headers'] = true;
                    for ($i = 0; /*$i < 2 &&*/ $i < $pCount; $i++) {
                        $lrw = $products[$i]['link_rewrite'];
                        if (!$tool->params->checkValue('link-to-product-page', 'No') && (!Tools::getValue('id_product', false) || (Tools::getValue('id_product', false) != $products[$i]['id_product']))) {
                            $lnk = $link->getProductLink($products[$i]['id_product'], $lrw, isset($products[$i]['category']) ? $products[$i]['category'] : null);
                        } else {
                            $lnk = false;
                        }

                        $image = $tool->getMainTemplate(array(
                            'id' => $currentTemplate.$products[$i]['id_image'],
                            'link' => $lnk,
                            'img' => $_link->getImageLink($lrw, $products[$i]['id_image'], $tool->params->getValue('large-image')),
                            'thumb' => $_link->getImageLink($lrw, $products[$i]['id_image'], $tool->params->getValue('thumb-image')),
                            'title' => $products[$i]['name'],
                            'group' => $currentTemplate,
                        ));
                        if (!$this->isPrestaShop17x) {
                            $image = '<div class="MagicToolboxContainer">'.$image.'</div>';
                        }
                        if (in_array($currentTemplate, array('blockbestsellers_home', 'blocknewproducts_home'))) {
                            $type = 'home'.$this->imageTypeSuffix;
                        } elseif ($this->isPrestaShop15x && $currentTemplate == 'blockbestsellers' || $this->isPrestaShop16x) {
                            $type = 'small'.$this->imageTypeSuffix;
                        } else {
                            $type = 'medium'.$this->imageTypeSuffix;
                        }

                        $pattern = preg_quote($_link->getImageLink($lrw, $products[$i]['id_image'], $type), '#');
                        $pattern = str_replace('\-'.$type, '\-[^"]*?', $pattern);
                        $pattern = '<img\b[^>]*?src\s*+=\s*+"[^"]*?'.$pattern.'"[^>]*+>';
                        $pattern = '(?:<a\b[^>]*+>[^<]*+)?'.
                                        '(?:<span class="number">.*?</span>[^<]*+)?'.
                                        $pattern.
                                    '(?:[^<]*+</a>)?';
                        $output = preg_replace('#'.$pattern.'#is', $image, $output);
                    }
                }
                break;
        }

        return self::prepareOutput($output);

    }

    public function getAllSpecial($id_lang, $beginning = false, $ending = false)
    {
        $currentDate = date('Y-m-d');
        $result = Db::getInstance()->ExecuteS('
        SELECT p.*, pl.`description`, pl.`description_short`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, p.`ean13`,
            i.`id_image`, il.`legend`, t.`rate`
        FROM `'._DB_PREFIX_.'product` p
        LEFT JOIN `'._DB_PREFIX_.'product_lang` pl ON (p.`id_product` = pl.`id_product` AND pl.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'image` i ON (i.`id_product` = p.`id_product` AND i.`cover` = 1)
        LEFT JOIN `'._DB_PREFIX_.'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = '.(int)$id_lang.')
        LEFT JOIN `'._DB_PREFIX_.'tax` t ON t.`id_tax` = p.`id_tax`
        WHERE (`reduction_price` > 0 OR `reduction_percent` > 0)
        '.((!$beginning && !$ending) ?
            'AND (`reduction_from` = `reduction_to` OR (`reduction_from` <= \''.pSQL($currentDate).'\' AND `reduction_to` >= \''.pSQL($currentDate).'\'))'
        :
            ($beginning ? 'AND `reduction_from` <= \''.pSQL($beginning).'\'' : '').($ending ? 'AND `reduction_to` >= \''.pSQL($ending).'\'' : '')).'
        AND p.`active` = 1
        ORDER BY RAND()');

        if (!$result) {
            return false;
        }

        $rows = array();
        foreach ($result as $row) {
            $rows[] = Product::getProductProperties($id_lang, $row);
        }

        return $rows;
    }

    /* for Prestashop ver 1.1 */
    public function getImageLink($name, $ids, $type = null)
    {
        $imageURL = _PS_IMG_.'magic360/'.$ids.($type ? '-'.$type : '').'.jpg';
        $sirv = Module::getInstanceByName('sirv');
        $doSirv = !(defined('PS_ADMIN_DIR') ||
            !$sirv || !(Configuration::get('SIRV_ACTIVE')) ||
            !Module::isEnabled('sirv'));
        if ($doSirv && Configuration::get('SIRV_IMAGES_CMS')) {
            $imageURL_original = preg_replace('/^\//', '', _PS_IMG_.'magic360/'.$ids.'.jpg');
            $imageURL = $sirv->syncImage(false, preg_replace('/[0-9]{1,}\-([0-9]{1,})/', '$1', $ids), $type, $imageURL_original, $imageURL_original, 'MAGIC360', true);
            if (!empty($imageURL)) {
                return $imageURL .= $sirv->convertTypeToParams($type);
            }
        }
        return $imageURL;
    }


    public function getProductDescription($id_product, $id_lang)
    {
        $sql = 'SELECT `description` FROM `'._DB_PREFIX_.'product_lang` WHERE `id_product` = '.(int)($id_product).' AND `id_lang` = '.(int)($id_lang);
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
        return isset($result[0]['description'])? $result[0]['description'] : '';
    }

    public function setImageSizes()
    {
        static $sizes = array();
        $tool = $this->loadTool();
        $profile = $tool->params->getProfile();
        if (!isset($sizes[$profile])) {
            $thumbImageType = $tool->params->getValue('thumb-image');
            $selectorImageType = $tool->params->getValue('selector-image');
            $sql = 'SELECT name, width, height FROM `'._DB_PREFIX_.'image_type` WHERE name in (\''.pSQL($thumbImageType).'\', \''.pSQL($selectorImageType).'\')';
            $result = Db::getInstance()->ExecuteS($sql);
            $result[$result[0]['name']] = $result[0];
            $result[$result[1]['name']] = $result[1];
            $tool->params->setValue('thumb-max-width', $result[$thumbImageType]['width']);
            $tool->params->setValue('thumb-max-height', $result[$thumbImageType]['height']);
            $tool->params->setValue('selector-max-width', $result[$selectorImageType]['width']);
            $tool->params->setValue('selector-max-height', $result[$selectorImageType]['height']);
            $sizes[$profile] = true;
        }
    }

    public function fillDB()
    {
        $sql = 'INSERT INTO `'._DB_PREFIX_.'magiczoomplus_settings` (`block`, `name`, `value`, `default_value`, `enabled`, `default_enabled`) VALUES
                (\'default\', \'include-headers-on-all-pages\', \'No\', \'No\', 1, 1),
                (\'default\', \'thumb-image\', \'large\', \'large\', 1, 1),
                (\'default\', \'selector-image\', \'small\', \'small\', 1, 1),
                (\'default\', \'large-image\', \'thickbox\', \'thickbox\', 1, 1),
                (\'default\', \'zoomWidth\', \'auto\', \'auto\', 1, 1),
                (\'default\', \'zoomHeight\', \'auto\', \'auto\', 1, 1),
                (\'default\', \'zoomPosition\', \'right\', \'right\', 1, 1),
                (\'default\', \'zoomDistance\', \'15\', \'15\', 1, 1),
                (\'default\', \'lazyZoom\', \'No\', \'No\', 1, 1),
                (\'default\', \'rightClick\', \'No\', \'No\', 1, 1),
                (\'default\', \'link-to-product-page\', \'Yes\', \'Yes\', 1, 1),
                (\'default\', \'cssClass\', \'\', \'\', 1, 1),
                (\'default\', \'show-message\', \'No\', \'No\', 1, 1),
                (\'default\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 1, 1),
                (\'default\', \'zoomMode\', \'zoom\', \'zoom\', 1, 1),
                (\'default\', \'zoomOn\', \'hover\', \'hover\', 1, 1),
                (\'default\', \'upscale\', \'Yes\', \'Yes\', 1, 1),
                (\'default\', \'smoothing\', \'Yes\', \'Yes\', 1, 1),
                (\'default\', \'variableZoom\', \'No\', \'No\', 1, 1),
                (\'default\', \'zoomCaption\', \'off\', \'off\', 1, 1),
                (\'default\', \'expand\', \'window\', \'window\', 1, 1),
                (\'default\', \'expandZoomMode\', \'zoom\', \'zoom\', 1, 1),
                (\'default\', \'expandZoomOn\', \'click\', \'click\', 1, 1),
                (\'default\', \'expandCaption\', \'Yes\', \'Yes\', 1, 1),
                (\'default\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 1, 1),
                (\'default\', \'hint\', \'once\', \'once\', 1, 1),
                (\'default\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 1, 1),
                (\'default\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 1, 1),
                (\'default\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 1, 1),
                (\'default\', \'textBtnClose\', \'Close\', \'Close\', 1, 1),
                (\'default\', \'textBtnNext\', \'Next\', \'Next\', 1, 1),
                (\'default\', \'textBtnPrev\', \'Previous\', \'Previous\', 1, 1),
                (\'default\', \'zoomModeForMobile\', \'off\', \'off\', 1, 1),
                (\'default\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 1, 1),
                (\'default\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 1, 1),
                (\'default\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 1, 1),
                (\'product\', \'template\', \'bottom\', \'bottom\', 0, 0),
                (\'product\', \'magicscroll\', \'No\', \'No\', 0, 0),
                (\'product\', \'thumb-image\', \'large\', \'large\', 0, 0),
                (\'product\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'product\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'product\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'product\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'product\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'product\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'product\', \'selectorTrigger\', \'click\', \'click\', 0, 0),
                (\'product\', \'transitionEffect\', \'Yes\', \'Yes\', 0, 0),
                (\'product\', \'360-as-primary-image\', \'Yes\', \'Yes\', 0, 0),
                (\'product\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'product\', \'enable-effect\', \'Yes\', \'Yes\', 1, 1),
                (\'product\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'product\', \'cssClass\', \'\', \'\', 0, 0),
                (\'product\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'product\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'product\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'product\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'product\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'product\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'product\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'product\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'product\', \'expand\', \'window\', \'window\', 0, 0),
                (\'product\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'product\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'product\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'product\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'product\', \'hint\', \'once\', \'once\', 0, 0),
                (\'product\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'product\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'product\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'product\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'product\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'product\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'product\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'product\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'product\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'product\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'product\', \'width\', \'auto\', \'auto\', 0, 0),
                (\'product\', \'height\', \'auto\', \'auto\', 0, 0),
                (\'product\', \'mode\', \'scroll\', \'scroll\', 0, 0),
                (\'product\', \'items\', \'3\', \'3\', 0, 0),
                (\'product\', \'speed\', \'600\', \'600\', 0, 0),
                (\'product\', \'autoplay\', \'0\', \'0\', 0, 0),
                (\'product\', \'loop\', \'infinite\', \'infinite\', 0, 0),
                (\'product\', \'step\', \'auto\', \'auto\', 0, 0),
                (\'product\', \'arrows\', \'inside\', \'inside\', 0, 0),
                (\'product\', \'pagination\', \'No\', \'No\', 0, 0),
                (\'product\', \'easing\', \'cubic-bezier(.8, 0, .5, 1)\', \'cubic-bezier(.8, 0, .5, 1)\', 0, 0),
                (\'product\', \'scrollOnWheel\', \'auto\', \'auto\', 0, 0),
                (\'product\', \'lazy-load\', \'No\', \'No\', 0, 0),
                (\'product\', \'scroll-extra-styles\', \'\', \'\', 0, 0),
                (\'product\', \'show-image-title\', \'No\', \'No\', 0, 0),
                (\'category\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'category\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'category\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'category\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'category\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'category\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'category\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'category\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'category\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'category\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'category\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'category\', \'cssClass\', \'\', \'\', 0, 0),
                (\'category\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'category\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'category\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'category\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'category\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'category\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'category\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'category\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'category\', \'expand\', \'window\', \'window\', 0, 0),
                (\'category\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'category\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'category\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'category\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'category\', \'hint\', \'once\', \'once\', 0, 0),
                (\'category\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'category\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'category\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'category\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'category\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'category\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'category\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'category\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'category\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'category\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'manufacturer\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'manufacturer\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'manufacturer\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'manufacturer\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'manufacturer\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'manufacturer\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'manufacturer\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'manufacturer\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'manufacturer\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'manufacturer\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'manufacturer\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'manufacturer\', \'cssClass\', \'\', \'\', 0, 0),
                (\'manufacturer\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'manufacturer\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'manufacturer\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'manufacturer\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'manufacturer\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'manufacturer\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'manufacturer\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'manufacturer\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'manufacturer\', \'expand\', \'window\', \'window\', 0, 0),
                (\'manufacturer\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'manufacturer\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'manufacturer\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'manufacturer\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'manufacturer\', \'hint\', \'once\', \'once\', 0, 0),
                (\'manufacturer\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'manufacturer\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'manufacturer\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'manufacturer\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'manufacturer\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'manufacturer\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'manufacturer\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'manufacturer\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'manufacturer\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'manufacturer\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'newproductpage\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'newproductpage\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'newproductpage\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'newproductpage\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'newproductpage\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'newproductpage\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'newproductpage\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'newproductpage\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'newproductpage\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'newproductpage\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'newproductpage\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'newproductpage\', \'cssClass\', \'\', \'\', 0, 0),
                (\'newproductpage\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'newproductpage\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'newproductpage\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'newproductpage\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'newproductpage\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'newproductpage\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'newproductpage\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'newproductpage\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'newproductpage\', \'expand\', \'window\', \'window\', 0, 0),
                (\'newproductpage\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'newproductpage\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'newproductpage\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'newproductpage\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'newproductpage\', \'hint\', \'once\', \'once\', 0, 0),
                (\'newproductpage\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'newproductpage\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'newproductpage\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'newproductpage\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'newproductpage\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'newproductpage\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'newproductpage\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'newproductpage\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'newproductpage\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'newproductpage\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blocknewproducts\', \'thumb-image\', \'medium\', \'medium\', 1, 1),
                (\'blocknewproducts\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blocknewproducts\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blocknewproducts\', \'zoomWidth\', \'150\', \'150\', 1, 1),
                (\'blocknewproducts\', \'zoomHeight\', \'150\', \'150\', 1, 1),
                (\'blocknewproducts\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blocknewproducts\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blocknewproducts\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blocknewproducts\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blocknewproducts\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blocknewproducts\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blocknewproducts\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blocknewproducts\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blocknewproducts\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blocknewproducts\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blocknewproducts\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blocknewproducts\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blocknewproducts\', \'textHoverZoomHint\', \'\', \'\', 1, 1),
                (\'blocknewproducts\', \'textClickZoomHint\', \'\', \'\', 1, 1),
                (\'blocknewproducts\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blocknewproducts\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blocknewproducts\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blocknewproducts\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blocknewproducts\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blocknewproducts\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blocknewproducts\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blocknewproducts\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blocknewproducts_home\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'blocknewproducts_home\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blocknewproducts_home\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blocknewproducts_home\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'blocknewproducts_home\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'blocknewproducts_home\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blocknewproducts_home\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blocknewproducts_home\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts_home\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blocknewproducts_home\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts_home\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts_home\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blocknewproducts_home\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts_home\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blocknewproducts_home\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blocknewproducts_home\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blocknewproducts_home\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts_home\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts_home\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blocknewproducts_home\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blocknewproducts_home\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blocknewproducts_home\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blocknewproducts_home\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blocknewproducts_home\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts_home\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blocknewproducts_home\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blocknewproducts_home\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'blocknewproducts_home\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'blocknewproducts_home\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blocknewproducts_home\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blocknewproducts_home\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blocknewproducts_home\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blocknewproducts_home\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blocknewproducts_home\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blocknewproducts_home\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blocknewproducts_home\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'bestsellerspage\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'bestsellerspage\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'bestsellerspage\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'bestsellerspage\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'bestsellerspage\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'bestsellerspage\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'bestsellerspage\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'bestsellerspage\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'bestsellerspage\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'bestsellerspage\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'bestsellerspage\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'bestsellerspage\', \'cssClass\', \'\', \'\', 0, 0),
                (\'bestsellerspage\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'bestsellerspage\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'bestsellerspage\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'bestsellerspage\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'bestsellerspage\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'bestsellerspage\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'bestsellerspage\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'bestsellerspage\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'bestsellerspage\', \'expand\', \'window\', \'window\', 0, 0),
                (\'bestsellerspage\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'bestsellerspage\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'bestsellerspage\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'bestsellerspage\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'bestsellerspage\', \'hint\', \'once\', \'once\', 0, 0),
                (\'bestsellerspage\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'bestsellerspage\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'bestsellerspage\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'bestsellerspage\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'bestsellerspage\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'bestsellerspage\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'bestsellerspage\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'bestsellerspage\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'bestsellerspage\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'bestsellerspage\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blockbestsellers\', \'thumb-image\', \'medium\', \'medium\', 1, 1),
                (\'blockbestsellers\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blockbestsellers\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blockbestsellers\', \'zoomWidth\', \'150\', \'150\', 1, 1),
                (\'blockbestsellers\', \'zoomHeight\', \'150\', \'150\', 1, 1),
                (\'blockbestsellers\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blockbestsellers\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blockbestsellers\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blockbestsellers\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blockbestsellers\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blockbestsellers\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockbestsellers\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blockbestsellers\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blockbestsellers\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blockbestsellers\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockbestsellers\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blockbestsellers\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blockbestsellers\', \'textHoverZoomHint\', \'\', \'\', 1, 1),
                (\'blockbestsellers\', \'textClickZoomHint\', \'\', \'\', 1, 1),
                (\'blockbestsellers\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blockbestsellers\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blockbestsellers\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blockbestsellers\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blockbestsellers\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blockbestsellers\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blockbestsellers\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blockbestsellers\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blockbestsellers_home\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'blockbestsellers_home\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blockbestsellers_home\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blockbestsellers_home\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'blockbestsellers_home\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'blockbestsellers_home\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blockbestsellers_home\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blockbestsellers_home\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers_home\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blockbestsellers_home\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers_home\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers_home\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blockbestsellers_home\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers_home\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blockbestsellers_home\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockbestsellers_home\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blockbestsellers_home\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers_home\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers_home\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blockbestsellers_home\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blockbestsellers_home\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blockbestsellers_home\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockbestsellers_home\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blockbestsellers_home\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers_home\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blockbestsellers_home\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blockbestsellers_home\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'blockbestsellers_home\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'blockbestsellers_home\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blockbestsellers_home\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blockbestsellers_home\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blockbestsellers_home\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blockbestsellers_home\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blockbestsellers_home\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blockbestsellers_home\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blockbestsellers_home\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'specialspage\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'specialspage\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'specialspage\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'specialspage\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'specialspage\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'specialspage\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'specialspage\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'specialspage\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'specialspage\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'specialspage\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'specialspage\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'specialspage\', \'cssClass\', \'\', \'\', 0, 0),
                (\'specialspage\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'specialspage\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'specialspage\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'specialspage\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'specialspage\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'specialspage\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'specialspage\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'specialspage\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'specialspage\', \'expand\', \'window\', \'window\', 0, 0),
                (\'specialspage\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'specialspage\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'specialspage\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'specialspage\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'specialspage\', \'hint\', \'once\', \'once\', 0, 0),
                (\'specialspage\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'specialspage\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'specialspage\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'specialspage\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'specialspage\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'specialspage\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'specialspage\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'specialspage\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'specialspage\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'specialspage\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blockspecials\', \'thumb-image\', \'medium\', \'medium\', 1, 1),
                (\'blockspecials\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blockspecials\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blockspecials\', \'zoomWidth\', \'150\', \'150\', 1, 1),
                (\'blockspecials\', \'zoomHeight\', \'150\', \'150\', 1, 1),
                (\'blockspecials\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blockspecials\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blockspecials\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blockspecials\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blockspecials\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blockspecials\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blockspecials\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blockspecials\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blockspecials\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockspecials\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blockspecials\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blockspecials\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blockspecials\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blockspecials\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockspecials\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blockspecials\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blockspecials\', \'textHoverZoomHint\', \'\', \'\', 1, 1),
                (\'blockspecials\', \'textClickZoomHint\', \'\', \'\', 1, 1),
                (\'blockspecials\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blockspecials\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blockspecials\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blockspecials\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blockspecials\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blockspecials\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blockspecials\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blockspecials\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blockspecials_home\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'blockspecials_home\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blockspecials_home\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blockspecials_home\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'blockspecials_home\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'blockspecials_home\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blockspecials_home\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blockspecials_home\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blockspecials_home\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blockspecials_home\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blockspecials_home\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials_home\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blockspecials_home\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blockspecials_home\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blockspecials_home\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockspecials_home\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blockspecials_home\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials_home\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials_home\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blockspecials_home\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blockspecials_home\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blockspecials_home\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockspecials_home\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blockspecials_home\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials_home\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blockspecials_home\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blockspecials_home\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'blockspecials_home\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'blockspecials_home\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blockspecials_home\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blockspecials_home\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blockspecials_home\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blockspecials_home\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blockspecials_home\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blockspecials_home\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blockspecials_home\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'blockviewed\', \'thumb-image\', \'medium\', \'medium\', 1, 1),
                (\'blockviewed\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'blockviewed\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'blockviewed\', \'zoomWidth\', \'150\', \'150\', 1, 1),
                (\'blockviewed\', \'zoomHeight\', \'150\', \'150\', 1, 1),
                (\'blockviewed\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'blockviewed\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'blockviewed\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'blockviewed\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'blockviewed\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'blockviewed\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'blockviewed\', \'cssClass\', \'\', \'\', 0, 0),
                (\'blockviewed\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'blockviewed\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'blockviewed\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockviewed\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'blockviewed\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'blockviewed\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'blockviewed\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'blockviewed\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'blockviewed\', \'expand\', \'window\', \'window\', 0, 0),
                (\'blockviewed\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'blockviewed\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'blockviewed\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'blockviewed\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'blockviewed\', \'hint\', \'once\', \'once\', 0, 0),
                (\'blockviewed\', \'textHoverZoomHint\', \'\', \'\', 1, 1),
                (\'blockviewed\', \'textClickZoomHint\', \'\', \'\', 1, 1),
                (\'blockviewed\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'blockviewed\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'blockviewed\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'blockviewed\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'blockviewed\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'blockviewed\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'blockviewed\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'blockviewed\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'homefeatured\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'homefeatured\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'homefeatured\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'homefeatured\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'homefeatured\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'homefeatured\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'homefeatured\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'homefeatured\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'homefeatured\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'homefeatured\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'homefeatured\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'homefeatured\', \'cssClass\', \'\', \'\', 0, 0),
                (\'homefeatured\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'homefeatured\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'homefeatured\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'homefeatured\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'homefeatured\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'homefeatured\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'homefeatured\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'homefeatured\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'homefeatured\', \'expand\', \'window\', \'window\', 0, 0),
                (\'homefeatured\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'homefeatured\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'homefeatured\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'homefeatured\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'homefeatured\', \'hint\', \'once\', \'once\', 0, 0),
                (\'homefeatured\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'homefeatured\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'homefeatured\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'homefeatured\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'homefeatured\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'homefeatured\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'homefeatured\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'homefeatured\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'homefeatured\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'homefeatured\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0),
                (\'search\', \'thumb-image\', \'home\', \'home\', 1, 1),
                (\'search\', \'selector-image\', \'small\', \'small\', 0, 0),
                (\'search\', \'large-image\', \'thickbox\', \'thickbox\', 0, 0),
                (\'search\', \'zoomWidth\', \'auto\', \'auto\', 0, 0),
                (\'search\', \'zoomHeight\', \'auto\', \'auto\', 0, 0),
                (\'search\', \'zoomPosition\', \'right\', \'right\', 0, 0),
                (\'search\', \'zoomDistance\', \'15\', \'15\', 0, 0),
                (\'search\', \'lazyZoom\', \'No\', \'No\', 0, 0),
                (\'search\', \'enable-effect\', \'No\', \'No\', 1, 1),
                (\'search\', \'rightClick\', \'No\', \'No\', 0, 0),
                (\'search\', \'link-to-product-page\', \'Yes\', \'Yes\', 0, 0),
                (\'search\', \'cssClass\', \'\', \'\', 0, 0),
                (\'search\', \'show-message\', \'No\', \'No\', 0, 0),
                (\'search\', \'message\', \'Move your mouse over image or click to enlarge\', \'Move your mouse over image or click to enlarge\', 0, 0),
                (\'search\', \'zoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'search\', \'zoomOn\', \'hover\', \'hover\', 0, 0),
                (\'search\', \'upscale\', \'Yes\', \'Yes\', 0, 0),
                (\'search\', \'smoothing\', \'Yes\', \'Yes\', 0, 0),
                (\'search\', \'variableZoom\', \'No\', \'No\', 0, 0),
                (\'search\', \'zoomCaption\', \'off\', \'off\', 0, 0),
                (\'search\', \'expand\', \'window\', \'window\', 0, 0),
                (\'search\', \'expandZoomMode\', \'zoom\', \'zoom\', 0, 0),
                (\'search\', \'expandZoomOn\', \'click\', \'click\', 0, 0),
                (\'search\', \'expandCaption\', \'Yes\', \'Yes\', 0, 0),
                (\'search\', \'closeOnClickOutside\', \'Yes\', \'Yes\', 0, 0),
                (\'search\', \'hint\', \'once\', \'once\', 0, 0),
                (\'search\', \'textHoverZoomHint\', \'Hover to zoom\', \'Hover to zoom\', 0, 0),
                (\'search\', \'textClickZoomHint\', \'Click to zoom\', \'Click to zoom\', 0, 0),
                (\'search\', \'textExpandHint\', \'Click to expand\', \'Click to expand\', 0, 0),
                (\'search\', \'textBtnClose\', \'Close\', \'Close\', 0, 0),
                (\'search\', \'textBtnNext\', \'Next\', \'Next\', 0, 0),
                (\'search\', \'textBtnPrev\', \'Previous\', \'Previous\', 0, 0),
                (\'search\', \'zoomModeForMobile\', \'off\', \'off\', 0, 0),
                (\'search\', \'textHoverZoomHintForMobile\', \'Touch to zoom\', \'Touch to zoom\', 0, 0),
                (\'search\', \'textClickZoomHintForMobile\', \'Double tap or pinch to zoom\', \'Double tap or pinch to zoom\', 0, 0),
                (\'search\', \'textExpandHintForMobile\', \'Tap to expand\', \'Tap to expand\', 0, 0)';
        if (!$this->isPrestaShop16x) {
            $sql = preg_replace('/\r\n\s*..(?:blockbestsellers_home|blocknewproducts_home|blockspecials_home)\b[^\r]*+/i', '', $sql);
            $sql = rtrim($sql, ',');
        }
        return Db::getInstance()->Execute($sql);
    }

    public function getBlocks()
    {
        $blocks = array(
            'default' => 'Default settings',
            'product' => 'Product page',
            'category' => 'Category page',
            'manufacturer' => 'Manufacturers page',
            'newproductpage' => 'New products page',
            'blocknewproducts' => 'New products sidebar',
            'blocknewproducts_home' => 'New products block',
            'bestsellerspage' => 'Bestsellers page',
            'blockbestsellers' => 'Bestsellers sidebar',
            'blockbestsellers_home' => 'Bestsellers block',
            'specialspage' => 'Specials page',
            'blockspecials' => 'Specials sidebar',
            'blockspecials_home' => 'Specials block',
            'blockviewed' => 'Viewed sidebar',
            'homefeatured' => 'Featured block',
            'search' => 'Search page'
        );
        if (!$this->isPrestaShop16x) {
            unset($blocks['blockbestsellers_home'], $blocks['blocknewproducts_home'], $blocks['blockspecials_home']);
        }
        if ($this->isPrestaShop17x) {
            unset($blocks['blocknewproducts'], $blocks['manufacturer'], $blocks['blockspecials'], $blocks['blockbestsellers'], $blocks['blockviewed']);
        }
        return $blocks;
    }

    public function getMessages()
    {
        return array(
            'default' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Default settings zoom hint text (on hover)',
                    'translate' => $this->l('Default settings zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Default settings zoom hint text (on click)',
                    'translate' => $this->l('Default settings zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Default settings zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Default settings zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Default settings zoom hint text for mobile (on click)',
                    'translate' => $this->l('Default settings zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Default settings expand hint text',
                    'translate' => $this->l('Default settings expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Default settings expand hint text for mobile',
                    'translate' => $this->l('Default settings expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Default settings close button text',
                    'translate' => $this->l('Default settings close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Default settings next button text',
                    'translate' => $this->l('Default settings next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Default settings prev button text',
                    'translate' => $this->l('Default settings prev button text')
                ),
                'message' => array(
                    'title' => 'Default settings message (under Magic Zoom Plus)',
                    'translate' => $this->l('Default settings message (under Magic Zoom Plus)')
                )
            ),
            'product' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Product page zoom hint text (on hover)',
                    'translate' => $this->l('Product page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Product page zoom hint text (on click)',
                    'translate' => $this->l('Product page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Product page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Product page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Product page zoom hint text for mobile (on click)',
                    'translate' => $this->l('Product page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Product page expand hint text',
                    'translate' => $this->l('Product page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Product page expand hint text for mobile',
                    'translate' => $this->l('Product page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Product page close button text',
                    'translate' => $this->l('Product page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Product page next button text',
                    'translate' => $this->l('Product page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Product page prev button text',
                    'translate' => $this->l('Product page prev button text')
                ),
                'message' => array(
                    'title' => 'Product page message (under Magic Zoom Plus)',
                    'translate' => $this->l('Product page message (under Magic Zoom Plus)')
                )
            ),
            'category' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Category page zoom hint text (on hover)',
                    'translate' => $this->l('Category page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Category page zoom hint text (on click)',
                    'translate' => $this->l('Category page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Category page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Category page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Category page zoom hint text for mobile (on click)',
                    'translate' => $this->l('Category page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Category page expand hint text',
                    'translate' => $this->l('Category page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Category page expand hint text for mobile',
                    'translate' => $this->l('Category page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Category page close button text',
                    'translate' => $this->l('Category page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Category page next button text',
                    'translate' => $this->l('Category page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Category page prev button text',
                    'translate' => $this->l('Category page prev button text')
                ),
                'message' => array(
                    'title' => 'Category page message (under Magic Zoom Plus)',
                    'translate' => $this->l('Category page message (under Magic Zoom Plus)')
                )
            ),
            'manufacturer' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Manufacturers page zoom hint text (on hover)',
                    'translate' => $this->l('Manufacturers page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Manufacturers page zoom hint text (on click)',
                    'translate' => $this->l('Manufacturers page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Manufacturers page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Manufacturers page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Manufacturers page zoom hint text for mobile (on click)',
                    'translate' => $this->l('Manufacturers page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Manufacturers page expand hint text',
                    'translate' => $this->l('Manufacturers page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Manufacturers page expand hint text for mobile',
                    'translate' => $this->l('Manufacturers page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Manufacturers page close button text',
                    'translate' => $this->l('Manufacturers page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Manufacturers page next button text',
                    'translate' => $this->l('Manufacturers page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Manufacturers page prev button text',
                    'translate' => $this->l('Manufacturers page prev button text')
                ),
                'message' => array(
                    'title' => 'Manufacturers page message (under Magic Zoom Plus)',
                    'translate' => $this->l('Manufacturers page message (under Magic Zoom Plus)')
                )
            ),
            'newproductpage' => array(
                'textHoverZoomHint' => array(
                    'title' => 'New products page zoom hint text (on hover)',
                    'translate' => $this->l('New products page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'New products page zoom hint text (on click)',
                    'translate' => $this->l('New products page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'New products page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('New products page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'New products page zoom hint text for mobile (on click)',
                    'translate' => $this->l('New products page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'New products page expand hint text',
                    'translate' => $this->l('New products page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'New products page expand hint text for mobile',
                    'translate' => $this->l('New products page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'New products page close button text',
                    'translate' => $this->l('New products page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'New products page next button text',
                    'translate' => $this->l('New products page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'New products page prev button text',
                    'translate' => $this->l('New products page prev button text')
                ),
                'message' => array(
                    'title' => 'New products page message (under Magic Zoom Plus)',
                    'translate' => $this->l('New products page message (under Magic Zoom Plus)')
                )
            ),
            'blocknewproducts' => array(
                'textHoverZoomHint' => array(
                    'title' => 'New products sidebar zoom hint text (on hover)',
                    'translate' => $this->l('New products sidebar zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'New products sidebar zoom hint text (on click)',
                    'translate' => $this->l('New products sidebar zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'New products sidebar zoom hint text for mobile (on hover)',
                    'translate' => $this->l('New products sidebar zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'New products sidebar zoom hint text for mobile (on click)',
                    'translate' => $this->l('New products sidebar zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'New products sidebar expand hint text',
                    'translate' => $this->l('New products sidebar expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'New products sidebar expand hint text for mobile',
                    'translate' => $this->l('New products sidebar expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'New products sidebar close button text',
                    'translate' => $this->l('New products sidebar close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'New products sidebar next button text',
                    'translate' => $this->l('New products sidebar next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'New products sidebar prev button text',
                    'translate' => $this->l('New products sidebar prev button text')
                ),
                'message' => array(
                    'title' => 'New products sidebar message (under Magic Zoom Plus)',
                    'translate' => $this->l('New products sidebar message (under Magic Zoom Plus)')
                )
            ),
            'blocknewproducts_home' => array(
                'textHoverZoomHint' => array(
                    'title' => 'New products block zoom hint text (on hover)',
                    'translate' => $this->l('New products block zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'New products block zoom hint text (on click)',
                    'translate' => $this->l('New products block zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'New products block zoom hint text for mobile (on hover)',
                    'translate' => $this->l('New products block zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'New products block zoom hint text for mobile (on click)',
                    'translate' => $this->l('New products block zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'New products block expand hint text',
                    'translate' => $this->l('New products block expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'New products block expand hint text for mobile',
                    'translate' => $this->l('New products block expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'New products block close button text',
                    'translate' => $this->l('New products block close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'New products block next button text',
                    'translate' => $this->l('New products block next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'New products block prev button text',
                    'translate' => $this->l('New products block prev button text')
                ),
                'message' => array(
                    'title' => 'New products block message (under Magic Zoom Plus)',
                    'translate' => $this->l('New products block message (under Magic Zoom Plus)')
                )
            ),
            'bestsellerspage' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Bestsellers page zoom hint text (on hover)',
                    'translate' => $this->l('Bestsellers page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Bestsellers page zoom hint text (on click)',
                    'translate' => $this->l('Bestsellers page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Bestsellers page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Bestsellers page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Bestsellers page zoom hint text for mobile (on click)',
                    'translate' => $this->l('Bestsellers page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Bestsellers page expand hint text',
                    'translate' => $this->l('Bestsellers page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Bestsellers page expand hint text for mobile',
                    'translate' => $this->l('Bestsellers page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Bestsellers page close button text',
                    'translate' => $this->l('Bestsellers page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Bestsellers page next button text',
                    'translate' => $this->l('Bestsellers page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Bestsellers page prev button text',
                    'translate' => $this->l('Bestsellers page prev button text')
                ),
                'message' => array(
                    'title' => 'Bestsellers page message (under Magic Zoom Plus)',
                    'translate' => $this->l('Bestsellers page message (under Magic Zoom Plus)')
                )
            ),
            'blockbestsellers' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Bestsellers sidebar zoom hint text (on hover)',
                    'translate' => $this->l('Bestsellers sidebar zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Bestsellers sidebar zoom hint text (on click)',
                    'translate' => $this->l('Bestsellers sidebar zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Bestsellers sidebar zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Bestsellers sidebar zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Bestsellers sidebar zoom hint text for mobile (on click)',
                    'translate' => $this->l('Bestsellers sidebar zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Bestsellers sidebar expand hint text',
                    'translate' => $this->l('Bestsellers sidebar expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Bestsellers sidebar expand hint text for mobile',
                    'translate' => $this->l('Bestsellers sidebar expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Bestsellers sidebar close button text',
                    'translate' => $this->l('Bestsellers sidebar close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Bestsellers sidebar next button text',
                    'translate' => $this->l('Bestsellers sidebar next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Bestsellers sidebar prev button text',
                    'translate' => $this->l('Bestsellers sidebar prev button text')
                ),
                'message' => array(
                    'title' => 'Bestsellers sidebar message (under Magic Zoom Plus)',
                    'translate' => $this->l('Bestsellers sidebar message (under Magic Zoom Plus)')
                )
            ),
            'blockbestsellers_home' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Bestsellers block zoom hint text (on hover)',
                    'translate' => $this->l('Bestsellers block zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Bestsellers block zoom hint text (on click)',
                    'translate' => $this->l('Bestsellers block zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Bestsellers block zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Bestsellers block zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Bestsellers block zoom hint text for mobile (on click)',
                    'translate' => $this->l('Bestsellers block zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Bestsellers block expand hint text',
                    'translate' => $this->l('Bestsellers block expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Bestsellers block expand hint text for mobile',
                    'translate' => $this->l('Bestsellers block expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Bestsellers block close button text',
                    'translate' => $this->l('Bestsellers block close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Bestsellers block next button text',
                    'translate' => $this->l('Bestsellers block next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Bestsellers block prev button text',
                    'translate' => $this->l('Bestsellers block prev button text')
                ),
                'message' => array(
                    'title' => 'Bestsellers block message (under Magic Zoom Plus)',
                    'translate' => $this->l('Bestsellers block message (under Magic Zoom Plus)')
                )
            ),
            'specialspage' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Specials page zoom hint text (on hover)',
                    'translate' => $this->l('Specials page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Specials page zoom hint text (on click)',
                    'translate' => $this->l('Specials page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Specials page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Specials page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Specials page zoom hint text for mobile (on click)',
                    'translate' => $this->l('Specials page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Specials page expand hint text',
                    'translate' => $this->l('Specials page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Specials page expand hint text for mobile',
                    'translate' => $this->l('Specials page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Specials page close button text',
                    'translate' => $this->l('Specials page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Specials page next button text',
                    'translate' => $this->l('Specials page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Specials page prev button text',
                    'translate' => $this->l('Specials page prev button text')
                ),
                'message' => array(
                    'title' => 'Specials page message (under Magic Zoom Plus)',
                    'translate' => $this->l('Specials page message (under Magic Zoom Plus)')
                )
            ),
            'blockspecials' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Specials sidebar zoom hint text (on hover)',
                    'translate' => $this->l('Specials sidebar zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Specials sidebar zoom hint text (on click)',
                    'translate' => $this->l('Specials sidebar zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Specials sidebar zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Specials sidebar zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Specials sidebar zoom hint text for mobile (on click)',
                    'translate' => $this->l('Specials sidebar zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Specials sidebar expand hint text',
                    'translate' => $this->l('Specials sidebar expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Specials sidebar expand hint text for mobile',
                    'translate' => $this->l('Specials sidebar expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Specials sidebar close button text',
                    'translate' => $this->l('Specials sidebar close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Specials sidebar next button text',
                    'translate' => $this->l('Specials sidebar next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Specials sidebar prev button text',
                    'translate' => $this->l('Specials sidebar prev button text')
                ),
                'message' => array(
                    'title' => 'Specials sidebar message (under Magic Zoom Plus)',
                    'translate' => $this->l('Specials sidebar message (under Magic Zoom Plus)')
                )
            ),
            'blockspecials_home' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Specials block zoom hint text (on hover)',
                    'translate' => $this->l('Specials block zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Specials block zoom hint text (on click)',
                    'translate' => $this->l('Specials block zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Specials block zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Specials block zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Specials block zoom hint text for mobile (on click)',
                    'translate' => $this->l('Specials block zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Specials block expand hint text',
                    'translate' => $this->l('Specials block expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Specials block expand hint text for mobile',
                    'translate' => $this->l('Specials block expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Specials block close button text',
                    'translate' => $this->l('Specials block close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Specials block next button text',
                    'translate' => $this->l('Specials block next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Specials block prev button text',
                    'translate' => $this->l('Specials block prev button text')
                ),
                'message' => array(
                    'title' => 'Specials block message (under Magic Zoom Plus)',
                    'translate' => $this->l('Specials block message (under Magic Zoom Plus)')
                )
            ),
            'blockviewed' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Viewed sidebar zoom hint text (on hover)',
                    'translate' => $this->l('Viewed sidebar zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Viewed sidebar zoom hint text (on click)',
                    'translate' => $this->l('Viewed sidebar zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Viewed sidebar zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Viewed sidebar zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Viewed sidebar zoom hint text for mobile (on click)',
                    'translate' => $this->l('Viewed sidebar zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Viewed sidebar expand hint text',
                    'translate' => $this->l('Viewed sidebar expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Viewed sidebar expand hint text for mobile',
                    'translate' => $this->l('Viewed sidebar expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Viewed sidebar close button text',
                    'translate' => $this->l('Viewed sidebar close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Viewed sidebar next button text',
                    'translate' => $this->l('Viewed sidebar next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Viewed sidebar prev button text',
                    'translate' => $this->l('Viewed sidebar prev button text')
                ),
                'message' => array(
                    'title' => 'Viewed sidebar message (under Magic Zoom Plus)',
                    'translate' => $this->l('Viewed sidebar message (under Magic Zoom Plus)')
                )
            ),
            'homefeatured' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Featured block zoom hint text (on hover)',
                    'translate' => $this->l('Featured block zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Featured block zoom hint text (on click)',
                    'translate' => $this->l('Featured block zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Featured block zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Featured block zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Featured block zoom hint text for mobile (on click)',
                    'translate' => $this->l('Featured block zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Featured block expand hint text',
                    'translate' => $this->l('Featured block expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Featured block expand hint text for mobile',
                    'translate' => $this->l('Featured block expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Featured block close button text',
                    'translate' => $this->l('Featured block close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Featured block next button text',
                    'translate' => $this->l('Featured block next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Featured block prev button text',
                    'translate' => $this->l('Featured block prev button text')
                ),
                'message' => array(
                    'title' => 'Featured block message (under Magic Zoom Plus)',
                    'translate' => $this->l('Featured block message (under Magic Zoom Plus)')
                )
            ),
            'search' => array(
                'textHoverZoomHint' => array(
                    'title' => 'Search page zoom hint text (on hover)',
                    'translate' => $this->l('Search page zoom hint text (on hover)')
                ),
                'textClickZoomHint' => array(
                    'title' => 'Search page zoom hint text (on click)',
                    'translate' => $this->l('Search page zoom hint text (on click)')
                ),
                'textHoverZoomHintForMobile' => array(
                    'title' => 'Search page zoom hint text for mobile (on hover)',
                    'translate' => $this->l('Search page zoom hint text for mobile (on hover)')
                ),
                'textClickZoomHintForMobile' => array(
                    'title' => 'Search page zoom hint text for mobile (on click)',
                    'translate' => $this->l('Search page zoom hint text for mobile (on click)')
                ),
                'textExpandHint' => array(
                    'title' => 'Search page expand hint text',
                    'translate' => $this->l('Search page expand hint text')
                ),
                'textExpandHintForMobile' => array(
                    'title' => 'Search page expand hint text for mobile',
                    'translate' => $this->l('Search page expand hint text for mobile')
                ),
                'textBtnClose' => array(
                    'title' => 'Search page close button text',
                    'translate' => $this->l('Search page close button text')
                ),
                'textBtnNext' => array(
                    'title' => 'Search page next button text',
                    'translate' => $this->l('Search page next button text')
                ),
                'textBtnPrev' => array(
                    'title' => 'Search page prev button text',
                    'translate' => $this->l('Search page prev button text')
                ),
                'message' => array(
                    'title' => 'Search page message (under Magic Zoom Plus)',
                    'translate' => $this->l('Search page message (under Magic Zoom Plus)')
                )
            )
        );
    }

    public function getParamsMap()
    {
        $map = array(
            'default' => array(
                'General' => array(
                    'include-headers-on-all-pages' => true
                ),
                'Image type' => array(
                    'thumb-image' => true,
                    'selector-image' => true,
                    'large-image' => true
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => true,
                    'zoomHeight' => true,
                    'zoomPosition' => true,
                    'zoomDistance' => true
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => true,
                    'rightClick' => true,
                    'link-to-product-page' => true,
                    'cssClass' => true,
                    'show-message' => true,
                    'message' => true
                ),
                'Zoom mode' => array(
                    'zoomMode' => true,
                    'zoomOn' => true,
                    'upscale' => true,
                    'smoothing' => true,
                    'variableZoom' => true,
                    'zoomCaption' => true
                ),
                'Expand mode' => array(
                    'expand' => true,
                    'expandZoomMode' => true,
                    'expandZoomOn' => true,
                    'expandCaption' => true,
                    'closeOnClickOutside' => true
                ),
                'Hint' => array(
                    'hint' => true,
                    'textHoverZoomHint' => true,
                    'textClickZoomHint' => true,
                    'textExpandHint' => true,
                    'textBtnClose' => true,
                    'textBtnNext' => true,
                    'textBtnPrev' => true
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => true,
                    'textHoverZoomHintForMobile' => true,
                    'textClickZoomHintForMobile' => true,
                    'textExpandHintForMobile' => true
                )
            ),
            'product' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'General' => array(
                    'template' => true,
                    'magicscroll' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'selector-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Multiple images' => array(
                    'selectorTrigger' => true,
                    'transitionEffect' => true
                ),
                'Settings for using Magic Zoom Plus and Magic 360 together' => array(
                    '360-as-primary-image' => true
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                ),
                'Scroll' => array(
                    'width' => true,
                    'height' => true,
                    'mode' => true,
                    'items' => true,
                    'speed' => true,
                    'autoplay' => true,
                    'loop' => true,
                    'step' => true,
                    'arrows' => true,
                    'pagination' => true,
                    'easing' => true,
                    'scrollOnWheel' => true,
                    'lazy-load' => true,
                    'scroll-extra-styles' => true,
                    'show-image-title' => true
                )
            ),
            'category' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'manufacturer' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'newproductpage' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blocknewproducts' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blocknewproducts_home' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'bestsellerspage' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blockbestsellers' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blockbestsellers_home' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'specialspage' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blockspecials' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blockspecials_home' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'blockviewed' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'homefeatured' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            ),
            'search' => array(
                'Enable effect' => array(
                    'enable-effect' => true
                ),
                'Image type' => array(
                    'thumb-image' => false,
                    'large-image' => false
                ),
                'Positioning and Geometry' => array(
                    'zoomWidth' => false,
                    'zoomHeight' => false,
                    'zoomPosition' => false,
                    'zoomDistance' => false
                ),
                'Miscellaneous' => array(
                    'lazyZoom' => false,
                    'rightClick' => false,
                    'link-to-product-page' => false,
                    'cssClass' => false,
                    'show-message' => false,
                    'message' => false
                ),
                'Zoom mode' => array(
                    'zoomMode' => false,
                    'zoomOn' => false,
                    'upscale' => false,
                    'smoothing' => false,
                    'variableZoom' => false,
                    'zoomCaption' => false
                ),
                'Expand mode' => array(
                    'expand' => false,
                    'expandZoomMode' => false,
                    'expandZoomOn' => false,
                    'expandCaption' => false,
                    'closeOnClickOutside' => false
                ),
                'Hint' => array(
                    'hint' => false,
                    'textHoverZoomHint' => false,
                    'textClickZoomHint' => false,
                    'textExpandHint' => false,
                    'textBtnClose' => false,
                    'textBtnNext' => false,
                    'textBtnPrev' => false
                ),
                'Mobile' => array(
                    'zoomModeForMobile' => false,
                    'textHoverZoomHintForMobile' => false,
                    'textClickZoomHintForMobile' => false,
                    'textExpandHintForMobile' => false
                )
            )
        );
        if (!$this->isPrestaShop16x) {
            unset($map['blockbestsellers_home'], $map['blocknewproducts_home'], $map['blockspecials_home']);
        }
        if ($this->isPrestaShop17x) {
            unset($map['blocknewproducts'], $map['manufacturer'], $map['blockspecials'], $map['blockbestsellers'], $map['blockviewed']);
        }
        return $map;
    }

    public function gebugVars($smarty = null)
    {
        if ($smarty === null) {
            $smarty = &$GLOBALS['smarty'];
        }
        $result = array();
        $vars = $smarty->{$this->getTemplateVars}();
        if (is_array($vars)) {
            foreach ($vars as $key => $value) {
                $result[$key] = gettype($value);
            }
        } else {
            $result = gettype($vars);
        }
        return $result;
    }
}
