{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL DREAM ME UP
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL DREAM ME UP is strictly forbidden.
*
*   .--.
*   |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
*   |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
*   '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
*        w w w . d r e a m - m e - u p . f r       '
*
*  @author    Dream me up <prestashop@dream-me-up.fr>
*  @copyright 2007 - 2025 Dream me up
*  @license   All Rights Reserved
*}

<script type="text/javascript" src="{$module_dir|escape:'htmlall':'UTF-8'}dmuassocprodcat/views/js/ajax.js?token={$token|escape:'htmlall':'UTF-8'}"></script>
<link type="text/css" rel="stylesheet" href="{$module_dir|escape:'htmlall':'UTF-8'}dmuassocprodcat/views/css/dmuassocprodcat.css" />

<div id="dmuassocprodcat">
<div id="content" {if !is_ps_18}class="bootstrap"{/if}>
		<div class="row">
			<div class="product-tab-content">
				
				<!-- Left side -->
				<div class="panel product-tab left-side">
					<fieldset>
						<legend>{$txt_pdts_associes|escape:'htmlall':'UTF-8'}</legend>
						<h2>{$txt_filtre|escape:'htmlall':'UTF-8'}</h2>
						<table class="table">
							<tr>
								<td>{$txt_marque|escape:'htmlall':'UTF-8'}</td>
								<td>
									<select id="id_manufacturer" onchange="filtre_produits()" style="width:300px;">
										<option value="0">-- {$txt_tous|escape:'htmlall':'UTF-8'} --</option>
										{foreach from=$option_select_manufacturer item=option_manu}
											<option value="{$option_manu.id_manufacturer|escape:'htmlall':'UTF-8'}">{$option_manu.name|escape:'htmlall':'UTF-8'} ( {$option_manu.nb_products|escape:'htmlall':'UTF-8'} {l s='products' mod='dmuassocprodcat'})</option>
										{/foreach}
									</select>
								</td>
							</tr>
							<tr>
								<td>{$txt_categories|escape:'htmlall':'UTF-8'}</td>
								<td>
									<select id="id_categorie" onchange="filtre_produits()" style="width:300px;">
										<option value="0">-- {$txt_tous|escape:'htmlall':'UTF-8'} --</option>
										{foreach from=$option_select_categories item=option_cat}
											{math equation="x * y" x=$option_cat.level_depth y=5 assign="nb_repeat"}
											<option value="{$option_cat.id_category|escape:'htmlall':'UTF-8'}" {if $option_cat.selected}selected="selected"{/if}>
												{'&nbsp;'|str_repeat:$nb_repeat}{$option_cat.name|escape:'htmlall':'UTF-8'} 
												{if $option_cat.nb_products > 0}
													( 
														{$option_cat.nb_products|escape:'htmlall':'UTF-8'} {l s='products' mod='dmuassocprodcat'}
														{if $option_cat.nb_pas_def > 0}
															, {$option_cat.nb_pas_def|escape:'htmlall':'UTF-8'} {l s='not by default' mod='dmuassocprodcat'}
														{/if}
													) 
												{/if}
											</option>
										{/foreach}
									</select>
								</td>
							</tr>
							<tr>
								<td>{$txt_recherche|escape:'htmlall':'UTF-8'}</td>
								<td><input type="text" id="recherche" onkeyup="saisie_recherche()" /></td>
							</tr>
						</table>
						<br/>
						<h2>{$txt_resultats|escape:'htmlall':'UTF-8'}</h2>
						<div id="resultats_produits">
							<div class="alert alert-info">
								<p class="message_info">{$txt_prem_recherche|escape:'htmlall':'UTF-8'}...</p>
							</div>
						</div>
						<p>
							{$txt_categorie_defaut|escape:'htmlall':'UTF-8'} 
							<input type="radio" name="cat_def" id="chk_default_yes" />&nbsp;{$txt_oui|escape:'htmlall':'UTF-8'}
							<input type="radio" name="cat_def" id="chk_default_no" checked="checked" />&nbsp;{$txt_non|escape:'htmlall':'UTF-8'}
						</p>
						<a href="javascript:void(0);" id="a_bassocier" onclick="click_associer()" class="btn btn-default btn-block">
							{$txt_assoc_produits|escape:'htmlall':'UTF-8'}&nbsp;<i class="icon-arrow-right"></i>
						</a>
						<a href="javascript:void(0);" id="a_bassocier" onclick="click_deplacer()" class="btn btn-default btn-block">
							{$txt_depla_produits|escape:'htmlall':'UTF-8'}&nbsp;<i class="icon-arrow-right"></i>
						</a>
					</fieldset>
				</div>
				<!-- /Left side -->

				<!-- Right side -->
				<div class="panel product-tab right-side">
					<fieldset>
						<legend>{$txt_cat_assocs|escape:'htmlall':'UTF-8'}</legend>
						<h2>{$txt_cat_explorer|escape:'htmlall':'UTF-8'}</h2>
						<table class="table">
							<tr>
								<td>{$txt_categories|escape:'htmlall':'UTF-8'}</td>
								<td>
									<select id="id_categorie_assoc" onchange="change_categorie(this.value)" style="width:300px;">
										<option value="0">-- {$txt_aucune|escape:'htmlall':'UTF-8'} --</option>
										{foreach from=$option_select_categories item=option_cat}
											{math equation="x * y" x=$option_cat.level_depth y=5 assign="nb_repeat"}
											<option value="{$option_cat.id_category|escape:'htmlall':'UTF-8'}" {if $option_cat.selected}selected="selected"{/if}>
												{'&nbsp;'|str_repeat:$nb_repeat}{$option_cat.name|escape:'htmlall':'UTF-8'} 
												{if $option_cat.nb_products > 0}
													( 
														{$option_cat.nb_products|escape:'htmlall':'UTF-8'} {l s='products' mod='dmuassocprodcat'}
														{if $option_cat.nb_pas_def > 0}
															, {$option_cat.nb_pas_def|escape:'htmlall':'UTF-8'} {l s='not by default' mod='dmuassocprodcat'}
														{/if}
													) 
												{/if}
											</option>
										{/foreach}
									</select>
								</td>
							</tr>
						</table>
						<br/>
						<h2>{$txt_produits_assocs|escape:'htmlall':'UTF-8'}</h2>
						<div id="resultats_associations">
							<div class="alert alert-info">
								<p class="message_info">
									{$txt_cadre|escape:'htmlall':'UTF-8'}
									<br/>
									{$txt_choix|escape:'htmlall':'UTF-8'}
								</p>
							</div>
						</div>
						<br/>
						<a href="javascript:void(0);" id="a_bmakedefault" onclick="click_makedefault()" class="btn btn-default btn-block">
							<i class="icon-chevron-down"></i>&nbsp;{$txt_categorie_defaut|escape:'htmlall':'UTF-8'}
						</a>
						<a href="javascript:void(0);" id="a_bsuppassoc" onclick="click_desassocier()" class="btn btn-default btn-block">
							<i class="icon-remove"></i>&nbsp;{$txt_enlever_produits|escape:'htmlall':'UTF-8'}
						</a>
					</fieldset>
				</div>
				<!-- /Right side -->

				<div class="clearfix"></div>

				<!-- Actual configuration -->
				<fieldset>
					<div class="panel" id="fieldset_0">
						<div class="panel-heading">
							<i class="icon-info"></i>&nbsp;{$txt_infos|escape:'htmlall':'UTF-8'}
						</div>						
						<div class="form-wrapper">								
							<div class="form-group">																	
								{$txt_select|escape:'htmlall':'UTF-8'}
								
								{if $cnf_value == "0"}
									<strong>{$txt_all_products|escape:'htmlall':'UTF-8'}</strong>
								{else}
									<strong>{$txt_only_active|escape:'htmlall':'UTF-8'}</strong>
								{/if}	

								({$txt_switch|escape:'htmlall':'UTF-8'}<a href="{$cnf_link|escape:'quotes':'UTF-8'}">{$txt_cnfpage|escape:'htmlall':'UTF-8'}</a>)			
								<div class="clearfix"></div>																	
							</div>	
						</div>
					</div>
				</fieldset>
				<!-- /Actual configuration -->

			</div>
		</div>
	</div>
</div>