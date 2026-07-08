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
{extends file="helpers/list/list_content.tpl"}

{block name="td_content"}
    {if $key == 'status'}
        {if $table == 'hifaq'}
            {assign var="id" value=$tr.id_faq}
        {else if $table == 'hifaqblock'}
            {assign var="id" value=$tr.id_block}
        {else}
            {assign var="id" value=$tr.id}
        {/if}
        <a data-id = {$id|escape:'htmlall':'UTF-8'} data-status = {$tr.active|escape:'htmlall':'UTF-8'} data-table-name = {$table|escape:'htmlall':'UTF-8'} class="{$table}-status btn {if $tr.active == '0'}btn-danger{else}btn-success{/if}" 
        href="#" title="{if $tr.active == '0'}{l s='Disabled' mod='hifaq'}{else}{l s='Enabled' mod='hifaq'}{/if}">
            <i class="{if $tr.active == '0'}icon-remove {else}icon-check{/if}"></i>
        </a>
    {elseif $key == 'custom_hook'}
        {literal}{{/literal}hook h="displayHiFAQ" id="{$tr.id_block|escape:'htmlall':'UTF-8'}"{literal}}{/literal}
    {elseif $key == 'sort'}
        {if isset($params.disableSort) && $params.disableSort}
            --
        {else}
            <a href="#">
                <i class="icon-move"></i>
            </a>
        {/if}
    {elseif $key == 'faqCategories'}
        {if $tr.categories|count < 3}
            <div>
                {foreach from=$tr.categories item=category name=faqCategories}
                    {$category.name|escape:'htmlall':'UTF-8'}
                    <br>
                {/foreach}
            </div>
        {else}
            <div
                data-container=".column-faqcategories"
                data-toggle="popover"
                data-trigger="hover"
                data-placement="top"
                data-html="true"
                data-content="
                    {foreach $tr.categories as $category}
                        {$category.name|escape:'htmlall':'UTF-8'}
                        <br>
                    {/foreach}
                "
            >
                {foreach from=$tr.categories item=category name=faqCategories}
                    {if $smarty.foreach.faqCategories.index == 2}
                        {break}
                    {/if}
                    {$category.name|escape:'htmlall':'UTF-8'}
                    <br>
                {/foreach}
                ...
            </div>
        {/if}
    {elseif isset($params.type) && $params.type == 'actionButton'}
        <a
            data-id-element="{$tr[$identifier]|intval}"
            data-action-type="{$params.actionType|escape:'htmlall':'UTF-8'}"
            class="btn btn-default hi-presta-module-action-button hi-presta-module-action-button-{$params.actionType|escape:'htmlall':'UTF-8'}"
            href="#"
            title="{$params.actionTitle|escape:'htmlall':'UTF-8'}"
        >
            <i class="{$params.actionIcon|escape:'htmlall':'UTF-8'}"></i>

            {if isset($params.actionCount)}
                <span class="badge badge-success hi-module-badge">{$tr[$params.actionCount]|intval}</span>
            {/if}
        </a>
    {elseif isset($params.type) && $params.type == 'multiActionButton'}
        <a
            data-id-element="{$tr[$identifier]|intval}"
            data-action-type="{$params.actionType|escape:'htmlall':'UTF-8'}"
            class="btn btn-default hi-presta-module-action-button hi-presta-module-action-button-{$params.actionType|escape:'htmlall':'UTF-8'}"
            href="#"
            title="{$params.actionTitle|escape:'htmlall':'UTF-8'}"
        >
            <i class="{$params.actionIcon|escape:'htmlall':'UTF-8'}"></i>

            {if isset($params.actionCount)}
                {foreach from=$params.actionCount item=actionName name=actionCount}
                    {if !$smarty.foreach.actionCount.first}
                        /
                    {/if}
                    <span class="badge badge-success hi-module-badge hi-module-badge-{$actionName|escape:'htmlall':'UTF-8'}">{$tr[$actionName]|intval}</span>
                {/foreach}
            {/if}
        </a>
    {elseif $key == 'feedback'}
        <button class="btn {if $tr.feedback == 0}btn-danger{else}btn-success{/if}" type="button">
            <i class="{if $tr.feedback == 0}hi-faq-sad-icon{else}hi-faq-good-icon{/if}"></i>
        </button>
    {else}
        {$smarty.block.parent}
    {/if}
{/block}