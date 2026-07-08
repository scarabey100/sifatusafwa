<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
class GiftCardCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        $this->assign();
    }

    /**
     * method manage post data
     *
     * @throws Exception
     */
    public function postProcess()
    {
        $message = '';
        // get the token
        $this->module->accesslog = $this->module->accessLogsDirectory();
        if ($token = Tools::getValue('token')) {
            if (trim($token) == trim(Configuration::get('GIFTCARD_TOKEN'))) {
                if ((int) date('Ymd') > (int) Configuration::get('GIFTCARD_BATCHLASTDATE')) {
                    Configuration::updateValue('GIFTCARD_BATCHLASTDATE', (int) date('Ymd'));
                    $this->module->sendPendingMailRecipient();
                    if ((int) Configuration::get('GIFTCARD_TRIGGERWEB') == 0) {
                        $message = 'Gift card launched';
                    }
                } else {
                    $this->module->loglongline('The date ' . (int) date('Ymd') . ' is every worked');
                    if ((int) Configuration::get('GIFTCARD_TRIGGERWEB') == 0) {
                        $message = 'The date ' . (int) date('Ymd') . ' is every worked';
                    }
                }
            } else {
                $message = $this->module->l('Internal server error! (security error)', 'cron');
            }
        } else {
            $this->module->loglongline('Token is required');
            if ((int) Configuration::get('GIFTCARD_TRIGGERWEB') == 0) {
                $message = 'Token is required';
            }
        }
        /* generate empty picture http://www.nexen.net/articles/dossier/16997-une_image_vide_sans_gd.php */
        if ((int) Configuration::get('GIFTCARD_TRIGGERWEB') == 1) {
            $hex = '47494638396101000100800000ffffff00000021f90401000000002c00000000010001000002024401003b';
            $img = '';
            $t = Tools::strlen($hex) / 2;
            for ($i = 0; $i < $t; ++$i) {
                $img .= chr(hexdec(Tools::substr($hex, $i * 2, 2)));
            }
            header('Last-Modified: Fri, 01 Jan 1999 00:00 GMT', true, 200);
            header('Content-Length: ' . Tools::strlen($img));
            header('Content-Type: image/gif');
            $message = $img;
        } else {
            $message = 'OK';
        }
        $this->context->smarty->assign([
            'message' => $message,
        ]);
    }

    public function assign()
    {
        if (version_compare(_PS_VERSION_, '1.7.0', '>=') === true) {
            $this->setTemplate(
                'module:giftcard/views/templates/front/cron.tpl'
            );
        } else {
            $this->setTemplate('cron.tpl');
        }
    }
}
