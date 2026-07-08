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
<div class="table-responsive">
    <table class="table" id="orderProducts">
      <thead>
        <tr>
          <th></th>
          <th><span class="title_box ">{l s='Product'  mod='bmsorderpreparation'}</span></th>
          <th class="text-center"><span class="title_box ">{l s='Qty'  mod='bmsorderpreparation'}</span></th>
        </tr>
      </thead>
      <tbody>
	      {foreach from=$products item=product key=k}
	      <tr class="product-line-row">
		        <td>{if isset($product.image) && $product.image->id}{$product.image_tag}{/if}</td>
		        <td>
					<a target='_patent' href="{$product['url']|escape:'html':'UTF-8'}">
						<span class="productName">{$product['product_name']}</span><br />
						{if $product.product_reference}{l s='Reference :'  mod='bmsorderpreparation'} {$product.product_reference}<br />{/if}
					</a>
		
				</td>
		        <td class="productQuantity text-center">
					<span class="product_quantity_show " style="color: {$product['color']}">
                        {$product['product_quantity']}
                    </span>
				</td>
			</tr>	
	      {/foreach}
     
      </tbody>
    </table>
</div>
