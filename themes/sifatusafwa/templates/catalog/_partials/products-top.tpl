{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
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
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
<div id="js-product-list-top" class="products-selection">
    <div class="total-products products-total">
        {if $listing.pagination.total_items > 1}
            <p>{l s='There are %product_count% products.' d='Shop.Theme.Catalog' sprintf=['%product_count%' => $listing.pagination.total_items]}</p>
        {elseif $listing.pagination.total_items > 0}
            <p>{l s='There is 1 product.' d='Shop.Theme.Catalog'}</p>
        {/if}
    </div>
    <div class="products-sort">
        <div class="sort-by-row">
            {block name='sort_by'}
                {include file='catalog/_partials/sort-orders.tpl' sort_orders=$listing.sort_orders}
            {/block}
            {if !empty($listing.rendered_facets)}
                <div class="filter-button__open">
                    <button class="btn">
                        <svg xmlns="http://www.w3.org/2000/svg" width="25.496" height="22.664" viewBox="140 694.668 25.496 22.664">
                            <path d="M148.367 694.668a68.145 68.145 0 0 0 .264 0c.522 0 1.035-.001 1.492.121a3.541 3.541 0 0 1 2.504 2.504c.123.457.122.97.121 1.493v.132h11.332a1.416 1.416 0 1 1 0 2.833h-11.332v.132c.001.522.002 1.035-.12 1.493a3.54 3.54 0 0 1-2.505 2.503c-.457.123-.97.122-1.492.121h-.264c-.522.001-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.504c-.122-.458-.122-.971-.12-1.493v-.132h-2.834a1.416 1.416 0 0 1 0-2.833h2.833v-.132c0-.522-.001-1.036.121-1.493a3.541 3.541 0 0 1 2.504-2.504c.457-.122.97-.122 1.493-.12Zm-.578 2.841c-.152.007-.187.018-.182.017a.708.708 0 0 0-.5.5c-.002.005-.011.049-.017.182-.007.158-.008.368-.008.71v2.833c0 .342 0 .551.008.71.007.151.018.187.016.181a.708.708 0 0 0 .501.501c-.005-.001.03.01.182.017.158.007.368.007.71.007.342 0 .551 0 .71-.007.151-.007.187-.018.181-.017a.708.708 0 0 0 .501-.5c-.001.005.01-.03.017-.182.007-.159.007-.368.007-.71v-2.833c0-.342 0-.552-.007-.71-.007-.151-.018-.187-.017-.182m-2.102-.517c.158-.007.368-.008.71-.008l-.71.008Zm.71-.008c.342 0 .551 0 .71.008l-.71-.008Zm.71.008c.133.006.176.015.181.017l-.181-.017Zm.182.017Zm7.475 8.474h.263c.523 0 1.036-.002 1.493.12a3.541 3.541 0 0 1 2.504 2.505c.123.457.122.97.121 1.493v.132h2.833a1.416 1.416 0 1 1 0 2.832h-2.833v.132c0 .523.002 1.036-.12 1.493a3.54 3.54 0 0 1-2.505 2.504c-.457.123-.97.122-1.493.121h-.263c-.523 0-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.505c-.123-.457-.122-.97-.121-1.493v-.132h-11.332a1.416 1.416 0 1 1 0-2.832h11.332v-.132c0-.523-.002-1.036.12-1.493a3.541 3.541 0 0 1 2.505-2.504c.457-.123.97-.122 1.493-.121Zm-.579 2.84c-.15.008-.186.019-.181.017a.709.709 0 0 0-.5.501c0-.005-.01.03-.017.181-.008.159-.008.368-.008.71v2.833c0 .343 0 .552.008.71.007.152.018.187.016.182a.708.708 0 0 0 .501.5c-.005 0 .03.01.181.017.159.008.368.008.71.008.343 0 .552 0 .71-.008.152-.007.187-.018.182-.016a.708.708 0 0 0 .5-.501c0 .005.01-.03.017-.181.008-.159.008-.368.008-.71v-2.833c0-.343 0-.552-.008-.71-.006-.152-.018-.187-.016-.182a.709.709 0 0 0-.5-.5c.004 0-.031-.01-.182-.017a17.328 17.328 0 0 0-.71-.008c-.343 0-.552 0-.71.008Z" fill="#0f1729" fill-rule="evenodd" data-name="filter-edit-svgrepo-com"></path>
                        </svg>
                        {l s='Filter' d='Shop.Theme.Actions'}
                    </button>
                </div>
            {/if}
        </div>
    </div>
</div>
