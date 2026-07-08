<?php
/**
 * Creative Popup - https://creativepopup.webshopworks.com
 *
 * @author    WebshopWorks <info@webshopworks.com>
 * @copyright 2018-2024 WebshopWorks
 * @license   One Domain Licence
 *
 * Not allowed to resell or redistribute this software
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminCreativePopupRevisionsController extends ModuleAdminController
{
    public function postProcess()
    {
        parent::postProcess();
        if (isset($this->context->cookie->cp_error)) {
            $this->errors[] = $this->context->cookie->cp_error;
            unset($this->context->cookie->cp_error);
        }
    }

    public function initPageHeaderToolbar()
    {
        // hide header toolbar
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        define('CP_URL_TOKEN', $this->token);
        $GLOBALS['cp_screen'] = (object) [
            'id' => 'cp_page_revisions',
            'base' => 'cp_page_revisions',
        ];
        // simulate page
        ${'_GET'}['page'] = 'revisions';

        require_once _PS_MODULE_DIR_ . $this->module->name . '/helper.php';
        require_once _PS_MODULE_DIR_ . $this->module->name . '/views/default.php';
    }

    public function display()
    {
        $this->context->smarty->assign(['content' => $this->content]);
        $this->display_footer = false;

        parent::display();
    }
}
