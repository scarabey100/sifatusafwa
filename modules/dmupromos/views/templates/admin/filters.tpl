{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL DREAM ME UP
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL DREAM ME UP is strictly forbidden.
*
*  .--.
*  |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
*  |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
*  '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
*       w w w . d r e a m - m e - u p . f r       '
*
* @author    Dream me up <prestashop@dream-me-up.fr>
* @copyright 2007 - 2024 Dream me up
* @license   All Rights Reserved
*}

<div class="panel dmup_reloadable">
    <div class="panel-heading">{l s='Filters' mod='dmupromos'}</div>
    <div class="row">
        <div class="cols col-xs-12 col-sm-12 col-lg-3">
            <div class="row">
                <label class="control-label col-xs-12">{l s='Products' mod='dmupromos'}&nbsp;:</label>
                <div class="cols col-xs-1 text-right"><input type="checkbox"{if !isset($campagne) || $campagne->search_only_active} checked="checked"{/if} id="show_active" class="dmup_filters" /></div>
                <div class="cols col-xs-11"><label for="show_active" style="font-weight:normal;">{l s='Only show active products' mod='dmupromos'}</label></div>
                <div class="cols col-xs-1 text-right"><input type="checkbox" {if isset($show_campaign) && $show_campaign}checked="checked"{/if} id="show_campaign" class="dmup_filters" /></div>
                <div class="cols col-xs-11"><label for="show_campaign">{l s='Only show products of this campaign' mod='dmupromos'}<div class="help-block" style="margin-top:0;font-size:11px;font-weight:normal;">{l s='Deactive this option to add new product to your campaign !' mod='dmupromos'}</div></label></div>
            </div>
        </div>
        <div class="cols col-xs-12 col-sm-12 col-lg-9">
            <div class="row">
                <div class="cols col-xs-12 col-sm-3 dmup_advanced_filters" style="display:none;">
                    <label class="control-label col-xs-12">{l s='Minimum stock of products' mod='dmupromos'}&nbsp;:</label>
                    <div class="cols col-xs-12">
                        <input type="text" id="filter_stock" class="dmup_filters" placeholder="0" value="0" />
                    </div>
                </div>
                <div class="cols col-xs-12 col-sm-3 dmup_advanced_filters" style="display:none;">
                    <label class="control-label col-xs-12">{l s='Minimum age of products' mod='dmupromos'}&nbsp;:</label>
                    <div class="cols col-xs-12">
                        <div class="input-group">
                            <input type="text" id="filter_newage" placeholder="0" value="{if isset($campagne) && isset($campagne->search_new_age)}{$campagne->search_new_age|intval}{/if}" class="dmup_filters" />
                            <span class="input-group-addon">{l s='days' mod='dmupromos'}</span>
                        </div>
                    </div>
                </div>
                <div class="cols col-xs-12 col-sm-3 dmup_advanced_filters" style="display:none;">
                    <label class="control-label col-xs-12">
                        <span class="label-tooltip" data-toggle="tooltip" data-original-title="{l s='Only products with advanced stock management are concerned by this filter !' mod='dmupromos'}">{l s='Minimum delay since last restock' mod='dmupromos'}&nbsp;:</span>
                    </label>
                    <div class="cols col-xs-12">
                        {if isset($advanced_stock_management) && $advanced_stock_management}
                            <div class="input-group">
                                <input type="text" id="filter_lastrestock" placeholder="0" value="{if isset($campagne) && isset($campagne->search_stock_age)}{$campagne->search_stock_age|intval}{/if}" class="dmup_filters" />
                                <span class="input-group-addon">{l s='days' mod='dmupromos'}</span>
                            </div>
                        {else}
                            <div class="help-block" style="font-size:11px;"><i class="icon-warning-sign"></i> {l s='You must activate the advanced stock management to use this filter.' mod='dmupromos'}</div>
                        {/if}
                    </div>
                </div>
                <div class="cols col-xs-12 col-sm-3 text-right pull-right">
                    &nbsp;<a href="javascript:;" id="filter_toggle" style="font-size:11px;">{l s='Advanced filters' mod='dmupromos'} <i class="icon-chevron-down"></i></a>
                </div>
            </div>
            <div class="row">
                <div class="cols col-xs-12 col-sm-3">
                    <label class="control-label col-xs-12">{l s='Category' mod='dmupromos'}&nbsp;:</label>
                    <div class="cols col-xs-12">
                        <select id="filter_id_category" class="dmup_filters">
                            <option value="0">{l s='All categories' mod='dmupromos'}</option>
                            {foreach from=$options_categories item=category name=categories}
                                <option value="{$category.infos.id_category|intval}">{"&nbsp;"|str_repeat:($category.infos.level_depth*5)}{$category.infos.name|escape:'htmlall':'UTF-8'} ({$category.nb_products|escape:'htmlall':'UTF-8'} {if $category.nb_products > 1}{l s='products' mod='dmupromos'}{else}{l s='product' mod='dmupromos'}{/if}{if $category.nb_products_reduc > 0}, {$category.nb_products_reduc|escape:'htmlall':'UTF-8'} {l s='with discount' mod='dmupromos'}{/if})</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="cols col-xs-12 col-sm-3">
                    <label class="control-label col-xs-12">{l s='Manufacturer' mod='dmupromos'}&nbsp;:</label>
                    <div class="cols col-xs-12">
                        <select id="filter_id_manufacturer" class="dmup_filters">
                            <option value="0">{l s='All manufacturers' mod='dmupromos'}</option>
                            {foreach from=$options_marques item=marque name=marques}
                                <option value="{$marque.id_manufacturer|intval}">{$marque.name|escape:'htmlall':'UTF-8'}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>
                <div class="cols col-xs-12 col-sm-3">
                    <label class="control-label col-xs-12">{l s='Keywords' mod='dmupromos'}&nbsp;:</label>
                    <div class="cols col-xs-12 col-sm-12">
                        <input type="text" id="filter_keywords" class="dmup_filters" placeholder="{l s='Product name or reference' mod='dmupromos'}" />
                    </div>
                </div>
                <div class="cols col-xs-12 col-sm-3">
                    <label class="control-label col-xs-12">&nbsp;</label>
                    <div class="cols col-xs-6">
                        <a href="javascript:;" id="filter_submit" class="button btn btn-default" style="width:100%;text-overflow:ellipsis;overflow:hidden;" title="{l s='Reset filters' mod='dmupromos'}"><i class="icon-search"></i> {l s='Search' mod='dmupromos'}</a>
                    </div>
                    <div class="cols col-xs-6">
                        <a href="javascript:;" id="filter_reset" class="button btn btn-warning" style="width:100%;text-overflow:ellipsis;overflow:hidden;" title="{l s='Reset filters' mod='dmupromos'}"><i class="icon-eraser"></i> {l s='Reset filters' mod='dmupromos'}</a>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" id="filter_orderby" value="id_product" />
        <input type="hidden" id="filter_orderway" value="ASC" />
    </div>
</div>