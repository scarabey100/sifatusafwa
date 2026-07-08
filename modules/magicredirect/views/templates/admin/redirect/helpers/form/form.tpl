{**
 * NOTICE OF LICENSE.
 *
 * This source file is subject to a commercial license from Agence Malttt SAS
 * Use, copy, modification or distribution of this source file without written
 * license agreement from the Agence Malttt SAS is strictly forbidden.
 * INFORMATION SUR LA LICENCE D'UTILISATION
 * L'utilisation de ce fichier source est soumise a une licence commerciale
 * concedee par la societe Agence Malttt SAS
 * Toute utilisation, reproduction, modification ou distribution du present
 * fichier source sans contrat de licence ecrit de la part d'Agence Malttt SAS est expressement interdite.
 *
 * @author    Matthieu Deroubaix
 * @copyright Copyright (c) 2015-2023 Agence Malttt SAS - 90 Rue faubourg saint martin - 75010 Paris
 * @license   Commercial license
 * Support by mail  :  support@agence-malttt.fr
 * Phone : +33.972535133
 *}

{extends file="helpers/form/form.tpl"}
{block name="field"}
	{if $input.type == 'file_lang'}
		<div class="row">
			{foreach from=$languages item=language}
				{if $languages|count > 1}
					<div class="translatable-field lang-{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang != $defaultFormLanguage}style="display:none"{/if}>
				{/if}
					<div class="col-lg-6">
						{if isset($fields[0]['form']['images'])}
						<img src="{$image_baseurl|escape:'htmlall':'UTF-8'}{$fields[0]['form']['images'][$language.id_lang]|escape:'htmlall':'UTF-8'}" class="img-thumbnail" />
						{/if}
						<div class="dummyfile input-group">
							<input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" type="file" name="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}" class="hide-file-upload" />
							<span class="input-group-addon"><i class="icon-file"></i></span>
							<input id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-name" type="text" class="disabled" name="filename" readonly />
							<span class="input-group-btn">
								<button id="{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-selectbutton" type="button" name="submitAddAttachments" class="btn btn-default">
									<i class="icon-folder-open"></i> {l s='Choose a file' mod='magicredirect'}
								</button>
							</span>
						</div>
					</div>
				{if $languages|count > 1}
					<div class="col-lg-2">
						<button type="button" class="btn btn-default dropdown-toggle" tabindex="-1" data-toggle="dropdown">
							{$language.iso_code|escape:'htmlall':'UTF-8'}
							<span class="caret"></span>
						</button>
						<ul class="dropdown-menu">
							{foreach from=$languages item=lang}
							<li><a href="javascript:hideOtherLanguage({$lang.id_lang|escape:'htmlall':'UTF-8'});" tabindex="-1">{$lang.name|escape:'htmlall':'UTF-8'}</a></li>
							{/foreach}
						</ul>
					</div>
				{/if}
				{if $languages|count > 1}
					</div>
				{/if}
				<script>
				$(document).ready(function(){
					$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-selectbutton').click(function(e){
						$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}').trigger('click');
					});
					$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}').change(function(e){
						var val = $(this).val();
						var file = val.split(/[\\/]/);
						$('#{$input.name|escape:'htmlall':'UTF-8'}_{$language.id_lang|escape:'htmlall':'UTF-8'}-name').val(file[file.length-1]);
					});
				});
			</script>
			{/foreach}
		</div>
	{elseif $input.type == "shop" && !empty($fields_value.checkBoxShopAsso_module)}
		<script>
		// Hack
		setTimeout(function(){
			$(document).ready(function(){
				uncheckAllAssociatedShops($('#shop-tree'));
				{foreach $fields_value.checkBoxShopAsso_module as $id}
				$('#shop-tree input[name="checkBoxShopAsso_module[{$id|escape:'htmlall':'UTF-8'}]"]').click();
				{/foreach}
			});
		},1000);
		</script>
	{/if}
	{$smarty.block.parent}
{/block}