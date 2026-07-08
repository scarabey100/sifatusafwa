{**
* 2008-2024 Prestaworld
*
* NOTICE OF LICENSE
*
* The source code of this module is under a commercial license.
* Each license is unique and can be installed and used on only one website.
* Any reproduction or representation total or partial of the module, one or more of its components,
* by any means whatsoever, without express permission from us is prohibited.
*
* DISCLAIMER
*
* Do not alter or add/update to this file if you wish to upgrade this module to newer
* versions in the future.
*
* @author    prestaworld
* @copyright 2008-2024 Prestaworld
* @license https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
* International Registered Trademark & Property of prestaworld
*}

{if isset($cart)}
    {foreach $cart.products as $product}
        <tr>
            <td style="border:1px solid #d6d4d4; text-align: center;">
                <table class="table"  style="width: 100%;">
                    <tbody>
                        <tr>
                            <td>
                                <font size="2" face="Open-sans, sans-serif" color="#555454">
                                    <strong>{$product.name|escape:'htmlall':'UTF-8'}</strong><br>
                                </font>
                            </td>
                            <td width="20" style="color:#333;padding:0">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="border:1px solid #d6d4d4; text-align: center;">
                <table class="table" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td align="right">
                                <font size="2" face="Open-sans, sans-serif" color="#555454">
                                    {$product.regular_price|escape:'htmlall':'UTF-8'}
                                </font>
                            </td>
                            <td width="20" style="color:#333;padding:0">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="border:1px solid #d6d4d4; text-align: center;">
                <table class="table" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td align="right">
                                <font size="2" face="Open-sans, sans-serif" color="#555454">
                                    {$product.cart_quantity|escape:'htmlall':'UTF-8'}
                                </font>
                            </td>
                            <td width="20" style="color:#333;padding:0">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="border:1px solid #d6d4d4; text-align: center;">
                <table class="table" style="width: 100%;">
                    <tbody>
                        <tr>
                            <td align="right">
                                <font size="2" face="Open-sans, sans-serif" color="#555454">
                                    {$product.total|escape:'htmlall':'UTF-8'}
                                </font>
                            </td>
                            <td width="20" style="color:#333;padding:0">&nbsp;</td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    {/foreach}
        <tr class="">
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                Products Total
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="4" face="Open-sans, sans-serif" color="#555454">
                                {$cart.subtotals.products.value|escape:'htmlall':'UTF-8'}<br>
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
        </tr>
        <tr class="">
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                Tax
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class=table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="4" face="Open-sans, sans-serif" color="#555454">
                                {$cart.subtotals.tax.value|escape:'htmlall':'UTF-8'}
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
        </tr>
        <tr class="">
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                Shipping
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="4" face="Open-sans, sans-serif" color="#555454">
                                {$cart.subtotals.shipping.value|escape:'htmlall':'UTF-8'}
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
        </tr>
        <tr class="">
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="2" face="Open-sans, sans-serif" color="#555454">
                                Total
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
            <td bgcolor="#f8f8f8" colspan="2" style="border:1px solid #d6d4d4;color:#333;padding:7px 0">
                <table class="table" style="width:100%;border-collapse:collapse">
                    <tbody><tr>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                        <td align="right" style="color:#333;padding:0">
                            <font size="4" face="Open-sans, sans-serif" color="#555454">
                                {$cart.totals.total.value|escape:'htmlall':'UTF-8'}
                            </font>
                        </td>
                        <td width="10" style="color:#333;padding:0">&nbsp;</td>
                    </tr>
                </tbody></table>
            </td>
        </tr>
{/if}
