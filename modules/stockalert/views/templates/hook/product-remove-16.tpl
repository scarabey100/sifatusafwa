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
<div class="stockalert-remove alert alert-info" style="display: none;">
    <form method="POST" action="{$link->getModuleLink('stockalert', 'account', ['process' => 'remove'], true)|escape:'html':'UTF-8'}">
        <div>
            <input type="hidden" name="stockalert_id_stockalert_subscriber" value=""/>
            <p class="text-center">
                <button class="btn btn-default btn-secondary" type="submit" rel="nofollow">{l s='Remove my stock alert' mod='stockalert'}</button>
            </p>
            <p>
                <span class="result"></span>
                <span class="disclaimer">{l s='You are subscribed to receive an email once the product becomes available.' mod='stockalert'}</span>
            </p>
        </div>
    </form>
</div>
