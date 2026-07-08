{**
 * 2012 - 2025 HiPresta
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0).
 * It is also available through the world-wide-web at this URL: https://opensource.org/licenses/AFL-3.0
 *
 * @author    HiPresta <support@hipresta.com>
 * @copyright HiPresta 2025
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *
 * @website   https://hipresta.com
 *}
<div class="col-lg-2">
    <div class="list-group">
        {foreach from=$tabs key=tab_key item=tab}
            <a 
                {if $tab_key == 'version' || $tab_key == 'rateMe'} style="margin-top:30px;" {/if}
                class="list-group-item {if $tab_key == $active_tab || ($active_tab == '' && $tab_key == 'faqs')}active{/if}"
                href="{if isset($tab.url)}{$tab.url nofilter}{else}{$module_url|escape:'htmlall':'UTF-8'}&{$module_tab_key}={$tab_key|escape:'htmlall':'UTF-8'}{/if}"
                {if isset($tab.target)}
                    target="{$tab.target}"
                {/if}
            >
                {if isset($tab.icon)}
                    <i class="{$tab.icon}" {if $tab_key == 'rateMe'}style="color: orange;"{/if}></i>
                {/if}
                {if $tab_key != 'version'}
                    {$tab.title|escape:'htmlall':'UTF-8'} 
                    {if isset($tab.counterTotal)}
                        <span class="hi-module-menu-tab-counter">({$tab.counterTotal|intval})</span>
                        {if isset($tab.counterNew) && $tab.counterNew > 0}
                            <span class="hi-module-menu-tab-counter hi-module-menu-tab-counter-new">({$tab.counterNew|intval})</span>
                        {/if}
                    {/if}
                {else}
                    {$tab.title|escape:'htmlall':'UTF-8'} - {$module_version|escape:'html':'UTF-8'}
                {/if}
            </a>
        {/foreach}
    </div>
</div>