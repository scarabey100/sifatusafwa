<?php
/**
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
/**
 * PSR-4 autoloader implementation for the MaxMind\DB namespace.
 * First we define the 'mmdb_autoload' function, and then we register
 * it with 'spl_autoload_register' so that PHP knows to use it.
 *
 * @param mixed $class
 */

/**
 * @param string $class
 *                      the name of the class to load
 */
function mmdb_autoload($class)
{
    /*
    * A project-specific mapping between the namespaces and where
    * they're located. By convention, we include the trailing
    * slashes. The one-element array here simply makes things easy
    * to extend in the future if (for example) the test classes
    * begin to use one another.
    */
    $namespace_map = ['MaxMind\\Db\\' => __DIR__ . '/src/MaxMind/Db/'];

    foreach ($namespace_map as $prefix => $dir) {
        /* First swap out the namespace prefix with a directory... */
        $path = str_replace($prefix, $dir, $class);

        /* replace the namespace separator with a directory separator... */
        $path = str_replace('\\', '/', $path);

        /* and finally, add the PHP file extension to the result. */
        $path = $path . '.php';

        /* $path should now contain the path to a PHP file defining $class */
        if (file_exists($path)) {
            include $path;
        }
    }
}

spl_autoload_register('mmdb_autoload');
