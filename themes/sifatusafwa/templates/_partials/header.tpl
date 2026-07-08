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

{block name='header_banner'}
    <div class="header__top">
        <div class="container">
            {hook h='displayNav1'}
            <div class="header__top--dropdowns">
                {hook h='displayBanner'}
            </div>
        </div>
    </div>
{/block}

{block name='header_top'}
    <div class="header__middle">
        <div class="container">
            <div class="header__toggle">
                <div class="header__toggle--button">
                    <span></span>
                    <span></span>
                    <span></span>
                </div>
            </div>
            <div class="header__logo">
                {if $shop.logo_details}
                    {if $page.page_name == 'index'}
                        <h1>{renderLogo}</h1>
                    {else}
                        {renderLogo}
                    {/if}
                {/if}
            </div>
            <div class="header__search">
                {hook h='displayTop'}
            </div>
            <div class="header__tools">
                {hook h='displayNav2'}
            </div>
        </div>
    </div>
{/block}

{block name='header_nav'}
    <div class="header__bottom">
        <div class="container">
            {hook h='displayNavFullWidth'}
        </div>
    </div>
{/block}
