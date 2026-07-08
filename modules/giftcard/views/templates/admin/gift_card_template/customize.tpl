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

  {if $ps_version >= "1.6"}
  <div class="form-group" id="giftcard_customize">
  	 {if !$imagick}<p class="info">{l s='The personalization feature requires php imagick extension, this extension is not present on your server.' mod='giftcard'}</p>{/if}
  	 <div class="col-lg-5 col-md-4" id="giftcardtemplateselect">
  	 	<div  id="giftcardtemplateselect_img">
								
		</div>
  	 </div>
  	 <div class="col-lg-7 col-md-8" >
  	 	{if isset($availablevars['giftcard_price']) || isset($availablevars['giftcard_code'])}
  	 			<h3>{l s='Data variable' mod='giftcard'}</h3>
  	 			
				{if isset($availablevars['giftcard_price'])}
				<div class="form-group">
					<label class="control-label col-lg-3" for="var_price_default">
						<span class="label-tooltip" data-toggle="tooltip"
							title="{l s='0 to not display in price in template' mod='giftcard'}">
							{l s='Price' mod='giftcard'}
						</span>
					</label>
					<div class="col-lg-9">
						<input type="text" size="10" name="var_price_default" class="custom_field" id="var_price_default" value="{$currentTab->getFieldValue($currentObject, 'var_price_default')|floatval|round|string_format:"%d"}"/> 
					</div>
				</div>
				{/if}
				{if isset($availablevars['giftcard_code'])}
				<div class="form-group">
					<label class="control-label col-lg-3" for="var_code_default">
							{l s='Discount code' mod='giftcard'}
					</label>
					<div class="col-lg-9">
						<input type="text" size="10" name="var_code_default" class="custom_field" id="var_code_default" value="{$currentTab->getFieldValue($currentObject, 'var_code_default')|escape:'htmlall':'UTF-8'}"/> 
					</div>
				</div>
				{/if}
				{if isset($availablevars['giftcard_from'])}
				<div class="form-group">
					<label class="control-label col-lg-3" for="var_from_default">
							{l s='From' mod='giftcard'}
					</label>
					<div class="col-lg-9">
						<input type="text" size="10" name="var_from_default" class="custom_field" id="var_from_default" value="{$currentTab->getFieldValue($currentObject, 'var_from_default')|escape:'htmlall':'UTF-8'}"/> 
					</div>
				</div>
				{/if}
				{if isset($availablevars['giftcard_lastname'])}
				<div class="form-group">
					<label class="control-label col-lg-3" for="var_lastname_default">
							{l s='Lastname' mod='giftcard'}
					</label>
					<div class="col-lg-9">
						<input type="text" size="10" name="var_lastname_default" class="custom_field" id="var_lastname_default" value="{$currentTab->getFieldValue($currentObject, 'var_lastname_default')|escape:'htmlall':'UTF-8'}"/> 
					</div>
				</div>
				{/if}
				{if isset($availablevars['giftcard_message'])}
				<div class="form-group">
					<label class="control-label col-lg-3" for="var_message_default">
							{l s='message' mod='giftcard'}
					</label>
					<div class="col-lg-9">
						<textarea name="var_message_default" class="custom_field" id="var_message_default" >{$currentTab->getFieldValue($currentObject, 'var_message_default')|escape:'htmlall':'UTF-8'}</textarea> 
					</div>
				</div>
				{/if}
				{if isset($availablevars['giftcard_expirate'])}
				<div class="form-group">
					<label class="control-label col-lg-3" for="var_expirate_default">
							{l s='Expirate' mod='giftcard'}
					</label>
					<div class="col-lg-9">
						<input type="text" size="10" name="var_expirate_default" class="custom_field" id="var_expirate_default" value="{$currentTab->getFieldValue($currentObject, 'var_expirate_default')|escape:'htmlall':'UTF-8'}"/> 
					</div>
				</div>
				{/if}
		{/if}
		{if $haveCustomText}
			<h3>{l s='Customizable text' mod='giftcard'}</h3>
			{foreach from=$availablevars key=field item=v}
				{if $field|strstr:"var_text"}
				<div class="form-group">
					<label class="control-label col-lg-3" >
							{$field|escape:'htmlall':'UTF-8'}
					</label>
					<div class="col-lg-9">
				{foreach from=$languages item=language}
					{if $languages|count > 1}
					<div class="row">
						<div class="translatable-field lang-{$language.id_lang|intval}" {if $language.id_lang != $id_lang_default}style="display:none"{/if}>
							<div class="col-lg-9">
					{/if}
					
					<input type="text" id="{$field|escape:'htmlall':'UTF-8'}" name="{$field|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, $field, $language.id_lang|intval)|escape:'html':'UTF-8'}"  />
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
				{/if} 
			{/foreach}
		{/if}
		{if $haveCustomColor}
			<h3>{l s='Customizable color' mod='giftcard'}</h3>
				{foreach from=$availablevars key=field item=v name=foo}
					{if $field|strstr:"var_color"}
					<div class="form-group">
						<label class="control-label col-lg-3" >
								{$field|escape:'htmlall':'UTF-8'}
						</label>
						<div class="col-lg-9">
							<div class="form-group">
								<div class="col-lg-2">
									<div class="row">
									<div class="input-group">
										<input type="text" size="33" data-hex="true" class="color mColorPickerInput mColorPicker" name="{$field|escape:'htmlall':'UTF-8'}" 
										value="{$currentTab->getFieldValue($currentObject, $field)|escape:'html':'UTF-8'}" id="color_{$smarty.foreach.foo.index|intval}" 
										style="background-color: {$currentTab->getFieldValue($currentObject, $field)|escape:'htmlall':'UTF-8'}; color: white;">
										<span style="cursor:pointer;" id="icp_color_{$smarty.foreach.foo.index|intval}" class="mColorPickerTrigger input-group-addon" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
									</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					{/if} 
				{/foreach}
			</table>
		{/if}
		<h3>{l s='PDF' mod='giftcard'}</h3>
		<div class="form-group">
			<label class="control-label col-lg-3" for="var_price_default">
						<span class="label-tooltip" data-toggle="tooltip"
							title="{l s='If activate, only image will be presented in PDF, content set in the configuration will be ignored.' mod='giftcard'}">
							{l s='PDF only image' mod='giftcard'}
						</span>
		</label>
		<div class="input-group col-lg-2">
		<span class="switch prestashop-switch">
			<input type="radio" name="pdf_image_only" id="pdf_image_only_on" value="1" {if $currentTab->getFieldValue($currentObject, 'pdf_image_only')|intval}checked="checked"{/if}/>
			<label for="pdf_image_only_on">{l s='Yes' mod='giftcard'}</label>
			<input type="radio" name="pdf_image_only" id="pdf_image_only_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'pdf_image_only')|intval}checked="checked"{/if} />
			<label for="pdf_image_only_off">{l s='No' mod='giftcard'}</label>
			<a class="slide-button btn"></a>
		</span>
		</div>
		</div>
		<br/>
		<div class="form-group">
			<a id="showTemplates" class="btn btn-default" href="javascript:previewTemplate('{$token|escape:'html':'UTF-8'}')" style="margin-left:20px;" >
				<i class="icon-eye"></i> <span>{l s='Preview' mod='giftcard'} </span>
			</a>
		
		</div>
  	 </div>
  </div>
  {else}
 	 {if !$imagick}<p class="info">{l s='The personalization feature requires php imagick extension, this extension is not present on your server.' mod='giftcard'}</p>{/if}


<div id="gifcard_uploadimage" {if $currentTab->getFieldValue($currentObject, 'usecustom')|intval && $imagick} style="display:none"{/if}>
		<div class="margin-form">
		</div>
</div>
<div id="giftcard_customize" >		
		<div id="giftcardtemplateselect" >
			<div  id="giftcardtemplateselect_img">
							
			</div>
		</div>
		<div id="giftcardcustom_form" >
			<h3>{l s='Data variable' mod='giftcard'}</h3>
			<table class="tableformcustom">
			{if isset($availablevars['giftcard_price']) || isset($availablevars['giftcard_code'])}
				{if isset($availablevars['giftcard_price'])}
				<tr>
					<td class="label">{l s='Price' mod='giftcard'}</td>
					<td>
						<input type="text" size="10" name="var_price_default" class="custom_field" id="var_price_default" value="{$currentTab->getFieldValue($currentObject, 'var_price_default')|floatval|round|string_format:"%d"}"/> 
					</td>
				</tr>
				{/if}
				{if isset($availablevars['giftcard_code'])}
				<tr>
					<td class="label">{l s='Discount code' mod='giftcard'}</td>
					<td>
						<input type="text" size="33" name="var_code_default" class="custom_field" id="var_code_default" value="{$currentTab->getFieldValue($currentObject, 'var_code_default')|escape:'htmlall':'UTF-8'}"/> 
					|escape:'htmlall':'UTF-8'}		</tr>
				{/if}
				{if isset($availablevars['giftcard_from'])}
				<tr>
					<td class="label">{l s='From' mod='giftcard'}</td>
					<td>
						<input type="text" size="33" name="var_from_default" class="custom_field" id="var_from_default" value="{$currentTab->getFieldValue($currentObject, 'var_from_default')|escape:'htmlall':'UTF-8'}"/> 
					</td>
				</tr>
				{/if}
				{if isset($availablevars['giftcard_lastname'])}
				<tr>
					<td class="label">{l s='Lastname' mod='giftcard'}</td>
					<td>
						<input type="text" size="33" name="var_lastname_default" class="custom_field" id="var_lastname_default" value="{$currentTab->getFieldValue($currentObject, 'var_lastname_default')|escape:'htmlall':'UTF-8'}"/> 
					</td>
				</tr>
				{/if}
				{if isset($availablevars['giftcard_message'])}
				<tr>
					<td class="label">{l s='Message' mod='giftcard'}</td>
					<td>
						<textarea name="var_message_default" class="custom_field" id="var_message_default" >{$currentTab->getFieldValue($currentObject, 'var_message_default')|escape:'htmlall':'UTF-8'}</textarea> 
					</td>
				</tr>
				{/if}
				{if isset($availablevars['giftcard_expirate'])}
				<tr>
					<td class="label">{l s='Expirate' mod='giftcard'}</td>
					<td>
						<input type="text" size="33" name="var_expirate_default" class="custom_field" id="var_expirate_default" value="{$currentTab->getFieldValue($currentObject, 'var_expirate_default')|escape:'htmlall':'UTF-8'}"/> 
					</td>
				</tr>
				{/if}
			{/if}
			</table>
			{if $haveCustomText}
			<h3>{l s='Customizable text' mod='giftcard'}</h3>
			<table class="tableformcustom">
			{foreach from=$availablevars key=field item=v}
				{if $field|strstr:"var_text"}
				<tr>
				<td>
				<div class="translatable">
					{foreach from=$languages item=language}
					<div class="lang_{$language.id_lang|intval}" style="display:{if $language.id_lang == $id_lang_default}block{else}none{/if};float:left">
						<input type="text" id="{$field|escape:'htmlall':'UTF-8'}" name="{$field|escape:'htmlall':'UTF-8'}_{$language.id_lang|intval}" value="{$currentTab->getFieldValue($currentObject, $field, $language.id_lang|intval)|escape:'html':'UTF-8'}" style="width:400px" />
						<sup>*</sup>
					</div>
					{/foreach}
				</div>
				</td>
				</tr>
				{/if} 
			{/foreach}
			</table>
			{/if}
			{if $haveCustomColor}
			<h3>{l s='Customizable color' mod='giftcard'}</h3>
			<table class="tableformcustom">
				{foreach from=$availablevars key=field item=v name=foo}
					{if $field|strstr:"var_color"}
					<tr>
						<td class="label">{$field|escape:'htmlall':'UTF-8'}</td>
						<td>
							<input type="text" size="33" data-hex="true" class="color mColorPickerInput mColorPicker" name="{$field|escape:'html':'UTF-8'}" 
								value="{$currentTab->getFieldValue($currentObject, $field)|escape:'html':'UTF-8'}" id="color_{$smarty.foreach.foo.index|intval}" 
									style="background-color: {$currentTab->getFieldValue($currentObject, $field)|escape:'html':'UTF-8'}; color: white;"><span style="cursor:pointer;" id="icp_color_{$smarty.foreach.foo.index|intval}" class="mColorPickerTrigger" data-mcolorpicker="true"><img src="../img/admin/color.png" style="border:0;margin:0 0 0 3px" align="absmiddle"></span>
						</td>
					</tr>
					{/if} 
				{/foreach}
			</table>
			{/if}
			<h3>{l s='PDF' mod='giftcard'}</h3>
			<table class="tableformcustom">
			<tr>
			<td class="label">{l s='Only image' mod='giftcard'}</td>
			<td>
				&nbsp;&nbsp;
				<input type="radio" name="pdf_image_only" id="pdf_image_only_on" value="1" {if $currentTab->getFieldValue($currentObject, 'pdf_image_only')|intval}checked="checked"{/if} />
				<label class="t" for="pdf_image_only_on"> <img src="../img/admin/enabled.gif" alt="{l s='Enabled' mod='giftcard'}" title="{l s='Enabled' mod='giftcard'}" style="cursor:pointer" /></label>
				&nbsp;&nbsp;
				<input type="radio" name="pdf_image_only" id="pdf_image_only_off" value="0"  {if !$currentTab->getFieldValue($currentObject, 'pdf_image_only')|intval}checked="checked"{/if} />
				<label class="t" for="pdf_image_only_off"> <img src="../img/admin/disabled.gif" alt="{l s='Disabled' mod='giftcard'}" title="{l s='Disabled' mod='giftcard'}" style="cursor:pointer" /></label>
				<p class="preference_description clear">{l s='If activate, only image will be presented in PDF, content set in the configuration will be ignored.' mod='giftcard'}</p>
			</td>
			</tr>
			</table>
			{if $haveCustomText}
			<h3>{l s='Preview' mod='giftcard'}</h3>
			<table class="tableformcustom">
					<tr>
						<td class="label">{l s='The Language used to preview' mod='giftcard'}</td>
						<td>
							<select id="id_lang_preview" name="id_lang_preview">
								{foreach from=$languages item='lang'}
									<option value="{$lang.id_lang|intval}" {if $currentObject->id && $currentObject->id_lang_display==$lang.id_lang}selected{/if}>{$lang.name|escape:'htmlall':'UTF-8'}</option>
								{/foreach}
							</select>
						</td>
					</tr>
			</table>
			{/if}
			
			<br/>
			<a href="javascript:previewTemplate('{$token|escape:'html':'UTF-8'}')"   id="showTemplates" class="btngiftcard" style="float:left">{l s='Preview' mod='giftcard'}</a>
		</div>
		
</div>
<div class="clear">
		</div>
{/if}