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

function upgrade_module_3_1_21($module)
{
   if (is_dir(_PS_MODULE_DIR_.'opartstat/config/boards/customs'))
        copyDirContent(_PS_MODULE_DIR_.'opartstat/config/boards/customs',_PS_MODULE_DIR_.'opartstat/config/reports/customs');

    $dirToDelete = [
        _PS_MODULE_DIR_.'opartstat/config/reports/partnersModules',
        _PS_MODULE_DIR_.'opartstat/config/boards',
        _PS_MODULE_DIR_.'opartstat/views/templates/admin/reports'
    ];
    foreach($dirToDelete as $dirPath) {
        if (is_dir($dirPath)) {
            rrmdir($dirPath);
        }
    }

    $fileToDelete = [
        _PS_MODULE_DIR_.'opartstat/views/templates/admin/partial/addReportsMenu.tpl',
        _PS_MODULE_DIR_.'opartstat/views/templates/admin/partial/reportsMenu.tpl'        
    ];
    foreach($fileToDelete as $filePath) {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }    

    deleteAllSubdirectories(_PS_MODULE_DIR_.'opartstat/config/reports/default');
    return true;
}

function rrmdir($dir) {
    if (is_dir($dir)) {
        $objects = scandir($dir);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dir."/".$object))
                    rrmdir($dir."/".$object);
                else
                    unlink($dir."/".$object);
            }
        }
        rmdir($dir);
    }
}

function deleteAllSubdirectories($dirPath) {
    if (is_dir($dirPath)) {
        $objects = scandir($dirPath);
        foreach ($objects as $object) {
            if ($object != "." && $object != "..") {
                if (is_dir($dirPath."/".$object)) {
                    rrmdir($dirPath."/".$object);
                }
            }
        }
    }
}

function copyDirContent($src, $dst) {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
        if (( $file != '.' ) && ( $file != '..' )) {
            if ( is_dir($src . '/' . $file) ) {
                copyDirContent($src . '/' . $file,$dst . '/' . $file);
            }
            else {
                copy($src . '/' . $file,$dst . '/' . $file);
            }
        }
    }
    closedir($dir);
}