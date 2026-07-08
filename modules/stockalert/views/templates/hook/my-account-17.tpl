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

{if $SA_ICONS_LIBRARY == '1'}
    <a class="col-lg-4 col-md-6 col-sm-6 col-xs-12" href="{$link->getModuleLink('stockalert', 'account', [], true)|escape:'html':'UTF-8'}" title="{l s='My stock alerts' mod='stockalert'}">
        <span class="link-item">
            <i class="material-icons">&#xE151;</i>
            {l s='My stock alerts' mod='stockalert'}
        </span>
    </a>
{elseif $SA_ICONS_LIBRARY == '2'}
    <div class="list-group-item">
        <a href="{$link->getModuleLink('stockalert', 'account', [], true)|escape:'html':'UTF-8'}" style="cursor:pointer" title="{l s='My stock alerts' mod='stockalert'}">
            <i class="fto-volume-high mar_r4 fs_lg"></i></i>{l s='My stock alerts' mod='stockalert'}
        </a>
    </div>
{else}
    <a href="{$link->getModuleLink('stockalert', 'account', [], true)|escape:'html':'UTF-8'}" class="col-lg-4 col-md-6 col-sm-6 col-xs-12" style="cursor:pointer" title="{l s='My stock alerts' mod='stockalert'}">
        <span class="link-item">
            <i class="fa fa-bell fa-fw" aria-hidden="true"></i>{l s='My stock alerts' mod='stockalert'}
        </span>
    </a>
{/if}

