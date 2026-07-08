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
<table cellpadding="0" cellspacing="0">
	<tr>
		<td>
			<label>{l s='Name' mod='giftcard'}</label>
			<div class="margin-form">
				<div class="translatable">
				{foreach from=$languages item=language}
					<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
						<input type="text" id="name_{$language.id_lang|intval}" name="name_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, 'name', $language.id_lang|intval)|escape:'html':'UTF-8'}" style="width:400px" />
						<sup>*</sup>
					</div>
				{/foreach}
				</div>
				<p class="preference_description">{l s='This will be displayed in the cart summary, as well as on the invoice.' mod='giftcard'}</p>
			</div>
			<label>{l s='Currency' mod='giftcard'}</label>
			<div class="margin-form">
				<select name="id_currency" id="id_currency">
				<option value="0">{l s='All Currencies' mod='giftcard'}</option>
				{foreach from=$currencies item='currency'}
					<option value="{$currency.id_currency|intval}"
					{if $currentTab->getFieldValue($currentObject, 'id_currency') == $currency.id_currency}
						selected="selected"
					{/if}
					>
					{$currency.iso_code|escape:'htmlall':'UTF-8'}
					</option>
				{/foreach}
				</select>
				<p class="preference_description">{l s='gift card only visible' mod='giftcard'}</p>
			</div>
			<label>{l s='Price' mod='giftcard'}</label>
			<div class="margin-form">
				<span id="currency_sign_pre_0" style="font-weight:bold; color:#000000; font-size:12px">
				</span>
				<input type="text"  name="amount" id="amount" value="{$currentTab->getFieldValue($currentObject, 'amount')|floatval}" size="11">
				<span id="currency_sign_post_0" style="font-weight:bold; color:#000000; font-size:12px">
				</span><sup>*</sup>
			</div>
			<label>{l s='Quantity' mod='giftcard'}</label>
			<div class="margin-form">
				<input type="text"  name="quantity" id="quantity" value="{$currentTab->getFieldValue($currentObject, 'quantity')|intval}" size="11">
				<sup>*</sup>
			</div>
			<label>{l s='Period of validity' mod='giftcard'}</label>
			<div class="margin-form">
				<input type="text"  name="period_val" id="period_val" value="{$currentTab->getFieldValue($currentObject, 'period_val')|intval}" size="4">
				{l s='Month' mod='giftcard'}<sup>*</sup>
			</div>
			<label>{l s='Reference' mod='giftcard'}</label>
			<div class="margin-form">
				<input type="text"  name="reference" id="reference" value="{$currentTab->getFieldValue($currentObject, 'reference')|escape:'htmlall':'UTF-8'}" size="33">
			</div>
			<label>{l s='Ean13' mod='giftcard'}</label>
			<div class="margin-form">
				<input type="text"  name="ean13" id="ean13" value="{$currentTab->getFieldValue($currentObject, 'ean13')|escape:'htmlall':'UTF-8'}" size="33">
			</div>
			<label>{l s='Upc' mod='giftcard'}</label>
			<div class="margin-form">
				<input type="text"  name="upc" id="upc" value="{$currentTab->getFieldValue($currentObject, 'upc')|escape:'htmlall':'UTF-8'}" size="33">
			</div>
			
			<label>{l s='Status' mod='giftcard'}</label>
			<div class="margin-form">
				&nbsp;&nbsp;
				<input type="radio" name="active" id="active_on" value="1" {if $currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
				<label class="t" for="active_on"> <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='giftcard'}" title="{l s='Enabled' mod='giftcard'}" style="cursor:pointer" /></label>
				&nbsp;&nbsp;
				<input type="radio" name="active" id="active_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
				<label class="t" for="active_off"> <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='giftcard'}" title="{l s='Disabled' mod='giftcard'}" style="cursor:pointer" /></label>
			</div>
		</td>
	</tr>
</table>