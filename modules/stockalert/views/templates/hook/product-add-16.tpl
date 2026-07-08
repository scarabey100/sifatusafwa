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
<div class="stockalert-add alert alert-info" style="display: none;">
    <form method="POST" action="{$link->getModuleLink('stockalert', 'account', ['process' => 'add'], true)|escape:'html':'UTF-8'}">
        <div>
            <span class="text-uppercase"><strong>{l s='Do you want to receive an alert when product is back in stock?' mod='stockalert'}</strong></span>
            <br /><br />
            {if $is_guest}
                <p>
                    <input class="form-control" type="email" name="stockalert_customer_email" placeholder="{l s='Email address' mod='stockalert'}"/>
                </p>
            {/if}
            <input type="hidden" name="stockalert_id_product" value=""/>
            <input type="hidden" name="stockalert_id_product_attribute" value=""/>
            <input type="hidden" name="stockalert_send_mail" value=""/>
            <input type="hidden" name="stockalert_id_stockalert_alert" value=""/>

            <p class="text-center">
                <button class="btn btn-default btn-secondary" type="submit" rel="nofollow">{l s='Notify me when this product becomes available again' mod='stockalert'}</button>
                {hook h='displayGDPRConsent' id_module=$id_module}
            </p>

            {if $displayCaptcha}
                <p class="input-group stockalert_captcha_{$id_product|intval}" style="margin-bottom: 1rem;">
                    <input form='stockalert-list-add-popup-form-{$id_product|intval}' name="stockalert_captcha_code" placeholder="{l s='Introduce the captcha value' mod='stockalert'}" type="text" value="" class="input-group form-control" />
                    <img src="{$captchaController|escape:'html':'UTF-8'}" alt="Captcha" title="Captcha" />
                </p>
            {/if}

            <p>
                <span class="result"></span>
                <span class="disclaimer">{l s='We will send you an email once the product becomes available. Your email address will not be shared with anyone else.' mod='stockalert'}</span>
            </p>
        </div>
    </form>
</div>

<script type="text/javascript">
    // GDPR validation when content is refreshed by JS
    if (window.jQuery) {
        $(document).on("change" ,'.stockalert-add input[name=psgdpr_consent_checkbox]', function() {
            if ($(this).prop('checked') == true) {
                $(this).closest('form').find('[type="submit"]').removeAttr('disabled');
            } else {
                $(this).closest('form').find('[type="submit"]').attr('disabled', 'disabled');
            }
        });
    };
</script>
