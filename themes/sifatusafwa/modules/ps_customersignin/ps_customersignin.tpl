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
<div id="_desktop_user_info">
    <div class="user-info">
        {if $logged}
            <a href="{$urls.pages.my_account}" rel="nofollow" class="header__tools--link">
                <svg xmlns="http://www.w3.org/2000/svg" width="21.236" height="23.547" viewBox="0 0 21.236 23.547">
                    <path id="Path_56" data-name="Path 56" d="M16.618,5a7.507,7.507,0,0,0-4.147,13.714A10.733,10.733,0,0,0,6,28.547H8.124a8.495,8.495,0,1,1,16.989,0h2.124a10.737,10.737,0,0,0-6.471-9.834A7.506,7.506,0,0,0,16.618,5Zm0,2.141a5.352,5.352,0,1,1-5.309,5.352A5.315,5.315,0,0,1,16.618,7.141Z" transform="translate(-6 -5)" fill="#393939"/>
                </svg>
            </a>
        {else}
            <a href="{$urls.pages.authentication}?back={$urls.current_url|urlencode}" rel="nofollow" class="header__tools--link">
                <svg xmlns="http://www.w3.org/2000/svg" width="21.236" height="23.547" viewBox="0 0 21.236 23.547">
                    <path id="Path_56" data-name="Path 56" d="M16.618,5a7.507,7.507,0,0,0-4.147,13.714A10.733,10.733,0,0,0,6,28.547H8.124a8.495,8.495,0,1,1,16.989,0h2.124a10.737,10.737,0,0,0-6.471-9.834A7.506,7.506,0,0,0,16.618,5Zm0,2.141a5.352,5.352,0,1,1-5.309,5.352A5.315,5.315,0,0,1,16.618,7.141Z" transform="translate(-6 -5)" fill="#393939"/>
                </svg>
            </a>
        {/if}
    </div>
</div>
