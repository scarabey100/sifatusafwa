{**
 * Prestashop module : OpartStat
 *
 * @author Olivier CLEMENCE <sav@store-opart.fr>
 * @copyright  Op'art
 * @license Tous droits réservés / Le droit d'auteur s'applique (All rights reserved / French copyright law applies)
 *}
<div id="osSideBar">
	<i class="material-icons mi-ordering displaySideBarBtn openMenuBtn" id="open_osSideBar"><span>close</span></i>
	<h2>{l s='Manage positions' mod='opartstat'}</h2>
	<div id="sortableMetricContainer">
		{foreach from=$allActiveMetrics key=position item=metric}
			<div class="sortableMetric" id="{$metric['name']|escape:'html':'UTF-8'}">
				<span class="help-box" data-html="true" data-placement="bottom" title="{$metric['help']|escape:'htmlall':'UTF-8'}"></span>
				{$metric['title']|escape:'html':'UTF-8'}
			</div>
		{/foreach}
	</div>
	<div class="sideBarSaveBtnContainer">
		<a id="sideBarSaveBtn" class="btn btn-primary pointer spacedBtn addIsLoadingClass">
			<i class="material-icons mi-save"><span>save</span></i>
			{l s='Save' mod='opartstat'}
		</a>
	</div>
</div>