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
<div class="stockalert-list-add" id='stockalert-list-add-{$id_product|intval}'>
    <div class="text-center text-xs-center button-container">
        <button class="btn btn-secondary" type="submit" rel="nofollow">{l s='Notify me when this product becomes available again' mod='stockalert'}</button>
    </div>
</div>

<div class="stockalert-list-add-popup stockalert-add" id='stockalert-list-add-popup-{$id_product|intval}'>
    <form id="stockalert-list-add-popup-form-{$id_product|intval}" method="POST" action="{$link->getModuleLink('stockalert', 'account', ['process' => 'add', 'stockalert_id_product' => {$id_product|intval}, 'stockalert_id_product_attribute' => {$id_product_attribute|intval}, 'stockalert_send_mail' => {$send_mail|intval}, 'stockalert_id_stockalert_alert' => {$id_stockalert_alert|intval}], true)|escape:'html':'UTF-8'}">
        <div>
            <span class="text-uppercase"><strong>{l s='Do you want to receive an alert when product is back in stock?' mod='stockalert'}</strong></span>
            <div>&nbsp;</div>
            {if $is_guest}
                <input class="form-control" type="email" name="stockalert_customer_email" placeholder="{l s='Email address' mod='stockalert'}"/>
            {/if}

            <input type="hidden" name="stockalert_popup_{$id_product|intval}" value="{$stockalert_popup|intval}"/>

            <div class="stockalert_gdpr_{$id_product|intval}">
               {hook h='displayGDPRConsent' id_module=$id_module}
            </div>
            <div class="text-center text-xs-center stockalert_button_container stockalert_button_container_{$id_product|intval}">
                <button form='stockalert-list-add-popup-form-{$id_product|intval}' data-id="{$id_product|intval}" class="btn btn-secondary" type="submit" rel="nofollow">{l s='Notify me when this product becomes available again' mod='stockalert'}</button>
            </div>

            {if $displayCaptcha}
                <div class="input-group stockalert_captcha_{$id_product|intval}" style="margin-bottom: 1rem;">
                    <input form='stockalert-list-add-popup-form-{$id_product|intval}' name="stockalert_captcha_code" placeholder="{l s='Introduce the captcha value' mod='stockalert'}" type="text" value="" class="input-group form-control" />
                    <img src="{$captchaController|escape:'html':'UTF-8'}" alt="Captcha" title="Captcha" />
                </div>
            {/if}

            <div class="stockalert_result_{$id_product|intval} stockalert-result"></div>
            <div class="stockalert-disclaimer stockalert_disclaimer_{$id_product|intval}">{l s='We will send you an email once the product becomes available. Your email address will not be shared with anyone else.' mod='stockalert'}</div>
        </div>
    </form>
</div>

<script type="text/javascript">
    // GDPR validation when content is refreshed by JS
    document.addEventListener("idnovateStockAlertEvent", function() {
        $(document).on("change" ,'.stockalert-add input[name=psgdpr_consent_checkbox]', function() {
            if ($(this).prop('checked') == true) {
                $(this).closest('form').find('[type="submit"]').removeAttr('disabled');
            } else {
                $(this).closest('form').find('[type="submit"]').attr('disabled', 'disabled');
            }
        });

        if (window.jQuery && typeof $.fancybox == 'function') {
            //if ($('[name=stockalert_popup]').val() == 1) {
                $('#stockalert-list-add-{$id_product|intval}').fancybox({
                    type: 'inline',
                    autoSize: false,
                    width: '500',
                    autoHeight: true,
                    padding: 0,
                    modal: false,
                    content: $('#stockalert-list-add-popup-{$id_product|intval}'),
                    wrapCSS: 'stockalert-popup',
                    parent: 'body',
                    helpers: {
                        overlay : {
                            closeClick : true,
                            locked: false,
                        }
                    },
                });
            //}
        }
    });
</script>
