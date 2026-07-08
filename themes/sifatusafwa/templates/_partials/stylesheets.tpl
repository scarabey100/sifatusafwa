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
{foreach $stylesheets.external as $stylesheet}
  <link rel="stylesheet" href="{$stylesheet.uri}" type="text/css" media="{$stylesheet.media}">
{/foreach}

{foreach $stylesheets.inline as $stylesheet}
  <style>
    {$stylesheet.content}
  </style>
{/foreach}
{if $page.page_name == 'checkout'}
  {literal}
    <style id="checkout-payment-critical-css">
      body#checkout section.checkout-step .payment-options.checkout-payment-options{display:grid;gap:1rem;margin-bottom:1.5rem}
      body#checkout section.checkout-step .payment-options .checkout-payment-option{display:flex;align-items:center;gap:.875rem;min-height:4.5rem;padding:1rem;border:.0625rem solid var(--gray-1,#d4d4d4);border-radius:.75rem;background:var(--white,#fff);box-shadow:0 .25rem 1rem rgba(0,0,0,.04)}
      body#checkout section.checkout-step .payment-options .checkout-payment-option:hover,body#checkout section.checkout-step .payment-options .checkout-payment-option:focus-within{border-color:var(--orange,#f7931d);box-shadow:0 .5rem 1.5rem rgba(0,0,0,.08)}
      body#checkout section.checkout-step .payment-options .checkout-payment-option .custom-radio{flex:0 0 auto;margin-top:.2rem}
      body#checkout section.checkout-step .payment-options .checkout-payment-option label.checkout-payment-option__label{display:flex;flex:1 1 auto;flex-wrap:wrap;align-items:center;width:100%;margin:0;gap:.35rem .625rem;cursor:pointer}
      body#checkout section.checkout-step .payment-options .checkout-payment-option__title{flex:0 0 auto;font-weight:600;line-height:1.35}
      body#checkout section.checkout-step .payment-options .checkout-payment-option__pay-later{order:2;display:inline-flex;flex:0 1 auto;align-items:center;width:fit-content;margin-right:auto;padding:.35rem .625rem;border-radius:999px;background:#ffc439;color:#111;font-size:.875rem;font-weight:600;line-height:1.25}
      body#checkout section.checkout-step .payment-options .checkout-payment-option__logo,body#checkout section.checkout-step .payment-options .checkout-payment-option label img{order:3;flex:0 0 auto;width:auto;max-height:1.65rem;margin:0;object-fit:contain}
      body#checkout section.checkout-step .payment-options .checkout-payment-option label img.revolut-card-logos{max-width:7.5rem;margin-left:auto}
      body#checkout section.checkout-step .payment-options .checkout-payment-option label>img:first-of-type{margin-left:auto}
      body#checkout #conditions-to-approve{margin-top:.5rem;padding:1rem;border-radius:.75rem;background:var(--gray-3,#f9f9f9)}
      body#checkout #conditions-to-approve .condition-label{display:flex;align-items:flex-start;gap:.625rem}
      body#checkout #conditions-to-approve .custom-checkbox{flex:0 0 auto;margin-top:.125rem}
      body#checkout #conditions-to-approve label{margin:0;line-height:1.45}
      body#checkout #payment-confirmation .btn{min-width:13rem;margin-top:1.25rem}
      @media (max-width:575.98px){body#checkout section.checkout-step .payment-options .checkout-payment-option{padding:.875rem}body#checkout .checkout-payment-additional,body#checkout .checkout-payment-form{margin-left:0}body#checkout section.checkout-step .payment-options .checkout-payment-option__title{width:auto}body#checkout section.checkout-step .payment-options .checkout-payment-option label img.revolut-card-logos,body#checkout section.checkout-step .payment-options .checkout-payment-option label>img:first-of-type{margin-left:0}body#checkout section.checkout-step .payment-options .checkout-payment-option__pay-later{width:100%;margin-right:0}body#checkout #payment-confirmation .btn{width:100%}}
    </style>
  {/literal}
  <link rel="stylesheet" href="/themes/sifatusafwa/assets/css/checkout-payment-custom.css?v=checkout-payment-3" media="all">
{/if}
<link rel="stylesheet" href="{$urls.css_url}rtl-header-fixes.css" type="text/css" media="all">
