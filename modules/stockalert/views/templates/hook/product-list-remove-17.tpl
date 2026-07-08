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
<div class="stockalert-list-remove" id="stockalert-list-remove-{$id_stockalert_subscriber|intval}">
    <form id="stockalert-list-remove-form-{$id_stockalert_subscriber|intval}" method="POST" action="{$link->getModuleLink('stockalert', 'account', ['process' => 'remove', 'stockalert_id_product' => {$id_product|intval}, 'stockalert_id_stockalert_subscriber' => {$id_stockalert_subscriber|intval}], true) nofilter}">
        <div>
            <input type="hidden" name="stockalert_id_stockalert_subscriber_{$id_stockalert_subscriber|intval}" value="{$id_stockalert_subscriber|intval}"/>
            <div class="text-center text-xs-center stockalert_button_container stockalert_button_container_{$id_stockalert_subscriber|intval}">
                <button form="stockalert-list-remove-form-{$id_stockalert_subscriber|intval}" data-id="{$id_stockalert_subscriber|intval}" class="btn btn-secondary" type="submit" rel="nofollow">{l s='Remove my stock alert' mod='stockalert'}</button>
            </div>
            <div class="stockalert_result_{$id_stockalert_subscriber|intval}"></div>
        </div>
    </form>
</div>
