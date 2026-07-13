{*
 * 2020  (c)  Egio digital
 *
 * MODULE EgBanner
 *
 * @author    Egio digital
 * @copyright Copyright (c) , Egio digital
 * @license   Commercial
 * @version    1.0.0
 */}
*}

{if $status|default:0 == 1}
    <div class="contact-banner">
        <div class="container">
            <div class="contact-banner__wrapper">
                {if !empty($img)}
                    <picture>
                        {if !empty($img_mobile)}
                            <source media="(max-width: 992px)" srcset="{$img_mobile}">
                        {/if}
                        <img width="1664" height="600" src="{$img}" alt="{$title|default:''}" loading="lazy" />
                    </picture>
                {/if}
                <div class="contact-banner__inner">
                    {if !empty($title)}
                        <h2>{$title}</h2>
                    {/if}
                    {if !empty($sub_title)}
                        <h3>{$sub_title}</h3>
                    {/if}
                    {if !empty($desc)}
                        <p>{$desc|strip_tags|truncate:360:'...' nofilter}</p>
                    {/if}
                    {if !empty($btn_url) && !empty($btn_txt)}
                        <a class="btn" href="{$btn_url}">{$btn_txt}</a>
                    {/if}
                </div>
            </div>
        </div>
    </div>
{/if}
