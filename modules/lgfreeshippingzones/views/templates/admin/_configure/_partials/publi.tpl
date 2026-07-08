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

{if !(empty($publi))}
    <table id="banner-lg" class="panel">
        <tr>
            <td id="guia">
                <a href="../modules/lgfreeshippingzones/readme/readme_{$lang_iso|escape:'html':'UTF-8'}.pdf" target="_blank">
                    <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$lang_iso|escape:'html':'UTF-8'}/img/help.png"/>
                </a>
            </td>
            <td id="soporte">
                <a href="https://addons.prestashop.com/en/contact-us?id_product=8707" target="_blank">
                    <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$lang_iso|escape:'html':'UTF-8'}/img/support.png"/>
                </a>
            </td>
            <td id="video">
                <a href="http://addons.prestashop.com/{$lang_iso|escape:'html':'UTF-8'}/8707-livraison-gratuite-par-zone-transporteur-frais-de-port.html" target="_blank">
                    <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$lang_iso|escape:'html':'UTF-8'}/img/video.png"/>
                </a>
            </td>
            <td id="opinion">
                <a href="http://addons.prestashop.com/{$lang_iso|escape:'html':'UTF-8'}/ratings.php" target="_blank">
                    <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$lang_iso|escape:'html':'UTF-8'}/img/rateus.png"/>
                </a>
            </td>
            <td id="titulo">
                {l s='Prestashop Modules' mod='lgfreeshippingzones'}</br>
                {l s='That may interest you...' mod='lgfreeshippingzones'}
            </td>

            {foreach $publi['modules'] as $module}
                {if isset($module['languages'][$lang_iso])}
                    {assign var='module_language' value=$lang_iso}
                {else}
                    {assign var='module_language' value=$publi['default_language']}
                {/if}
                <td class="logos">
                    <a href="{$publi['addons_domain']|escape:'html':'UTF-8'}/{$module_language|escape:'html':'UTF-8'}/{$module['languages'][$module_language]['module_url']|escape:'html':'UTF-8'}" target="_blank">
                        <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$module_language|escape:'html':'UTF-8'}/img/{$module['icon']|escape:'html':'UTF-8'}"/>
                    </a>
                </td>
                <td class="modulos">
                    <a href="{$publi['addons_domain']|escape:'html':'UTF-8'}/{$module_language|escape:'html':'UTF-8'}/{$module['languages'][$module_language]['module_url']|escape:'html':'UTF-8'}" target="_blank">
                        <span class="enlaceverde">{l s='Module' mod='lgfreeshippingzones'}</span></br>
                        <span class="enlacenegro">{$module['languages'][$module_language]['module_name']|escape:'html':'UTF-8'}</span>
                    </a>
                </td>
            {/foreach}

            <td id="boton">
                <a href="http://addons.prestashop.com/{$lang_iso|escape:'html':'UTF-8'}/22_linea-grafica" target="_blank">
                    <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$lang_iso|escape:'html':'UTF-8'}/img/all.png"/>
                </a>
            </td>
            <td id="logo">
                <a href="http://addons.prestashop.com/{$lang_iso|escape:'html':'UTF-8'}/22_linea-grafica" target="_blank">
                    <img src="{$uri|escape:'html':'UTF-8'}modules/lgfreeshippingzones/views/img/publi/{$lang_iso|escape:'html':'UTF-8'}/img/logo_lgaddons.png"/>
                </a>
            </td>
        </tr>
    </table>
{/if}
