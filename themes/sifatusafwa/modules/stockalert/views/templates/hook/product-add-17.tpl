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

<div class="stock__alert">

    <button class="btn btn-primary popup__open" data-popup="stockalert-list-add-popup-{$id_product|intval}">{l s='Email Me When Available' d='Shop.Theme.Global'}</button>
    <div id="stockalert-list-add-popup-{$id_product|intval}" class="popup">
        <div class="popup__wrapper">
            <div class="popup__header">
                {l s='Do you want to receive an alert when product is back in stock?' mod='stockalert'}
                <button class="popup__close">
                    <svg width="18" height="18" viewBox="0 0 18 18" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill-rule="evenodd" clip-rule="evenodd" d="M9.00045 8.145L0.908488 0.0530396L0.0476066 0.913921L8.13957 9.00588L0.0476066 17.0978L0.908488 17.9587L9.00045 9.86676L17.0922 17.9586L17.9531 17.0977L9.86133 9.00588L17.9531 0.914085L17.0922 0.0532037L9.00045 8.145Z" fill="black"/>
                    </svg>
                </button>
            </div>
            <div class="popup__body">
                {if $custom_text}
                    <div>{$custom_text|escape:'html':'UTF-8'}</div>
                {/if}
                {if $is_guest}
                    <input form='stockalert-list-add-popup-form-{$id_product|intval}' class="form-control" type="email" name="stockalert_customer_email" placeholder="{l s='Email address' mod='stockalert'}"/>
                {/if}

                <input form='stockalert-list-add-popup-form-{$id_product|intval}' type="hidden" name="stockalert_popup_{$id_product|intval}" value="{$stockalert_popup|intval}"/>

                <div class="stockalert_gdpr_{$id_product|intval}">
                    {hook h='displayGDPRConsent' id_module=$id_module}
                </div>

                <button form='stockalert-list-add-popup-form-{$id_product|intval}' data-id="{$id_product|intval}" class="btn btn-primary" type="submit" rel="nofollow">{l s='Notify me when this product becomes available again' mod='stockalert'}</button>

                {if $displayCaptcha}
                    <div class="input-group stockalert_captcha_{$id_product|intval}" style="margin-bottom: 1rem;">
                        <input form='stockalert-list-add-popup-form-{$id_product|intval}' name="stockalert_captcha_code" placeholder="{l s='Introduce the captcha value' mod='stockalert'}" type="text" value="" class="input-group form-control" />
                        <img src="{$captchaController|escape:'html':'UTF-8'}" alt="Captcha" title="Captcha" />
                    </div>
                {/if}

                <div class="stockalert_result_{$id_product|intval} stockalert-result"></div>
                <div class="stockalert-disclaimer stockalert_disclaimer_{$id_product|intval}">{l s='We will send you an email once the product becomes available. Your email address will not be shared with anyone else.' mod='stockalert'}</div>
            </div>
        </div>
    </div>

    <script>
        // To avoid nesting forms, we append the form to the body
        var form = document.createElement("form");
        form.method = "POST";
        form.id = "stockalert-list-add-popup-form-{$id_product|intval}";
        form.action = "{$link->getModuleLink('stockalert', 'account', ['process' => 'add', 'stockalert_id_product' => {$id_product|intval}, 'stockalert_id_product_attribute' => {$id_product_attribute|intval}, 'stockalert_send_mail' => {$send_mail|intval}, 'stockalert_id_stockalert_alert' => {$id_stockalert_alert|intval}], true) nofilter}";

        // Remove previous forms
        // Find the form by ID
        var existingForm = document.getElementById("stockalert-list-add-popup-form-{$id_product|intval}");

        // Check if the form exists before attempting to remove it
        if (existingForm) {
            // Remove the form from the DOM
            existingForm.parentNode.removeChild(existingForm);
        }

        // Append the form to the body
        document.body.appendChild(form);

        // GDPR validation when content is refreshed by JS
        if (typeof listenerAdded === 'undefined') {
            document.addEventListener("idnovateStockAlertEvent", displayStockAlertPopup);
            listenerAdded = true;
        }

        function displayStockAlertPopup() {
            // by default forms submit will be disabled, only will enable if agreement checkbox is checked
            let element = $('.stockalert-add input[name=psgdpr_consent_checkbox]');
            if (element.prop('checked') != true) {
                element.closest('.stockalert-add').find('[type="submit"]').attr('disabled', 'disabled');
            }

            $(document).on("change" , element, function() {
                if (element.prop('checked') == true) {
                    element.closest('.stockalert-add').find('[type="submit"]').removeAttr('disabled');
                } else {
                    element.closest('.stockalert-add').find('[type="submit"]').attr('disabled', 'disabled');
                }
            });

            if ($('[name=stockalert_popup_{$id_product|intval}]').val() == 1) {
                $.fancybox({
                    type: 'inline',
                    autoSize: false,
                    width: '500',
                    autoHeight: true,
                    padding: 0,
                    href: $('.stockalert-add'),
                    modal: false,
                    wrapCSS: 'stockalert-popup',
                    parent: 'body',
                    helpers: {
                        overlay : {
                            closeClick : true,
                            locked: false,
                        }
                    },
                    afterClose: function() {
                        $('.stockalert-add').show();
                    }
                });
            }
        }
    </script>
</div>
