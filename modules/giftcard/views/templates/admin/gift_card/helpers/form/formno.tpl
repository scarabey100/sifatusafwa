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
 * + PS version: 1.5,1.6
 *
 *}
 {if $ps_version <= "1.6" }
	{include file="toolbar.tpl" toolbar_btn=$toolbar_btn toolbar_scroll=$toolbar_scroll title=$title}
	<div class="leadin">{block name="leadin"}{/block}</div>
{/if}

<div class="panel">
{if $ps_version >= "1.6" }
<h3><i class="icon-credit-card "></i> {l s='Gift Card' mod='giftcard'}</h3>
{/if}
<div>
 	<div class="productTabs">
		<ul class="tab">
			<li class="tab-row">
				<a class="tab-page" id="gift_card_link_informations" href="javascript:displayGiftCardTab('informations');">{l s='Information' mod='giftcard'}</a>
			</li>
		</ul>
	</div>
</div>
<form action="{$currentIndex|escape:'quotes':'UTF-8'}&token={$currentToken|escape:'htmlall':'UTF-8'}&addgiftcardproduct" id="giftcardproduct_form" method="post" enctype="multipart/form-data">
	{if $currentObject->id}<input type="hidden" name="id_product" value="{$currentObject->id|intval}" />{/if}
	<input type="hidden" id="currentFormTab" name="currentFormTab" value="informations" />
	<div id="gift_card_informations" class="gift_card_tab">
		<h4>{l s='Gift card information' mod='giftcard'}</h4>
		<div class="separation"></div>
		{include file='../../informations.tpl'}
	</div>
	<input type="submit" value="{l s='Save' mod='giftcard'}" class="button" name="submitAddgiftcardproduct" id="{$table|escape:'htmlall':'UTF-8'}_form_submit_btn" />
</form>
</div>
<script type="text/javascript">
	var currentToken = '{$currentToken|escape:'quotes':'UTF-8'}';
	var currentFormTab = '{if isset($smarty.post.currentFormTab)}{$smarty.post.currentFormTab|escape:'quotes':'UTF-8'}{else}informations{/if}';
	
	var languages = new Array();
	{foreach from=$languages item=language key=k}
		languages[{$k|intval}] = {
			id_lang: {$language.id_lang|intval},
			iso_code: '{$language.iso_code|escape:'quotes':'UTF-8'}',
			name: '{$language.name|escape:'quotes':'UTF-8'}'
		};
	{/foreach}
	displayFlags(languages, {$id_lang_default|intval});
	
	$(document).ready(function() {
		changeCurrency('0');
		$("#id_currency").change(function(){
  		changeCurrency('0');
	});
	});
	function changeCurrency(index)
	{
		var id_currency = $('#id_currency').val();
		if(id_currency!=0)
		{
			if (currencies[id_currency]["format"] == 2 || currencies[id_currency]["format"] == 4)
			{
				$('#currency_sign_pre_' + index).html('');
				$('#currency_sign_post_' + index).html(' ' + currencies[id_currency]["sign"]);
			}
			else if (currencies[id_currency]["format"] == 1 || currencies[id_currency]["format"] == 3)
			{
				$('#currency_sign_post_' + index).html('');
				$('#currency_sign_pre_' + index).html(currencies[id_currency]["sign"] + ' ');
			}
		}
	}
	var currencies = new Array();
	{foreach from=$currencies item=curr}
		currencies[{$curr.id_currency|intval}] = new Array();
		currencies[{$curr.id_currency|intval}]["sign"] = '{$curr.sign|escape:'quotes':'UTF-8'}';
		currencies[{$curr.id_currency|intval}]["format"] = '{$curr.formatn|escape:'quotes':'UTF-8'}';
	{/foreach}
</script>