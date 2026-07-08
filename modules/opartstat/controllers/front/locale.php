<?php
/**
 * Prestashop module : OpartStat
 *
 * Ce contrôleur sert les fichiers de locale ApexCharts (fr.json, en.json, etc.)
 * en tant que JSON public, sans exposer directement le dossier /modules/.
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class OpartstatLocaleModuleFrontController extends ModuleFrontController
{
    // On veut juste renvoyer du JSON, pas tout le header/backoffice
    public $ssl = true; // mets false si ton back-office de stats tourne en http sur ton env dev
    public $display_header = false;
    public $display_footer = false;

    public function initContent()
    {
        parent::initContent();

        // Locale demandée via ?lang=fr
        $lang = Tools::getValue('lang', 'fr');

        // Construire le chemin du fichier JSON
        $filePath = _PS_MODULE_DIR_
            . 'opartstat/views/js/apexcharts.js/dist/locales/'
            . $lang
            . '.json';

        if (!file_exists($filePath)) {
            header('HTTP/1.1 404 Not Found');
            die(json_encode(array(
                'error' => true,
                'message' => 'Locale file not found'
            )));
        }

        // Envoyer le JSON brut
        header('Content-Type: application/json; charset=utf-8');

        // ajaxRender évite que PrestaShop essaie de coller du template autour
        $this->ajaxRender(file_get_contents($filePath));
        exit;
    }
}
