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

declare(strict_types=1);

namespace GeoIp2\Exception;

if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * This class represents an error returned by MaxMind's GeoIP2
 * web service.
 */
class InvalidRequestException extends HttpException
{
    /**
     * The code returned by the MaxMind web service.
     *
     * @var string
     */
    public $error;

    public function __construct(
        string $message,
        string $error,
        int $httpStatus,
        string $uri,
        \Exception $previous = null
    ) {
        $this->error = $error;
        parent::__construct($message, $httpStatus, $uri, $previous);
    }
}
