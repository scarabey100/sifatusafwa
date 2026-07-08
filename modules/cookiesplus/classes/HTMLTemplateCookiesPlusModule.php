<?php
/**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class HTMLTemplateCookiesPlusModule extends HTMLTemplate
{
    /**
     * @var array
     */
    public $cookiesPlusData;

    /**
     * @var Context
     */
    public $context;

    /**
     * @param array $cookiesPlusData
     * @param Smarty $smarty
     *
     * @throws PrestaShopException
     */
    public function __construct($cookiesPlusData, Smarty $smarty)
    {
        $this->cookiesPlusData = $cookiesPlusData;
        $this->smarty = $smarty;
        $this->context = Context::getContext();

        $this->shop = new Shop((int) Context::getContext()->shop->id);
    }

    /**
     * Returns the template's HTML footer
     *
     * @return string HTML footer
     *
     * @throws SmartyException
     */
    public function getFooter()
    {
        $shop_address = $this->getShopAddress();
        $this->smarty->assign([
            'available_in_your_account' => false,
            'shop_address' => $shop_address,
            'shop_fax' => Configuration::get('PS_SHOP_FAX'),
            'shop_phone' => Configuration::get('PS_SHOP_PHONE'),
            'shop_details' => Configuration::get('PS_SHOP_DETAILS'),
            'free_text' => '',
        ]);

        return $this->smarty->fetch($this->getTemplate('footer'));
    }

    /**
     * Returns the template's HTML content
     *
     * @return string HTML content
     *
     * @throws SmartyException
     */
    public function getContent()
    {
        // Generate smarty data
        $this->smarty->assign([
            'info' => $this->cookiesPlusData['info'],
            'finalities' => $this->cookiesPlusData['cookiesPlusFinalities'],
        ]);

        // Generate templates after, to be able to reuse data above
        $this->smarty->assign([
            'style' => $this->smarty->fetch($this->getCookiesPlusTemplate('style')),
            'info' => $this->smarty->fetch($this->getCookiesPlusTemplate('info')),
            'finalities' => $this->smarty->fetch($this->getCookiesPlusTemplate('finalities')),
        ]);

        return $this->smarty->fetch($this->getCookiesPlusTemplate('consent'));
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getFilename()
    {
        return _PS_MODULE_DIR_ . 'cookiesplus/consent/' . $this->cookiesPlusData['info']['consent_hash'] . '.pdf';
    }

    /**
     * Returns the template filename
     *
     * @return string filename
     */
    public function getBulkFilename()
    {
        return _PS_MODULE_DIR_ . 'cookiesplus/consent/' . $this->cookiesPlusData['info']['consent_hash'] . '.pdf';
    }

    /**
     * If the template is not present in the theme directory, it will return the default template
     * in _PS_PDF_DIR_ directory
     *
     * @param string $template_name
     *
     * @return string
     */
    protected function getCookiesPlusTemplate($template_name)
    {
        return _PS_MODULE_DIR_ . 'cookiesplus/views/templates/front/pdf/' . $template_name . '.tpl';
    }
}
