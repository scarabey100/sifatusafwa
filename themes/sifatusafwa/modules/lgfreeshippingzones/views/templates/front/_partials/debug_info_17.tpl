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

{* DEBUG MODE *}
<p class="lgmessage lgdebug" style="background-color:#2eacce;">
    {l s='DEBUG FREE SHIPPING' mod='lgfreeshippingzones'}<br>
    ---<br><br>
    <strong>{l s='Shop' mod='lgfreeshippingzones'}:</strong><br>----<br>
    <strong>{l s='Id' mod='lgfreeshippingzones'}:</strong> {$debugInfo.Shop.id|intval}<br>
    <strong>{l s='Name' mod='lgfreeshippingzones'}:</strong> {$debugInfo['Shop']['name']|escape:'htmlall':'UTF-8'}<br><br>
    <strong style="padding-left: 20px;">{l s='Addresses' mod='lgfreeshippingzones'}:</strong><br><span style="padding-left: 20px;">----</span><br>
    <strong style="padding-left: 20px;">{l s='Number of addresses' mod='lgfreeshippingzones'}:</strong> {$debugInfo.NumberOfShippingAddress|intval}<br><br>
    {if (isset($debugInfo['Addresses']))}
        {foreach $debugInfo['Addresses'] as $address}
            <strong style="padding-left: 20px;">{l s='Id' mod='lgfreeshippingzones'}:</strong> {$address['id']|intval}<br>
            <strong style="padding-left: 20px;">{l s='Name' mod='lgfreeshippingzones'}:</strong> {$address['address']|escape:'htmlall':'UTF-8'}<br>
            <strong style="padding-left: 20px;">{l s='Zone ID' mod='lgfreeshippingzones'}:</strong> {$address['ZoneID']|intval}<br>
            <strong style="padding-left: 20px;">{l s='Zone Name' mod='lgfreeshippingzones'}:</strong> {$address['ZoneName']|escape:'htmlall':'UTF-8'}<br><br>
            <strong style="padding-left: 40px;">{l s='Packages' mod='lgfreeshippingzones'}:</strong><br><span style="padding-left: 40px;">----</span><br>
            <strong style="padding-left: 40px;">{l s='Number of packages' mod='lgfreeshippingzones'}:</strong> {$address['NumberOfPackages']|intval}<br><br>
            {foreach $address['Packages'] as $package}
                <strong style="padding-left: 40px;">{l s='Id' mod='lgfreeshippingzones'}:</strong> {$package['id']|intval}<br>
                <strong style="padding-left: 40px;">{l s='Number of Products' mod='lgfreeshippingzones'}:</strong> {$package['NumberOfProducts']|intval}<br>
                <strong style="padding-left: 40px;">{l s='Products Cost' mod='lgfreeshippingzones'}:</strong> {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'}<br>
                {*<strong style="padding-left: 40px;">{l s='Shipping Cost' mod='lgfreeshippingzones'}:</strong> {if $package['TotalShipping'] == 0}{l s='Free' mod='lgfreeshippingzones'}{else}{Tools::displayPrice($package['TotalShipping'])}{/if}<br>*}
                <strong style="padding-left: 40px;">{l s='Package Weight' mod='lgfreeshippingzones'}:</strong> {$package['TotalWeight']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}<br><br>
                <strong style="padding-left: 60px;">{l s='Carriers' mod='lgfreeshippingzones'}:</strong><br><span style="padding-left: 60px;">----</span><br>
                <strong style="padding-left: 60px;">{l s='Number of carriers' mod='lgfreeshippingzones'}:</strong> {$package['NumberOfCarriers']|escape:'htmlall':'UTF-8'}<br>
                {if $package['undeliverable']}
                    <span style="padding-left: 60px;">{l s='Undeliverable' mod='lgfreeshippingzones'}</span><br><br>
                {else}
                    {foreach $package['Carriers'] as $carrier}
                        <strong style="padding-left: 60px;">{l s='Id' mod='lgfreeshippingzones'}:</strong> {$carrier['id']|intval}<br>
                        <strong style="padding-left: 60px;">{l s='Name' mod='lgfreeshippingzones'}:</strong> {$carrier['name']|escape:'htmlall':'UTF-8'}<br>
                        <strong style="padding-left: 60px;">{l s='Price:' mod='lgfreeshippingzones'}:</strong>
                        {if ($carrier['PriceMin'] == 0 and $carrier['PriceMin'] == 0)}
                            {l s='All' mod='lgfreeshippingzones'}
                        {else}
                            {l s='From' mod='lgfreeshippingzones'} {if $carrier['PriceMin'] > 0}{$carrier['PriceMin']|escape:'htmlall':'UTF-8'}{else}&infin;{/if}
                            {l s='to' mod='lgfreeshippingzones'} {if $carrier['PriceMax'] > 0}{$carrier['PriceMax']|escape:'htmlall':'UTF-8'}{else}&infin;{/if}
                        {/if}
                        <br>
                        <strong style="padding-left: 60px;">{l s='Weight:' mod='lgfreeshippingzones'}:</strong>
                        {if ($carrier['WeightMin'] == 0 and $carrier['WeightMin'] == 0)}
                            {l s='All' mod='lgfreeshippingzones'}
                        {else}
                            {l s='From' mod='lgfreeshippingzones'} {if $carrier['WeightMin'] > 0}{$carrier['WeightMin']|escape:'htmlall':'UTF-8'}{else}&infin;{/if}
                            {l s='to' mod='lgfreeshippingzones'} {if $carrier['WeightMax'] > 0}{$carrier['WeightMax']|escape:'htmlall':'UTF-8'}{else}&infin;{/if}
                        {/if}
                        {if $lgfs_usetax}
                            {l s='TAX included' mod='lgfreeshippingzones'}{else}{l s='TAX excluded' mod='lgfreeshippingzones'}
                        {/if}
                        <br>
                        <strong style="padding-left: 60px;">{l s='Reference' mod='lgfreeshippingzones'}:</strong> {$carrier['reference']|escape:'htmlall':'UTF-8'}<br>
                        <strong style="padding-left: 60px;">{l s='Is free (By Carrier Configuration)' mod='lgfreeshippingzones'}:</strong> {if $carrier['isFree']}{l s='Yes' mod='lgfreeshippingzones'}{else}{l s='No' mod='lgfreeshippingzones'}{/if}<br><br>
                        <strong style="padding-left: 60px;">{l s='Shipping Cost' mod='lgfreeshippingzones'}:</strong> {if $carrier['shippingCost'] == 0 || $debugInfo['free_shipping_voucher_code']}{l s='Free' mod='lgfreeshippingzones'}{else}{Tools::displayPrice($carrier['shippingCost'])|escape:'htmlall':'UTF-8'}{/if}<br><br>
                        <span style="padding-left: 60px;">{if $carrier['our_shipping_costs_are_free'] || $debugInfo['free_shipping_voucher']}{l s='Our shipping costs are free' mod='lgfreeshippingzones'}{else}{l s='Our shipping costs are not free' mod='lgfreeshippingzones'}{/if}</span><br>
                        <span style="padding-left: 60px;">
                            {*<p class="lgmessage lgdebug" style="background-color:#2eacce;">*}
                            {if $carrier['shippingCost'] > 0 && !$debugInfo['free_shipping_voucher']}
                                {if $carrier['PriceMin'] == 0 and $carrier['PriceMax'] == 0 and $carrier['WeightMin'] == 0 and $carrier['WeightMax'] == 0}
                                    <!--span style="background-color:#FE9126; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='Free shipping is not configured in the module for the zone' mod='lgfreeshippingzones'} {$address['ZoneName']|escape:'htmlall':'UTF-8'}
                                        {l s='and carrier' mod='lgfreeshippingzones'} {$carrier['name']|escape:'htmlall':'UTF-8'}
                                        {l s='(please check the module configuration)' mod='lgfreeshippingzones'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br-->
                                {/if}
                                {if $carrier['PriceMin'] > 0 and $package['TotalCost'] < $carrier['PriceMin']}
                                    <span style="background-color:#FE9126; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='Minimum price not reached' mod='lgfreeshippingzones'}:
                                        {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'} < {Tools::displayPrice($carrier['PriceMin'])|escape:'htmlall':'UTF-8'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                                {if $carrier['WeightMin'] > 0 and $package['TotalWeight'] < $carrier['WeightMin']}
                                    <span style="background-color:#FE9126; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='Minimum weight not reached' mod='lgfreeshippingzones'}:
                                        {$package['TotalWeight']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                        < {$carrier['WeightMin']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                                {if $carrier['PriceMax'] > 0 and $package['TotalCost'] > $carrier['PriceMax']}
                                    <span style="background-color:#FE9126; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='Maximum price exceeded' mod='lgfreeshippingzones'}:
                                        {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'} > {Tools::displayPrice($carrier['PriceMax'])|escape:'htmlall':'UTF-8'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                                {if $carrier['WeightMax'] > 0 and $package['TotalWeight'] > $carrier['WeightMax']}
                                    <span style="background-color:#FE9126; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='Maximum weight exceeded' mod='lgfreeshippingzones'}:
                                        {$package['TotalWeight']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                        > {$carrier['WeightMax']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                            {/if}
                            {* DEBUG - FREE SHIPPING *}
                            {if $carrier['shippingCost'] === 0 || $debugInfo['free_shipping_voucher']}
                                {if $debugInfo['free_shipping_voucher']}
                                    <span style="background-color:#55C65E; padding:5px;">
                                        {l s='The are a cupon code applied with code:' mod='lgfreeshippingzones'}
                                        <span style="color: #000000">{$debugInfo['free_shipping_voucher_code']|escape:'htmlall':'UTF-8'}</span>
                                    </span>
                                    <br>
                                {/if}
                                {if $carrier['isFree'] > 0}
                                    <span style="background-color:#ff0000; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='Free shipping is permanently enabled for the carrier' mod='lgfreeshippingzones'} {$carrier['name']|escape:'htmlall':'UTF-8'}
                                        {l s='(please check your carrier configuration)' mod='lgfreeshippingzones'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                                {if $carrier['carrierCheck'] > 0}
                                    <span style="background-color:#ff0000; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='The carrier' mod='lgfreeshippingzones'} {$carrier['name']|escape:'htmlall':'UTF-8'}
                                        {l s='is not enabled for the zone' mod='lgfreeshippingzones'} {$address['ZoneName']|escape:'htmlall':'UTF-8'}
                                        {l s='(please check your carrier configuration)' mod='lgfreeshippingzones'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                                {if $debugInfo['GeneralConfig'] > 0}
                                    <span style="background-color:#ff0000; padding:5px;">
                                        <i class="icon-exclamation-triangle"></i>
                                        {l s='The general configuration of your shop for free shipping is not set at 0' mod='lgfreeshippingzones'}
                                        {l s='(please check your carrier configuration)' mod='lgfreeshippingzones'}
                                        <i class="icon-exclamation-triangle"></i>
                                    </span>
                                    <br>
                                {/if}
                                {if !$debugInfo['free_shipping_voucher'] and !$carrier['isFree'] and !$carrier['carrierCheck'] and !$debugInfo['GeneralConfig']}
                                    {if $carrier['PriceMin'] > 0 and $package['TotalCost'] > $carrier['PriceMin']}
                                        <span style="background-color:#55C65E; padding:5px;">
                                            {l s='Minimum price reached' mod='lgfreeshippingzones'}:
                                            {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'} > {Tools::displayPrice($carrier['PriceMin'])|escape:'htmlall':'UTF-8'}
                                            {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'} > {Tools::displayPrice($carrier['PriceMin'])|escape:'htmlall':'UTF-8'}
                                        </span>
                                        <br>
                                    {/if}
                                    {if $carrier['PriceMax'] > 0 and $package['TotalCost'] < $carrier['PriceMax']}
                                        <span style="background-color:#55C65E; padding:5px;">
                                            {l s='Maximum price not exceeded' mod='lgfreeshippingzones'}:
                                            {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'} < {Tools::displayPrice($carrier['PriceMax'])|escape:'htmlall':'UTF-8'}
                                            {Tools::displayPrice($package['TotalCost'])|escape:'htmlall':'UTF-8'} < {Tools::displayPrice($carrier['PriceMax'])|escape:'htmlall':'UTF-8'}
                                        </span>
                                        <br>
                                    {/if}
                                    {if $carrier['WeightMin'] > 0 and $package['TotalWeight'] > $carrier['WeightMin']}
                                        <span style="background-color:#55C65E; padding:5px;">
                                            {l s='Minimum weight reached' mod='lgfreeshippingzones'}:
                                            {$package['TotalWeight']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                            > {$carrier['WeightMin']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                        </span>
                                        <br>
                                    {/if}
                                    {if $carrier['WeightMax'] > 0 and $package['TotalWeight'] < $carrier['WeightMax']}
                                        <span style="background-color:#55C65E; padding:5px;">
                                            {l s='Maximum weight not exceeded' mod='lgfreeshippingzones'}:
                                            {$package['TotalWeight']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                            < {$carrier['WeightMax']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}
                                        </span>
                                        <br>
                                    {/if}
                                {/if}
                            {/if}
                            {*</p>*}
                        </span><br>
                    {/foreach}
                {/if}
            {/foreach}
        {/foreach}
    {else}

    {/if}
    {if !$debugInfo['undeliverable']}
        <strong>{l s='Totals' mod='lgfreeshippingzones'}:</strong><br>------<br>
        <strong>{l s='Total number of Products' mod='lgfreeshippingzones'}:</strong> {$debugInfo['Totals']['NumberOfProducts']|intval}<br>
        <strong>{l s='Total Products cost' mod='lgfreeshippingzones'}:</strong> {Tools::displayPrice($debugInfo['Totals']['ProductsCost'])|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='Total Shipping cost' mod='lgfreeshippingzones'}:</strong> {Tools::displayPrice($debugInfo['Totals']['ShippingCost'])|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='Total weight' mod='lgfreeshippingzones'}:</strong> {$debugInfo['Totals']['Weight']|escape:'htmlall':'UTF-8'} {$weight_unit|escape:'htmlall':'UTF-8'}<br>
        <strong>{l s='Free Shipping Vouchers' mod='lgfreeshippingzones'}:</strong>
        {if $debugInfo['free_shipping_voucher']}
            {$debugInfo['free_shipping_voucher_code']|escape:'htmlall':'UTF-8'}: -{Tools::displayPrice($debugInfo['Totals']['ShippingCost'])|escape:'htmlall':'UTF-8'}<br>
        {else}
            {l s='None' mod='lgfreeshippingzones'}<br>
        {/if}
        <strong>{l s='Final shipping costs' mod='lgfreeshippingzones'}: {if $debugInfo['free_shipping_voucher']}{Tools::displayPrice(0)|escape:'htmlall':'UTF-8'}{else}{Tools::displayPrice($debugInfo['Totals']['ShippingCost'])|escape:'htmlall':'UTF-8'}{/if}</strong>
    {/if}
</p>
<p class="lgmessage lgdebug" style="background-color:#2eacce;">
    {* DEBUG - SHIPPING NOT FREE *}
    {l s='Resume:' mod='lgfreeshippingzones'}<br><br>
    {if !$debugInfo['undeliverable']}
        {l s='The order will be delivered to ' mod='lgfreeshippingzones'}{$debugInfo.NumberOfShippingAddress|intval}{l s='addresses' mod='lgfreeshippingzones'}<br>
        {if (isset($debugInfo['Addresses']))}
            {foreach $debugInfo['Addresses'] as $address}
                <span style="padding-left: 20px;">
                        {*{l s='For this address:' mod='lgfreeshippingzones'} <span style="font-style: italic">{$address['address']|escape:'htmlall':'UTF-8'}</span>,*}
                    {$address['NumberOfPackages']|intval}{l s=' packages will be delivered' mod='lgfreeshippingzones'}
                    </span><br>
                {foreach $address['Packages'] as $package}
                    {assign var='package_number' value=$package['id']+1}
                    <span style="padding-left: 40px;">
                            {l s='Pacackage number ' mod='lgfreeshippingzones'}{$package_number|intval}
                        {if $package['undeliverable']}
                            {l s='is undeliverable' mod='lgfreeshippingzones'}
                        {else}
                            {foreach $package['Carriers'] as $carrier}
                                {l s='shipping costs' mod='lgfreeshippingzones'}
                                {if ($debugInfo['free_shipping_voucher_code'] or $carrier['isFree'] or $carrier['shippingCost'] == 0)}
                                    {l s='are free' mod='lgfreeshippingzones'}
                                {/if}
                                {if (!$debugInfo['free_shipping_voucher_code'] and !$carrier['isFree'] and $carrier['shippingCost'] > 0)}
                                    {l s='are:' mod='lgfreeshippingzones'}
                                    {Tools::displayPrice($carrier['shippingCost'])|escape:'htmlall':'UTF-8'}
                                {/if}
                            {/foreach}
                        {/if}
                        </span><br>
                {/foreach}
            {/foreach}
        {else}

        {/if}
        <br>
        {if $debugInfo['free_shipping_voucher']}
            {l s='The are a cupon code applied with code:' mod='lgfreeshippingzones'}
            <span style="color: #000000">{$debugInfo['free_shipping_voucher_code']|escape:'htmlall':'UTF-8'}</span>
            {l s='that give your customer free shipping costs.' mod='lgfreeshippingzones'}
            <br>
        {/if}
        {if !$debugInfo['free_shipping_voucher'] && $debugInfo['Totals']['ShippingCost'] > 0}
            {l s='Total shipping costs will not be free' mod='lgfreeshippingzones'}
        {/if}
        {if $debugInfo['free_shipping_voucher'] || $debugInfo['Totals']['ShippingCost'] == 0}
            {l s='Total shipping costs will be free' mod='lgfreeshippingzones'}
        {/if}
    {else}
        {l s='The order can not be delivered' mod='lgfreeshippingzones'}<br>
    {/if}
</p>
{* DEBUG MODE END *}
