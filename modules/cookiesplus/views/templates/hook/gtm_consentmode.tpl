{**
 * ISC License
 *
 * Copyright (c) 2025 idnovate.com
 * idnovate is a Registered Trademark & Property of idnovate.com, innovación y desarrollo SCP
 *
 * Permission to use, copy, modify, and/or distribute this software for any
 * purpose with or without fee is hereby granted, provided that the above
 * copyright notice and this permission notice appear in all copies.
 *
 * THE SOFTWARE IS PROVIDED "AS IS" AND THE AUTHOR DISCLAIMS ALL WARRANTIES WITH
 * REGARD TO THIS SOFTWARE INCLUDING ALL IMPLIED WARRANTIES OF MERCHANTABILITY
 * AND FITNESS. IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY SPECIAL, DIRECT,
 * INDIRECT, OR CONSEQUENTIAL DAMAGES OR ANY DAMAGES WHATSOEVER RESULTING FROM
 * LOSS OF USE, DATA OR PROFITS, WHETHER IN AN ACTION OF CONTRACT, NEGLIGENCE OR
 * OTHER TORTIOUS ACTION, ARISING OUT OF OR IN CONNECTION WITH THE USE OR
 * PERFORMANCE OF THIS SOFTWARE.
 *
 * @author    idnovate
 * @copyright 2025 idnovate.com
 * @license   https://www.isc.org/licenses/ https://opensource.org/licenses/ISC ISC License
 *}
/* window.st_pro_videos.options.enablePrivacyEnhancedMode= 1; */

let cookiesplusCookieValue = null;

if (window.cookiesplus_debug) {
    debugger;
}

for (let cookie of document.cookie.split(';')) {
    let [cookieName, ...rest] = cookie.trim().split('=');
    let value = rest.join('=');
    if (cookieName === 'cookiesplus') {
        try {
            let decoded = decodeURIComponent(value);
            cookiesplusCookieValue = JSON.parse(decoded);
            break;
        } catch (e) {
            console.error('Failed to parse cookiesplus cookie value', e);
            throw new Error('Invalid cookiesplus cookie value');
        }
    }
}

if (cookiesplusCookieValue === null) {
    console.log('%ccookiesplus cookie doesn\'t exist', 'background: red; color: white');
    throw new Error('cookiesplus cookie not found');
}

/* Define keys and their default values */
const defaultConsents = {
    'ad_storage': false,
    'ad_user_data': false,
    'ad_personalization': false,
    'analytics_storage': false,
    'functionality_storage': false,
    'personalization_storage': false,
    'security_storage': false,
};

Object.keys(defaultConsents).forEach(function (key) {
    window[key] = false;
});

const cookiesplusConsents = Object.keys(cookiesplusCookieValue.consents).filter(v => v.startsWith('cookiesplus-finality'));

/* Check and update values based on cookiesplusCookieValue */
if (cookiesplusCookieValue && (cookiesplusCookieValue.consent_mode || cookiesplusCookieValue.gtm_consent_mode)) {
    window.gtm = window.gtm || [];

    cookiesplusConsents.forEach(function (key) {
        const consentMode = cookiesplusCookieValue.consent_mode || cookiesplusCookieValue.gtm_consent_mode;
        const gtmConsentType = consentMode && consentMode[key] && consentMode[key].gtm_consent_type;
        if (cookiesplusCookieValue
            && cookiesplusCookieValue.consents
            && typeof cookiesplusCookieValue.consents[key] !== 'undefined'
            && cookiesplusCookieValue.consents[key] === 'on'
            && gtmConsentType && typeof gtmConsentType === 'object') {

            Object.entries(gtmConsentType).forEach(function(entry) {
                var consentKey = entry[0];
                var value = entry[1];

                if (value) {
                    if (!window.gtm) {
                        window.gtm = {}; // We ensure that window.gtm exists
                    }
                    window.gtm[consentKey] = true;
                }
            });
        }

    });

    window.dataLayer = window.dataLayer || [];
    function gtag() { dataLayer.push(arguments); }

    gtag('consent', 'default', {
        'ad_storage' : 'denied',
        'ad_user_data' : 'denied',
        'ad_personalization' : 'denied',
        'analytics_storage' : 'denied',
        'functionality_storage' : 'denied',
        'personalization_storage' : 'denied',
        'security_storage' : 'denied',
        'wait_for_update' : 500
    });

    gtag('consent', 'update', {
        'ad_storage': window.gtm.ad_storage ? 'granted' : 'denied',
        'ad_user_data': window.gtm.ad_user_data ? 'granted' : 'denied',
        'ad_personalization': window.gtm.ad_personalization ? 'granted' : 'denied',
        'analytics_storage': window.gtm.analytics_storage ? 'granted' : 'denied',
        'functionality_storage': window.gtm.functionality_storage ? 'granted' : 'denied',
        'personalization_storage': window.gtm.personalization_storage ? 'granted' : 'denied',
        'security_storage': window.gtm.security_storage ? 'granted' : 'denied',
    });

    if (cookiesplusCookieValue.gtm_consent_mode !== undefined && cookiesplusCookieValue.gtm_consent_mode.url_passthrough !== undefined) {
        gtag('set', 'url_passthrough', cookiesplusCookieValue.gtm_consent_mode.url_passthrough);
    }

    if (cookiesplusCookieValue.gtm_consent_mode !== undefined && cookiesplusCookieValue.gtm_consent_mode.ads_data_redaction !== undefined) {
        gtag('set', 'ads_data_redaction', cookiesplusCookieValue.gtm_consent_mode.ads_data_redaction);
    }
}

if (cookiesplusCookieValue && cookiesplusCookieValue.muet_consent_mode) {
    window.muet = window.muet || [];

    cookiesplusConsents.forEach(function (key) {
        const consentMode = cookiesplusCookieValue.muet_consent_mode;
        const muetConsentType = consentMode && consentMode[key] && consentMode[key].muet_consent_type;
        if (cookiesplusCookieValue
            && cookiesplusCookieValue.consents
            && typeof cookiesplusCookieValue.consents[key] !== 'undefined'
            && cookiesplusCookieValue.consents[key] === 'on'
            && muetConsentType && typeof muetConsentType === 'object') {

            Object.entries(muetConsentType).forEach(function(entry) {
                var consentKey = entry[0];
                var value = entry[1];

                if (value) {
                    if (!window.muet || typeof window.muet !== 'object') {
                        window.muet = {}; // We ensure that window.muet exists
                    }
                    window.muet[consentKey] = true;
                }
            });
        }
    });

    window.uetq = window.uetq || [];
    window.uetq.push('consent', {
      ad_storage: 'denied',
      analytics_storage: 'denied'
    });

    window.uetq.push('consent', {
      ad_storage: window.muet.ad_storage ? 'granted' : 'denied',
      analytics_storage: window.muet.analytics_storage ? 'granted' : 'denied',
    });
}
