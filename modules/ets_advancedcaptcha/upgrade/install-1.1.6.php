<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */

if (!defined('_PS_VERSION_')) { exit; }


function upgrade_module_1_1_6($object)
{
    $object->regexTemplates();

    Configuration::deleteByName('PA_CAPTCHA_TMP_CONTACT');
    Configuration::deleteByName('PA_CAPTCHA_TMP_LOGIN');
    Configuration::deleteByName('PA_CAPTCHA_TMP_RE_PASSWORD');

    uninstallModuleInGDPR($object);

    if ($object->getOverrides() != null) {
        $dir = realpath(dirname(__FILE__) . '/../') . '/override/controllers/front/';
        $classes = array(
            'Auth',
            'Contact',
            'Password'
        );
        foreach ($classes as $class){
            beforeUninstallOverride($dir, $class);
        }
        $object->uninstallOverrides();
        try {
            foreach ($classes as $class) {
                beforeInstallOverride($dir, $class);
            }
            $object->installOverrides();
        } catch (Exception $e) {
            $object->_errors[] = $e->getMessage();
        }
    }

    return count($object->_errors) > 0 ? false : true;
}

function uninstallModuleInGDPR($object)
{
    if (Module::isInstalled('psgdpr') && $object->id) {
        if ($gdpr = Db::getInstance()->getRow('
            SELECT id_module, id_gdpr_consent 
            FROM `' . _DB_PREFIX_ . 'psgdpr_consent` 
            WHERE id_module = ' . (int)$object->id
        )) {
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "psgdpr_consent` WHERE id_module = " . (int)$gdpr['id_module']);
            Db::getInstance()->execute("DELETE FROM `" . _DB_PREFIX_ . "psgdpr_consent_lang` WHERE id_gdpr_consent = " . (int)$gdpr['id_gdpr_consent']);
        }
    }
}

function beforeUninstallOverride($dir, $class)
{
    $controllerFile = $dir . $class . 'Controller.php';
    if (!file_exists($controllerFile)) {
        return false;
    }
    $originalContent = Tools::file_get_contents($controllerFile);
    if ($originalContent === false) {
        return false;
    }
    $rebuild_class_content = preg_replace(
        '#(class\s+' . $class . 'Controller\s+extends\s+' . $class . 'ControllerCore\s+\{)#ms'
        , '$1/*-----start-----*/public function initContent(){parent::initContent();}/*-----end-----*/'
        , $originalContent
    );
    if ($rebuild_class_content === null) {
        return false;
    }
    if (file_put_contents($controllerFile, $rebuild_class_content) === false) {
        return false;
    }
    return true;
}

function beforeInstallOverride($dir, $class)
{
    $controllerFile = $dir . $class . 'Controller.php';
    if (!file_exists($controllerFile)) {
        return false;
    }
    $originalContent = Tools::file_get_contents($controllerFile);
    if ($originalContent === false) {
        return false;
    }
    $rebuildClassContent = preg_replace('#\/\*[-]{5}start[-]{5}\*\/(.*?)\/*[-]{5}end[-]{5}\*\/#ms', '', $originalContent);
    if ($rebuildClassContent === null) {
        return false;
    }
    if (file_put_contents($dir . $class . 'Controller.php', $rebuildClassContent)) {
        return true;
    }
    return false;
}