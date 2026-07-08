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

<div class="block-contact links wrapper">
    <div id="contact-infos" class="collapse">
        {if $contact_infos.email && $display_email}
            <div class="block-contact__email">
                <svg xmlns="http://www.w3.org/2000/svg" width="13.827" height="10.375" viewBox="1382.087 7328.02 13.827 10.375">
                    <g data-name="Page-1">
                        <g data-name="Icon-Set-Filled">
                            <path d="m1389 7334.464-1.433-1.187-4.999 5.117h12.736l-4.906-5.126-1.398 1.196Zm2.113-1.74 4.754 4.943c.027-.095.046-.195.046-.3v-8.543l-4.8 3.9Zm-9.026-3.92v8.563c0 .105.02.205.046.3l4.77-4.926-4.816-3.937Zm13.394-.784h-12.962l6.48 5.193 6.482-5.193Z" fill-rule="evenodd" data-name="mail"/>
                        </g>
                    </g>
                </svg>
                {mailto address=$contact_infos.email encode="javascript"}
            </div>
        {/if}
        <div class="block-contact__address">
            <svg xmlns="http://www.w3.org/2000/svg" width="11.335" height="17" viewBox="1383.332 7375 11.335 17">
                <g data-name="location-svgrepo-com (1)">
                    <g data-name="Group 4726">
                        <g data-name="Group 4725">
                            <path d="M1389 7379.957a.71.71 0 1 0 .002 1.418.71.71 0 0 0-.001-1.418Z" fill-rule="evenodd" data-name="Path 17791"/>
                            <path d="M1392.604 7376.291a5.705 5.705 0 0 0-4.732-1.183 5.631 5.631 0 0 0-4.46 4.598 5.633 5.633 0 0 0 .721 3.861l.29.483c.925 1.542 1.88 3.137 2.646 4.778l1.29 2.763a.708.708 0 0 0 1.283 0l1.162-2.488c.774-1.66 1.73-3.277 2.655-4.84l.43-.732c.51-.866.779-1.857.779-2.865a5.653 5.653 0 0 0-2.064-4.375Zm-3.603 6.5a2.128 2.128 0 0 1-2.125-2.125c0-1.172.953-2.126 2.125-2.126 1.171 0 2.125.954 2.125 2.126a2.128 2.128 0 0 1-2.125 2.125Z" fill-rule="evenodd" data-name="Path 17792"/>
                        </g>
                    </g>
                </g>
            </svg>
            {$contact_infos.address.formatted nofilter}
        </div>
{*        {if $contact_infos.phone}*}
{*            <div class="block-contact__phone">*}
{*                {l s='Call us: [1]%phone%[/1]'*}
{*                sprintf=[*}
{*                '[1]' => "<a href='tel:{$contact_infos['phone']|replace:' ':''}'>",*}
{*                '[/1]' => '</a>',*}
{*                '%phone%' => $contact_infos.phone*}
{*                ]*}
{*                d='Shop.Theme.Global'*}
{*                }*}
{*            </div>*}
{*        {/if}*}
        {if $contact_infos.fax}
            <div class="block-contact__fax">
                {l
                s='Fax: [1]%fax%[/1]'
                sprintf=[
                '[1]' => '<span>',
                '[/1]' => '</span>',
                '%fax%' => $contact_infos.fax
                ]
                d='Shop.Theme.Global'
                }
            </div>
        {/if}
        <div class="h3 block-contact__delivery">{l s='WE DELIVER WORLDWIDE !' d='Shop.Theme.Global'}</div>
    </div>
</div>
