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
<div id="cookiesplus-checker" class="hide">
    <div class="close-btn">&times;</div>
    <h2>GTM/GTAG</h2>
    <div>
        <div class="alert alert-warning" id="gtmGtagLoader"><span class="spinner"></span> Checking</div>
        <div class="alert alert-success" id="gtmResultSuccess"></div>
        <div class="alert alert-danger" id="gtmResultError"></div>
        <div class="alert alert-success" id="gtagResultSuccess"></div>
        <div class="alert alert-danger" id="gtagResultError"></div>
    </div>

    <h2>Consents</h2>
    <div>
        <div class="alert alert-warning" id="consentsLoader"><span class="spinner"></span> Checking</div>
        <div class="alert alert-danger" id="consentsResultError"></div>
        <table id="consentsResultSuccess">
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Default consent status</th>
                    <th>Updated consent status</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td><em>ad_storage</em></td>
                    <td><span id="ad_storage_def">-</span></td>
                    <td><span id="ad_storage_upd">-</span></td>
                </tr>
                <tr>
                    <td><em>ad_user_data</em></td>
                    <td><span id="ad_user_data_def">-</span></td>
                    <td><span id="ad_user_data_upd">-</span></td>
                </tr>
                <tr>
                    <td><em>ad_personalization</em></td>
                    <td><span id="ad_personalization_def">-</span></td>
                    <td><span id="ad_personalization_upd">-</span></td>
                </tr>
                <tr>
                    <td><em>analytics_storage</em></td>
                    <td><span id="analytics_storage_def">-</span></td>
                    <td><span id="analytics_storage_upd">-</span></td>
                </tr>
                <tr>
                    <td><em>functionality_storage</em></td>
                    <td><span id="functionality_storage_def">-</span></td>
                    <td><span id="functionality_storage_upd">-</span></td>
                </tr>
                <tr>
                    <td><em>personalization_storage</em></td>
                    <td><span id="personalization_storage_def">-</span></td>
                    <td><span id="personalization_storage_upd">-</span></td>
                </tr>
                <tr>
                    <td><em>security_storage</em></td>
                    <td><span id="security_storage_def">-</span></td>
                    <td><span id="security_storage_upd">-</span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <h2>Cookies installed</h2>
    <div>
        <table id="cookies-table">
            <thead>
                <tr>
                    <th>Name</th>
                </tr>
            </thead>
            <tbody>
                <!-- Cookies will be dynamically added here -->
            </tbody>
        </table>
    </div>
</div>

<script>
    {literal}
    // Function to retrieve cookie names
    function getCookieNames() {
        var cookies = document.cookie.split(';');
        var cookieNames = [];

        // Iterate through cookies and extract their names
        cookies.forEach(function(cookie) {
            var parts = cookie.split('=');
            var name = parts[0].trim();
            cookieNames.push(name);
        });

        return cookieNames;
    }

    // Function to update the list of cookie names
    function updateCookieList() {
        var cookieNames = getCookieNames();

        // Display cookie names in the "Cookies" section
        var cookiesTableBody = document.querySelector('#cookies-table tbody');

        // Clear previous content
        cookiesTableBody.innerHTML = '';

        // Iterate through cookie names and add them to the table
        cookieNames.forEach(function(cookieName) {
            var cookieRow = document.createElement('tr');
            var cookieCell = document.createElement('td');
            cookieCell.textContent = cookieName;
            cookieRow.appendChild(cookieCell);
            cookiesTableBody.appendChild(cookieRow);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        var cookiesplusChecker = document.getElementById('cookiesplus-checker');
        var closeBtn = cookiesplusChecker.querySelector('.close-btn');

        // Move the cookiesplusChecker to the beginning of the body
        document.body.insertBefore(cookiesplusChecker, document.body.firstChild);

        // Show the cookiesplusChecker by default
        cookiesplusChecker.classList.add('show');

        closeBtn.addEventListener('click', function () {
            cookiesplusChecker.classList.add('hide');
            cookiesplusChecker.classList.remove('show');
        });

        // GTM/GTAG detection
        var gtmGtagLoader = document.getElementById('gtmGtagLoader');
        var gtmResultSuccess = document.getElementById('gtmResultSuccess');
        var gtmResultError = document.getElementById('gtmResultError');
        var gtagResultSuccess = document.getElementById('gtagResultSuccess');
        var gtagResultError = document.getElementById('gtagResultError');

        var gtmPresent = false;
        var gtagPresent = false;
        var timer;

        // Consents
        var consentsLoader = document.getElementById('consentsLoader');
        var consentsResultSuccess = document.getElementById('consentsResultSuccess');
        var consentsResultError = document.getElementById('consentsResultError');

        // Display Loader
        gtmGtagLoader.style.display = 'block';
        consentsLoader.style.display = 'block';

        // Function to check for GTM/GTAG
        function checkForTags() {
            // Check for Google Tag Manager (GTM)
            if (typeof window["google_tag_data"] !== 'undefined') {
                gtmPresent = true;
            }

            // Check for Google Tag (gtag.js)
            if (typeof window['GoogleAnalyticsObject'] !== 'undefined') {
                gtagPresent = true;
            } else {
                var scripts = document.getElementsByTagName('script');
                for (var i = 0; i < scripts.length; i++) {
                    if (scripts[i].src && scripts[i].src.indexOf('https://www.googletagmanager.com/gtag/js') !== -1) {
                        gtagPresent = true;
                    }
                }
            }

            // If either GTM or GTAG is detected or 10 seconds have elapsed, stop checking
            if (gtmPresent || gtagPresent || timer <= 0) {
                clearInterval(interval);

                gtmGtagLoader.style.display = 'none';

                // Display result
                if (gtmPresent) {
                    gtmResultSuccess.style.display = 'block';
                    gtmResultSuccess.textContent = ' Google Tag Manager detected.';
                } else {
                    //gtmResultError.style.display = 'block';
                    //gtmResultError.textContent = ' Google Tag Manager not detected.';
                }

                if (gtagPresent) {
                    gtagResultSuccess.style.display = 'block';
                    gtagResultSuccess.textContent = ' Google Tag (gtag.js) detected.';
                } else {
                    //gtagResultError.style.display = 'block';
                    //gtagResultError.textContent = ' Google Tag (gtag.js) not detected.';
                }

                // Default consents
                if ((gtmPresent || gtagPresent) && typeof dataLayer === 'object') {
                    consentsLoader.style.display = 'none';
                    consentsResultSuccess.style.display = 'block';

                    // Values to check
                    const valuesToCheck = [
                        "ad_storage",
                        "ad_user_data",
                        "ad_personalization",
                        "analytics_storage",
                        "functionality_storage",
                        "personalization_storage",
                        "security_storage"
                    ];

                    // Reverse the array
                    const reversedArray = dataLayer.slice().reverse();

                    // Function to check if values exist in a "consent" entry
                    function checkValuesInConsent(consentEntry) {
                        for (const value of valuesToCheck) {
                            if (consentEntry[2] && consentEntry[2][value]) {
                                if ($('#' + value + '_def').text() == '') {
                                    $('#' + value + '_def').text(consentEntry[2][value]);
                                    // Set colors based on consent status
                                    if (consentEntry[2][value] === 'granted') {
                                        $('#' + value + '_def').removeClass('granted denied');
                                        $('#' + value + '_def').addClass('granted');
                                    } else if (consentEntry[2][value] === 'denied') {
                                        $('#' + value + '_def').removeClass('granted denied');
                                        $('#' + value + '_def').addClass('denied');
                                    }
                                }
                                //console.log(`Value '${value}' exists in the 'consent' entry.`);
                            }
                        }
                    }

                    // Clear values
                    for (const value of valuesToCheck) {
                        $('#' + value + '_def').text('');
                        $('#' + value + '_def').removeClass();
                    }

                    // Iterate through the reversed array to find "consent" and check values
                    for (const entry of reversedArray) {
                        if (entry["0"] === "consent") {
                            checkValuesInConsent(entry);
                        }
                    }

                    if ($('#ad_storage_def').text() == '') {
                        setTimeout(checkUpdatedConsents, 1000);
                    }
                } else {
                    consentsLoader.style.display = 'none';
                    gtmResultError.style.display = 'block';
                    gtmResultError.textContent = ' Google Tag Manager nor Google Tag (gtag.js) detected.';
                    consentsResultError.style.display = 'block';
                    consentsResultError.textContent = ' Consents not detected.';
                }
            }
            timer--;
        }

        // Start checking every second
        timer = 5;
        var interval = setInterval(checkForTags, 1000);

        // Updated consents
        (function checkUpdatedConsents() {
            if (typeof dataLayer === 'object') {
                let consentCount = 0;
                for (let i = 0; i < dataLayer.length; i++) {
                    if (dataLayer[i]["0"] === "consent") {
                        consentCount++;
                    }
                }

                if (consentCount < 2) {
                    setTimeout(checkUpdatedConsents, 1000);
                    return;
                }

                // Values to check
                const valuesToCheck = [
                    "ad_storage",
                    "ad_user_data",
                    "ad_personalization",
                    "analytics_storage",
                    "functionality_storage",
                    "personalization_storage",
                    "security_storage"
                ];

                // Reverse the array
                const reversedArray = dataLayer.slice().reverse();

                // Function to check if values exist in a "consent" entry
                function checkValuesInConsent(consentEntry) {
                    for (const value of valuesToCheck) {
                        if (consentEntry[2] && consentEntry[2][value]) {
                            if ($('#' + value + '_upd').text() == '') {
                                $('#' + value + '_upd').text(consentEntry[2][value]);
                                // Set colors based on consent status
                                if (consentEntry[2][value] === 'granted') {
                                    $('#' + value + '_upd').removeClass('granted denied');
                                    $('#' + value + '_upd').addClass('granted');
                                } else if (consentEntry[2][value] === 'denied') {
                                    $('#' + value + '_upd').removeClass('granted denied');
                                    $('#' + value + '_upd').addClass('denied');
                                }
                            }
                        }
                    }
                }

                // Clear values
                for (const value of valuesToCheck) {
                    $('#' + value + '_upd').text('');
                    $('#' + value + '_upd').removeClass();
                }

                // Iterate through the reversed array to find "consent" and check values
                for (const entry of reversedArray) {
                    if (entry["0"] === "consent") {
                        checkValuesInConsent(entry);
                    }
                }

                setTimeout(checkUpdatedConsents, 1000);
            }
        })();

        // Cookies
        // Initial update of cookie list
        updateCookieList();

        // Update cookie list every 2 seconds
        setInterval(updateCookieList, 2000);
    });
    {/literal}
</script>
