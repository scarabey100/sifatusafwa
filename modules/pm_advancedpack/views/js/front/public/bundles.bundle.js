/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	// The require scope
/******/ 	var __webpack_require__ = {};
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// ESM COMPAT FLAG
__webpack_require__.r(__webpack_exports__);

;// CONCATENATED MODULE: ./bundles/bundle-handler.js
/**
 *
 * Advanced Pack
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/

const { $ } = window;

class BundleHandler {
  constructor() {
    this.addToCartFormDefaultValues = {};
    this.storeBundleFormDefaultValues();

    var self = this;

    this.initEvents();
    prestashop.on('updatedProduct', function (datas) {
      self.initEvents();
    });
  }

  initEvents() {
    $('.bundle-products .bundle').on(
      'click',
      (event) => this.updateProduct(event),
    );

    $(document).on(
      'ap5-Before-AddPackToCart',
      (event) => this.resetAddToCartFormDefaultValues()
    );
  }

  /**
   * Store 'add to cart' form values
   * for future usage
   *
   * @private
   */
  storeBundleFormDefaultValues() {
    const storage = this.addToCartFormDefaultValues;

    storage['id_product'] = parseInt($('input[name="id_product"]').val());
    storage['form_url'] = $('#add-to-cart-or-refresh').attr('action');
    storage['data-button-action'] = $('[data-button-action="add-to-cart"]').attr('data-button-action');
    storage['product-title'] = $(prestashop.selectors.product.container).find('h1').first().html();

    this.addToCartFormDefaultValues = storage;
  }

  /**
   * Reset 'add to cart' form values
   * using previously stored default values
   *
   * @private
   */
  resetAddToCartFormDefaultValues() {
    const previouslyStoredValues = this.addToCartFormDefaultValues;

    $('input[name="id_product"]').removeData('ap5BundleId');
    $('#add-to-cart-or-refresh').attr('action', previouslyStoredValues['form_url']);
    $(prestashop.selectors.product.container).find('h1').html(previouslyStoredValues['product-title']);
  }

  /**
   * @private
   */
  updateProduct(event, test) {
    if ($(event.currentTarget).hasClass('active')) {
      this.resetAddToCartFormDefaultValues();
      // Reset bundle selection
      prestashop.emit('updateProduct', {
        reason: {
          idProduct: this.addToCartFormDefaultValues['id_product'],
        }
      });
      return;
    }
    $('.bundle-products .bundle.active').removeClass('active');
    $(event.currentTarget).addClass('active');

    const selectedProduct = $('.bundle-products .bundle.active');

    if (parseInt(selectedProduct.data('quantity')) >= 1) {
      $('#add-to-cart-or-refresh').addClass('ap5-buy-block');
      $('[data-button-action="add-to-cart"]').attr('data-button-action', 'add-pack-to-cart');
      $('.product-add-to-cart .js-add-to-cart').removeClass('js-add-to-cart');
      $('#add-to-cart-or-refresh').attr('action', selectedProduct.data('addToCartUrl'));

      this.updateProductId(selectedProduct);
    } else {
      $('#add-to-cart-or-refresh').removeClass('ap5-buy-block');
      $('[data-button-action="add-pack-to-cart"]').attr('data-button-action', 'add-to-cart');
      $('.product-add-to-cart').addClass('js-add-to-cart');
      this.resetAddToCartFormDefaultValues();
    }

    this.updatePrice(selectedProduct);
    this.selectThumbnails(selectedProduct);
  }

  /**
   * @private
   */
  updateProductId(selectedProduct) {
    if (selectedProduct === undefined) {
      selectedProduct = $('.bundle-products .bundle.active');
    }
    $('input[name="id_product"]').data('ap5BundleId', parseInt(selectedProduct.data('product')));
  }

  /**
   * @private
   */
  updatePrice(selectedProduct) {
    const url = pm_advancedpack.ajaxUrl;

    $.ajax({
      type: 'POST',
      url,
      data: {
        ajax: 1,
        action: 'getBundlePrices',
        bundleId: parseInt(selectedProduct.data('product')),
        productId: parseInt(selectedProduct.data('mainProduct')),
        token: pm_advancedpack.staticToken
      },
      dataType: 'json',
    })
      .done((response) => {
        $(prestashop.selectors.product.prices)
          .first()
          .replaceWith(response.product_prices);
        $(prestashop.selectors.product.container).find('h1')
          .first()
          .html(response.product_title);
      })
      .fail((errors) => {
        alert(errors.responseJSON.errors);
      });
  }

  /**
   * @private
   */
  selectThumbnails(selectedProduct) {
    const imgSrc = $(selectedProduct).data('image');

    if (imgSrc !== undefined) {
        $(prestashop.selectors.product.imageContainer.split(', ').map(selector => selector + ' [src="' + imgSrc + '"]').join(', ')).click();
    }
  }
}

/* harmony default export */ const bundle_handler = (BundleHandler);

;// CONCATENATED MODULE: ./bundles/index.js
/**
 *
 * Advanced Pack
 *
 * @author Presta-Module.com <support@presta-module.com>
 * @copyright Presta-Module
 *
 *           ____     __  __
 *          |  _ \   |  \/  |
 *          | |_) |  | |\/| |
 *          |  __/   | |  | |
 *          |_|      |_|  |_|
 *
 ****/



const {$: bundles_$} = window;

bundles_$(() => {
  new bundle_handler();
});

window.bundles = __webpack_exports__;
/******/ })()
;