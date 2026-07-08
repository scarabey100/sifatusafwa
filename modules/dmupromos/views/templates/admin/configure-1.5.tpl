{**
* NOTICE OF LICENSE
*
* This source file is subject to a commercial license from SARL DREAM ME UP
* Use, copy, modification or distribution of this source file without written
* license agreement from the SARL DREAM ME UP is strictly forbidden.
*
*  .--.
*  |   |.--..-. .--, .--.--.   .--.--. .-.   .  . .,-.
*  |   ;|  (.-'(   | |  |  |   |  |  |(.-'   |  | |   )
*  '--' '   `--'`-'`-'  '  `-  '  '  `-`--'  `--`-|`-'
*       w w w . d r e a m - m e - u p . f r       '
*
* @author    Dream me up <prestashop@dream-me-up.fr>
* @copyright 2007 - 2024 Dream me up
* @license   All Rights Reserved
*}

<script type="text/javascript">
dlc_oldversion = true;
</script>
<link href="{$module_path|escape:'htmlall':'UTF-8'}/views/css/configure.css" rel="stylesheet" type="text/css" />
<div class="productTabs">
	<ul class="tab">
    	<li class="tab-row">
    		<a class="tab-page{if !isset($smarty.get.module_tab)} selected{/if}" href="javascript:;" rel="Informations">Informations</a>
        </li>
        {foreach from=$config_tabs item=tab_item key=key}
        <li class="tab-row">
        	<a class="tab-page{if isset($smarty.get.module_tab) && $smarty.get.module_tab == $key} selected{/if}" href="javascript:;" rel="{$key|escape:'htmlall':'UTF-8'}">{$tab_item.name|escape:'htmlall':'UTF-8'}</a>
        </li>
        {/foreach}
    </ul>
</div>
<script type="text/javascript">
$(document).ready(function()
{
	$(".product-tab-content").not(".active").hide();
	$(".productTabs .tab-page").each(function()
	{
		$(this).unbind('click').click(function()
		{
			// On désactive tous les onglets
			$(".productTabs .tab-page").removeClass("selected");
			
			$(this).addClass("selected");
			
			$(".product-tab-content").hide();
			$("#product-tab-content-"+$(this).attr("rel")).show();
		});
	});
});
</script>
<div class="tab_config_dmu_15">
    <div id="product-tab-content-Informations" class="product-tab-content{if !isset($smarty.get.module_tab)} active{/if}">
    	<div class="panel">
			<h3 class="tab"><i class="icon-info"></i> {l s='Informations' mod='dmupromos'}</h3>
			<div>
				<img src="{$module_path|escape:'htmlall':'UTF-8'}/views/img/logo-dream-me-up.png" style="float:right" />
				<h1>{l s='Module' mod='dmupromos'} {$module_name|escape:'htmlall':'UTF-8'}</h1>
				<p>{l s='Module version' mod='dmupromos'} : <strong>{$version_module|escape:'htmlall':'UTF-8'}</strong></p>
				<p>{$module_description|escape:'htmlall':'UTF-8'}</p>
				<h2>{l s='How to use the module ?' mod='dmupromos'}</h2>
				<p>{$module_how_to|escape:'quotes':'UTF-8'|replace:"\'":"'"}</p>
				<h2>{l s='Who are we ?' mod='dmupromos'}</h2>
				<p>{l s='Dream me up specializes in the creation of addons to improve the merchant experience, mainly in back office area. We develop tools to help you save time or to have a better view of your business. Discover now our addons for quick product administration, easy associations, or even real-time statistics.' mod='dmupromos'}</p>
				<ul>
					<li>{l s='Our' mod='dmupromos'} <a href="https://www.prestashop.com/fr/agences-web-partenaires/platinum/dreammeup" target="_blank">{l s='Prestashop partner dedicated page' mod='dmupromos'}</a></li>
					<li>{l s='Discover all our modules on our' mod='dmupromos'} <a href="http://addons.prestashop.com/fr/9_dream-me-up" target="_blank">{l s='Prestashop Addons dedicated page' mod='dmupromos'}</a></li>
				</ul>
                <h2>{$txt_follow|escape:'htmlall':'UTF-8'} !</h2>
                <ul>
                	<li>{$txt_follow|escape:'htmlall':'UTF-8'} <i class="icon-facebook-square"></i> {$txt_on|escape:'htmlall':'UTF-8'} Facebook {$txt_and|escape:'htmlall':'UTF-8'} <i class="icon-twitter-square"></i> {$txt_on|escape:'htmlall':'UTF-8'} Twitter {$txt_know_actu|escape:'htmlall':'UTF-8'}.</li>
                    <li>{$txt_follow_our|escape:'htmlall':'UTF-8'} <i class="icon-rss-square"></i> Blog {$txt_to_have_details|escape:'htmlall':'UTF-8'}.</li>
                </ul>
 				<h2>{l s='Support and documentation' mod='dmupromos'}</h2>
				<p><img src="{$module_path|escape:'htmlall':'UTF-8'}/views/img/icon_pdf.png" style="vertical-align:middle" /> <a href="{$module_path|escape:'quotes':'UTF-8'|replace:"\'":"'"}/{$documentation_pdf|escape:'quotes':'UTF-8'|replace:"\'":"'"}" target="_blank">{l s='Click here to open the module\'s documentation' mod='dmupromos'}</a></p>
				<p>{l s='The support of our modules is done exclusively' mod='dmupromos'} <a href="http://addons.prestashop.com/fr/9_dream-me-up" target="_blank">{l s='via Prestashop Addons' mod='dmupromos'}</a>. {l s='Visit the concerned module\'s page and use the link "Contact the Developer".' mod='dmupromos'}</p>
				<p>{l s='You must mention' mod='dmupromos'} :</p>
				<ul>
					<li>{l s='A detailed description of the problem' mod='dmupromos'}</li>
					<li>{l s='Your Prestashop version' mod='dmupromos'}: <strong>{$version_prestashop|escape:'htmlall':'UTF-8'}</strong></li>
					<li>{l s='Your module version' mod='dmupromos'} : <strong>{$version_module|escape:'htmlall':'UTF-8'}</strong></li>
				</ul>
			</div>
        </div>
    </div>
    {foreach from=$config_tabs item=tab_item key=key}
    <div id="product-tab-content-{$key|escape:'htmlall':'UTF-8'}" class="product-tab-content{if isset($smarty.get.module_tab) && $smarty.get.module_tab == $key} active{/if}">
    	<div class="panel">
        	<h3>{$tab_item.name|escape:'htmlall':'UTF-8'}</h3>
            <div>
            	{$content.$key|escape:'quotes':'UTF-8'|replace:"\'":"'"}
            </div>
        </div>
    </div>
    {/foreach}
</div>