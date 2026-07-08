{*
 * NOTICE OF LICENSE
 *
 * This product is licensed for one customer to use on one installation (test stores and multishop included).
 * Site developer has the right to modify this module to suit their needs, but can not redistribute the module in
 * whole or in part. Any other use of this module constitues a violation of the user agreement.
 *
 * DISCLAIMER
 *
 * NO WARRANTIES OF DATA SAFETY OR MODULE SECURITY
 * ARE EXPRESSED OR IMPLIED. USE THIS MODULE IN ACCORDANCE
 * WITH YOUR MERCHANT AGREEMENT, KNOWING THAT VIOLATIONS OF
 * PCI COMPLIANCY OR A DATA BREACH CAN COST THOUSANDS OF DOLLARS
 * IN FINES AND DAMAGE A STORES REPUTATION. USE AT YOUR OWN RISK.
 *
 * @author    idnovate.com <info@idnovate.com>
 * @copyright 2025 idnovate.com
 * @license   See above
 *}

{extends file='customer/page.tpl'}

{block name='page_title'}
    {l s='My stock alerts' mod='stockalert'}
{/block}

{block name='page_content'}
    {if $stockAlerts}
        <table class="table table-striped table-bordered table-labeled hidden-sm-down">
            <thead class="thead-default">
                <tr>
                    <th>{l s='Image' mod='stockalert'}</th>
                    <th>{l s='Product name' mod='stockalert'}</th>
                    <th>{l s='Subscribed on' mod='stockalert'}</th>
                    <th>&nbsp;</th>
                </tr>
            </thead>
            <tbody>
            {foreach from=$stockAlerts item=stockAlert}
                <tr>
                    <td class="text-sm-center">
                        <a target="_blank" href="{$link->getProductLink($stockAlert.id_product|intval)}" title="{$stockAlert.name|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}">
                            <img src="{$stockAlert.cover_url|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}"/>
                        </a>
                    </td>
                    <td>
                        <a target="_blank" href="{$link->getProductLink($stockAlert.id_product|intval)}" title="{$stockAlert.name|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}">
                            {$stockAlert.name|escape:'htmlall':'UTF-8'}
                        </a>
                        <br />
                        <span>{$stockAlert.attributes_small|escape:'html':'UTF-8'}</span>
                    </td>
                    <td>{$stockAlert.date_add|escape:'htmlall':'UTF-8'}</td>
                    <td class="text-sm-center order-actions">
                        <a href="{$link->getModuleLink('stockalert', 'account', ['process' => 'remove', 'stockalert_id_stockalert_subscriber' => $stockAlert.id_stockalert_subscriber|intval], true)|escape:'htmlall':'UTF-8'}">
                            {l s='Remove' mod='stockalert'}
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <div class="hidden-md-up">
            {foreach from=$stockAlerts item=stockAlert}
                <div class="row">
                    <div class="col-xs-4 text-xs-left">
                        <a target="_blank" href="{$link->getProductLink($stockAlert.id_product|intval)}" title="{$stockAlert.name|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}">
                            <img src="{$stockAlert.cover_url|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}"/>
                        </a>
                    </div>
                    <div class="col-xs-6">
                        <a href="{$link->getProductLink($stockAlert.id_product|intval)}" title="{$stockAlert.name|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}"><h3>{$stockAlert.name|escape:'htmlall':'UTF-8'}</h3></a>
                        <div class="attributes">{$stockAlert.attributes_small|escape:'html':'UTF-8'}</div>
                        <div class="date">{$stockAlert.date_add|escape:'htmlall':'UTF-8'}</div>
                    </div>
                    <div class="col-xs-2 text-xs-right">
                        <div>
                            <a href="{$link->getModuleLink('stockalert', 'account', ['process' => 'remove', 'stockalert_id_stockalert_subscriber' => $stockAlert.id_stockalert_subscriber|intval], true)|escape:'htmlall':'UTF-8'}">
                                <i class="material-icons">delete</i>
                            </a>
                        </div>
                    </div>
                </div>
            {/foreach}
        </div>
    {else}
        <div class="alert alert-warning warning">{l s='No stock alerts yet' mod='stockalert'}</div>
    {/if}
{/block}
