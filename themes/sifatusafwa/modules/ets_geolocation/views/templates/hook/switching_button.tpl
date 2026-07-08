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
{if isset($geo_country) && $geo_country}
    <div class="header__top--geolocation">
        <a class="ets_click_show{if !$is17} is_ver16{/if}" href="javascript:void(0)" {if isset($hasFirstAddress) && $hasFirstAddress}style="pointer-events: none;" {/if}>
            <svg xmlns="http://www.w3.org/2000/svg" width="11.423" height="14.104" viewBox="128 19.948 11.423 14.104">
                <g data-name="XMLID_2_">
                    <g data-name="Group 4691">
                        <g data-name="Group 4690">
                            <path d="M133.712 19.948A5.718 5.718 0 0 0 128 25.66c0 1.3.426 2.523 1.231 3.539 1.044 1.315 4.078 4.439 4.206 4.571l.275.282.274-.282c.129-.132 3.163-3.256 4.207-4.572a5.639 5.639 0 0 0 1.23-3.538 5.718 5.718 0 0 0-5.711-5.712Zm3.881 8.774c-.84 1.06-3.096 3.416-3.881 4.231-.786-.815-3.041-3.17-3.881-4.23a4.883 4.883 0 0 1-1.066-3.063 4.952 4.952 0 0 1 4.947-4.947 4.952 4.952 0 0 1 4.946 4.947 4.881 4.881 0 0 1-1.065 3.062Z" fill="#f7931d" fill-rule="evenodd" data-name="Path 17769"/>
                            <path d="M133.712 22.215a3.385 3.385 0 0 0-3.381 3.381 3.385 3.385 0 0 0 3.38 3.381 3.385 3.385 0 0 0 3.382-3.38 3.385 3.385 0 0 0-3.381-3.382Zm0 5.997a2.619 2.619 0 0 1-2.616-2.616 2.619 2.619 0 0 1 2.616-2.616 2.619 2.619 0 0 1 2.615 2.616 2.619 2.619 0 0 1-2.615 2.616Z" fill="#f7931d" fill-rule="evenodd" data-name="Path 17770"/>
                        </g>
                    </g>
                </g>
            </svg>
            {$geo_country->name|escape:'html':'UTF-8'}
        </a>
    </div>
{/if}

