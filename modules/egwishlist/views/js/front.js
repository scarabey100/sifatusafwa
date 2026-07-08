/**
 * 2024 (c) Egio digital
 *
 * MODULE EgWishList
 *
 * @version 1.0.0
 */

$(document).ready(function () {
  // Utility functions
  const showLoginRequired = () => {
    $('.need_login_notification .notification__item').addClass('active');
    setTimeout(() => $('.need_login_notification .notification__item').removeClass('active'), 4000);
  };

  const updateWishlistCount = (count) => {
    egwishlist.nbProducts = count;
    $('.egwishlist-nb, #wishlist-count').text(count);
    // $('.egwishlist-nb').toggle(count > 0);
    $('.egwishlist-nb').toggleClass("egwishlist-nb-zero", count === 0);
  };

  const showNotification = (isAdd) => {
    $('.notification__item').addClass('active');
    $('.notification__content p').hide();
    $(`.notification__content p.${isAdd ? 'add' : 'remove'}`).show();
    setTimeout(() => $('.notification__item').removeClass('active'), 4000);
  };

  // Initialize wishlist count
  updateWishlistCount(egwishlist.nbProducts);

  // Add to wishlist handler
  $(document).on('click', '.js-egwishlist-add:not(.egwishlist-added)', function(event) {
    event.preventDefault();
    const self = this;
    $.post(self.dataset.url, {
      process: 'add',
      ajax: 1,
      idProduct: self.dataset.idProduct,
      idProductAttribute: self.dataset.idProductAttribute
    }, null, 'json')
    .then((resp) => {
      if (resp.success) {
        const wishedCount = resp.data.wished_count;
        updateWishlistCount(wishedCount);
        $(self).addClass('egwishlist-added');
        $(`.btn-egwishlist-add[data-id-product="${self.dataset.idProduct}"]`).addClass('egwishlist-added');
        showNotification(true);

        if (wishedCount === 0 && window.location.pathname === "/module/egwishlist/view") {
          location.reload();
        }
      }
    })
    .fail((resp) => prestashop.emit('handleError', { eventType: 'clickegWishlistAdd', resp }));
  });

  // Remove from wishlist handler
  $(document).on('click', '.js-egwishlist-remove, .egwishlist-added', function(event) {
    event.preventDefault();
    const self = this; 
    $.post(wishlist_url, {
      ajax: true,
      idProduct: self.dataset.idProduct,
      action: 'removeWish'
    })
    .then((result) => {
      result = JSON.parse(result);
      if (result.status) {
        const wishedCount = result.wished_count;
        updateWishlistCount(wishedCount);
        $(`.btn-egwishlist-add[data-id-product="${self.dataset.idProduct}"]`).removeClass('egwishlist-added');
        showNotification(false);

        if (wishedCount === 0 && window.location.pathname === "/module/egwishlist/view") {
          location.reload();
        } else {
          $(`article[data-id-product="${self.dataset.idProduct}"]`)
            .closest('.wishlist-no-btm')
            .add(`#egwishlist-product-${self.dataset.idProduct}`)
            .remove();
        }
      }
    })
    .fail((resp) => prestashop.emit('handleError', { eventType: 'clickegWishlistRemove', resp }));
  });

  // Add to cart handler
  $(document).on('click', '.remove-from-cart', function(event) {
    event.preventDefault();
    const self = this;
    prestashop.emit('clickegWishlistAddToCart', { dataset: self.dataset });
  });

  // Remove all items handler
  $('#delete-all').click(function() {
 
    const productIds = $(".js-egwishlist-remove")
      .map(function() { return $(this).data("id-product"); })
      .get()
      .join(",");
    const url = $(this).data('url');
    const rvType = $(this).data("rv-type");

    $.post(url, {
      process: 'remove-all',
      ajax: 1,
      idProducts: productIds,
      rvType
    }, null, 'json')
    .then(() => {
      $(".js-egwishlist-remove").each((_, elem) => {
        const productId = $(elem).data("id-product");
        $(`#egwishlist-product-${productId}`).remove();
        updateWishlistCount(--egwishlist.nbProducts);
      });
      showNotification(false);
    });
  });
});
