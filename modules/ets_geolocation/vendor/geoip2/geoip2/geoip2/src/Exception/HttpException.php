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

namespace GeoIp2\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 *  This class represents an HTTP transport error.
 */
class HttpException extends GeoIp2Exception
{
    /**
     * The URI queried.
     */
    public $uri;

    public function __construct(
        $message,
        $httpStatus,
        $uri,
        \Exception $previous = null
    ) {
        $this->uri = $uri;
        parent::__construct($message, $httpStatus, $previous);
    }
}
