{*
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */
*}

{if isset($banners) && !empty($banners) && $status == 1}
    {if $banners|@count > 1}
        <div class="hp-launchers">
            <div class="container">
                <ul class="hp-launchers__items">
                    {foreach from=$banners item=banner}
                        <li class="hp-launchers__item {if isset($banner.recto) && $banner.recto} use background {/if}">
{*                            {if isset($banner.link) && !empty($banner.link)}<a href="{$banner.link}">{/if}*}
                                <picture>
                                    <source media="(max-width: 992px)" srcset="{$uri}{$banner.image_mobile_background|escape:'html':'UTF-8'}">
                                    <img
                                            class="bg" width="820" height="300"
                                            src="{$uri}{$banner.image_background|escape:'html':'UTF-8'}"
                                            alt="{if $banner.alt}{$banner.alt|escape:'htmlall':'UTF-8'}{/if}"
                                            loading="lazy"
                                    />
                                </picture>
                                <div class="hp-launchers__item--inner">
                                    {if isset($banner.use_background) && $banner.use_background}
                                        {if isset($banner.image_book) && !empty($banner.image_book)}
                                            <img
                                                    class="logo" width="160" height="160"
                                                    src="{$uri}{$banner.image_book|escape:'html':'UTF-8'}"
                                                    alt="{if $banner.alt}{$banner.alt|escape:'htmlall':'UTF-8'}{/if}"
                                                    loading="lazy"
                                            />
                                        {/if}
                                    {/if}
                                    <h2>
                                        {if isset($banner.title_arabic) && !empty($banner.title_arabic)}
                                            <span class="font-ar">{$banner.title_arabic nofilter}</span>
                                        {/if}
                                        {if isset($banner.title) && !empty($banner.title)}
                                            {$banner.title nofilter}
                                        {/if}
                                    </h2>

                                    {if isset($banner.description) && !empty($banner.description)}
                                        {$banner.description nofilter}
                                    {/if}

                                    {if isset($banner.link) && !empty($banner.link)}
                                        <a class="btn btn-light" href="{$banner.link}">
                                            {if isset($banner.link_text) && !empty($banner.link_text)}
                                                {$banner.link_text nofilter}
                                            {else}
                                                {l s='Descover' d='Modules.Egbanner.Egbanner'}
                                            {/if}
                                        </a>
                                    {/if}
                                </div>
{*                            {if isset($banner.link) && !empty($banner.link)}</a>{/if}*}
                        </li>
                    {/foreach}
                </ul>
            </div>
        </div>
    {elseif $banners|@count == 1}
        <div class="hp-banner">
            <div class="container">
                {foreach from=$banners item=banner}
                    <div class="hp-banner__wrapper">
                        <picture>
                            {if isset($banner.image_background) && !empty($banner.image_background)}
                                <img class="bg" width="820" height="300"
                                     src="{$uri}{$banner.image_background|escape:'html':'UTF-8'}"
                                     alt="{if $banner.alt}{$banner.alt|escape:'htmlall':'UTF-8'}{/if}"
                                     title="{if $banner.title}{$banner.title|escape:'htmlall':'UTF-8'}{/if}"
                                />
                            {/if}
                            {if isset($banner.image_mobile_background) && !empty($banner.image_mobile_background)}
                                <img class="bg" width="820" height="300"
                                     src="{$uri}{$banner.image_mobile_background|escape:'html':'UTF-8'}"
                                     alt="{if $banner.alt}{$banner.alt|escape:'htmlall':'UTF-8'}{/if}"
                                     title="{if $banner.title}{$banner.title|escape:'htmlall':'UTF-8'}{/if}"
                                />
                            {/if}
                        </picture>
                        <div class="hp-banner__inner">
                            {if isset($banner.image_book) && !empty($banner.image_book)}
                                <img width="160" height="44" class="logo"
                                     src="{$uri}{$banner.image_book|escape:'html':'UTF-8'}"
                                     alt="{if $banner.alt}{$banner.alt|escape:'htmlall':'UTF-8'}{/if}"
                                     title="{if $banner.title}{$banner.title|escape:'htmlall':'UTF-8'}{/if}"
                                />
                            {/if}
                            <h2>
                                {if isset($banner.title_arabic) && !empty($banner.title_arabic)}
                                    <span class="font-ar">{$banner.title_arabic nofilter}</span>
                                {/if}
                                {if isset($banner.title) && !empty($banner.title)}
                                    {$banner.title nofilter}
                                {/if}
                            </h2>
                            {if isset($banner.description) && !empty($banner.description)}
                                {$banner.description nofilter}
                            {/if}
                            {if isset($banner.link) && !empty($banner.link)}
                                <a href="{$banner.link}" class="btn btn-light">
                                    {if isset($banner.link_text) && !empty($banner.link_text)}
                                        {$banner.link_text nofilter}
                                    {else}
                                        {l s='Descover' d='Modules.Egbanner.Egbanner'}
                                    {/if}
                                </a>
                            {/if}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
{/if}
