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

namespace MaxMind\WebService\Http;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * Class RequestFactory.
 *
 * @internal
 */
class RequestFactory
{
    /**
     * Keep the cURL resource here, so that if there are multiple API requests
     * done the connection is kept alive, SSL resumption can be used
     * etcetera.
     *
     * @var resource
     */
    private $ch;

    public function __destruct()
    {
        if (!empty($this->ch)) {
            curl_close($this->ch);
        }
    }

    private function getCurlHandle()
    {
        if (empty($this->ch)) {
            $this->ch = curl_init();
        }

        return $this->ch;
    }

    /**
     * @param string $url
     * @param array $options
     *
     * @return Request
     */
    public function request($url, $options)
    {
        $options['curlHandle'] = $this->getCurlHandle();

        return new CurlRequest($url, $options);
    }
}
