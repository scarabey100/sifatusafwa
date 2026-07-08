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
if (!defined('_PS_VERSION_')) { exit; }

require_once __DIR__ . '/traits/EtsGeoTranslationTrait.php';

/**
 * Class EtsGeoBackwardCompatibilityHelper
 * @package ETS Geolocation
 */
class EtsGeoBackwardCompatibilityHelper
{
    use EtsGeoGetInstanceTrait;

    /**
     * Helper displaying warning message(s).
     *
     * @param string|array $warning
     *
     * @return string
     */
    public function displayWarning($warning)
    {
        // DON NOT REMOVE COMMENT LINE BELLOW
        // phpcs:disable
        $output = '
        <'.'div class="bootstrap"'.'>
        <'.'div class="module_warning alert alert-warning" '.'>
            <'.'button type="button" class="close" data-dismiss="alert"'.'>'.'&times;'.'<'.'/button'.'>';

        if (is_array($warning)) {
            $output .= '<'.'ul'.'>';
            foreach ($warning as $msg) {
                $output .= '<'.'li'.'>' . $msg . '<'.'/li'.'>';
            }
            $output .= '<'.'/ul'.'>';
        } else {
            $output .= $warning;
        }

        // Close div openned previously
        $output .= '<'.'/div'.'><'.'/div'.'>';

        return $output;
        // phpcs:enable
    }
}