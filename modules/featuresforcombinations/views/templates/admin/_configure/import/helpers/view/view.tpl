{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
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
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 *}
{extends file="helpers/view/view.tpl"}
{block name="override_tpl"}
	<script type="text/javascript">
		var current = 0;
		function showTable(nb) {
			$('#btn_left').disabled = null;
			$('#btn_right').disabled = null;
			if (nb <= 0) {
				nb = 0;
				$('#btn_left').disabled = 'true';
			}
			if (nb >= {$nb_table} - 1) {
				nb = {$nb_table} - 1;
				$('#btn_right').disabled = 'true';
			}
			$('#table' + current).hide();
			current = nb;
			$('#table' + current).show();
		}
		$(document).ready(function() {
			showTable(current);
		});
	</script>
	<div id="container-customer" class="panel">
		<h3><i class="icon-list-alt"></i> {l s='Match your data' mod='featuresforcombinations'}</h3>
		<div class="alert alert-info">
			<p>{l s='Please match each column of your source file to one of the destination columns.' mod='featuresforcombinations'}</p>
		</div>

		<div id="error_duplicate_type" class="alert alert-warning" style="display:none;">
			{l s='Two columns cannot have the same type of values' mod='featuresforcombinations'}
		</div>
		<div id="required_column" class="alert alert-warning" style="display:none;">
			{l s='This column must be set:' mod='featuresforcombinations'} <span id="missing_column">&nbsp;</span>
		</div>
		<form action="{$current|escape:'html':'UTF-8'}&amp;token={$token|escape:'html':'UTF-8'}" method="post" id="import_form" name="import_form" class="form-horizontal">
			<input type="hidden" name="csv" value="{$fields_value.csv}" />
			<input type="hidden" name="regenerate" value="{$fields_value.regenerate}" />
			<input type="hidden" name="entity" value="{$fields_value.entity}" />
			<input type="hidden" name="iso_lang" value="{$fields_value.iso_lang}" />
			<input type="hidden" name="sendemail" value="{$fields_value.sendemail}" />
			{if $fields_value.truncate}
				<input type="hidden" name="truncate" value="1" />
			{/if}
			{if $fields_value.forceIDs}
				<input type="hidden" name="forceIDs" value="1" />
			{/if}
			{if $fields_value.match_ref}
				<input type="hidden" name="match_ref" value="1" />
			{/if}
			<input type="hidden" name="separator" value="{$fields_value.separator}" />
			<input type="hidden" name="multiple_value_separator" value="{$fields_value.multiple_value_separator}" />
			<div class="form-group">
				<label class="control-label col-lg-3" for="skip">{l s='Rows to skip' mod='featuresforcombinations'}</label>
				<div class="col-lg-9">
					<input class="fixed-width-sm" type="text" name="skip" id="skip" value="1" />
					<p class="help-block">{l s='Indicate how many of the first rows of your file should be skipped when importing the data. For instance set it to 1 if the first row of your file contains headers.' mod='featuresforcombinations'}</p>
				</div>
			</div>
			<div class="form-group">
				<div class="col-lg-12">
					{section name=nb_i start=0 loop=$nb_table step=1}
						{assign var=i value=$smarty.section.nb_i.index}
						{$data.$i}
					{/section}
					<button id="btn_left" type="button" class="btn btn-default pull-left" onclick="showTable(current - 1);">
						<i class="icon-chevron-sign-left"></i>
					</button>
					<button id="btn_right" type="button" class="btn btn-default pull-right" onclick="showTable(current + 1);">
						<i class="icon-chevron-sign-right"></i>
					</button>
				</div>
			</div>
			<div class="panel-footer">
				<button type="button" class="btn btn-default" onclick="window.history.back();">
					<i class="process-icon-cancel text-danger"></i>
					{l s='Cancel' mod='featuresforcombinations'}
				</button>
				<button id="import" name="import" type="submit" onclick="return (validateImportation(new Array({$res})));"  class="btn btn-default pull-right">
					<i class="process-icon-ok text-success"></i>
					{l s='Import' mod='featuresforcombinations'}
				</button>
			</div>
		</form>
	</div>
{/block}
