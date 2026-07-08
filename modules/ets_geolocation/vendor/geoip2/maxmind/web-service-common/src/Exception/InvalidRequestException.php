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
 * Thrown when a MaxMind web service returns an error relating to the request.
 */
class InvalidRequestException extends HttpException
{
    /**
     * The code returned by the MaxMind web service.
     */
    private $error;

    /**
     * @param string $message the exception message
     * @param int $error the error code returned by the MaxMind web service
     * @param int $httpStatus the HTTP status code of the response
     * @param string $uri the URI queries
     * @param \Exception $previous the previous exception, if any
     */
    public function __construct(
        $message,
        $error,
        $httpStatus,
        $uri,
        \Exception $previous = null
    ) {
        $this->error = $error;
        parent::__construct($message, $httpStatus, $uri, $previous);
    }

    public function getErrorCode()
    {
        return $this->error;
    }
}
