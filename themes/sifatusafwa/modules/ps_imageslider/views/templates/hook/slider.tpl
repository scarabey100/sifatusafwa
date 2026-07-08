{**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 *}

{if $homeslider.slides}
    {assign var="_first_img_emitted" value=false}
    <div id="carousel" class="carousel">
        <div class="carousel__items">
            {foreach from=$homeslider.slides item=slide name='homeslider'}
                <div class="carousel__item">
                    <figure>
                        {{assign var="type_video" value=$slide.type_video}}
                        {if $type_video != 'video_type_image' && $slide.vimeo_video}
                            {if $slide.vimeo_video}
                                {if $type_video == 'video_type_youtube'}
                                    <iframe width="1920" height="950" playsinline autoplay loop muted src="https://www.youtube.com/embed/{$video}" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                {elseif $type_video == 'video_type_vimeo'}
                                    <iframe width="1920" height="950" playsinline autoplay loop muted src="https://player.vimeo.com/video/{$video}" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
                                {elseif $type_video == 'video_type_other'}
                                    <video width="1920" height="950" playsinline="true" autoplay="true" loop="true" muted="true" autoplay loop muted>
                                        <source src="{$slide.vimeo_video|escape:'html':'UTF-8'}" type="video/mp4">
                                        {l s=' Your browser does not support the video tag.' d='Modules.Egbanner.Egbanner'}
                                    </video>
                                {/if}
                            {/if}
                        {else}
                            {if !empty($slide.url)}<a href="{$slide.url}">{/if}
                            <picture>
                                <source media="(max-width: 992px)" srcset="{$slide.image_mobile_url}" />
                                {if !$_first_img_emitted}
                                    <img src="{$slide.image_url}" alt="{$slide.legend|escape}" loading="eager" fetchpriority="high" width="1920" height="731">
                                    {assign var="_first_img_emitted" value=true}
                                {else}
                                    <img src="{$slide.image_url}" alt="{$slide.legend|escape}" loading="lazy" width="1920" height="731">
                                {/if}
                            </picture>
                            {if !empty($slide.url)}</a>{/if}
                        {/if}
                        {if $slide.title || $slide.description}
                            <figcaption>
                                <div class="container">
                                    <h1>{$slide.title}</h1>
                                    {$slide.description nofilter}
                                    <div class="carousel__item--actions">
                                        {if !empty($slide.btn_1_url) && !empty($slide.btn_1_title)}
                                            <a href="{$slide.btn_1_url}" class="btn">
                                                {$slide.btn_1_title}
                                            </a>
                                        {/if}
                                        {if !empty($slide.btn_2_url) && !empty($slide.btn_2_title)}
                                            <a href="{$slide.btn_2_url}" class="btn">
                                                {$slide.btn_2_title}
                                            </a>
                                        {/if}
                                    </div>
                                </div>
                            </figcaption>
                        {/if}
                    </figure>
                </div>
            {/foreach}
        </div>
    </div>
{/if}
