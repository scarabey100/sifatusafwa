{**
 * 2007-2020 PrestaShop SA and Contributors
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.txt.
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
 * needs please refer to https://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * International Registered Trademark & Property of PrestaShop SA
 *}

<div id="search_widget" class="search-widgets" data-search-controller-url="{$search_controller_url}">
    <form method="get" action="{$search_controller_url}">
        <input type="hidden" name="controller" value="search">
        <i class="search" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="28.335" height="28.335" viewBox="0 0 28.335 28.335">
                <g id="Icon_feather-search" data-name="Icon feather-search" transform="translate(1 1)">
                    <path id="Path_492" data-name="Path 492" d="M27.541,16.02A11.52,11.52,0,1,1,16.02,4.5,11.52,11.52,0,0,1,27.541,16.02Z" transform="translate(-4.5 -4.5)" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                    <path id="Path_493" data-name="Path 493" d="M31.239,31.239l-6.264-6.264" transform="translate(-5.318 -5.318)" fill="none" stroke="#fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
                </g>
            </svg>
        </i>
        <input type="text" name="s" value="{$search_string}" placeholder="{l s='Search our catalog' d='Shop.Theme.Catalog'}" aria-label="{l s='Search' d='Shop.Theme.Catalog'}">
        <i class="material-icons clear" aria-hidden="true">clear</i>
    </form>
</div>
