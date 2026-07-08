<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */

if (!defined('_PS_VERSION_')) {
    exit;
}
class pm_advancedpackcronModuleFrontController extends ModuleFrontController
{
    public $ajax = true;
    public $display_header = false;
    public $display_footer = false;
    public $display_column_left = false;
    public $display_column_right = false;
    public $module;
    public function init()
    {
        if (ob_get_length() > 0) {
            ob_clean();
        }
        header('X-Robots-Tag: noindex, nofollow', true);
        header('Content-type: application/json');
        $secureKey = Configuration::getGlobalValue('PM_AP5_SECURE_KEY');
        if (empty($secureKey) || $secureKey !== Tools::getValue('secure_key')) {
            Tools::redirect('404');
            die;
        }
        die(json_encode($this->module->runCron()));
    }
}
