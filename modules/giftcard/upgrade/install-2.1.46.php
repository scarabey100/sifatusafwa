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

function upgrade_module_2_1_46($object, $install = false)
{
    $languages = Language::getLanguages(false);
    $picture_l = [];
    foreach ($languages as $language) {
        if (Tools::strtolower($language['iso_code']) == 'fr') {
            $picture_l[$language['id_lang']] = 'Image';
        } elseif (Tools::strtolower($language['iso_code']) == 'es') {
            $picture_l[$language['id_lang']] = 'Image';
        } else {
            $picture_l[$language['id_lang']] = 'Picture';
        }
    }
    Configuration::updateValue('GIFTCARD_CF_IMAGE', $picture_l);

    return true;
}
