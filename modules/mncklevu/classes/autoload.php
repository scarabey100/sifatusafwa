<?php
/**
 * License
 * @author mnemonic88uk
 * @copyright 2024 mnemonic88uk
 * @license https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

spl_autoload_register(function ($className) {
    $dir = dirname(__FILE__);

    $file = $dir . DIRECTORY_SEPARATOR . str_replace(
        ['MncKlevu\\', 'PrestaShop\\', '\\'],
        ['', '', DIRECTORY_SEPARATOR],
        $className
    ) . '.php';

    if (!file_exists($file)) {
        $file = $dir . DIRECTORY_SEPARATOR . $className . '.php';
        if (!file_exists($file)) {
            return;
        }
    }

    require_once($file);
});
