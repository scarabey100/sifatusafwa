<?php

use AthosFeed\Configuration\ModuleConfiguration;
use AthosFeed\Export\ExportManager;
use AthosFeed\Security\RequestAuthorizer;

class AthosFeedFeedModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        $shopId = (int) Tools::getValue('shop', $this->context->shop->id);
        $languageId = (int) Tools::getValue('language', $this->context->language->id);
        if (ModuleConfiguration::get(ModuleConfiguration::MODE, $shopId, 'disabled') === 'disabled') {
            http_response_code(404);
            exit('Feed disabled');
        }
        $expected = (string) ModuleConfiguration::get(ModuleConfiguration::ACCESS_TOKEN, $shopId, '');
        $provided = $this->bearerToken();
        if ($provided === '') {
            $provided = (string) Tools::getValue('token', '');
        }
        $ips = (string) ModuleConfiguration::get(ModuleConfiguration::ALLOWED_IPS, $shopId, '');
        if (!(new RequestAuthorizer())->authorize($expected, $provided, isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', $ips)) {
            http_response_code(403);
            exit('Forbidden');
        }
        $path = (new ExportManager(dirname(__DIR__, 2)))->feedPath($shopId, $languageId);
        if (!is_file($path) || !is_readable($path)) {
            http_response_code(404);
            exit('Feed not generated');
        }
        header('Content-Type: application/x-ndjson; charset=utf-8');
        header('Content-Length: ' . filesize($path));
        header('Content-Disposition: inline; filename="athos-products.ndjson"');
        header('X-Content-Type-Options: nosniff');
        $handle = fopen($path, 'rb');
        while (!feof($handle)) {
            echo fread($handle, 1048576);
            flush();
        }
        fclose($handle);
        exit;
    }

    private function bearerToken()
    {
        $header = isset($_SERVER['HTTP_AUTHORIZATION']) ? trim((string) $_SERVER['HTTP_AUTHORIZATION']) : '';
        return stripos($header, 'Bearer ') === 0 ? trim(substr($header, 7)) : '';
    }

}
