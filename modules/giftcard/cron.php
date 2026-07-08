<?php
/**
 * GIFT CARD
 *
 * @author    EIRL Timactive De Véra
 * @copyright Copyright (c) TIMACTIVE 2015 -EIRL Timactive De Véra
 * @license   Commercial license
 *
 * @category pricing_promotion
 *
 * @version 1.1.0
 *************************************
 **         GIFT CARD                *
 **          V 1.0.0                 *
 *************************************
 * +
 * + Languages: EN, FR, ES
 * + PS version: 1.5,1.6,1.7
 */
if (!defined('_PS_VERSION_')) {
    exit;
}
ini_set('memory_limit', '600M');
if (function_exists('set_time_limit')) {
    @set_time_limit(1200);
}
include dirname(__FILE__) . '/../../config/config.inc.php';
include dirname(__FILE__) . '/../../init.php';
include dirname(__FILE__) . '/giftcard.php';

/**
 * @Deprecated for future version
 * Backward compatibility with legacy usage.
 */
$giftCard = Module::getInstanceByName('giftcard');
$mvcController = Context::getContext()->link->getModuleLink(
    'giftcard',
    'cron',
    ['token' => Tools::getValue('token')],
    true
);
Tools::redirect($mvcController);
