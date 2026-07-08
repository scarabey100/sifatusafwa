<?php
/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <contact@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}


class HTMLTemplateOpartStatPdf extends HTMLTemplate
{

    public $html;
    private $css;

    public function __construct($html, Smarty $smarty)
    {
        $this->html = $html;
        $this->smarty = $smarty;
        $this->title = 'OpartStat Report';
        $this->date = date('Y-m-d');
        $this->shop = new Shop(Context::getContext()->shop->id);

        $this->css = $this->loadCSS();
    }

    private function loadCSS()
    {
        $cssPath = _PS_MODULE_DIR_ . 'opartstat/views/css/admin.css';
        if (file_exists($cssPath)) {
            return file_get_contents($cssPath);
        }
        return '';
    }

    public function getFooter()
    {
        return '';
    }
   
    public function getHeader()
    {
        return '';
    }
 
    public function getContent()
    {
        return $this->html;
    }

     public function getFilename()
    {
        return 'opartStat-' . date('Y-m-d') . '.pdf';
    }

    public function getBulkFilename()
    {
        return 'opartStat-' . date('Y-m-d') . '.pdf';
    }
}
