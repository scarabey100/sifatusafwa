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

namespace Sendinblue\Services;

if (!defined('_PS_VERSION_')) {
    exit;
}

class WebserviceService
{
    const SENDINBLUE_API_ACCOUNT = 'apiAccount';
    const SENDINBLUE_WEBSERVICE_KEY_DESCRIPTION = 'Sendinblue';

    const METHOD_RESOURCES = [
        'GET' => [
            'groups',
            'products',
            'sendinblueinfo',
            'sendinbluetest',
            'sendinblueproducts',
            'sendinbluecustomers',
            'sendinbluenewsletterrecipients',
            'sendinblueorders',
            'sendinblueordercount',
            'sendinblueconfigs',
            'sendinbluecategorycount',
            'sendinblueproductcount',
            'sendinbluecategories',
            'sendinblueproductsfullsync',
            'sendinbluecustomercount',
        ],
        'POST' => [
            'sendinbluedisconnect',
            'sendinblueunsubscribe',
            'sendinbluesubscribe',
            'sendinbluesendtestmail',
        ],
        'PUT' => [
            'customers',
            'sendinblueconfig',
        ],
    ];

    /**
     * @var \DB
     */
    private $dbInstance;

    /**
     * @var int
     */
    private $shopId;

    public function __construct()
    {
        $this->dbInstance = \Db::getInstance();
        // Support multistore: prefer id_shop from request, fallback to context
        $this->shopId = (int) (\Tools::getValue('id_shop') ?: \Context::getContext()->shop->id);
    }

    /**
     * @return string
     */
    public function generateWebServiceKey()
    {
        $apiKey = \Tools::strtoupper(md5(time()));

        $this->dbInstance->insert('webservice_account', [
            'key' => $apiKey,
            'description' => 'Sendinblue',
            'active' => '1',
        ]);
        $accountId = $this->dbInstance->Insert_ID();

        $this->dbInstance->insert('webservice_account_shop', [
            'id_webservice_account' => $accountId,
            'id_shop' => (int) $this->shopId,
        ]);

        $values = [];

        foreach (self::METHOD_RESOURCES as $method => $resources) {
            foreach ($resources as $resource) {
                $values[] = [
                    'resource' => $resource,
                    'method' => $method,
                    'id_webservice_account' => $accountId,
                ];
            }
        }

        try {
            $this->dbInstance->insert('webservice_permission', $values);

            // Means the above Insert operation had failed
            if ($this->dbInstance->Insert_ID()) {
                \PrestaShopLoggerCore::addLog($this->dbInstance->getMsgError(), ConfigService::ERROR_LEVEL);
                $this->dbInstance->insert('webservice_permission', $values);

                if (!$this->dbInstance->Insert_ID()) {
                    \PrestaShopLoggerCore::addLog($this->dbInstance->getMsgError(), ConfigService::ERROR_LEVEL);
                }
            }
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e->getMessage(), ConfigService::ERROR_LEVEL);
        }
        $configService = new ConfigService();
        $configService->upsertSibConfig(ConfigService::CONFIG_SENDINBLUE_WEBSERVICE_KEY, $apiKey);
        $configService->upsertSibConfig(self::SENDINBLUE_API_ACCOUNT, $accountId);
        \Configuration::updateValue('PS_WEBSERVICE', 1);

        $sapi = php_sapi_name();
        if (strpos($sapi, 'cgi') !== false) {
            \Configuration::updateValue('PS_WEBSERVICE_CGI_HOST', 1);
        }

        return $apiKey;
    }

    public function deleteSendinblueWebserviceKey()
    {
        $keys = $this->dbInstance->executeS(
            sprintf(
                'SELECT * FROM %swebservice_account WHERE description = "%s"',
                _DB_PREFIX_,
                self::SENDINBLUE_WEBSERVICE_KEY_DESCRIPTION
            )
        );
        $accountIds = array_column($keys, 'id_webservice_account');
        try{
            foreach ($accountIds as $id) {

                $this->dbInstance->delete(
                    'webservice_account',
                    sprintf('id_webservice_account = %d', (int) $id)
                );
                $this->dbInstance->delete(
                    'webservice_account_shop',
                    sprintf('id_webservice_account = %d', (int) $id)
                );
                $this->dbInstance->delete(
                    'webservice_permission',
                    sprintf('id_webservice_account = %d', (int) $id)
                );
            }
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog($e->getMessage(), ConfigService::ERROR_LEVEL);
        }
    }

    /**
     * Validate if the provided webservice key is valid and active for Sendinblue
     *
     * @param string $wsKey The webservice key to validate
     * @param int|null $shopId Optional shop ID to validate against (null uses instance shopId)
     * @return bool True if valid and active, false otherwise
     */
    public function validateWebserviceKey($wsKey, $shopId = null)
    {
        if (empty($wsKey)) {
            return false;
        }

        $targetShopId = $shopId !== null ? (int) $shopId : $this->shopId;

        try {
            $result = $this->dbInstance->getRow(
                sprintf(
                    'SELECT wa.active, wa.description 
                    FROM %swebservice_account wa
                    INNER JOIN %swebservice_account_shop was ON wa.id_webservice_account = was.id_webservice_account
                    WHERE wa.key = "%s" 
                    AND wa.description = "%s"
                    AND was.id_shop = %d
                    AND wa.active = 1',
                    _DB_PREFIX_,
                    _DB_PREFIX_,
                    pSQL($wsKey),
                    self::SENDINBLUE_WEBSERVICE_KEY_DESCRIPTION,
                    $targetShopId
                )
            );

            return !empty($result);
        } catch (\Exception $e) {
            \PrestaShopLogger::addLog(
                'WebserviceService::validateWebserviceKey - ' . $e->getMessage(),
                ConfigService::ERROR_LEVEL
            );
            return false;
        }
    }
}
