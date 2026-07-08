<?php

/**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <manit4c@gmail.com>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_4_2_6($module)
{
    $res = true;
    $customReportsPath = _PS_MODULE_DIR_ . 'opartstat/config/reports/customs';
    
    if (is_dir($customReportsPath)) {
        $directories = glob($customReportsPath . '/*', GLOB_ONLYDIR);
        
        foreach ($directories as $directory) {
            $reportFiles = glob($directory . '/report*.json');
            
            foreach ($reportFiles as $reportFile) {
                $content = file_get_contents($reportFile);
                
                if ($content !== false) {
                    $updatedContent = str_replace('purchaseFrequency', 'averageNumberOfOrdersPerCustomer', $content);
                    
                    if (file_put_contents($reportFile, $updatedContent) === false) {
                        $res = false;
                        break 2;
                    }
                } else {
                    $res = false;
                    break 2;
                }
            }
        }
    }

    return $res;
}