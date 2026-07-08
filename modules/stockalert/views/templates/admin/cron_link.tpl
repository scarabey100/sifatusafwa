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

<div class="alert alert-warning">
    {l s='You only need to schedule the cron job if you do not update stock from the back office or via the web service.' mod='stockalert'}
</div>

<div class="form-group">
    <label class="control-label col-lg-4">
        {l s='Cron URL:' mod='stockalert'}
    </label>
    <label class="control-label col-lg-8" style="text-align: left">
        <a href="{$cron_job_link|escape:'htmlall':'UTF-8'}" target="_blank">{$cron_job_link|escape:'htmlall':'UTF-8'}</a>
    </label>
</div>
