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
<style>
    #cookiesplus-checker li {
        margin-bottom: 10px;
    }

    #cookiesplus-checker .thumbnail {
        max-width: 100%;
        height: 100px;
        margin: 10px;
        cursor: pointer;
    }

    #cookiesplus-checker-modal {
        display: none;
        position: fixed;
        z-index: 1;
        padding-top: 50px;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        justify-content: center;
        align-items: center;
        background-color: rgba(0, 0, 0, 0.6);
        z-index: 504;
    }

    #cookiesplus-checker-modal.show {
        display: flex !important;
    }

    #cookiesplus-checker-modal img {
        margin: auto;
        display: block;
        max-width: 80%;
        max-height: 80%;
        margin: 0 auto;
    }

    #cookiesplus-checker-modal .close {
        position: absolute;
        top: 10px;
        right: 25px;
        color: #fff;
        font-size: 35px;
        font-weight: bold;
        cursor: pointer;
        opacity: 1;
    }
</style>

<div id="cookiesplus-checker">
    <div id="cookiesplus-checker-modal" class="modal">
        <span class="close" onclick="closeModal()">&times;</span>
        <img src="" alt="" id="modalImg">
    </div>
    <div>
        <h3 style="margin: 10px 0">{l s='How to prevent cookies from being installed' mod='cookiesplus'}</h3>
        <ul>
            <li>
                {l s='When consent is not granted, only technical cookies should be permitted. If any cookies are installed prior to user consent, it\'s required to block the associated module, script, or tag:' mod='cookiesplus'}
                <ul>
                    <li>
                        {l s='If the cookie is installed by a module, block the module within the [1]Modules blocked[/1] within the [1]Cookies finalities[/1] option.' tags=['<strong>'] mod='cookiesplus'}
                        <img src="https://easycaptures.com/fs/uploaded/1692/4489727501.png"
                             class="thumbnail"
                             onclick="openModal('https://easycaptures.com/fs/uploaded/1692/4489727501.png')">
                    </li>
                    <li>
                        {l s='If the cookie is installed from a script within the template, relocate the script from the template to the designated field labeled [1]Execute this JS script when this cookie finality is accepted[/1] within the [1]Cookies finalities[/1] option.' tags=['<strong>'] mod='cookiesplus'}
                        <img src="https://easycaptures.com/fs/uploaded/1692/5739523640.png"
                             class="thumbnail"
                             onclick="openModal('https://easycaptures.com/fs/uploaded/1692/5739523640.png')">
                    </li>
                    <li>
                        {l s='If the cookie is installed via a Google Tag Manager tag, establish a consent protocol as outlined in this resource:' mod='cookiesplus'}
                        <a target="_blank"
                           href="https://www.simoahava.com/analytics/consent-settings-google-tag-manager/#additional-consent-checks">https://www.simoahava.com/analytics/consent-settings-google-tag-manager/#additional-consent-checks</a>.
                    </li>
                </ul>
            </li>
            <li>
                {l s='Verify the configuration using the built-in [1][2]Checker[/2][/1] feature within the module.' tags=["<a style=\"cursor:pointer\" onclick=\"changeTab('fieldset_2_2')\">", '<strong>'] mod='cookiesplus'}
            </li>
        </ul>
    </div>
    <br /><br />
    <div>
        <h3 style="margin: 10px 0">{l s='If you want to configure Consent Mode V2' mod='cookiesplus'}</h3>
        <div>
            <ol>
                <li>
                    {l s='To enable the Consent Overview screen, open [1]Google Tag Manager[/1], go to [2]Admin[/2] and then [2]Container Settings[/2].' tags=['<a target="_blank" href="https://tagmanager.google.com/">', '<strong>'] mod='cookiesplus'}
                    <br/>
                    {l s='Check the box next to [1]Enable consent overview[/1].' tags=['<strong>'] mod='cookiesplus'}
                    <img src="https://www.simoahava.com/images/2021/05/container-settings.jpg"
                         class="thumbnail"
                         onclick="openModal('https://www.simoahava.com/images/2021/05/container-settings.jpg')">
                </li>
                <li>
                    {l s='Ensure that consent is granted for all tags:' mod='cookiesplus'}
                    <img src="https://www.simoahava.com/images/2021/05/consent-overview-button.jpg"
                         class="thumbnail"
                         onclick="openModal('https://www.simoahava.com/images/2021/05/consent-overview-button.jpg')">

                    {l s='If any tag lacks consent, add it using the guidelines provided here: [1]https://www.simoahava.com/analytics/consent-settings-google-tag-manager/#additional-consent-checks[/1].' tags=['<a target="_blank" href="https://www.simoahava.com/analytics/consent-settings-google-tag-manager/#additional-consent-checks">'] mod='cookiesplus'}
                </li>
                <li>
                    {l s='Enable the integration with Google in the option [1]Integration with other platforms[/1] from the module. Ensure that the finalities that should grant the consents are enabled too.' tags=['<strong>'] mod='cookiesplus'}

                    <img src="https://easycaptures.com/fs/uploaded/1692/9069356091.jpg"
                         class="thumbnail"
                         onclick="openModal('https://easycaptures.com/fs/uploaded/1692/9069356091.jpg')">
                </li>
            </ol>
        </div>
    </div>
</div>


<script>
    function openModal(imageUrl) {
        var modal = document.getElementById("cookiesplus-checker-modal");
        var modalImg = document.getElementById("modalImg");
        var body = document.getElementsByTagName("body")[0];

        modalImg.src = imageUrl;
        modal.classList.add("show");
        body.style.overflow = "hidden";
    }

    function closeModal() {
        var modal = document.getElementById("cookiesplus-checker-modal");
        var body = document.getElementsByTagName("body")[0];

        modal.classList.remove("show");
        body.style.overflow = "auto";
    }

    var modalOverlay = document.getElementById("cookiesplus-checker-modal");
    // When the user clicks anywhere outside of the modal, close it
    window.onclick = function (event) {
        if (event.target === modalOverlay) {
            closeModal();
        }
    };

    function changeTab(tabId) {
        // Remove 'active' class from current active tab and tab content
        $('#module-tabs a').removeClass('active');

        // Activate the new tab and corresponding tab content
        $('a[href="#' + tabId + '"]').addClass('active');
        $('a[href="#' + tabId + '"]').tab('show');
    }
</script>
