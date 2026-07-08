<?php
/**
 * 2007-2017 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2017 PrestaShop SA
 * @license   http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

abstract class BmsShippingLabelTemplate
{

    abstract protected function createExportFiles($orders = array(), $config = array());

    abstract protected function importTrackingFiles($file, $config = array());

    protected function getFileDelimiter($file, $checkLines = 2)
    {
        $file = new SplFileObject($file);
        $delimiters = array(
            ',',
            '\t',
            ';',
            '|',
            ':'
        );
        $results = array();
        $i = 0;
        while ($file->valid() && $i <= $checkLines) {
            $line = $file->fgets();
            foreach ($delimiters as $delimiter) {
                $regExp = '/[' . $delimiter . ']/';
                $fields = preg_split($regExp, $line);
                if (count($fields) > 1) {
                    if (! empty($results[$delimiter])) {
                        $results[$delimiter] ++;
                    } else {
                        $results[$delimiter] = 1;
                    }
                }
            }
            $i ++;
        }
        $results = array_keys($results, max($results));
        return $results[0];
    }
}
