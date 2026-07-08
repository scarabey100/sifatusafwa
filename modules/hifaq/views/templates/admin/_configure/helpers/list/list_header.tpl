{**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 *}
<form method="post" action="{$action|escape:'html':'UTF-8'}" class="form-horizontal hi-presta-module-form clearfix" id="form-{$list_id}">
    <input type="hidden" id="submitFilter{$list_id}" name="submitFilter{$list_id}" value="0">
	<input type="hidden" name="page" value="{$page|intval}">
	<input type="hidden" name="selected_pagination" value="{$selected_pagination|intval}">

	<div class="panel col-lg-12">
		<div class="panel-heading">
            {if isset($icon)}
				<i class="{$icon}"></i>
			{/if}

			{if is_array($title)}
				{$title|end|escape:'html':'UTF-8'}
			{else}
				{$title|escape:'html':'UTF-8'}
			{/if}

            {if isset($toolbar_btn) && count($toolbar_btn) > 0}
				<span class="badge">{$list_total}</span>
				<span class="panel-heading-action">
                    {foreach from=$toolbar_btn item=btn key=k}
                        {if $k != 'modules-list' && $k != 'back'}
                            <a id="desc-{$table}-{if isset($btn.imgclass)}{$btn.imgclass}{else}{$k}{/if}" class="list-toolbar-btn{if isset($btn.target) && $btn.target} _blank{/if}"{if isset($btn.href)} href="{$btn.href|escape:'html':'UTF-8'}"{/if}{if isset($btn.js) && $btn.js} onclick="{$btn.js}"{/if}>
                                <span data-toggle="tooltip" class="label-tooltip" data-original-title="{$btn.desc}" data-html="true" data-placement="top">
                                    <i class="process-icon-{if isset($btn.imgclass)}{$btn.imgclass}{else}{$k}{/if}{if isset($btn.class)} {$btn.class}{/if}"></i>
                                </span>
                            </a>
                        {/if}
                    {/foreach}
					<a class="list-toolbar-btn" href="javascript:location.reload();">
						<span data-toggle="tooltip" class="label-tooltip" data-original-title="{l s='Refresh list' mod='hifaq'}" data-html="true" data-placement="top">
							<i class="process-icon-refresh"></i>
						</span>
					</a>
				</span>
			{/if}
        </div>
		
        {if $bulk_actions && $has_bulk_actions}
            {assign var=y value=2}
        {else}
            {assign var=y value=1}
        {/if}
        <style>
            @media (max-width: 992px) {
                {foreach from=$fields_display item=param name=params}
                    .table-responsive-row td:nth-of-type({math equation="x+y" x=$smarty.foreach.params.index y=$y}):before {
                        content: "{$param.title}";
                    }
                {/foreach}
            }
        </style>
    
	    <div class="table-responsive-row clearfix{if isset($use_overflow) && $use_overflow} overflow-y{/if}">
		    <table id="table-{if $table_id}{$table_id}{elseif $table}{$table}{/if}" class="table{if $table_dnd} tableDnD{/if} {$table}">
			    <thead>
				    <tr class="nodrag nodrop">
                        {if $bulk_actions && $has_bulk_actions}
                            <th class="center fixed-width-xs"></th>
                        {/if}
                        {foreach $fields_display AS $key => $params}
                            <th class="{if isset($params.class)}{$params.class}{/if}{if isset($params.align)} {$params.align}{/if}">
                                <span class="title_box">
                                    {if isset($params.hint)}
                                        <span class="label-tooltip" data-toggle="tooltip"
                                            title="
                                                {if is_array($params.hint)}
                                                    {foreach $params.hint as $hint}
                                                        {if is_array($hint)}
                                                            {$hint.text}
                                                        {else}
                                                            {$hint}
                                                        {/if}
                                                    {/foreach}
                                                {else}
                                                    {$params.hint}
                                                {/if}
                                            ">
                                            {$params.title}
                                        </span>
                                    {else}
                                        {$params.title}
                                    {/if}
                                </span>
                            </th>
                        {/foreach}

                        {if $has_actions}
                            <th></th>
                        {/if}
                    </tr>
                    {if 1}
                        <tr class="nodrag nodrop filter {if $row_hover}row_hover{/if}">
                            {if $has_bulk_actions}
                                <th class="text-center">
                                    --
                                </th>
                            {/if}
                            {foreach $fields_display AS $key => $params}
                                <th {if isset($params.align)} class="{$params.align}" {/if}>
                                    {if isset($params.search) && !$params.search}
                                        --
                                    {else}
                                        {if $params.type == 'bool'}
                                            <select 
                                                class="filter fixed-width-sm center"
                                                onchange="$('#submitFilterButton{$list_id}').focus();$('#submitFilterButton{$list_id}').click();"
                                                name="{$list_id}Filter_{if isset($params.filter_key)}{$params.filter_key}{else}{$key}{/if}"
                                            >
                                                <option value="">-</option>
                                                <option value="1" {if $params.value == 1} selected="selected" {/if}>{l s='Yes' d='Admin.Global'}</option>
                                                <option value="0" {if $params.value == 0 && $params.value != ''} selected="selected" {/if}>{l s='No' d='Admin.Global'}</option>
                                            </select>
                                        {elseif $params.type == 'date' || $params.type == 'datetime'}
                                            <div class="date_range row">
                                                <div class="input-group fixed-width-md center">
                                                    <input type="text" class="filter datepicker date-input form-control" id="local_{$params.id_date}_0" name="local_{$params.name_date}[0]"  placeholder="{l s='From' mod='hifaq'}" />
                                                    <input type="hidden" id="{$params.id_date}_0" name="{$params.name_date}[0]" value="{if isset($params.value.0)}{$params.value.0}{/if}">
                                                    <span class="input-group-addon">
                                                        <i class="icon-calendar"></i>
                                                    </span>
                                                </div>
                                                <div class="input-group fixed-width-md center">
                                                    <input type="text" class="filter datepicker date-input form-control" id="local_{$params.id_date}_1" name="local_{$params.name_date}[1]"  placeholder="{l s='To' mod='hifaq'}" />
                                                    <input type="hidden" id="{$params.id_date}_1" name="{$params.name_date}[1]" value="{if isset($params.value.1)}{$params.value.1}{/if}">
                                                    <span class="input-group-addon">
                                                        <i class="icon-calendar"></i>
                                                    </span>
                                                </div>
                                                <script>
                                                    $(function() {
                                                        var dateStart = parseDate($("#{$params.id_date}_0").val());
                                                        var dateEnd = parseDate($("#{$params.id_date}_1").val());
                                                        $("#local_{$params.id_date}_0").datepicker("option", "altField", "#{$params.id_date}_0");
                                                        $("#local_{$params.id_date}_1").datepicker("option", "altField", "#{$params.id_date}_1");
                                                        if (dateStart !== null){
                                                            $("#local_{$params.id_date}_0").datepicker("setDate", dateStart);
                                                        }
                                                        if (dateEnd !== null){
                                                            $("#local_{$params.id_date}_1").datepicker("setDate", dateEnd);
                                                        }
                                                    });
                                                </script>
                                            </div>
                                        {elseif $params.type == 'select'}
                                            {if isset($params.filter_key)}
                                                <select
                                                    class="filter{if isset($params.align) && $params.align == 'center'}center{/if}"
                                                    name="{$list_id}Filter_{$params.filter_key}"
                                                    {if isset($params.width)} style="width:{$params.width}px"{/if}
                                                >
                                                    <option value="" {if $params.value == ''} selected="selected" {/if}>-</option>
                                                    {if isset($params.list) && is_array($params.list)}
                                                        {foreach $params.list AS $option_value => $option_display}
                                                            <option value="{$option_value}" {if (string)$option_display === (string)$params.value ||  (string)$option_value === (string)$params.value} selected="selected"{/if}>{$option_display}</option>
                                                        {/foreach}
                                                    {/if}
                                                </select>
                                            {/if}
                                        {else}
                                            <input type="text" class="filter" name="{$list_id}Filter_{if isset($params.filter_key)}{$params.filter_key}{else}{$key}{/if}" value="{if isset($params.searchTerm)}{$params.searchTerm|escape:'html':'UTF-8'}{/if}" {if isset($params.width) && $params.width != 'auto'} style="width:{$params.width}px"{/if} />
                                        {/if}
                                    {/if}
                                </th>
                            {/foreach}

                            {if $has_actions}
                                <th class="actions"></th>
                            {/if}
                        </tr>
                    {/if}
			    </thead>




