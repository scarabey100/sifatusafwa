{*
 *
 * GIFT CARD
 *
 * @category pricing_promotion
 * @author Timactive
 * @copyright TIMACTIVE 2013
 * @version 1.0.0
 *
 *************************************
 **            GIFT CARD			 *              
 *************************************
 *
 *}
{extends file='page.tpl'}
{block name='page_content'}
<div id="choicegiftcard" data-link-controller="{$linkcgc|escape:'htmlall':'UTF-8'}">
	{capture name=path}{l s='Gift card' mod='giftcard'}{/capture}
	
	{$front_content nofilter}{** CONTENT HTML / SETTING MODULE **}
	
	{if !isset($gc_templates) || count($gc_templates) == 0}
		<p class="warning">
					{l s='No model available' mod='giftcard'}
		</p>
	{elseif !isset($cards) || $cards|@count == 0}
		<p class="warning">
					{l s='No card available' mod='giftcard'}
		</p>
	{else}
       <form class="form" id="formgiftcard" action="{$linkcgc|escape:'quotes':'UTF-8'}" method="POST" target="_blank">

        {*** STEP 1 RECEPTION MODE ***}
        <section  id= "gc-step-receptmode">
            <h2>
                1 &bull;{l s='Select the reception mode' mod='giftcard'}
            </h2>
            <div class="gc-receptmode-option">
                {if $virtual_cards_available}

                <input class="ps-shown-by-js" id="receptmode_printathome" name="receptmode" type="radio" value="0">
                <label for="receptmode_printathome">
                    <svg height="20" viewBox="0 0 512 512" width="20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M437,129h-14V75c0-41.4-33.6-75-75-75H164c-41.4,0-75,33.6-75,75v54H75c-41.4,0-75,33.6-75,75v120c0,41.4,33.6,75,75,75h14v68c0,24.8,20.2,45,45,45h244c24.8,0,45-20.2,45-45v-68h14c41.4,0,75-33.6,75-75V204C512,162.6,478.4,129,437,129z M119,75c0-24.8,20.2-45,45-45h184c24.8,0,45,20.2,45,45v54H119V75z M393,467c0,8.3-6.7,15-15,15H134c-8.3,0-15-6.7-15-15V319h274V467zM482,324c0,24.8-20.2,45-45,45h-14v-50h9c8.3,0,15-6.7,15-15s-6.7-15-15-15h-24H104H80c-8.3,0-15,6.7-15,15s6.7,15,15,15h9v50H75c-24.8,0-45-20.2-45-45V204c0-24.8,20.2-45,45-45h29h304h29c24.8,0,45,20.2,45,45V324z" />
                        <path
                            d="M296,353h-80c-8.3,0-15,6.7-15,15s6.7,15,15,15h80c8.3,0,15-6.7,15-15S304.3,353,296,353z" />
                        <path
                            d="M296,417h-80c-8.3,0-15,6.7-15,15s6.7,15,15,15h80c8.3,0,15-6.7,15-15S304.3,417,296,417z" />
                        <path
                            d="M128,193H80c-8.3,0-15,6.7-15,15c0,8.3,6.7,15,15,15h48c8.3,0,15-6.7,15-15C143,199.7,136.3,193,128,193z" />
                    </svg>
                    {l s='Print at home' mod='giftcard'}
                </label>


                <input class="ps-shown-by-js" id="receptmode_mail" name="receptmode" type="radio" value="1">
                <label for="receptmode_mail">
                    <svg height="20" viewBox="0 0 32 32" width="20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="m30.853 13.87a15 15 0 0 0 -29.729 4.082 15.1 15.1 0 0 0 12.876 12.918 15.6 15.6 0 0 0 2.016.13 14.85 14.85 0 0 0 7.715-2.145 1 1 0 1 0 -1.031-1.711 13.007 13.007 0 1 1 5.458-6.529 2.149 2.149 0 0 1 -4.158-.759v-10.856a1 1 0 0 0 -2 0v1.726a8 8 0 1 0 .2 10.325 4.135 4.135 0 0 0 7.83.274 15.2 15.2 0 0 0 .823-7.455zm-14.853 8.13a6 6 0 1 1 6-6 6.006 6.006 0 0 1 -6 6z" />
                    </svg>
                    {l s='Send by e-mail' mod='giftcard'}
                </label>

                {/if}
                {if $physical_cards_available}

                <input class="ps-shown-by-js" id="receptmode_post" name="receptmode" type="radio" value="2">
                <label for="receptmode_post">
                    <svg height="20" viewBox="0 0 512 512" width="20" xmlns="http://www.w3.org/2000/svg">
                        <path
                            d="M461.913,83.478H50.087C22.467,83.478,0,105.974,0,133.565v244.87c0,27.622,22.498,50.087,50.087,50.087h411.826c27.578,0,50.087-22.453,50.087-50.087v-244.87C512,105.984,489.543,83.478,461.913,83.478z M460.563,116.87c-8.494,8.494-186.871,186.871-192.757,192.758c-6.527,6.526-17.085,6.526-23.612,0C238.303,303.735,59.927,125.36,51.437,116.87H460.563z M33.391,377.085V146.046l115.519,115.519L33.391,377.085z M62.567,395.13l109.954-109.954l48.061,48.061c19.526,19.528,51.304,19.529,70.834,0l48.061-48.061L449.432,395.13H62.567z M478.609,377.085L363.089,261.565l115.519-115.519V377.085z" />
                    </svg>
                    {l s='Send by post' mod='giftcard'}
                </label>

                {/if}
            </div>
            {if $virtual_cards_available}
            <div id="recepmode-mail-additional-information">
                <h3>{l s='Send by e-mail' mod='giftcard'}</h3>
                <div class="form-group">
                    <input name="mailto" type="text" class="form-control email"
                        placeholder="{l s='To : Email' mod='giftcard'}" />
                </div>
                <p>
                    {l s='The gift card will be sent by email to the recipient as soon as your payment is confirmed. You can also choose to send at a later date by selecting that date below.' mod='giftcard'}
                </p>
                <div class="form-group datesendcard">
                    {* Not delete is use in translat tools
                    {l s='January' mod='giftcard'}
                    {l s='February' mod='giftcard'}
                    {l s='March' mod='giftcard'}
                    {l s='April' mod='giftcard'}
                    {l s='May' mod='giftcard'}
                    {l s='June' mod='giftcard'}
                    {l s='July' mod='giftcard'}
                    {l s='August' mod='giftcard'}
                    {l s='September' mod='giftcard'}
                    {l s='October' mod='giftcard'}
                    {l s='November' mod='giftcard'}
                    {l s='December' mod='giftcard'}
                    *}
                    <select class="form-control" name="days" id="days">
                        <option value="">-</option>
                        {foreach from=$days item=v}
                        <option value="{$v|intval}" {if ($sl_day==$v)}selected="selected" {/if}>
                            {$v|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
                        {/foreach}
                    </select>
                    <select class="form-control" id="months" name="months">
                        <option value="">-</option>
                        {foreach from=$months key=k item=v}
                        <option value="{$k|intval}" {if ($sl_month==$k)}selected="selected" {/if}>{l s=$v
                            mod='giftcard'}&nbsp;</option>
                        {/foreach}
                    </select>
                    <select class="form-control" id="years" name="years">
                        <option value="">-</option>
                        {foreach from=$years item=v}
                        <option value="{$v|intval}" {if ($sl_year==$v)}selected="selected" {/if}>
                            {$v|escape:'htmlall':'UTF-8'}&nbsp;&nbsp;</option>
                        {/foreach}
                    </select>
                </div>
            </div>
            {/if}
	</section>
	{*** END STEP 1 RECEPTION MODE ***}

	{*** STEP 2 BLOCK SELECT TEMPLATE ***}
    <section id="gc-step-template">
        <h2>
        2 &bull;{l s='Select a template' mod='giftcard'}
        </h2>
        <div id="templates_block">
            {if isset($gc_tags) && $gc_tags && $gc_tags|@count > 0}
            <ul  class="gctabs">
                <li>
                    <a id="tab_template_all"  href="javascript:;" class="selected tab_template" data-tab="block_templates_all">
                        {l s='All' mod='giftcard'}&nbsp;<em class="text-muted">(<span class="ta-gc-number">{$gc_templates|@count|intval}</span>)</em>
                    </a>
                </li>										
                {foreach from=$gc_tags item=tag} 
                    {if (isset($templatesGroupTag[$tag.id_gift_card_tag]) && $templatesGroupTag[$tag.id_gift_card_tag]|@count > 0)}
                        <li>
                            <a id="tab_template_tag{$tag.id_gift_card_tag|intval}" href="javascript:;"  data-tab="block_templates_in_tags{$tag.id_gift_card_tag|intval}"  class="tab_template">
                                {$tag.name|escape:'htmlall':'UTF-8'}&nbsp;<em class="text-muted">(<span class="ta-gc-number">{$templatesGroupTag[$tag.id_gift_card_tag]|@count}</span>)</em>
                            </a>
                        </li>
                    {/if}
                {/foreach}
            </ul>
            {/if}
            {******* TAB CONTENT ALL TEMPLATES ******}
            <div id="block_templates_all" class="gctab_content selected">
                {if isset($gc_templates) && count($gc_templates) > 0}
                    <ul class="row">
                        {foreach from=$gc_templates item=template name=thumbnails}
                            {if $template.id_gift_card_template > 0}
                            <li class="col-sm-6 col-md-4 col-lg-3 template_item template_item{$template.id_gift_card_template|intval} {if isset($template_default) && $template_default->id==$template.id_gift_card_template}js-template-default selected{/if}"  data-physicaluse="{$template.physicaluse|intval}" data-virtualuse="{$template.virtualuse|intval}">
                                    <a href="javascript:;"  class="link_template" rel="link_template{$template.id_gift_card_template|intval}">
                                        <img src="{$link->getMediaLink("`$giftcard_templates_dir``$template.id_gift_card_template`/`$template.id_gift_card_template`-front{if $template.issvg}-{$id_lang|intval}{/if}.jpg")|escape:'quotes':'UTF-8'}"
                                            class="img-fluid" alt="{$template.name|escape:'htmlall':'UTF-8'}">
                                    </a>
                            </li>
                            {/if}
                        {/foreach}
                    </ul>
                {/if}
            </div>
            {******* END TAB CONTENT ALL TEMPLATES ******}
            {******* TAB CONTENT BY FILTER TAG ******}
            {if isset($gc_tags) && $gc_tags && $gc_tags|@count > 0}
                {foreach from=$gc_tags item=tag} 
                    <div id="block_templates_in_tags{$tag.id_gift_card_tag|intval}" class="rte gctab_content">
                    {if isset($templatesGroupTag[$tag.id_gift_card_tag]) && $templatesGroupTag[$tag.id_gift_card_tag]|@count > 0}
                        <ul class="row">
                            {foreach from=$templatesGroupTag[$tag.id_gift_card_tag] item=template}
                            <li class="col-sm-6 col-md-4 col-lg-3 template_item template_item{$template.id_gift_card_template|intval} {if isset($template_default) && $template_default->id==$template.id_gift_card_template}selected{/if}"  data-physicaluse="{$template.physicaluse|intval}" data-virtualuse="{$template.virtualuse|intval}">
                                <a href="javascript:;"  class="link_template" rel="link_template{$template.id_gift_card_template|intval}">
                                    <img src="{$link->getMediaLink("`$giftcard_templates_dir``$template.id_gift_card_template`/`$template.id_gift_card_template`-front{if $template.issvg}-{$id_lang}{/if}.jpg")|escape:'quotes':'UTF-8'}"
                                        class="img-fluid" alt="{$template.name|escape:'htmlall':'UTF-8'}">
                                </a>
                            </li>
                            {/foreach}
                        </ul>
                    {/if}
                    </div>
                {/foreach}
            {/if}
            {******* END TAB CONTENT BY FILTER TAG ******}
        </div>
	</section>
	{*** END STEP 2 BLOCK SELECT TEMPLATE ***}

	{*** STEP 3 GIFT CARD INFORMATION ***}
	<section  id="gc-step-information">
        <h2>3 &bull;{l s='Gift card information' mod='giftcard'}</h2>
        <input type="hidden" name="action" value="" />
        <input type="hidden" name="id_lang" value="{$id_lang|intval}" />
        <input type="hidden" name="token" value="{$token_page|escape:'html':'UTF-8'}" />
        <input type="hidden" name="id_gift_card_template" id="id_gift_card_template" value="{$template_default->id|intval}"/>
        <div class="form-group">
            <label for="id_product_virtual">{l s='Amount' mod='giftcard'}</label>
            <select class="form-control" name="id_product_virtual" id="ta_gc_products_virtual">
            {foreach from=$cards item=carditem name=foo}
                {if $carditem.virtualcard == 1}
                    <option value="{$carditem.id_product|intval}" {if ((!isset($card)&&$carditem.isdefaultgiftcard) || (isset($card) && $card->id==$carditem.id_product))}selected{/if}>
                    {$carditem.price_dp|escape:'htmlall':'UTF-8'}
                    </option>
                {/if}
            {/foreach}
            </select>
            <select class="form-control" name="id_product_physical" id="ta_gc_products_physical">
            {foreach from=$cards item=carditem name=foo}
                {if $carditem.virtualcard == 0}
                    <option value="{$carditem.id_product|intval}" {if ((!isset($card)&&$carditem.isdefaultgiftcard) || (isset($card) && $card->id==$carditem.id_product))}selected{/if}>
                    {$carditem.price_dp|escape:'htmlall':'UTF-8'}
                    </option>
                {/if}
            {/foreach}
            </select>
        </p>
        <div class="form-group">
            <input name="from" type="text" class="form-control input_user_from" placeholder="{l s='From : your lastname' mod='giftcard'}"  />
        </div>
        <div class="form-group">
            <input name="lastname" type="text" class="form-control input_user_to" placeholder="{l s='To : Lastname' mod='giftcard'}"  />
        </div>
        <div class="form-group">
        <textarea name="message" class="form-control textarea_comment" rows="7" placeholder="{l s='Indicate your message' mod='giftcard'}" onkeyup="countChar(this)"></textarea>
        <p id="remaining_characters" class="small font-italic">(<span id="charNum">200</span>&nbsp;{l s='remaining characters' mod='giftcard'})</p>
        </div>
        {*** BUTTON SUBMIT ***}
        <div class="ta-gc-submit mt-3">
            <button class="btn btn-outline-primary mr-2" type="button"  disabled  data-ta-action="preview">
                {l s='Preview' mod='giftcard'}
            </button>
            <button class="btn btn-primary" type="button"  disabled  data-ta-action="add_to_cart">
                {l s='Add to cart' mod='giftcard'}
            </button>
        </div>
        {*** END BUTTON SUBMIT ***}
	</section>
	{*** END STEP 3 GIFT CARD INFORMATION ***}

	<br>
	{* is use for display ajax messages errors, sucess*}
	<div class="messages"></div>
	
	</form>
	{/if}
</div>
{/block}
