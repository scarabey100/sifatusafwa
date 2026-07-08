<?php
/**
 * Advanced Pack 5
 *
 * @author    Presta-Module.com <support@presta-module.com> - https://www.presta-module.com
 * @copyright Presta-Module - https://www.presta-module.com
 * @license   see file: LICENSE.txt
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class Ps_EmailAlertsOverride extends Ps_EmailAlerts
{
    public function hookActionUpdateQuantity($params)
    {
        // We do not have to care about pack email alerts
        if (isset($params['id_product']) && class_exists('AdvancedPack') && AdvancedPack::isValidPack((int)$params['id_product'])) {
            return;
        }

        // Run native process
        parent::hookActionUpdateQuantity($params);
    }

    public function hookDisplayProductAdditionalInfo($params)
    {
        // We do not have to care about pack while trying to display the template
        if (isset($params['product'], $params['product']['id']) && class_exists('AdvancedPack') && AdvancedPack::isValidPack((int)$params['product']['id'])) {
            return;
        }

        // Run native process
        return parent::hookDisplayProductAdditionalInfo($params);
    }
}
