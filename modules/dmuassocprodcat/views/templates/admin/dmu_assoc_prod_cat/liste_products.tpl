{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL DREAM ME UP
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL DREAM ME UP is strictly forbidden.
*
*   .--.
*   |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
*   |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
*   '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
*        w w w . d r e a m - m e - u p . f r       '
*
*  @author    Dream me up <prestashop@dream-me-up.fr>
*  @copyright 2007 - 2025 Dream me up
*  @license   All Rights Reserved
*}

{if (!empty($id_manufacturer) || !empty($id_categorie) || !empty($r))}
    {if !empty($products)}
        <table class="table" width="100%">
            <tr>
                <td width="20">
                    <input type="checkbox" id="chk_all_prods" onclick="chk_all(this, 'chk_produits')" />
                </td>
                <td>
                    <label for="chk_all_prods" style="width:150px;">
                        <em>{l s='Select all' mod='dmuassocprodcat'}</em>
                    </label>
                </td>
            </tr>
        </table>
        <table class="table" width="100%">
            {foreach from=$products item=product}
                <tr {if $product.class} class="{$product.class|escape:'htmlall':'UTF-8'}" {/if}>
                    <td width="20">
                        {if $product.warning}
                            <img src="/img/admin/error.png" width="16" height="16" border="0" alt="{l s='This product is already in this category !' mod='dmuassocprodcat'}" title="{l s='This product is already in this category !' mod='dmuassocprodcat'}" style="opacity: .2; cursor: help;" />
                        {else}
                            <input type="checkbox" name="chk_produits" value="{$product.id_product|escape:'htmlall':'UTF-8'}" id="chk_produits_{$product.id_product|escape:'htmlall':'UTF-8'}"/>
                        {/if}
                    </td>
                    <td style="position:relative;">
                        {if isset($product.img_src)}
                            <div 
                            id="popup_produits_{$product.id_product|escape:'htmlall':'UTF-8'}" 
                            class="popup_produits" 
                            style="top:-{$product.img_top|escape:'htmlall':'UTF-8'}px">
                                <img src="{$product.img_src|escape:'htmlall':'UTF-8'}" alt="" style="width:100px;"/>
                            </div>
                        {/if}
                        <label 
                            for="chk_produits_{$product.id_product|escape:'htmlall':'UTF-8'}" 
                            ref="{$product.id_product|escape:'htmlall':'UTF-8'}" 
                            class="productName">{$product.reference|escape:'htmlall':'UTF-8'} - {$product.name|escape:'htmlall':'UTF-8'}<label>
                    </td>
                    <td width="25">
                        {if $product.id_category_default == $id_categorie}
                            <a class="btn btn-default btn-block" 
                                href="javascript:void(0);" 
                                title="{l s='Default Category' mod='dmuassocprodcat'}" 
                                style="cursor:default;">&nbsp;<i class="icon-check"></i>&nbsp;</a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
        </table>
    {else}
        <div class="alert alert-warning">
            <p class="message_info">{l s='No products found.' mod='dmuassocprodcat'}</p>
        </div>
    {/if}
{else}
    <div class="alert alert-info">
        <p class="message_info">{l s='Use search field to find products in categories.' mod='dmuassocprodcat'}</p>
    </div>
{/if}