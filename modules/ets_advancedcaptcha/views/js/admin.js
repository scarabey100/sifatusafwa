/**
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
 */

var func_pa = {
    init: function () {
        func_pa.captchaType();
    },
    captchaType: function (val) {
        var _sl = val || $('input[name=PA_CAPTCHA_TYPE]:checked').val();
        if (val == 'google' || ($('#PA_CAPTCHA_TYPE_google').length > 0 && $('#PA_CAPTCHA_TYPE_google').is(':checked'))) {
            $('.row_pa_google_captcha_site_key, .row_pa_google_captcha_secret_key, .row_pa_google_captcha_theme,.row_pa_google_captcha_label').show();
            $('.row_pa_google_v3_captcha_site_key, .row_pa_google_v3_captcha_score, .row_pa_google_v3_captcha_secret_key, .row_pa_google_v3_position').hide();
        } else if (val == 'google' || ($('#PA_CAPTCHA_TYPE_google_v3').length > 0 && $('#PA_CAPTCHA_TYPE_google_v3').is(':checked'))) {
            $('.row_pa_google_v3_captcha_site_key, .row_pa_google_v3_captcha_score, .row_pa_google_v3_captcha_secret_key, .row_pa_google_v3_position, .row_pa_google_captcha_theme').show();
            $('.row_pa_google_captcha_site_key, .row_pa_google_captcha_secret_key,.row_pa_google_captcha_label').hide();
        } else {
            $('.row_pa_google_captcha_site_key, .row_pa_google_captcha_secret_key,.row_pa_google_v3_captcha_site_key, .row_pa_google_v3_captcha_secret_key, .row_pa_google_v3_captcha_score, .row_pa_google_v3_position, .row_pa_google_captcha_theme,.row_pa_google_captcha_label').hide();
        }
    }
};
$(document).ready(function () {
    func_pa.init();
    $('input[name="PA_CAPTCHA_TYPE"]').change(function () {
        func_pa.captchaType($(this).val());
    });
});