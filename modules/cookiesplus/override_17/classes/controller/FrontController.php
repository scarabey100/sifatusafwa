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

class FrontController extends FrontControllerCore
{
    protected function smartyOutputContent($content)
    {
        $this->context->cookie->write();

        $html = '';

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $theme = _THEME_NAME_;
        } else {
            $theme = $this->context->shop->theme->getName();
        }

        if (is_array($content)) {
            foreach ($content as $tpl) {
                $html .= $this->context->smarty->fetch($tpl, null, $theme . $this->getLayout());
            }
        } else {
            $html = $this->context->smarty->fetch($content, null, $theme . $this->getLayout());
        }

        Hook::exec('actionOutputHTMLBefore', ['html' => &$html]);

        echo trim($html);
    }
}
