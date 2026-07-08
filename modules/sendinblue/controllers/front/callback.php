<?php
/**
 * 2007-2025 Sendinblue
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@sendinblue.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Sendinblue <contact@sendinblue.com>
 * @copyright 2007-2025 Sendinblue
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registered Trademark & Property of Sendinblue
 */

use Sendinblue\Services\ConfigService;
use Sendinblue\Services\WebserviceService;

require_once dirname(__FILE__) . '/../../vendor/autoload.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class SendinblueCallbackModuleFrontController extends ModuleFrontController
{
    const CALLBACK_PARAMS = [
        ConfigService::CONFIG_USER_CONNECTION_ID => 'user_connection_id',
        ConfigService::API_KEY_V3 => 'api_key',
    ];

    /**
     * @var ConfigService
     */
    private $configService;

    /**
     * @var WebserviceService
     */
    private $webserviceService;

    /**
     * @var bool
     */
    private $isAuthenticated = false;

    public function __construct()
    {
        parent::__construct();
        $this->configService = new ConfigService();
        $this->webserviceService = new WebserviceService();
    }

    public function init()
    {
        parent::init();
        header('Content-Type: application/json; charset=utf-8');

        // Validate webservice key authentication
        if (!$this->validateAuthentication()) {
            $this->sendUnauthorizedResponse();
        }
    }

    /**
     * Validate webservice key authentication
     *
     * @return bool
     */
    private function validateAuthentication()
    {
        $wsKey = Tools::getValue('ws_key');

        if (empty($wsKey)) {
            PrestaShopLogger::addLog(
                'Callback authentication failed: Missing key parameter',
                ConfigService::ERROR_LEVEL
            );
            return false;
        }

        // Validate against the same shop ID that ConfigService will use for updates
        $shopId = (int) (Tools::getValue('id_shop') ?: Context::getContext()->shop->id);
        $this->isAuthenticated = $this->webserviceService->validateWebserviceKey($wsKey, $shopId);

        if (!$this->isAuthenticated) {
            PrestaShopLogger::addLog(
                sprintf(
                    'Callback authentication failed: Invalid key for shop %d',
                    $shopId
                ),
                ConfigService::ERROR_LEVEL
            );
        }

        return $this->isAuthenticated;
    }

    public function initContent()
    {
        if (!$this->isAuthenticated) {
            return;
        }

        $hasUpdates = false;

        foreach (self::CALLBACK_PARAMS as $key => $param) {
            if (Tools::getIsset($param)) {
                $value = Tools::getValue($param);

                if (empty($value)) {
                    PrestaShopLogger::addLog(
                        sprintf('Callback received empty value for parameter: %s', $param),
                        ConfigService::ERROR_LEVEL
                    );
                    continue;
                }

                $this->configService->upsertSibConfig($key, $value);
                $hasUpdates = true;

                PrestaShopLogger::addLog(
                    sprintf('Callback successfully updated configuration: %s', $key),
                    1
                );
            }
        }

        if (!$hasUpdates) {
            PrestaShopLogger::addLog(
                'Callback called but no valid parameters provided',
                2
            );
            $this->sendErrorResponse('No valid parameters provided');
            return;
        }
    }

    /**
     * Send unauthorized response
     */
    private function sendUnauthorizedResponse()
    {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'error' => 'Unauthorized',
            'message' => 'Valid webservice key is required'
        ]);
        exit;
    }

    /**
     * Send error response
     *
     * @param string $message
     */
    private function sendErrorResponse($message)
    {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Bad Request',
            'message' => $message
        ]);
        exit;
    }

    public function display(): void
    {
        if ($this->isAuthenticated) {
            echo json_encode(['success' => true]);
        }
    }
}
