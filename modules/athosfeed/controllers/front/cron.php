<?php

use AthosFeed\Configuration\ModuleConfiguration;
use AthosFeed\Export\ExportManager;
use AthosFeed\Security\RequestAuthorizer;

class AthosFeedCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $shopId = (int) Tools::getValue('shop', $this->context->shop->id);
        if (ModuleConfiguration::get(ModuleConfiguration::MODE, $shopId, 'disabled') === 'disabled') {
            http_response_code(409);
            exit('Athos feed is disabled');
        }
        $expected = (string) ModuleConfiguration::get(ModuleConfiguration::CRON_TOKEN, $shopId, '');
        if (!(new RequestAuthorizer())->authorize($expected, (string) Tools::getValue('token', ''), isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '')) {
            http_response_code(403);
            exit('Forbidden');
        }
        try {
            $stats = (new ExportManager(dirname(__DIR__, 2)))->export($shopId, (int) Tools::getValue('language', $this->context->language->id));
            header('Content-Type: application/json');
            exit(json_encode($stats));
        } catch (Throwable $exception) {
            http_response_code(500);
            exit('Export failed; see PrestaShop logs');
        }
    }
}
