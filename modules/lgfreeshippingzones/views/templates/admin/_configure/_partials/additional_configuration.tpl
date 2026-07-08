{**
 * Copyright 2024 LÍNEA GRÁFICA E.C.E S.L.
 *
 * @author    Línea Gráfica E.C.E. S.L.
 * @copyright Lineagrafica.es - Línea Gráfica E.C.E. S.L. all rights reserved.
 * @license   https://www.apache.org/licenses/LICENSE-2.0
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *}

<div id="configuration">
    {if false &&  $bootstrap}<fieldset>{else}<div class="panel">{/if}
            <form name="config" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}">
                {if false &&  $bootstrap}<legend>{else}<h3>{/if}
                        <a href="../modules/{$module_name|escape:'html':'UTF-8'}/readme/readme_{l s='en' mod='lgfreeshippingzones'}.pdf#page=4" target="_blank">
                            <i class="icon-wrench"></i> {l s='Additional configuration' mod='lgfreeshippingzones'}
                            <img src="../modules/{$module_name|escape:'html':'UTF-8'}/views/img/info.png">
                        </a>
                    {if false &&  $bootstrap}</legend>{else}</h3>{/if}
                <br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='Price with tax included:' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg lgfloat">
                    <input type="radio" name="price_tax" id="price_tax_on" value="1"{if $PS_LGFSZP_TAX == 1} checked="checked"{/if} />
                    <label for="price_tax_on" style="width:75px;">{l s='Yes' mod='lgfreeshippingzones'}</label>
                    <input type="radio" name="price_tax" id="price_tax_off" value="0"{if $PS_LGFSZP_TAX == 0} checked="checked"{/if} />
                    <label for="price_tax_off" style="width:75px;">{l s='No' mod='lgfreeshippingzones'}</label>
                    <a class="slide-button btn"></a>
                </span>
                </h3>
                <p>
                    {l s='Choose to calculate free shipping based on the order total with TAX included or excluded.' mod='lgfreeshippingzones'}
                </p>
                <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='Display message in cart:' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg lgfloat">
                    <input type="radio" name="shipping_message" id="shipping_message_on" value="1"{if $PS_LGFSZP_MESSAGE == 1} checked="checked"{/if} />
                    <label for="shipping_message_on" style="width:75px;">{l s='Yes' mod='lgfreeshippingzones'}</label>
                    <input type="radio" name="shipping_message" id="shipping_message_off" value="0"{if $PS_LGFSZP_MESSAGE == 0} checked="checked"{/if}/>
                    <label for="shipping_message_off" style="width:75px;">{l s='No' mod='lgfreeshippingzones'}</label>
                    <a class="slide-button btn"></a>
                </span>
                </h3>
                <p>
                    {l s='Choose to display or hide messages about free shipping in the cart.' mod='lgfreeshippingzones'}
                </p>

                {* <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='Ignore vouchers' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg lgfloat">
                    <input type="radio" name="vouchers" id="vouchers_on" value="1"{if $PS_LGFSZP_VOUCHERS == 1} checked="checked"{/if} />
                    <label for="vouchers_on" style="width:75px;">{l s='Yes' mod='lgfreeshippingzones'}</label>
                    <input type="radio" name="vouchers" id="vouchers_off" value="0"{if $PS_LGFSZP_VOUCHERS == 0} checked="checked"{/if}/>
                    <label for="vouchers_off" style="width:75px;">{l s='No' mod='lgfreeshippingzones'}</label>
                    <a class="slide-button btn"></a>
                </span>
                </h3>
                <p>
                    {l s='Check this option to yes if you want voucher codes to be ignored when calculating shipping costs.' mod='lgfreeshippingzones'}
                </p> *}
                

                <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='Message color when shipping is free:' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="lgfloat"">
                    <input id="message_color1" name="message_color1" class="color" type="text" value="{$PS_LGFSZP_COLOR1|escape:'html':'UTF-8'}">
                    </span>
                </h3>
                <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='Message color when shipping is not free:' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="lgfloat"">
                    <input id="message_color2" name="message_color2" class="color" type="text" value="{$PS_LGFSZP_COLOR2|escape:'html':'UTF-8'}">
                    </span>
                </h3>
                <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='Display debug in cart:' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="switch prestashop-switch fixed-width-lg lgfloat">
                    <input type="radio" name="debug_mode" id="debug_mode_on" value="1"{if $PS_LGFSZP_DEBUG == 1} checked="checked"{/if} />
                    <label for="debug_mode_on" style="width:75px;">{l s='Yes' mod='lgfreeshippingzones'}</label>
                    <input type="radio" name="debug_mode" id="debug_mode_off" value="0"{if $PS_LGFSZP_DEBUG == 0} checked="checked"{/if} />
                    <label for="debug_mode_off" style="width:75px;">{l s='No' mod='lgfreeshippingzones'}</label>
                    <a class="slide-button btn"></a>
                </span>
                </h3>
                <p>
                    {l s='Enable this option to get debug information about free shipping in the cart.' mod='lgfreeshippingzones'}
                </p>
                <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='IP address for the debug:' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="lgfloat">
                    <input type="text" name="debugip" id="debugip" value="{$PS_LGFSZP_DEBUGIP|escape:'html':'UTF-8'}">
                    <a href="#menubar" onclick="addDebugIp();">{l s='CLICK HERE TO ADD YOUR IP' mod='lgfreeshippingzones'}</a>
                </span>
                </h3>
                <div class="lgclear"></div>
                <p>
                    {l s='Only the indicated IP address will be able to see the debug window in the cart.' mod='lgfreeshippingzones'}
                </p>
                <div class="lgclear"></div>
                <br><br>
                <h3>
                    <label class="lgfloat">
                        &nbsp;&nbsp;
                        {l s='A problem with the module?' mod='lgfreeshippingzones'}
                        &nbsp;&nbsp;
                    </label>
                    <span class="lgfloat">
                    <i class="icon-info-circle"></i>&nbsp;
                    <a href="../modules/{$module_name|escape:'html':'UTF-8'}/readme/readme_{l s='en' mod='lgfreeshippingzones'}.pdf#page=7"
                       target="_blank">
                        {l s='Read the module FAQ' mod='lgfreeshippingzones'}
                    </a>
                </span>
                </h3>
                <div class="lgclear"></div>
                <button class="button btn btn-default" type="submit" name="updateConf2">
                    <i class="process-icon-save"></i>{l s='Save' mod='lgfreeshippingzones'}
                </button>
            </form>
        {if false &&  $bootstrap}</fieldset>{else}</div>{/if}
</div>
