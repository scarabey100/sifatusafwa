{**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 *}
<div class="row">
    <div class="col-lg-12 alert alert-warning">
        {l s='Select the modules that install cookies from this finality. The selected modules will be blocked until the customer gives the consent to this finality, so the module won\'t be able to install the cookies.' mod='cookiesplus'}
        <br /><br />
        {l s='If a module is selected in more than one finality, the module won\'t be unblocked until the customer gives consent to all the finalities involved.' mod='cookiesplus'}
    </div>

    <div class="col-lg-12 alert alert-info cookiesplus-module-list">
        <table id="cookiesplus-module-list-legend">
            <tbody>
                <tr>
                    <td>
                        {l s='Legend:' mod='cookiesplus'}
                    </td>
                    <td class="module-disabled">
                        <img src="../modules/cookiesplus/views/img/logo-not-found.png" width="32" height="32">
                        {l s='Module disabled' mod='cookiesplus'}
                    </td>
                    <td class="module-blocked">
                        <img src="../modules/cookiesplus/views/img/logo-not-found.png" width="32" height="32">
                        {l s='Module blocked' mod='cookiesplus'}
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="col-lg-12 cookiesplus-module-list">
        <table id="cookiesplus-module-list-table">
            <tbody>
                <tr>
                    {foreach name=modules from=$allModules item=module}
                    {if ($smarty.foreach.modules.iteration-1) && ($smarty.foreach.modules.iteration-1) % 3 == 0}
                </tr>
                <tr>
                    {/if}
                    <td class="{if !$module['active']}module-disabled{/if} {if isset($module['checked']) && $module['checked']}module-blocked{/if}">
                        <img src="../modules/{$module['name']|escape:'htmlall':'UTF-8'}/logo.{if version_compare($smarty.const._PS_VERSION_, '1.5', '>=')}png{else}gif{/if}"
                             onerror="this.onerror=null; this.src='../modules/cookiesplus/views/img/logo-not-found.png';"
                             alt="{$module['displayName']|escape:'quotes':'UTF-8'}"
                             {if version_compare($smarty.const._PS_VERSION_, '1.5', '>=')}width="32" height="32" {else}width="16"
                             height="16"{/if} />
                        <label style="float: none;"
                               for="{$fieldName|escape:'htmlall':'UTF-8'}_module_{$module['id_module']|escape:'htmlall':'UTF-8'}">
                            <input type="checkbox" name="{$fieldName|escape:'htmlall':'UTF-8'}[]"
                                   id="{$fieldName|escape:'htmlall':'UTF-8'}_module_{$module['id_module']|escape:'htmlall':'UTF-8'}"
                                   value="{$module['id_module']|escape:'htmlall':'UTF-8'}"
                                   {if isset($module['checked']) && $module['checked']}checked=checked{/if}
                                    onclick="$(this).closest('td').toggleClass('checked')"
                            />
                            {$module['displayName']|escape:'quotes':'UTF-8'}</label> ({$module['name']|escape:'htmlall':'UTF-8'})
                    </td>
                    {/foreach}
                </tr>
            </tbody>
        </table>
    </div>
</div>
