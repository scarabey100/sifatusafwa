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

namespace MaxMind\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 *  This class represents an HTTP transport error.
 */
class HttpException extends WebServiceException
{
    /**
     * The URI queried.
     */
    private $uri;

    /**
     * @param string $message a message describing the error
     * @param int $httpStatus the HTTP status code of the response
     * @param string $uri the URI used in the request
     * @param \Exception $previous the previous exception, if any
     */
    public function __construct(
        $message,
        $httpStatus,
        $uri,
        \Exception $previous = null
    ) {
        $this->uri = $uri;
        parent::__construct($message, $httpStatus, $previous);
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getStatusCode()
    {
        return $this->getCode();
    }
}
