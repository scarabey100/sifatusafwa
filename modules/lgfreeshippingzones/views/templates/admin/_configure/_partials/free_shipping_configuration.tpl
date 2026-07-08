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

<div id="freeshipping">
    {if false &&  $bootstrap}<fieldset>{else}<div class="panel">{/if}
            <form name="zonesform" method="post" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}">
                {if false &&  $bootstrap}<legend>{else}<h3>{/if}
                        <a href="../modules/{$module_name|escape:'html':'UTF-8'}/readme/readme_{l s='en' mod='lgfreeshippingzones'}.pdf#page=4" target="_blank">
                            <i class="icon-table"></i> {l s='Free shipping configuration' mod='lgfreeshippingzones'}.
                            &nbsp;
                            <img src="../modules/{$module_name|escape:'html':'UTF-8'}/views/img/info.png">
                        </a>
                    {if false &&  $bootstrap}</legend>{else}</h3>{/if}
                <div class="{$class_alert|escape:'html':'UTF-8'}">
                    {l s='Free shipping is disabled for the zones and carrier' mod='lgfreeshippingzones'}
                    {l s='that have 0 set for the 4 values (0.00 | 0.000 | 0.00 | 0.000)' mod='lgfreeshippingzones'}
                    <br>
                    {l s='To set free shipping, you need to configure at least one value' mod='lgfreeshippingzones'}
                    {l s='for the zone and carrier (not necessary to configure the 4 values)' mod='lgfreeshippingzones'}
                    <br>
                    {l s='Ex: if you set "Minimum price : 50.00" and "Maximum price : 0.00",' mod='lgfreeshippingzones'}
                    {l s='shipping will be free from 50 to infinity.' mod='lgfreeshippingzones'}
                    <br>
                    {l s='Ex: if you set "Minimum price : 0.00" and "Maximum price : 300.00",' mod='lgfreeshippingzones'}
                    {l s='shipping will be free from 0 to 300.' mod='lgfreeshippingzones'}
                </div>
                <div style="overflow-x:auto;">
                    <table class="table" width="100%" style="border-collapse: collapse;">
                        <tr>
                            <th style="text-transform: uppercase; text-align:center;" colspan="2">
                                <a href="index.php?controller=AdminZones&token={Tools::getAdminTokenLite('AdminZones')|escape:'html':'UTF-8'}" target="_blank">
                                    <i class="icon-map-marker"></i> {l s='Zone' mod='lgfreeshippingzones'}
                                </a>
                            </th>
                            <th style="text-transform: uppercase; text-align:center;" colspan="3">
                                <a href="index.php?controller=AdminCarriers&token={Tools::getAdminTokenLite('AdminCarriers')|escape:'html':'UTF-8'}" target="_blank">
                                    <i class="icon-truck"></i> {l s='Carrier' mod='lgfreeshippingzones'}
                                </a>
                            </th>
                            <th style="text-transform: uppercase; text-align:center;" colspan="2">
                                {l s='Free shipping from:' mod='lgfreeshippingzones'}
                            </th>
                            <th style="text-transform: uppercase; text-align:center;" colspan="2">
                                {l s='Free shipping up to:' mod='lgfreeshippingzones'}
                            </th>
                        </tr>
                        <tr>
                            <th style="text-align:center;">
                                {l s='By default' mod='lgfreeshippingzones'}
                                <span class="toolTip">
                                <a href="#default_zone"><img src="../modules/{$module_name|escape:'html':'UTF-8'}/views/img/info.png"></a>
                                <p class="tooltipDesc">
                                    {l s='When the delivery address has not been set yet in the cart by the customer,' mod='lgfreeshippingzones'}
                                    {l s='the module will use the selected default zone to calculate free shipping.' mod='lgfreeshippingzones'}
                                    <br>
                                </p>
                            </span>
                            </th>
                            <th style="text-align:left;">
                                {l s='Name' mod='lgfreeshippingzones'}
                            </th>
                            <th style="text-align:center;">
                                {l s='Active' mod='lgfreeshippingzones'}
                                <span class="toolTip">
                                <a href="#active_zone"><img src="../modules/{$module_name|escape:'html':'UTF-8'}/views/img/info.png"></a>
                                <p class="tooltipDesc">
                                    {l s='Enable or Disable this Carrier to offer free shippings.' mod='lgfreeshippingzones'}
                                    <br>
                                </p>
                            </span>
                            </th>
                            <th style="text-align:center;">
                                {l s='By default' mod='lgfreeshippingzones'}
                                <span class="toolTip">
                                    <a href="#default_zone"><img src="../modules/{$module_name|escape:'html':'UTF-8'}/views/img/info.png"></a>
                                    <p class="tooltipDesc">
                                        {l s='When the carrier has not been selected yet in the cart by the customer,' mod='lgfreeshippingzones'}
                                        {l s='the module will use the selected default carrier assigned to the zone' mod='lgfreeshippingzones'}
                                        {l s='to calculate free shipping.' mod='lgfreeshippingzones'}
                                        <br>
                                    </p>
                                </span>
                            </th>
                            <th style="text-align:left;">
                                {l s='Name' mod='lgfreeshippingzones'}
                            </th>
                            <th style="text-transform: uppercase;">
                                <i class="icon-tag"></i> {l s='Minimum price' mod='lgfreeshippingzones'}
                            </th>
                            <th style="text-transform: uppercase;">
                                <i class="icon-cube"></i> {l s='Minimum weight' mod='lgfreeshippingzones'}
                            </th>
                            <th style="text-transform: uppercase;">
                                <i class="icon-tag"></i> {l s='Maximum price' mod='lgfreeshippingzones'}
                            </th>
                            <th style="text-transform: uppercase;">
                                <i class="icon-cube"></i> {l s='Maximum weight' mod='lgfreeshippingzones'}
                            </th>
                        </tr>
                        {foreach $zonas as $zona}
                            <tr style="border-top: 0.2em solid #ddd;">
                            <td rowspan="{if count($zona['carriers']) > 0}{count($zona['carriers'])+1|intval}{else}1{/if}" style="text-align:center;">
                                <input type="radio" id="{$zona['id_zone']|escape:'html':'UTF-8'}" name="def_zone" value="{$zona['id_zone']|escape:'html':'UTF-8'}"{if Configuration::get('PS_LGFSZP_DEFZONE') == $zona['id_zone']} checked="checked"{/if}>
                            </td>
                            <td rowspan="{if count($zona['carriers']) > 0}{count($zona['carriers'])+1|intval}{else}1{/if}">
                                {$zona['name']|escape:'html':'UTF-8'}
                            </td>
                            {if count($zona['carriers']) > 0}
                                </tr>
                                {foreach $zona['carriers'] as $carrier}
                                    <tr>
                                        <td style="text-align:center;">
                                            <input type="checkbox" id="{$carrier['id_reference']|escape:'html':'UTF-8'}" name="act_carrier_{$zona['id_zone']|escape:'html':'UTF-8'}[]" value="{$carrier['id_reference']|escape:'html':'UTF-8'}"{if $carrier['active']} checked="checked"{/if}>
                                        </td>
                                        <td style="text-align:center;">
                                            <input type="radio" id="{$carrier['id_reference']|escape:'html':'UTF-8'}" name="def_carrier_{$zona['id_zone']|escape:'html':'UTF-8'}" value="{$carrier['id_reference']|escape:'html':'UTF-8'}"{if $carrier['default']} checked="checked"{/if}>
                                        </td>
                                        <td>{$carrier['name']|escape:'html':'UTF-8'}</td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon">{$currency|escape:'html':'UTF-8'}</span>
                                                <input type="text" name="price_{$zona['id_zone']|escape:'html':'UTF-8'}_{$carrier['id_reference']|escape:'html':'UTF-8'}"
                                                       value="{if $carrier['price_aux_1']}{$carrier['price_aux_1']|escape:'html':'UTF-8'}{else}0.00{/if}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon">{$weight_unit|escape:'html':'UTF-8'}</span>
                                                <input type="text" name="weight_{$zona['id_zone']|escape:'html':'UTF-8'}_{$carrier['id_reference']|escape:'html':'UTF-8'}"
                                                       value="{if $carrier['weight_aux_1']|escape:'html':'UTF-8'}{$carrier['weight_aux_1']|escape:'html':'UTF-8'}{else}0.000{/if}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon">{$currency|escape:'html':'UTF-8'}</span>
                                                <input type="text" name="price2_{$zona['id_zone']|escape:'html':'UTF-8'}_{$carrier['id_reference']|escape:'html':'UTF-8'}"
                                                       value="{if $carrier['price_aux_2']|escape:'html':'UTF-8'}{$carrier['price_aux_2']|escape:'html':'UTF-8'}{else}0.00{/if}">
                                            </div>
                                        </td>
                                        <td>
                                            <div class="input-group">
                                                <span class="input-group-addon">{$weight_unit|escape:'html':'UTF-8'}</span>
                                                <input type="text" name="weight2_{$zona['id_zone']|escape:'html':'UTF-8'}_{$carrier['id_reference']|escape:'html':'UTF-8'}"
                                                       value="{if $carrier['weight_aux_2']}{$carrier['weight_aux_2']|escape:'html':'UTF-8'}{else}0.000{/if}">
                                            </div>
                                        </td>
                                    </tr>
                                {/foreach}
                                <tr>
                            {else}
                                <td colspan="7">{l s='There is no carrier available for this zone' mod='lgfreeshippingzones'}</td>
                            {/if}
                            </tr>
                        {/foreach}
                    </table>
                    <br>
                    <button class="button btn btn-default" type="submit" name="updateConf" >
                        <i class="process-icon-save"></i> {l s='Save' mod='lgfreeshippingzones'}
                    </button>
                </div>
            </form>
        {if false &&  $bootstrap}</fieldset>{else}</div>{/if}
</div>