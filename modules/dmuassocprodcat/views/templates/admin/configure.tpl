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
<div class="productTabs col-lg-2 col-md-3">
	<div class="list-group">
    	<a class="list-group-item {if $form_id==""}active{/if}" href="javascript:;" rel="Informations">Informations</a>
        {foreach from=$config_tabs item=tab_item key=key}
            <a class="list-group-item {if {$form_id|lower}=={$key|lower}}active{/if}" href="javascript:;" rel="{$key|escape:'htmlall':'UTF-8'}">{$tab_item.name|escape:'htmlall':'UTF-8'}</a>
        {/foreach}
    </div>
</div>
<script type="text/javascript">
$(document).ready(function()
{
	$(".product-tab-content").not(".active").hide();
	$(".productTabs .list-group-item").each(function()
	{
		$(this).unbind('click').click(function()
		{
			// On désactive tous les onglets
			$(".productTabs .list-group-item").removeClass("active");
			
			$(this).addClass("active");
			
			$(".product-tab-content").hide();
			$("#product-tab-content-"+$(this).attr("rel")).show();
		});
	});
});
</script>
<div class="form-horizontal col-lg-10 col-md-9">
    <div id="product-tab-content-Informations" class="product-tab-content {if $form_id==""}active{/if}">
    	<div class="panel">
        	<h3 class="tab"><i class="icon-info"></i> Informations</h3>
           	<div>
            	<img src="{$path_module|escape:'htmlall':'UTF-8'}/views/img/logo-dream-me-up.png" style="float:right" />
                <h1>Module {$nom_module|escape:'htmlall':'UTF-8'}</h1>
                <p>{$txt_module_version|escape:'htmlall':'UTF-8'} : <strong>{$version_module|escape:'htmlall':'UTF-8'}</strong></p>
                <p>{$description_complete|escape:'htmlall':'UTF-8'}</p>
                <h2>{$txt_howto|escape:'htmlall':'UTF-8'}</h2>
                <p>{$comment_acceder|escape:'quotes':'UTF-8'|replace:"\'":"'"}</p>
                <h2>{$txt_qui|escape:'htmlall':'UTF-8'}</h2>
                <p>{$txt_dmu|escape:'htmlall':'UTF-8'}</p>
                <ul>
                    <li>{$txt_notre|escape:'htmlall':'UTF-8'} <a href="https://experts.prestashop.com/English/experts/partner/2118692/dream-me-up" target="_blank">{$txt_page|escape:'htmlall':'UTF-8'}</a></li>
                    <li>{$txt_decouvrez|escape:'htmlall':'UTF-8'} <a href="{$lnk_page_prestashop|escape:'htmlall':'UTF-8'}" target="_blank">{$txt_addons_page|escape:'htmlall':'UTF-8'}</a></li>
                </ul>
                <h2>{$txt_support|escape:'htmlall':'UTF-8'}</h2>
                {if $path_documentation}<p><img src="{$path_module|escape:'htmlall':'UTF-8'}/views/img/icon_pdf.png" style="vertical-align:middle" /> <a href="{$path_module|escape:'htmlall':'UTF-8'}/{$path_documentation|escape:'htmlall':'UTF-8'}" target="_blank">{$txt_open_doc|escape:'htmlall':'UTF-8'}</a></p>{/if}
                <p>{$txt_support_only|escape:'htmlall':'UTF-8'} <a href="{$lnk_page_prestashop|escape:'htmlall':'UTF-8'}" target="_blank">{$txt_interm|escape:'htmlall':'UTF-8'}</a>. {$txt_rdv|escape:'quotes':'UTF-8'|replace:"\'":"'"}.</p>
                <p><strong>{$txt_mention|escape:'htmlall':'UTF-8'} :</strong></p>
                <ul>
                    <li>{$txt_desc_problem|escape:'htmlall':'UTF-8'}</li>
                    <li>{$txt_version_presta|escape:'htmlall':'UTF-8'} : <strong>{$version_prestashop|escape:'htmlall':'UTF-8'}</strong></li>
                    <li>{$txt_version_module|escape:'htmlall':'UTF-8'} : <strong>{$version_module|escape:'htmlall':'UTF-8'}</strong></li>
                </ul>
            </div>
        </div>
    </div>
    {foreach from=$config_tabs item=tab_item key=key}
    <div id="product-tab-content-{$key|escape:'htmlall':'UTF-8'}" class="product-tab-content {if {$form_id|lower}=={$key|lower}}active{/if}">
    {if !$tab_item.is_helper}
    	<div class="panel">
        	<h3 class="tab">{$tab_item.name|escape:'htmlall':'UTF-8'}</h3>
            <div>
            {/if}

            	{*HTML CONTENT*}
                    {$content_html.$key|escape:'quotes':'UTF-8'|replace:"\'":"'"}
                {*HTML CONTENT*}
            	
            {if !$tab_item.is_helper}
            </div>
        </div>
     {/if}
    </div>
    {/foreach}
</div>
<div class="clearfix"></div>