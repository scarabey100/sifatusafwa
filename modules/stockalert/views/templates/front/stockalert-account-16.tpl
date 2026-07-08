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

{capture name=path}
    <a href="{$link->getPageLink('my-account', true)|escape:'html':'UTF-8'}" title="{l s='My account' mod='stockalert'}" rel="nofollow">{l s='My account' mod='stockalert'}</a><span class="navigation-pipe">{$navigationPipe|escape:'html':'UTF-8'}</span>{l s='My stock alerts' mod='stockalert'}
{/capture}

<div>
    <h1 class="page-heading">{l s='My stock alerts' mod='stockalert'}</h1>

    {include file="$tpl_dir./errors.tpl"}

    {if isset($success) && $success}
        <div class="alert alert-success">
            {$success|escape:'html':'UTF-8'}
        </div>
    {/if}

    {if $stockAlerts}
        <table class="table table-bordered table-labeled">
            <thead>
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
                    <td class="text-center">
                        <img src="{$stockAlert.cover_url|escape:'htmlall':'UTF-8'}" alt="{$stockAlert.name|escape:'htmlall':'UTF-8'}"/>
                    </td>
                    <td>
                        {$stockAlert.name|escape:'htmlall':'UTF-8'}
                        <br />
                        <span>{$stockAlert.attributes_small|escape:'htmlall':'UTF-8'}</span>
                    </td>
                    <td>{$stockAlert.date_add|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        <a class="btn btn-default button button-small" href="{$link->getModuleLink('stockalert', 'account', ['process' => 'remove', 'stockalert_id_stockalert_subscriber' => $stockAlert.id_stockalert_subscriber|intval], true)|escape:'html':'UTF-8'}" title="{l s='Remove' mod='stockalert'}">
                        <span>{l s='Remove' mod='stockalert'}
                            <i class="icon-remove right"></i>
                        </span>
                        </a>
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    {else}
        <div class="warning alert alert-warning">{l s='No stock alerts yet' mod='stockalert'}</div>
    {/if}
</div>
