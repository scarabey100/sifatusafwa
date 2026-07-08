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

{if $left_message > 0}
    {* MESSAGES IN CART *}
    {if $debugInfo['Totals']['NumberOfProducts'] > 0}
        {* DEBUG - SHIPPING NOT FREE *}
        {if $debugInfo['undeliverable']}
            {l s='The order can not be delivered' mod='lgfreeshippingzones'}<br>
        {else}
            {assign var=is_free_shipping value=true}
            {assign var=more_money value=0}
            {assign var=more_weight value=0}
            {assign var=less_money value=0}
            {assign var=less_weight value=0}

            {if (isset($debugInfo['Addresses']))}
                {foreach $debugInfo['Addresses'] as $address}
                    {foreach $address['Packages'] as $package}
                        {if $package['undeliverable']}
                            {l s='is undeliverable' mod='lgfreeshippingzones'}
                        {else}
                            {foreach $package['Carriers'] as $carrier}
                                {if $is_free_shipping && !$debugInfo['free_shipping_voucher_code'] && $carrier['shippingCost']>0}
                                    {assign var=is_free_shipping value=false}
                                {/if}
                                {if $carrier['PriceMin'] > 0}
                                    {$more_money=$more_money+($carrier['PriceMin']-$package['TotalCost'])}
                                {/if}
                                {if $carrier['WeightMin'] > 0}
                                    {$more_weight=$more_weight+($carrier['WeightMin']-$package['TotalWeight'])}
                                {/if}
                                {if $carrier['PriceMax'] > 0}
                                    {$less_money=$less_money+($package['TotalCost']-$carrier['PriceMax'])}
                                {/if}
                                {if $carrier['WeightMax'] > 0}
                                    {$less_weight=$less_weight+($package['TotalWeight']-$carrier['WeightMax'])}
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                {/foreach}
            {/if}

            {if $debugInfo['free_shipping_voucher']}
                <div class="card-block" id="freeshippinginfo" style="background-color: #{$message_color1|escape:'html':'UTF-8'};">
                    {l s='The are a cupon code applied with code:' mod='lgfreeshippingzones'}
                    <span style="color: #000000">{$debugInfo['free_shipping_voucher_code']|escape:'html':'UTF-8'}</span>
                    {l s='that give your customer free shipping costs.' mod='lgfreeshippingzones'}
                </div>
            {elseif $is_free_shipping}
                <div class="card-block" id="freeshippinginfo" style="background-color: #{$message_color1|escape:'html':'UTF-8'};">
                    {l s='Total shipping costs will be free' mod='lgfreeshippingzones'}
                </div>
            {elseif $more_money > 0 && $less_weight <= 0 && $less_money <= 0}
                <div class="card-block" id="freeshippinginfo" style="background-color: #{$message_color2|escape:'html':'UTF-8'};">
                    {l s='Spend %s more to get free shipping' sprintf=[Tools::displayPrice($more_money)] mod='lgfreeshippingzones'}{if $more_weight > 0} {l s='or you can also add %1$d %2$s more' sprintf=[$more_weight, $weight_unit] mod='lgfreeshippingzones'}{/if}
                </div>
            {elseif $more_weight > 0 && $less_weight <= 0 && $less_money <= 0}
                <div class="card-block" id="freeshippinginfo" style="background-color: #{$message_color2|escape:'html':'UTF-8'};">
                    {l s='Add %1$d %2$s more for get free shipping' sprintf=[$more_weight, $weight_unit] mod='lgfreeshippingzones'}
                </div>
            {elseif $carrier['WeightMax'] > 0 and $package['TotalWeight'] > $carrier['WeightMax']}
                <div class="card-block" id="freeshippinginfo" style="background-color: #{$message_color2|escape:'html':'UTF-8'};">
                    {l s='Maximum weight exceeded %.2f %2$s' sprintf=[$less_weight, $weight_unit] mod='lgfreeshippingzones'}
                </div>
            {elseif $more_money == 0 and $less_money == 0 and $carrier['WeightMin'] == 0 and $carrier['WeightMax'] == 0}
                <div class="card-block" id="freeshippinginfo" style="background-color: #{$message_color2|escape:'html':'UTF-8'};">
                    <i class="bi bi-exclamation-triangle"></i>
                    {l s='Free shipping is not configured in the module for the zone' mod='lgfreeshippingzones'} {$address['ZoneName']|escape:'htmlall':'UTF-8'}
                    {l s='and carrier' mod='lgfreeshippingzones'} {$carrier['name']|escape:'htmlall':'UTF-8'}
                    {l s='(please contact the shop administrator)' mod='lgfreeshippingzones'}
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
            {else}
                <div class="card-block" id="freeshippinginfo"></div>
            {/if}
        {/if}
    {else}
        <div class="card-block lgmessage lgdebug" style="background-color:#{$message_color2|escape:'html':'UTF-8'}">
            {l s='There is no product on your cart.' mod='lgfreeshippingzones'}
        </div>
    {/if}
    {* MESSAGES IN CART END *}
{/if}
