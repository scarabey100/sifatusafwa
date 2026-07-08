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
 {*** PSVERSION >= 1.6 ***}
 {if $ps_version >= 1.6}
 <div class="form-group">
	<label class="control-label col-lg-3 required">
		<span class="label-tooltip" data-toggle="tooltip"
		title="{l s='This will be displayed in template list.' mod='giftcard'}">
			{l s='Name' mod='giftcard'}
		</span>
	</label>
	<div class="col-lg-8">	
		{foreach from=$languages item=language}
		{if $languages|count > 1}
		<div class="row">
			<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $id_lang_default}style="display:none"{/if}>
				<div class="col-lg-9">
		{/if}
					<input id="name_{$language.id_lang|intval}" type="text"  name="name_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, 'name', $language.id_lang|intval)|escape:'html':'UTF-8'}">
		{if $languages|count > 1}
				</div>
				<div class="col-lg-2">
					<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
						{$language.iso_code|escape:'htmlall':'UTF-8'} 
						<span class="caret"></span>
					</button>
					<ul class="dropdown-menu">
						{foreach from=$languages item=language}
						<li><a href="javascript:hideOtherLanguage({$language.id_lang|intval});" tabindex="-1">{$language.name|escape:'htmlall':'UTF-8'}</a></li>
						{/foreach}
					</ul>
				</div>		
			</div>
		</div>
		{/if}
		{/foreach}
	</div>
</div>
<div class="form-group">
		<label class="control-label col-lg-3" for="tags_{$id_lang_default|intval}">
			<span class="label-tooltip" data-toggle="tooltip"
				title="{l s='Each tag has to be followed by a comma. Following characters are forbiden: %s' sprintf='!&lt;;&gt;;?=+#&quot;&deg;{}_$%' mod='giftcard'}">
				{l s='Tags:' mod='giftcard'}
			</span>
		</label>
		<div class="col-lg-9">
			{if $languages|count > 1}
			<div class="row">
			{/if}
				{foreach from=$languages item=language}
					{literal}
					<script type="text/javascript">
						$().ready(function () {
							var input_id = '{/literal}tags_{$language.id_lang|intval}{literal}';
							$('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
							$({/literal}'#{$table|escape:'html':'UTF-8'}{literal}_form').submit( function() {
								$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
							});
						});
					</script>
					{/literal}
				{if $languages|count > 1}
				<div class="translatable-field lang-{$language.id_lang|intval}">
					<div class="col-lg-9">
				{/if}
						<input type="text" id="tags_{$language.id_lang|intval}" class="tagify updateCurrentText" name="tags_{$language.id_lang|intval}" value="{$currentObject->getTags($language.id_lang, true)|escape:'html':'UTF-8'}" />
				{if $languages|count > 1}
					</div>
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
							{$language.iso_code|escape:'htmlall':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=language}
							<li>
								<a href="javascript:hideOtherLanguage({$language.id_lang|intval});">{$language.name|escape:'htmlall':'UTF-8'}</a>
							</li>
							{/foreach}
						</ul>
					</div>
				</div>
				{/if}
				{/foreach}
			{if $languages|count > 1}
				</div>
			{/if}
		</div>
</div>
<div class="form-group">
	<label class="control-label col-lg-3">
		
		<span class="label-tooltip" data-toggle="tooltip"
			title="{l s='If you select a language, the model will be displayed only in this language' mod='giftcard'}">
		{l s='Language' mod='giftcard'}
		</span>
	</label>
	<div class="col-lg-6">
		<div class="row">
				<select id="id_lang_display" name="id_lang_display">
					<option value="0" >{l s='All Languages' mod='giftcard'}</option>
					{foreach from=$languages item='lang'}
						<option value="{$lang.id_lang|intval}" {if $currentObject->id && $currentObject->id_lang_display==$lang.id_lang}selected{/if}>{$lang.name|escape:'html':'UTF-8'}</option>
					{/foreach}
				</select>
		</div>
	</div>
</div>
<div class="form-group">
	<label class="control-label col-lg-3">
			<span class="label-tooltip" data-toggle="tooltip"
			title="{l s='Upload a image from your computer.' mod='giftcard'}">{l s='Image' mod='giftcard'}</span>
	</label>
	<div class="input-group col-lg-9">
		<input type="file" name="image_template" />
		{if isset($image_template.image) && $image_template.image}
					{$image_template.image}{*HTML CONTENT*}
		{/if}
	</div>
</div>
<div class="form-group">
	<label class="control-label col-lg-3">
			{l s='Virtual use' mod='giftcard'}
	</label>
	<div class="input-group col-lg-2">
		<span class="switch prestashop-switch">
			<input type="radio" name="virtualuse" id="virtualuse_on" value="1" {if $currentTab->getFieldValue($currentObject, 'virtualuse')|intval}checked="checked"{/if}/>
			<label for="virtualuse_on">{l s='Yes' mod='giftcard'}</label>
			<input type="radio" name="virtualuse" id="virtualuse_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'virtualuse')|intval}checked="checked"{/if} />
			<label for="virtualuse_off">{l s='No' mod='giftcard'}</label>
			<a class="slide-button btn"></a>
		</span>
	</div>
</div>
<div class="form-group">
	<label class="control-label col-lg-3">
			{l s='Physical use' mod='giftcard'}
	</label>
	<div class="input-group col-lg-2">
		<span class="switch prestashop-switch">
			<input type="radio" name="physicaluse" id="physicaluse_on" value="1" {if $currentTab->getFieldValue($currentObject, 'physicaluse')|intval}checked="checked"{/if}/>
			<label for="physicaluse_on">{l s='Yes' mod='giftcard'}</label>
			<input type="radio" name="physicaluse" id="physicaluse_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'physicaluse')|intval}checked="checked"{/if} />
			<label for="physicaluse_off">{l s='No' mod='giftcard'}</label>
			<a class="slide-button btn"></a>
		</span>
	</div>
</div>

<div class="form-group">
	<label class="control-label col-lg-3">
			{l s='Status' mod='giftcard'}
	</label>
	<div class="input-group col-lg-2">
		<span class="switch prestashop-switch">
			<input type="radio" name="active" id="active_on" value="1" {if $currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if}/>
			<label for="active_on">{l s='Enabled' mod='giftcard'}</label>
			<input type="radio" name="active" id="active_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'active')|intval}checked="checked"{/if} />
			<label for="active_off">{l s='Disabled' mod='giftcard'}</label>
			<a class="slide-button btn"></a>
		</span>
	</div>
</div>
{if isset($asso_shops)}
		<div class="form-group">
			<label class="control-label col-lg-3">{l s='Choose shop association:' mod='giftcard'}</label>
			<div class="col-lg-9">{$asso_shops}{*HTML CONTENT*}</div>
		</div>
{/if}
 {else}
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
				<p class="preference_description">{l s='This will be displayed in template list.' mod='giftcard'}</p>
			</div>
		</td>
	</tr>
	<tr>
		<td>
			<label>{l s='Tags:' mod='giftcard'}</label>
			<div class="margin-form form-group">
					<div class="translatable">
					{foreach from=$languages item=language}
						<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float: left;">
							{literal}
							<script type="text/javascript">
								$().ready(function () {
									var input_id = '{/literal}tags_{$language.id_lang|intval}{literal}';
									$('#'+input_id).tagify({delimiters: [13,44], addTagPrompt: '{/literal}{l s='Add tag' js=1}{literal}'});
									$({/literal}'#{$table|escape:'htmlall':'UTF-8'}{literal}_form').submit( function() {
										$(this).find('#'+input_id).val($('#'+input_id).tagify('serialize'));
									});
								});
							</script>
							{/literal}
							<input size="55" type="text" id="tags_{$language.id_lang|intval}" name="tags_{$language.id_lang|intval}"
								value="{$currentObject->getTags($language.id_lang, true)|escape:'htmlall':'UTF-8'}" class="tagify" />
							<span class="hint" name="help_box">{l s='Forbidden characters:' mod='giftcard'} !&lt;;&gt;;?=+#&quot;&deg;{}_$%<span class="hint-pointer">&nbsp;</span></span>
						</div>
					{/foreach}
					</div>
					<p class="preference_description clear">{l s='Tags separated by commas (e.g. birthday, valentine, heart,organization,...)' mod='giftcard'}</p>
			</div>
		</td>
	</tr>
	
	<tr>
		<td>
			<label>{l s='Language' mod='giftcard'}</label>
			<div class="margin-form">
				<select id="id_lang_display" name="id_lang_display">
					<option value="0" >{l s='All Languages' mod='giftcard'}</option>
					{foreach from=$languages item='lang'}
						<option value="{$lang.id_lang|intval}" {if $currentObject->id && $currentObject->id_lang_display==$lang.id_lang}selected{/if}>{$lang.name|escape:'htmlall':'UTF-8'}</option>
					{/foreach}
				</select>
				<p class="preference_description clear">{l s='If you select a language, the model will be displayed only in this language' mod='giftcard'}</p>
			</div>
		</td>
	</tr>
	
	<tr>
		<td>
		<label>{l s='Image' mod='giftcard' mod='giftcard'}</label>
		<div class="margin-form">
		<input type="file" name="image_template" />
		<p class="description">{l s='Upload a image from your computer.' mod='giftcard'}</p>
		{if isset($image_template.image) && $image_template.image}
				<div id="image">
					{$image_template.image}{*HTML CONTENT*}
					<p align="center">{l s='File size' mod='giftcard'} {$image_template.size|intval}kb</p>
					<br />
				</div><br />
		{/if}
		</div>
		</td>
	</tr>
	{if isset($asso_shops)}
		<tr>
			<td class="label">
				{l s='Choose shop association:' mod='giftcard'}					
			</td>
			<td>{$asso_shops}{*HTML CONTENT*}</td>
		</tr>
	{/if}
</table>
{/if}
<script type="text/javascript">
$().ready(function() {
	$('.input_all_shop').live('click', function() {
		var checked = $(this).prop('checked');
		$('.input_shop_group:not(:disabled)').attr('checked', checked);
		$('.input_shop:not(:disabled)').attr('checked', checked);
	});

	// Click on a group shop
	$('.input_shop_group').live('click', function() {
		$('.input_shop[value='+$(this).val()+']').attr('checked', $(this).prop('checked'));
		check_all_shop();
	});

	// Click on a shop
	$('.input_shop').live('click', function() {
		check_shop_group_status($(this).val());
		check_all_shop();
	});

	// Initialize checkbox
	$('.input_shop_group').each(function(k, v) {
		check_shop_group_status($(v).val());
		check_all_shop();
	});
});

function check_shop_group_status(id_group) {
	var groupChecked = true;
	var total = 0;
	$('.input_shop[value='+id_group+']').each(function(k, v) {
		total++;
		if (!$(v).prop('checked'))
			groupChecked = false;
	});

	if (total > 0)
		$('.input_shop_group[value='+id_group+']').attr('checked', groupChecked);
}

function check_all_shop() {
	var allChecked = true;
	$('.input_shop_group:not(:disabled)').each(function(k, v) {
		if (!$(v).prop('checked'))
			allChecked = false;
		});
	$('.input_all_shop').attr('checked', allChecked);
}
</script>
