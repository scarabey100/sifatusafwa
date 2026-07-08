{**
 * 2007-2022 Boostmyshop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
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
 * @copyright 2007-2022 Boostmyshop
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<table border="0" width="100%">
    <tr>
        <td>
            <input
                    type="text"
                    id="bin_location_{$id_product|escape:'htmlall':'UTF-8'}_{$id_attribute|escape:'htmlall':'UTF-8'}"
                    data-product-id="{$id_product|escape:'htmlall':'UTF-8'}"
                    data-attribute-id="{$id_attribute|escape:'htmlall':'UTF-8'}"
                    data-field="bin_location"
                    onchange="saveBinLocation(this);"
                    value="{$location|escape:'htmlall':'UTF-8'}"
                    size="5"
                    >
        </td>
        <td width="25px">
            <img src="" style="display: none; height: 20px; width: 20px;" id="img_bin_location_{$id_product|escape:'htmlall':'UTF-8'}_{$id_attribute|escape:'htmlall':'UTF-8'}">
        </td>
    </tr>
</table>


