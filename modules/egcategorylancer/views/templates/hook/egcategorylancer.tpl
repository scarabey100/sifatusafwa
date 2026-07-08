{*
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @version    1.0.0
 *}
{if isset($banners) && !empty($banners) && $status == 1}
    <div class="hp-cat-seo">
        <div class="container">
            <ul class="hp-cat-seo__items">
                {foreach from=$banners item=banner}
                        
                    {if isset($banner.id_category) && !empty($banner.id_category)}
                    <li class="hp-cat-seo__item">
                        <h2>
                            <a href="{$banner.category_link}">
                                <span class="hp-cat-seo__item--name">{if !empty($banner.title)}{$banner.title nofilter}{else}{$banner.category_name nofilter}{/if}</span>
                                <span class="hp-cat-seo__item--icon">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M7 17L17 7M17 7H8M17 7V16" stroke="#F7931D" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </span>
                            </a>
                        </h2>
                    </li>
                    {/if}
                {/foreach}
            </ul>
        </div>
    </div>
{/if}
