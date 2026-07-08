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
<script type="text/javascript" src="{$hifaq_path|escape:'htmlall':'UTF-8'}views/js/admin.js"></script>

<div id="extra-faq-list" class="combinations-list">
    <a class="btn btn-action m-b-2 add-new-product-faq"  href="#" data-id-product="{$id_product|intval}">
        <i class="material-icons">add_circle</i> {l s='Add a new FAQ' mod='hifaq'}
    </a>
    <table class="table table-striped table-no-bordered">
        <thead id="combinations_thead">
            <tr class="uppercase">
                <th>{l s='Id' mod='hifaq'}</th>
                <th>{l s='Name' mod='hifaq'}</th>
                <th>{l s='Question' mod='hifaq'}</th>
                <th>{l s='Status' mod='hifaq'}</th>
                <th class="text-xs-right">{l s='Actions' mod='hifaq'}</th>
            </tr>
        </thead>
        <tbody class="js-combinations-list panel-group accordion" id="accordion_combinations">
            {if !empty($all_extra_faqs)}
                {foreach from=$all_extra_faqs item=extra}
                    <tr class="combination" data-id-row="{$extra['id_faq']}" data-id-product="{$id_product|intval}">
                        <td>{$extra['id_faq']}</td>
                        <td>{$extra['title']}</td>
                        <td>{$extra['question']}</td>
                        <td>
                            <a data-id = {$extra['id_faq']|escape:'htmlall':'UTF-8'} data-status = {$extra['active']|escape:'htmlall':'UTF-8'} data-table-name ='hifaqextra' class="hifaqextra-status btn {if $extra['active'] == '0'}btn-danger{else}btn-success{/if}" 
                            href="#" title="{if $extra['active'] == '0'}{l s='Disabled' mod='hifaq'}{else}{l s='Enabled' mod='hifaq'}{/if}">
                                {if $psv >= 1.7}
                                    <i class="material-icons">{if $extra['active'] == '0'}cancel{else}check{/if}</i>
                                {else}
                                    <i class="{if $extra['active'] == '0'}icon-remove {else}icon-check{/if}"></i>
                                {/if}
                            </a>
                        </td>
                        <td class="attribute-actions">
                            <div class="btn-group btn-group-sm" role="group">
                                <a href="#" class="btn btn-invisible btn-sm edit_faq"><i class="material-icons">mode_edit</i></a>
                                <a href="#" class="btn btn-invisible btn-sm delete_faq"><i class="material-icons">delete</i></a>
                            </div>
                        </td>
                  </tr>
                {/foreach}
            {else}
                <td class="list-empty" colspan="5">
                    <div class="list-empty-msg">
                        <i class="icon-warning-sign list-empty-icon"></i>
                        {l s='No records found' mod='hifaq'}
                    </div>
                </td>
            {/if}
        </tbody>
    </table>
</div>
