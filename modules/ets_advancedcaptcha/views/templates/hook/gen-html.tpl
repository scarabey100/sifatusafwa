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
{if isset($key) && $key}{if $key == 'PA_CAPTCHA_HELP'}
    {if $is17}
        <div class="container">
            <div class="group-0">
                <h2>Quick Help - Prestashop 1.7.x</h2>
                <div class="note">
                    This module should work perfectly on most Prestashop websites without any code modification, however if you
                    website is installed with a custom theme or custom modules you may (rarely) get into some problems when
                    installing the module to your website. <br />
                    This quick help will guide you how to quickly fix the problems by modifying some code so you can fix the problems
                    yourself but we recommend you to ,<a href="https://addons.prestashop.com/en/contact-us?pab=1&id_product=26997"
                                                         target="_blank">contact us via Prestashop Addons</a>, We're happy to support you and we'll help you solve the
                    issues for free!
                    <br />
                    For more information of how to install, use and troubleshoot problems (rarely happen), please refer to user-guide
                    document that is attached to your download.
                </div>
                <div class="line"></div>
                <p>If you see an error that says method <strong>validate</strong> and <strong>getFormat</strong> are overriden
                    already by another module,
                    it means some other modules have overridden the method that causes blocking the CAPTCHA module to implement its
                    overriding code<br />
                    To solve the problem, You need to manually edit the methods (in overriding files) with overriding code of the
                    CAPTCHA module
                </p>
            </div>
            <div class="group-1">
                <h3>Error: "Method validate() is overriden already"</h3>
                Follow steps below to fix the problem: <br />
                <ul>
                    <li>1. Open this file: <i>root/YOUR-SITE/modules/ets_advancedcaptcha/override/classes/form/CustomerForm.php</i></li>
                    <li>2. Copy the code highlighted on the photo below<br />
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/validate.jpg" style="width:100%" />
                    </li>
                    <li>3. Open this file: <i>root/YOUR-SITE/override/classes/form/CustomerForm.php</i></li>
                    <li>4. Find a method (function) named <strong>"validate"</strong>, paste the code you copied at step 2 into the
                        method just at the <strong>START</strong> of the method then save your changes</li>
                </ul>
            </div>
            <div class="group-2">
                <h3>Error: "Method getFormat() is overriden already"</h3>
                Follow steps below to fix the problem: <br />
                <ul>
                    <li>1. Open this file: <i>root/YOUR-SITE/modules/ets_advancedcaptcha/override/classes/form/CustomerFormatter.php</i></li>
                    <li>2. Copy the code highlighted on the photo below<br />
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/getFormat.jpg" style="width:100%" />
                    </li>
                    <li>3. Open this file: <i>root/YOUR-SITE/override/classes/form/CustomerFormatter.php</i></li>
                    <li>4. Find a method (function) named <strong>"getFormat"</strong>, paste the code you copied at step 2 into the
                        method just at the <strong>START</strong> of the method then save your changes</li>
                </ul>
                <h3>Error: "Method submit() is overriden already"</h3>
                Follow steps below to fix the problem: <br />
                <ul>
                    <li>1. Open this file: <i>root/YOUR-SITE/modules/ets_advancedcaptcha/override/classes/form/CustomerLoginForm.php</i></li>
                    <li>2. Copy the code highlighted on the photo below<br />
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/submit.jpg" style="width:100%" />
                    </li>
                    <li>3. Open this file: <i>root/YOUR-SITE/override/classes/form/CustomerLoginForm.php</i></li>
                    <li>4. Find a method (function) named <strong>"submit"</strong>, paste the code you copied at step 2 into the
                        method just at the <strong>START</strong> of the method then save your changes</li>
                </ul>
            </div>
        </div>
    {else}
        <div class="container">
            <div class="group-0">
                <h2>Quick Help - Prestashop 1.5.x - 1.6.x</h2>
                <br />
                <div class="note">
                    This module should work perfectly on most Prestashop websites without any code modification, however if you
                    website is installed with a custom theme or custom modules you may (rarely) get into some problems when
                    installing the module to your website. <br />
                    This quick help will guide you how to quickly fix the problems by modifying some code so you can fix the problems
                    yourself but we recommend you to ,<a href="https://addons.prestashop.com/en/contact-us?pab=1&id_product=26997"
                                                         target="_blank">contact us via Prestashop Addons</a>, We're happy to support you and we'll help you solve the
                    issues for free!
                    <br />
                    For more information of how to install, use and troubleshoot problems (rarely happen), please refer to user-guide
                    document that is attached to your download.
                </div>
                <div class="line"></div>
                If you install CAPTCHA on a Prestashop website that has custom theme or custom modules (that override to the same
                classes/methods that CAPTCHA does), you may see following problems after installing CAPTCHA to your website.<br />
                - On frontend, you may see your website contact form misses translations or CSS styles.<br />
                - In backend, you may see an error saying <strong>"Method processSubmitAccount is overriden already"</strong> or
                <strong>"Method postProcess is overriden already"</strong>.<br />
                This document will quickly guide you how to solve the problems.<br />
            </div>
            <div class="group-1">
                <h3>Problem: Missing contact form is translations or styles</h3>
                <span>Follow steps below to fix the problem:</span><br />
                <ul>
                    <li>1. In your backend, open CAPTCHA configuration panel</li>
                    <li>2. Enable this option <strong>"Disable template overrides contact form"</strong>
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/contact-form-config.jpg" style="width:100%" />
                    </li>
                    <li>3. Copy this code: <i>{literal}{hook h='displayPaCaptcha' posTo='contact'}{/literal}</i></li>
                    <li>4. Open this file: <i>root/YOUR-SITE/themes/YOUR-THEME/contact-form.tpl </i></li>
                    <li>5. Paste the code you copied at step 3 into the contact-form.tpl just below the file upload field then save your changes
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/contact-form-code.jpg" style="width:100%" />
                    </li>
                </ul>
            </div>
            <div class="group-2">
                <h3>Problem: Missing login form is translations or styles</h3>
                Follow steps below to fix the problem:<br />
                <ul>
                    <li>1. In your backend, open CAPTCHA configuration panel</li>
                    <li>2. Enable this option <strong>"Disable template override login form"</strong>
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/login-form-config.jpg" style="width:100%" />
                    </li>
                    <li>3. Copy this code: <i>{literal}{hook h='displayPaCaptcha' posTo='login'}{/literal}</i></li>
                    <li>4. Open this file: <i>root/YOUR-SITE/themes/YOUR-THEME/authentication.tpl </i></li>
                    <li>5. Paste the code you copied at step 3 into the authentication.tpl just below the file upload field then save your changes
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/login-form-code.jpg" style="width:100%" />
                    </li>
                </ul>
            </div>
            <div class="group-3">
                <h3>Problem: Missing forgot your password form's translations or styles</h3>
                Follow steps below to fix the problem:<br />
                <ul>
                    <li>1. In your backend, open CAPTCHA configuration panel</li>
                    <li>2. Enable this option <strong>"Disable template override forgot your password form"</strong>
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/forgot-password-config.jpg" style="width:100%" />
                    </li>
                    <li>3. Copy this code: <i>{literal}{hook h='displayPaCaptcha' posTo='pwd_recovery'}{/literal}</i></li>
                    <li>4. Open this file: <i>root/YOUR-SITE/themes/YOUR-THEME/password.tpl </i></li>
                    <li>5. Paste the code you copied at step 3 into the password.tpl just below the file upload field then save your changes
                        <img src="{$path|escape:'quotes':'UTF-8'}views/img/help/forgot-password-code.jpg" style="width:100%" />
                    </li>
                </ul>
            </div>
            <div class="group-4">
                <h3>Problem: Error override controller</h3>
                <span>- Method <strong>processSubmitAccount</strong>, <strong>processSubmitLogin</strong>,  <strong>sendRenewPasswordLink</strong>,  <strong>postProcess</strong>,  <strong>initContent</strong> is overriden already</span>
                Follow steps below to fix the problem:<br />
                <ul>
                    <li>1. Open 2 files: <strong>AuthController.php</strong>, <strong>ContactController.php</strong>, <strong>PasswordController.php</strong> that are
                        located in <i>"root/YOUR-SITE/modules/ets_advancedcaptcha/override/controllers/front/"</i> folder</li>
                    <li>2. Copy methods (functions) that're defined on the files<br /></li>
                    <li>3. Open this respective overriding files (<strong>AuthController.php</strong>, <strong>ContactController.php</strong>, <strong>PasswordController.php</strong>)
                        in overriding folder of your website at <i>"root/YOUR-SITE/override/controllers/front/"</i><br /></li>
                    <li>4. Find and replace the methods defined in those files by the methods you copied at step 2 then save your changes</li>
                </ul>
                <div style="font-style: italic; border: 1px solid #eee; font-size: 14px;padding: 10px;margin-top: 10px;">
                    *Note: if you replace the whole methods that are overriden by other modules, the other modules may not work
                    properly anymore (but the CAPTCHA module will surely work).<br />
                    So if you have programming knowledge, you should check the existing overriden methods and only insert necessary
                    code that are defined on overriding files of the CAPTCHA MODULE.<br />
                    We recommend you to contact us for a quick and free fix of the problem (as it requires coding work), We're happy
                    to support you.
                </div>
            </div>
        </div>
    {/if}
{/if}{/if}