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

require_once _PS_MODULE_DIR_ . 'opartstat/classes/HTMLTemplateOpartStatPdf.php';

class AdminOpartStatExportToPdfController extends ModuleAdminController
{
    private $pdfContent = '';
    private $currentColumn;

    public function ajaxProcessAddElementToPDF()
    {
        $html = Tools::getValue('html');
        $columnSpan = Tools::getValue('columnSpan');
        $rowSpan = Tools::getValue('rowSpan');
        $pdfId = Tools::getValue('pdfId');

        $html = $this->cleanHtmlForPdf($html);

        if (!$pdfId) {
            $pdfId = uniqid('pdf_');
            $this->pdfContent = '<table style="width:100%; border-collapse: collapse; background-color:#f0f1f3;"><tr>';
            $this->currentColumn = 0;
        
        } else {
            $cachePath = _PS_MODULE_DIR_ . $this->module->name . '/cache/' . $pdfId . '.html';
            $this->pdfContent = file_get_contents($cachePath);
            $this->currentColumn = (int)file_get_contents(_PS_MODULE_DIR_ . $this->module->name . '/cache/' . $pdfId . '_column.txt');
        }

        if ($this->currentColumn + $columnSpan > 4) {
            $this->pdfContent .= '</tr><tr>';
            $this->currentColumn = 0;
        }
        
        $this->pdfContent .= "<td style='padding:5px; vertical-align:top;' colspan='{$columnSpan}'>{$html}</td>";
        $this->currentColumn += $columnSpan;

        $cacheDir = _PS_MODULE_DIR_ . $this->module->name . '/cache/';

        $cachePath = $cacheDir . $pdfId . '.html';
        file_put_contents($cachePath, $this->pdfContent);
        file_put_contents($cacheDir . $pdfId . '_column.txt', $this->currentColumn);


        die(json_encode(['pdfId' => $pdfId]));
    }

    public function ajaxProcessFinalizePDF()
    {
        $pdfId = Tools::getValue('pdfId');
        
        //$pdfId = 'pdf_66c72d6ff0c05';

        $this->pdfContent = '';
        $this->pdfContent .= $this->addCssToPdf($this->pdfContent);

        $cachePath = _PS_MODULE_DIR_ . $this->module->name . '/cache/' . $pdfId . '.html';
        $this->pdfContent .= file_get_contents($cachePath);
        //$this->pdfContent .= '</div>';

        // Ajouter les colonnes manquantes si nécessaire        
        $this->currentColumn = (int)file_get_contents(_PS_MODULE_DIR_ . $this->module->name . '/cache/' . $pdfId . '_column.txt');
        if ($this->currentColumn < 4) {
            $missingColumns = 4 - $this->currentColumn;
            for ($i = 0; $i < $missingColumns; $i++) {
                $this->pdfContent .= "<td style='padding:5px; vertical-align:top;'>ha</td>";
            }
        }
        $this->pdfContent .= '</tr></table>';
        die($this->pdfContent);
        $pdf = new PDF($this->pdfContent, 'OpartStatPdf', $this->context->smarty, 'P');
        $pdfRender = $pdf->render(false);

        $pdfFile = 'Rapport_' . date('Y-m-d') . '.pdf';

        $pdfPath = _PS_MODULE_DIR_ . $this->module->name . '/pdf/' . $pdfFile;

        // Assurez-vous que le dossier existe
        if (!is_dir(dirname($pdfPath))) {
            mkdir(dirname($pdfPath), 0755, true);
        }

        // Sauvegarde du PDF
        if (file_put_contents($pdfPath, $pdfRender) === false) {
            throw new PrestaShopException("Impossible d'enregistrer le fichier PDF.");
        }


        $pdfUrl = _PS_BASE_URL_ . __PS_BASE_URI__ . 'modules/' . $this->module->name . '/pdf/' . $pdfFile;
        unset($this->context->cookie->{$pdfId});
        $this->context->cookie->write();

        die(json_encode(['pdfUrl' => $pdfUrl]));
    }

    public function cleanHtmlForPdf($html) {
        $dom = new DOMDocument('1.0', 'UTF-8');

        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');

        $dom->preserveWhiteSpace = false;
        @$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        $elementsToRemoveByCssClass = [
            'osLoader',
            'reloadBtn', 
            'help-box', 
            'material-icons',
            'osListItemTemplate',
            'osShowMoreLink',
            'osShowAllLink',
            'osShowLessLink'
        ];

        foreach ($elementsToRemoveByCssClass as $classe) {
            $elements = $xpath->query("//*[contains(@class, '$classe')]");
            foreach ($elements as $element) {
                $element->parentNode->removeChild($element);
            }
        }

        $inputsHidden = $xpath->query("//input[@type='hidden']");
        foreach ($inputsHidden as $input) {
            $input->parentNode->removeChild($input);
        }

        $cleanedHtml = $dom->saveHTML();
        return html_entity_decode($cleanedHtml, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    
    }

    private function addCssToPdf($html)
    {
        $cssPath = _PS_MODULE_DIR_ . 'opartstat/views/css/pdf.css';
        if (file_exists($cssPath)) {
            $css = file_get_contents($cssPath);
            return '<style>' . $css . '</style>' . $html;
        }
        return $html;
    }
}
