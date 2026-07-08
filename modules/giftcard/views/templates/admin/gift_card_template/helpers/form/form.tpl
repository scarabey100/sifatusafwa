{*
 *
 * GIFT CARD
 *
 * @category pricing_promotion
 * @author EIRL Timactive De Véra
 * @copyright TIMACTIVE 2013
 * @version 1.0.0
 *
 *************************************
 **            GIFT CARD			 *              
 **             V 1.0.0              *
 *************************************
 * +
 * + Languages: EN, FR
 * + PS version: 1.5
 *
 *}
{if $ps_version <= "1.6" }
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
{/if}
<div class="panel">
<h3><i class="icon-credit-card "></i> {l s='Gift Card template' mod='giftcard'}</h3>
 	<div class="productTabs">
		<ul class="tab nav nav-tabs">
			<li class="tab-row">
				<a class="tab-page" id="gift_card_link_informations" href="javascript:displayGiftCardTab('informations');">{if $ps_version >= "1.6" }<i class="icon-info"></i>&nbsp;{/if}{l s='Information' mod='giftcard'}</a>
			</li>
			<li class="tab-row" {if !$currentObject->issvg}style="display:none"{/if}>
				<a class="tab-page" id="gift_card_link_custom" href="javascript:displayGiftCardTab('custom');">{if $ps_version >= "1.6" }<i class="icon-magic"></i>&nbsp;{/if}{l s='Customize' mod='giftcard'}</a>
			</li>
		</ul>
	</div>
<form action="{$currentIndex|escape:'htmlall':'UTF-8'}&token={$currentToken|escape:'htmlall':'UTF-8'}&addgiftcardtemplate" id="giftcardtemplate_form" method="post" enctype="multipart/form-data" class="form-horizontal">
	{if $currentObject->id}<input type="hidden" name="id_gift_card_template" value="{$currentObject->id|intval}" />{/if}
	<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
	<div id="gift_card_informations" class="gift_card_tab panel">
	{if $ps_version < "1.6" }<h4>{l s='Template information' mod='giftcard'}</h4>{/if}
		<div class="separation"></div>
			{include file='../../informations.tpl'}
		</div>
		<div id="gift_card_custom" class="gift_card_tab panel">
			{if $ps_version < "1.6" }<h4>{l s='Customize template' mod='giftcard'}</h4>{/if}
			<div class="separation"></div>
			{include file='../../customize.tpl'}
		</div>
		{if $ps_version >= "1.6" }
			<button type="submit" class="btn btn-default pull-right" name="submitAddgiftcardtemplate" id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn">{l s='Save' mod='giftcard'}</button>
		{else}
			<input type="submit" value="{l s='Save' mod='giftcard'}" class="button" name="submitAddgiftcardtemplate" id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn" />
		{/if}
		
</form>
{if $ps_version >= "1.6" }
	{include file="footer_toolbar.tpl"}
{/if}
</div>
<script type="text/javascript">
	var currentToken = '{$currentToken|escape:'quotes':'UTF-8'}';
	var currentFormTab = '{if isset($smarty.get.currentFormTab)}{$smarty.get.currentFormTab|escape:'quotes':'UTF-8'}{else}informations{/if}';
	{if $ps_version < "1.6" }
		var languages = new Array();
		{foreach from=$languages item=language key=k}
			languages[{$k|intval}] = {
				id_lang: {$language.id_lang|intval},
				iso_code: '{$language.iso_code|escape:'quotes':'UTF-8'}',
				name: '{$language.name|escape:'quotes':'UTF-8'}'
			};
		{/foreach}
		displayFlags(languages, {$id_lang_default|intval});
	{/if}
	var giftcard_img_dir ='{$giftcard_img_dir|escape:'quotes':'UTF-8'}';
	$(document).ready(function() {
		{if $ps_version >= "1.6" }
			hideOtherLanguage({$id_lang_default|intval});
		{/if}
		{if $currentObject->issvg}
			previewTemplate(currentToken);
		{/if}
	});
</script>

