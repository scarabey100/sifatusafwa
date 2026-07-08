<?php
/**
 * NOTICE OF LICENSE
 *  _____ _            ___       _   _
 * |_   _(_)          / _ \     | | (_)
 *   | |  _ _ __ ___ / /_\ \ ___| |_ ___   _____
 *   | | | | '_ ` _ \|  _  |/ __| __| \ \ / / _ \
 *   | | | | | | | | | | | | (__| |_| |\ V /  __/
 *   \_/ |_|_| |_| |_\_| |_/\___|\__|_| \_/ \___|
 *
 * This source file is subject to a commercial license from TimActive Siret 750 571 366 00046
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the TimActive EIRL is strictly forbidden.
 *
 * @author    TimActive
 * @copyright Since 2012 TimActive
 * @license   Commercial license
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

$sql = [];
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardproduct`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardorder`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_shop`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_lang`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardtag`;';
$sql[] = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'giftcardtemplate_tag`;';
