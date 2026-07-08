/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Revolut
 * @copyright Since 2020 Revolut
 * @license   https://opensource.org/licenses/AFL-3.0  Academic Free License (AFL 3.0)
 */

jQuery(function ($) {
  function mountOrderConfirmationBanner() {
    const target = document.getElementById("OrderConfirmationBanner");
    if (!target) return;

    RevolutUpsell = RevolutUpsell({
      locale: target.dataset.bannerLocale,
      publicToken: target.dataset.bannerPublicToken,
    });

    if (target.dataset.bannerType === "enrollment") {
      RevolutUpsell.enrollmentConfirmationBanner.mount(target, {
        orderToken: target.dataset.bannerOrderToken,
        promotionalBanner: true,
        __metadata: { channel: "prestashop" },
      });
    }

    if (target.dataset.bannerType === "promotional") {
      RevolutUpsell.promotionalBanner.mount(target, {
        transactionId: target.dataset.bannerTransactionId,
        currency: target.dataset.bannerCurrency,
        customer: {
          email: target.dataset.bannerCustomerEmail,
          phone: target.dataset.bannerCustomerPhone,
        },
        __metadata: { channel: "prestashop" },
      });
    }
  }

  function mountRevolutBenefitsBanner() {
    const target = document.getElementById("revolut-benefits-banner");
    if (!target) return;

    const { publicToken, locale, currency, amount } =
      window.informationalBannerData;

    RevolutUpsell({ locale, publicToken }).promotionalBanner.mount(target, {
      currency,
      amount,
      variant: "banner",
      __metadata: { channel: "prestashop" },
    });
  }

  function injectRevolutPayInformationalIcon() {
    const label = $("input[data-module-name='Revolut Pay']")
      .closest("div.payment-option")
      .find("label");
    if (!label.length) return;

    label.css({ display: "flex", "white-space": "nowrap", "align-items": "flex-start"});
    lastImg = label.find('img:last');
    if (!lastImg.length) return;
    lastImg.after('<div id="revolut-pay-informational-icon"></div>');
  }

  function mountRevolutPayInformationalIcon() {
    const target = document.getElementById("revolut-pay-informational-icon");
    if (!target) return;

    const {
      publicToken,
      locale,
      currency,
      amount,
      revolutPayIconVariant: variant,
    } = window.informationalBannerData;

    if (variant === "disabled") return;

    const bannerOptions = {
      currency,
      amount,
      variant,
      style: { color: "blue" },
      __metadata: { channel: "prestashop" },
    };

    if (variant === "cashback") {
      bannerOptions.variant = "link";
      bannerOptions.style.text = "cashback";
    }

    RevolutUpsell({ locale, publicToken }).promotionalBanner.mount(
      target,
      bannerOptions
    );
  }

  function mountCardGatewaySignUpBanner() {
    const upsellBannerElement = document.getElementById(
      "revolut-gateway-signup-banner"
    );
    if (!upsellBannerElement) return;

    const { publicToken, locale } = window.informationalBannerData;

    RevolutUpsell({ locale, publicToken }).cardGatewayBanner.mount(
      upsellBannerElement,
      {
        orderToken: upsellBannerElement.dataset.bannerOrderToken,
      }
    );
  }

  if (window.currentPage === "order-confirmation") {
    mountOrderConfirmationBanner();
  }

  if (window.currentPage === "order") {
    mountRevolutBenefitsBanner();
    mountCardGatewaySignUpBanner();
    injectRevolutPayInformationalIcon();
    mountRevolutPayInformationalIcon();
  }
});
