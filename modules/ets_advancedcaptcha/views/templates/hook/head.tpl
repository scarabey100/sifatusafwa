{*
 * Copyright ETS Software Technology Co., Ltd
 *
 * NOTICE OF LICENSE
 *
 * This file is not open source! Each license that you purchased is only available for 1 website only.
 * If you want to use this file on more websites (or projects), you need to purchase additional licenses.
 * You are not allowed to redistribute, resell, lease, license, sub-license or offer our resources to any third party.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future.
 *
 * @author ETS Software Technology Co., Ltd
 * @copyright  ETS Software Technology Co., Ltd
 * @license    Valid for 1 website (or project) for each purchase of license
*}

<script src="https://www.google.com/recaptcha/api.js?{if $PA_CAPTCHA_TYPE == 'google'}onload=onloadCallback&render=explicit{/if}{if isset($hl) && $hl}&hl={$hl|escape:'quotes':'UTF-8'}{/if}" {if $PA_CAPTCHA_TYPE == 'google'}async defer{/if}></script>
<script type="text/javascript">
    var PA_GOOGLE_CAPTCHA_THEME = '{$PA_GOOGLE_CAPTCHA_THEME|escape:'html':'UTF-8'}';
    {if $PA_CAPTCHA_TYPE == 'google'}
        var PA_GOOGLE_CAPTCHA_SITE_KEY = '{$PA_GOOGLE_CAPTCHA_SITE_KEY|escape:'html':'UTF-8'}';
        {literal}
            var recaptchaWidgets = [];
            var onloadCallback = function () {
                ets_captcha_load(document.getElementsByTagName('form'));
            };
            var ets_captcha_load = function (forms) {
                var pattern = /(^|\s)g-recaptcha(\s|$)/;
                for (var i = 0; i < forms.length; i++) {
                    var items = forms[i].getElementsByTagName('div');
                    for (var k = 0; k < items.length; k++) {
                        if (items[k].className && items[k].className.match(pattern) && PA_GOOGLE_CAPTCHA_SITE_KEY) {
                            var widget_id = grecaptcha.render(items[k], {
                                'sitekey': PA_GOOGLE_CAPTCHA_SITE_KEY,
                                'theme': PA_GOOGLE_CAPTCHA_THEME ? PA_GOOGLE_CAPTCHA_THEME : 'light',
                            });
                            recaptchaWidgets.push(widget_id);
                            break;
                        }
                    }
                }
            };
        {/literal}
    {else}
        var PA_GOOGLE_V3_CAPTCHA_SITE_KEY = '{$PA_GOOGLE_V3_CAPTCHA_SITE_KEY|escape:'html':'UTF-8'}';
        var PA_GOOGLE_V3_POSITION = '{$PA_GOOGLE_V3_POSITION|escape:'html':'UTF-8'}';
    {/if}
</script>
