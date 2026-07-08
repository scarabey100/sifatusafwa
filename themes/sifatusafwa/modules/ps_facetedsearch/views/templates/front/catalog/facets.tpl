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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}
{if $displayedFacets|count}
    <div id="search_filters">

        {block name='facets_title'}
            <div id="search_filters_top">
                <div id="search_filters_title">
                    <svg xmlns="http://www.w3.org/2000/svg" width="25.496" height="22.664" viewBox="140 694.668 25.496 22.664">
                        <path d="M148.367 694.668a68.145 68.145 0 0 0 .264 0c.522 0 1.035-.001 1.492.121a3.541 3.541 0 0 1 2.504 2.504c.123.457.122.97.121 1.493v.132h11.332a1.416 1.416 0 1 1 0 2.833h-11.332v.132c.001.522.002 1.035-.12 1.493a3.54 3.54 0 0 1-2.505 2.503c-.457.123-.97.122-1.492.121h-.264c-.522.001-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.504c-.122-.458-.122-.971-.12-1.493v-.132h-2.834a1.416 1.416 0 0 1 0-2.833h2.833v-.132c0-.522-.001-1.036.121-1.493a3.541 3.541 0 0 1 2.504-2.504c.457-.122.97-.122 1.493-.12Zm-.578 2.841c-.152.007-.187.018-.182.017a.708.708 0 0 0-.5.5c-.002.005-.011.049-.017.182-.007.158-.008.368-.008.71v2.833c0 .342 0 .551.008.71.007.151.018.187.016.181a.708.708 0 0 0 .501.501c-.005-.001.03.01.182.017.158.007.368.007.71.007.342 0 .551 0 .71-.007.151-.007.187-.018.181-.017a.708.708 0 0 0 .501-.5c-.001.005.01-.03.017-.182.007-.159.007-.368.007-.71v-2.833c0-.342 0-.552-.007-.71-.007-.151-.018-.187-.017-.182m-2.102-.517c.158-.007.368-.008.71-.008l-.71.008Zm.71-.008c.342 0 .551 0 .71.008l-.71-.008Zm.71.008c.133.006.176.015.181.017l-.181-.017Zm.182.017Zm7.475 8.474h.263c.523 0 1.036-.002 1.493.12a3.541 3.541 0 0 1 2.504 2.505c.123.457.122.97.121 1.493v.132h2.833a1.416 1.416 0 1 1 0 2.832h-2.833v.132c0 .523.002 1.036-.12 1.493a3.54 3.54 0 0 1-2.505 2.504c-.457.123-.97.122-1.493.121h-.263c-.523 0-1.036.002-1.493-.12a3.54 3.54 0 0 1-2.504-2.505c-.123-.457-.122-.97-.121-1.493v-.132h-11.332a1.416 1.416 0 1 1 0-2.832h11.332v-.132c0-.523-.002-1.036.12-1.493a3.541 3.541 0 0 1 2.505-2.504c.457-.123.97-.122 1.493-.121Zm-.579 2.84c-.15.008-.186.019-.181.017a.709.709 0 0 0-.5.501c0-.005-.01.03-.017.181-.008.159-.008.368-.008.71v2.833c0 .343 0 .552.008.71.007.152.018.187.016.182a.708.708 0 0 0 .501.5c-.005 0 .03.01.181.017.159.008.368.008.71.008.343 0 .552 0 .71-.008.152-.007.187-.018.182-.016a.708.708 0 0 0 .5-.501c0 .005.01-.03.017-.181.008-.159.008-.368.008-.71v-2.833c0-.343 0-.552-.008-.71-.006-.152-.018-.187-.016-.182a.709.709 0 0 0-.5-.5c.004 0-.031-.01-.182-.017a17.328 17.328 0 0 0-.71-.008c-.343 0-.552 0-.71.008Z" fill="#0f1729" fill-rule="evenodd" data-name="filter-edit-svgrepo-com"/>
                    </svg>
                    {l s='Filter' d='Shop.Theme.Actions'}
                </div>
                {block name='facets_clearall_button'}
                    {if $activeFilters|count}
                        <div id="_desktop_search_filters_clear_all" class="clear-all-wrapper">
                            <button data-search-url="{$clear_all_link}" class="btn btn-tertiary js-search-filters-clear-all">
                                {l s='Reset' d='Shop.Theme.Actions'}
                            </button>
                        </div>
                    {/if}
                {/block}
            </div>
        {/block}

        {foreach from=$displayedFacets item="facet"}
            <section class="facet" data-type="{$facet.type}" data-name="{$facet.label}">
{*                <p class="h6 facet-title hidden-sm-down">{$facet.label}</p>*}
                {assign var=_expand_id value=10|mt_rand:100000}
                {assign var=_collapse value=true}
                {foreach from=$facet.filters item="filter"}
                    {if $filter.active}{assign var=_collapse value=false}{/if}
                {/foreach}

                <div class="facet-title" data-target="#facet_{$_expand_id}" data-toggle="collapse"{if $_collapse} aria-expanded="true"{/if}>
                    {$facet.label}
                    <span class="navbar-toggler collapse-icons">
                        <i class="material-icons add">&#xE313;</i>
                        <i class="material-icons remove">&#xE316;</i>
                    </span>
                </div>

                {if in_array($facet.widgetType, ['radio', 'checkbox'])}
                    {block name='facet_item_other'}
                        <ul id="facet_{$_expand_id}" class="collapse{if $_collapse} in{/if}">
                            {foreach from=$facet.filters key=filter_key item="filter"}
                                {if !$filter.displayed}
                                    {continue}
                                {/if}

                                <li>
                                    <label class="facet-label{if $filter.active} active {/if}" for="facet_input_{$_expand_id}_{$filter_key}">
                                        {if $facet.multipleSelectionAllowed}
                                            <span class="custom-checkbox">
                                                <input
                                                    id="facet_input_{$_expand_id}_{$filter_key}"
                                                    data-search-url="{$filter.nextEncodedFacetsURL}"
                                                    type="checkbox"
                                                    {if $filter.active }checked{/if}
                                                >
                                                {if isset($filter.properties.color)}
                                                    <span class="color" style="background-color:{$filter.properties.color}"></span>
                                                {elseif isset($filter.properties.texture)}
                                                    <span class="color texture" style="background-image:url({$filter.properties.texture})"></span>
                                                {else}
                                                    <span {if !$js_enabled} class="ps-shown-by-js" {/if}><i class="material-icons rtl-no-flip checkbox-checked">&#xE5CA;</i></span>
                                                {/if}
                                            </span>
                                        {else}
                                            <span class="custom-radio">
                                                <input
                                                    id="facet_input_{$_expand_id}_{$filter_key}"
                                                    data-search-url="{$filter.nextEncodedFacetsURL}"
                                                    type="radio"
                                                    name="filter {$facet.label}"
                                                    {if $filter.active }checked{/if}
                                                >
                                                <span {if !$js_enabled} class="ps-shown-by-js" {/if}></span>
                                            </span>
                                        {/if}

                                        <a href="{$filter.nextEncodedFacetsURL}" class="_gray-darker search-link js-search-link" rel="nofollow">
                                            {$filter.label}
                                            {if $filter.magnitude and $show_quantities}
                                                <span class="magnitude">({$filter.magnitude})</span>
                                            {/if}
                                        </a>
                                    </label>
                                </li>
                            {/foreach}
                        </ul>
                    {/block}

                {elseif $facet.widgetType == 'dropdown'}
                    {block name='facet_item_dropdown'}
                        <ul id="facet_{$_expand_id}" class="collapse{if $_collapse} in{/if}">
                            <li>
                                <div class="col-sm-12 col-xs-12 col-md-12 facet-dropdown dropdown">
                                    <a class="select-title" rel="nofollow" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                        {$active_found = false}
                                        <span>
                                            {foreach from=$facet.filters item="filter"}
                                                {if $filter.active}
                                                    {$filter.label}
                                                    {if $filter.magnitude and $show_quantities}
                                                        ({$filter.magnitude})
                                                    {/if}
                                                    {$active_found = true}
                                                {/if}
                                            {/foreach}
                                            {if !$active_found}
                                                {l s='(no filter)' d='Shop.Theme.Global'}
                                            {/if}
                                        </span>
                                        <i class="material-icons float-xs-right">&#xE5C5;</i>
                                    </a>
                                    <div class="dropdown-menu">
                                        {foreach from=$facet.filters item="filter"}
                                            {if !$filter.active}
                                                <a
                                                        rel="nofollow"
                                                        href="{$filter.nextEncodedFacetsURL}"
                                                        class="select-list js-search-link"
                                                >
                                                    {$filter.label}
                                                    {if $filter.magnitude and $show_quantities}
                                                        ({$filter.magnitude})
                                                    {/if}
                                                </a>
                                            {/if}
                                        {/foreach}
                                    </div>
                                </div>
                            </li>
                        </ul>
                    {/block}

                {elseif $facet.widgetType == 'slider'}
                    {block name='facet_item_slider'}
                        {foreach from=$facet.filters item="filter"}
                            <ul id="facet_{$_expand_id}"
                                class="faceted-slider collapse{if $_collapse} in{/if}"
                                data-slider-min="{$facet.properties.min}"
                                data-slider-max="{$facet.properties.max}"
                                data-slider-id="{$_expand_id}"
                                data-slider-values="{$filter.value|@json_encode}"
                                data-slider-unit="{$facet.properties.unit}"
                                data-slider-label="{$facet.label}"
                                data-slider-specifications="{$facet.properties.specifications|@json_encode}"
                                data-slider-encoded-url="{$filter.nextEncodedFacetsURL}"
                            >
                                <li>
                                    <p id="facet_label_{$_expand_id}">
                                        {$filter.label}
                                    </p>

                                    <div id="slider-range_{$_expand_id}"></div>
                                </li>
                            </ul>
                        {/foreach}
                    {/block}
                {/if}
            </section>
        {/foreach}
    </div>
{else}
    <div id="search_filters" style="display:none;">
    </div>
{/if}
