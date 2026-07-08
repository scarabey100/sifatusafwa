<?php
/**
* 2005-2017 Magic Toolbox
*
* NOTICE OF LICENSE
*
* This file is licenced under the Software License Agreement.
* With the purchase or the installation of the software in your application
* you accept the licence agreement.
*
* You must not modify, adapt or create derivative works of this source code
*
*  @author    Magic Toolbox <support@magictoolbox.com>
*  @copyright Copyright (c) 2017 Magic Toolbox <support@magictoolbox.com>. All rights reserved
*  @license   https://www.magictoolbox.com/license/
*/

chdir(dirname(__FILE__).'/../blocklayered');

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../init.php');

/* NOTE: spike for prestashop validator */
if (false) {
    $smarty = $GLOBALS['smarty'];
}

$magiczoomplusInstance = Module::getInstanceByName('magiczoomplus');

if ($magiczoomplusInstance && $magiczoomplusInstance->active) {
    $magiczoomplusTool = $magiczoomplusInstance->loadTool();
    $magiczoomplusFilter = 'parseTemplate'.($magiczoomplusTool->type == 'standard' ? 'Standard' : 'Category');
    if ($magiczoomplusInstance->isSmarty3) {
        /* Smarty v3 template engine */
        $smarty->registerFilter('output', array($magiczoomplusInstance, $magiczoomplusFilter));
    } else {
        /* Smarty v2 template engine */
        $smarty->register_outputfilter(array($magiczoomplusInstance, $magiczoomplusFilter));
    }
    if (!isset($GLOBALS['magictoolbox']['filters'])) {
        $GLOBALS['magictoolbox']['filters'] = array();
    }
    $GLOBALS['magictoolbox']['filters']['magiczoomplus'] = $magiczoomplusFilter;
}

include(dirname(__FILE__).'/../blocklayered/blocklayered.php');

Context::getContext()->controller->php_self = 'category';
$blockLayered = new BlockLayered();
echo $blockLayered->ajaxCall();
