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

{block name='hook_footer_before'}
    {hook h='displayFooterBefore'}
{/block}

{block name='hook_footer_after'}
    {hook h='displayFooterAfter'}
{/block}

<div class="footer">
    <div class="footer__top">
        <div class="container">
            <div class="footer__logo">
                {renderLogo}
                {hook h='displayFooterCustomText'}
                <div class="footer__reviews">
                    <a href="https://fr.trustpilot.com/review/sifatusafwa.com" target="_blank">
                        <img width="152" height="32" src="{$urls.img_url}trustpilot.png" alt="Trustpilot" loading="lazy">
                    </a>
                </div>
            </div>
            {block name='hook_footer'}
                {hook h='displayFooter'}
            {/block}
        </div>
    </div>
    <div class="footer__bottom">
        <div class="container">
            <div class="footer__copyright">
                {block name='copyright_link'}
                    {l
                    s='%copyright% %shopname% - %year%. All rights reserved.'
                    sprintf=[
                    '%shopname%' => $shop.name,
                    '%year%' => 'Y'|date,
                    '%copyright%' => '©'
                    ]
                    d='Shop.Theme.Global'
                    }
                {/block}
            </div>
            <div class="footer__methods">
                <img width="756" height="39" src="{$urls.img_url}payment-options.png" alt="{l s='Payment methods' d='Shop.Theme.Global'}" loading="lazy" />
            </div>
        </div>
    </div>
</div>
