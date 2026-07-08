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

class AdminCreativePopupController extends ModuleAdminController
{
    const ACTIVATE_URL = 'https://webshopworks.com/activate/';

    public function postProcess()
    {
        parent::postProcess();

        if (isset($this->context->cookie->cp_error)) {
            $this->errors[] = $this->context->cookie->cp_error;
            unset($this->context->cookie->cp_error);
        }
    }

    protected function processActivate()
    {
        $url = $this->context->link->getAdminLink('AdminCreativePopup');

        if (strpos($url, 'index.php') === 0) {
            $url = Tools::getShopProtocol() . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/$url";
        }

        if (Tools::getIsset('license')) {
            Configuration::updateGlobalValue('CP_LICENSE', Tools::getValue('license'));
            $url .= '#license';
        } else {
            list($p, $r) = explode('://', cp_referer());
            $encode = 'base64_encode';
            $url = self::ACTIVATE_URL . '?' . http_build_query([
                'response_type' => 'code',
                'client_id' => substr($encode(_COOKIE_KEY_), 0, 32),
                'auth_secret' => rtrim($encode("$r?" . Tools::passwdGen(23 - strlen($r))), '='),
                'state' => substr($encode($this->module->module_key), 0, 12),
                'redirect_uri' => urlencode($url),
            ]);
        }
        Tools::redirectAdmin($url);
    }

    protected function processDownloadPopup()
    {
        $id = Tools::getValue('id');
        $source = 'https://creativepopup.webshopworks.com/?get=template';
        $destination = _PS_UPLOAD_DIR_ . 'cpimport.zip';
        $data = cp_remote_get($source, [
            'body' => cp_apply_filters('cp_download_popup/body_args', ['id' => $id]),
            'timeout' => 300,
        ]);
        if (isset($data[0]) && '{' === $data[0] && $error = json_decode($data, true)) {
            $this->context->cookie->cp_error = $error['error'];

            if (419 == $error['code']) {
                Configuration::updateGlobalValue('CP_LICENSE', '');
            }
        } elseif ($data) {
            if (call_user_func('file_put_contents', $destination, $data)) {
                // import file
                require_once _PS_MODULE_DIR_ . 'creativepopup/base/core.php';
                require_once _PS_MODULE_DIR_ . 'creativepopup/base/classes/ImportUtil.php';

                $import = new CpImportUtil($destination);

                @call_user_func('unlink', $destination);
                // rename imported popup
                $title = !empty(${'_COOKIE'}['cpNewTitle']) ? ${'_COOKIE'}['cpNewTitle'] : 'Unnamed';
                setcookie('cpNewTitle', '', 1);

                Db::getInstance()->update('creativepopup', ['name' => pSQL($title)], 'id = ' . (int) $import->lastImportId);
                // redirect after import
                Tools::redirectAdmin(
                    $this->context->link->getAdminLink('AdminCreativePopup') . "&action=edit&id={$import->lastImportId}#appearance"
                );
            } else {
                $this->context->cookie->cp_error = cp__('Unable to write file: ') . $destination;
            }
        } else {
            $this->context->cookie->cp_error = cp__('Import returned without data!');
        }
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminCreativePopup'));
    }

    public function initPageHeaderToolbar()
    {
        // hide header toolbar
    }

    public function initToolbar()
    {
        // hide toolbar
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);

        define('CP_URL_TOKEN', $this->token);
        $GLOBALS['cp_screen'] = (object) [
            'id' => 'cp_page_popups',
            'base' => 'cp_page_popups',
        ];
        // simulate page
        ${'_GET'}['page'] = 'popups';

        if (version_compare(_PS_VERSION_, '1.6', '<')) {
            // CreativePopup admin requires at least jQuery v1.10.2
            foreach ($this->context->controller->js_files as &$js) {
                if (preg_match('/jquery-\d\.\d\.\d(\.min)?\.js$/i', $js)) {
                    $js = __PS_BASE_URI__ . "modules/{$this->module->name}/views/js/jquery.min.js";
                    break;
                }
            }
        }

        require_once _PS_MODULE_DIR_ . $this->module->name . '/helper.php';
        if (isset(${'_COOKIE'}['cp-login'])) {
            $this->content = '<script>
                var doc = window.parent.document, $ = window.parent.jQuery;
                doc.cookie = "cp-login=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                $(".cp-publish button").click();
                $(doc.getElementById("main")).css({ opacity: "", pointerEvents: "" });
                $(doc.getElementById("cp-login")).remove();
            </script>';
        } else {
            require_once _PS_MODULE_DIR_ . $this->module->name . '/views/default.php';
        }

        $this->context->smarty->unregisterFilter('output', 'smartyPackJSinHTML');
    }

    public function display()
    {
        $this->context->smarty->assign(['content' => $this->content]);
        $this->display_footer = false;

        parent::display();
    }
}
