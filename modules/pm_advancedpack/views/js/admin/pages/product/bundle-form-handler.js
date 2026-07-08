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

// Backward compatibility for PrestaShop 8.1+
if (typeof (window.modalConfirmation == 'undefined')) {
  window.modalConfirmation = {
    content: null,
    title: null,
    callback: null,

    create: function(content, title, callback) {
      this.content = content;
      this.title = title;
      this.callback = callback;

      return this;
    },

    show: function () {
      if (confirm(this.content)) {
        return this.callback.onContinue();
      }
    }
  }
}

class BundleFormHandler {
  constructor(refresh) {
    if (refresh !== true) {
      // Update bundle form when product is saved, or combinations generated (PS < 8.1)
      var saveProductObserver = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
          if (mutation.attributeName === 'disabled' && mutation.target[mutation.attributeName] == false) {
            this.updateEditBundleForm();
          }
        });
      });

      if (pm_advancedpack.bundle.selectors.submit && document.querySelector(pm_advancedpack.bundle.selectors.submit) != null) {
        const submitInput = document.querySelector(pm_advancedpack.bundle.selectors.submit);
        saveProductObserver.observe(submitInput, {
          attributes: true
        });
      }
    }

    if (!refresh && !$('#bundles-container').length) {
      this.appendBundleForm();
    }

    this.$createBundleFormDefaultValues = {};
    this.storeBundleFormDefaultValues();

    this.loadAndDisplayExistingBundleList();

    this.configureAddBundleFormBehavior();

    this.configureEditBundleFormBehavior();

    this.configureDeleteBundleButtonsBehavior();
  }

  /**
   * Append bundle form to product sheet if not already added
   */
  appendBundleForm() {
    if (typeof (pm_advancedpack.bundle.form) != 'undefined') {
      $(pm_advancedpack.bundle.form).insertAfter('#product_description-tab div.form-group:last');
    }
  }

  /**
   * Converts a price string into a number
   * @param {String} price
   * @return {Number}
   */
  normalizePrice(price) {
    return Tools.parseFloatFromString(price, true);
  }

  /**
   * @private
   */
  loadAndDisplayExistingBundleList() {
    const listContainer = $('#js-bundle-list');

    const url = listContainer.data('listingUrl');

    $.ajax({
      type: 'GET',
      url,
      data: {
        ajax: 1,
        submitAjaxMethod: 1,
        productId: this.getProductId()
      },
      dataType: 'json',
    })
      .done((bundles) => {
        const tbody = listContainer.find('tbody');
        tbody.find('tr').remove();

        if (bundles.length > 0) {
          listContainer.removeClass('hide');
        } else {
          listContainer.addClass('hide');
        }

        const bundlesList = this.renderBundlesListingAsHtml(bundles);

        tbody.append(bundlesList);
      });
  }

  /**
   * @param array bundles
   *
   * @returns string
   *
   * @private
   */
  renderBundlesListingAsHtml(bundles) {
    let bundlesList = '';

    const self = this;

    $.each(bundles, (index, bundle) => {
      const deleteUrl = $('#js-bundle-list')
        .attr('data-action-delete')
        .replace('productId=0', `productId=${bundle.id_pack}`);
      const row = self.renderBundleRow(bundle, deleteUrl);

      bundlesList += row;
    });

    return bundlesList;
  }

  /**
   * @param Object bundle
   * @param string deleteUrl
   *
   * @returns string
   *
   * @private
   */
  renderBundleRow(bundle, deleteUrl) {
    const bundleId = bundle.id_pack;

    /* eslint-disable max-len */
    const canDelete = bundle.can_delete
      ? `<a href="${deleteUrl}" class="js-delete delete btn tooltip-link delete pl-0 pr-0"><i class="material-icons">delete</i></a>`
      : '';
    const canEdit = bundle.can_edit
      ? `<a href="#" data-bundle-id="${bundleId}" class="js-edit edit btn tooltip-link delete pl-0 pr-0"><i class="material-icons">edit</i></a>`
      : '';
    const img = bundle.default_image.link
      ? `<img src="${bundle.default_image.link}" /`
      : '';
    const row = `<tr> \
     <td>${img}</td> \
     <td>${bundle.quantity}</td> \
     <td>${bundle.combination_name}</td> \
     <td>${bundle.datas.packaging[ap5_currentLanguageId]}</td> \
     <td>${bundle.datas.name[ap5_currentLanguageId]}</td> \
     <td>${bundle.badge_name}</td> \
     <td>${bundle.productPriceTaxesExcluded}</td> \
     <td>${bundle.reductionToDisplay}</td> \
     <td>${bundle.bundlePriceTaxesExcluded}</td> \
     <td><a href="${bundle.publicUrl}" target="_blank"><i class="material-icons">visibility</i></a></td> \
     <td>${canEdit}</td> \
     <td>${canDelete}</td></tr>`;
    /* eslint-enable max-len */

    return row;
  }

  /**
   * @private
   */
  configureAddBundleFormBehavior() {
    this.updateBundlePrice(1);
    var bundleBadges = $("#pack-bundle-badges");
    bundleBadges.select2();
    var bundleCombinations = $("#pack-bundle-combinations");
    bundleCombinations.select2();

    $('#advancedpack-bundle-form .product-image').click((event) => {
      $('#advancedpack-bundle-form .product-image.img-highlight').toggleClass('img-highlight', false);
      $('#advancedpack-bundle-form .product-image input:checked').prop('checked', false);

      const input = $(event.currentTarget).find('input');
      const isChecked = input.prop('checked');
      input.prop('checked', !isChecked);
      $(event.currentTarget).toggleClass('img-highlight', !isChecked);
    });

    $('#advancedpack-bundle-form .js-depending').on(
      'change',
      () => this.checkDependingInputs(),
    );

    $('#advancedpack-bundle-form #bundle-reduction-type').on(
      'change',
      (event) => this.adaptReductionInputs(event),
    );

    $('#advancedpack-bundle-form .js-cancel').click(() => {
      this.resetBundleFormDefaultValues();
      $('#advancedpack-bundle-form').collapse('hide');
      $('#js-open-create-bundle-form').removeClass('hide');
    });

    $('#advancedpack-bundle-form .js-save').on(
      'click',
      () => this.submitCreateBundleForm(),
    );

    $('#bundle-input-quantity, #pack-bundle-combinations, #form_step1_price_shortcut').on(
      'change',
      (e) => this.updateBundlePrice(parseInt($('#bundle-input-quantity').val())),
    );
  }

  checkDependingInputs() {
    const price = $('#bundle-price').val();
    const reduction = $('#bundle-reduction-amount').val();

    if ((price === '' || !price) && (reduction === '')) {
      $('#bundle-price').prop('disabled', false);
      $('#bundle-reduction-amount').prop('disabled', false);
    } else if (price !== '' && !price) {
      $('#bundle-reduction-amount').prop('disabled', true);
    } else if (reduction !== '') {
      $('#bundle-price').prop('disabled', true);
    }
  }

  adaptReductionInputs(event) {
    const type = $(event.currentTarget).val();

    if (type == 'amount') {
      $('#bundle-reduction-amount').addClass('price');
      $('#bundle-reduction-taxes').removeClass('hide');
    } else {
      $('#bundle-reduction-amount').removeClass('price');
      $('#bundle-reduction-taxes').addClass('hide');
    }
  }

  updateBundlePrice(quantity) {
    let basePrice = parseFloat($(pm_advancedpack.bundle.selectors.price).val().replace(/,/g, '.'));
    if ($('#pack-bundle-combinations option:selected').data('priceImpact')) {
      basePrice += parseFloat($('#pack-bundle-combinations option:selected').data('priceImpact'));
    }
    const price = parseFloat(parseInt(quantity) * basePrice).toFixed(6);

    formatCurrencyCldr(price, (result) => {
      $('#bundle-base-price').text(result);
    }, 6);
  }

  /**
   * @private
   */
  setEditBundleDatas(datas) {
    // Quantity
    $('#bundle-input-quantity').val(parseInt(datas.quantity));
    // Package Name
    for (const property in datas.packaging) {
      $('input[name="bundle[packaging][' + property + ']"').val(datas.packaging[property]);
    }
    // Bundle name
    for (const property in datas.name) {
      $('input[name="bundle[name][' + property + ']"').val(datas.name[property]);
    }
    // Badge
    $('#pack-bundle-badges option:selected').attr('selected', false);
    $('#pack-bundle-badges option[value="' + parseInt(datas.badge) + '"]').prop('selected', 'selected');
    $('#pack-bundle-badges').change();
    // Combination
    $('#pack-bundle-combinations option:selected').attr('selected', false);
    $('#pack-bundle-combinations option[value="' + parseInt(datas.combination) + '"]').prop('selected', 'selected');
    $('#pack-bundle-combinations').change();
    // Image
    $('#bundle-input-image-' + parseInt(datas.image)).parent('.product-image').click();
    // Price | Reduction
    if (datas.reduction.amount) {
      // Reduction amount
      $('#bundle-reduction-amount').val(parseFloat(datas.reduction.amount));
      // Reduction type
      $('#bundle-reduction-type option:selected').prop('selected', false);
      $('#bundle-reduction-type option[value="' + datas.reduction.type + '"]').prop('selected', 'selected');
      $('#bundle-reduction-type').change();
      // Reduction taxes
      $('#bundle-reduction-taxes option:selected').prop('selected', false);
      $('#bundle-reduction-taxes option[value="' + parseInt(datas.reduction.taxes) + '"]').prop('selected', 'selected');
      $('#bundle-reduction-taxes').change();
      // Price
      $('#bundle-price').val('');
    } else {
      // Reduction amount
      $('#bundle-reduction-amount').val('');
      // Price
      $('#bundle-price').val(parseFloat(datas.price));
    }

    $('#bundle-price').prop('disabled', false);
    $('#bundle-reduction-amount').prop('disabled', false);
    this.checkDependingInputs();
  }

  /**
   * @private
   */
  configureEditBundleFormBehavior() {
    $(document).on('click', '#js-bundle-list .js-edit', (event) => {
      event.preventDefault();

      const bundleId = $(event.currentTarget).data('bundleId');

      this.openEditBundleForm(bundleId);
    });
  }

  /**
   * @private
   */
  configureDeleteBundleButtonsBehavior() {
    $(document).on('click', '#js-bundle-list .js-delete', (event) => {
      event.preventDefault();
      this.deleteBundle(event.currentTarget);
    });
  }

  /**
   * @private
   */
  submitCreateBundleForm() {
    const url = $('#advancedpack-bundle-form').attr('data-action');
    this.updateMissingTranslatedNames();
    let data = $('#advancedpack-bundle-form input, #advancedpack-bundle-form select').serialize();
    data += '&ajax=1&submitAjaxMethod=1&productId=' + this.getProductId();

    $('#advancedpack-bundle-form .js-save').attr('disabled', 'disabled');
    $('#advancedpack-bundle-form .js-error').addClass('hide');

    $.ajax({
      type: 'POST',
      url,
      data,
    }).done(() => {
      window.showSuccessMessage(pm_advancedpack.bundle.translations['Form update success']);
      this.resetBundleFormDefaultValues();
      $('#advancedpack-bundle-form').collapse('hide');
      this.loadAndDisplayExistingBundleList();
    }).fail((errors) => {
      window.showErrorMessage(pm_advancedpack.bundle.translations['Form update errors']);
      const errorsMessages = Object.values(errors.responseJSON);

      var idx = 0;
      for (var error in errors.responseJSON) {
        $('#advancedpack-bundle-form').find(`.js-error[data-rel='${error}']`).removeClass('hide').text(errorsMessages[idx]);
        idx++;
      }
    }).complete(() => {
      $('#advancedpack-bundle-form .js-save').removeAttr('disabled');
    });
  }

  /**
   *
   * @private
   */
  updateMissingTranslatedNames() {
    const namesDiv = $('#bundle_packaging_names');
    let defaultLanguageValue = null;
    $("input[id^='bundle-input-packaging-']", namesDiv).each(function (index) {
      const value = $(this).val();

      // The first language is ALWAYS the employee language
      if (index === 0) {
        defaultLanguageValue = value;
      } else if (value.length === 0) {
        $(this).val(defaultLanguageValue);
      }
    });
  }

  /**
   * @param string clickedLink selector
   *
   * @private
   */
  deleteBundle(clickedLink) {
    window.modalConfirmation.create(
      pm_advancedpack.bundle.translations['Are you sure you want to delete this item?'],
      null,
      {
        onContinue: () => {
          const url = $(clickedLink).attr('href');
          $(clickedLink).attr('disabled', 'disabled');

          $.ajax({
            type: 'GET',
            url,
            data: {
              ajax: 1,
              submitAjaxMethod: 1
            },
          }).done((response) => {
            this.loadAndDisplayExistingBundleList();
            window.showSuccessMessage(response);
            $(clickedLink).removeAttr('disabled');
          }).fail((errors) => {
            window.showErrorMessage(errors.responseJSON);
            $(clickedLink).removeAttr('disabled');
          });
        },
      },
    ).show();
  }

  /**
   * Store 'add bundle' form values
   * for future usage
   *
   * @private
   */
  storeBundleFormDefaultValues() {
    const storage = this.$createBundleFormDefaultValues;

    $('#advancedpack-bundle-form').find('select,input').each((index, value) => {
      storage[$(value).attr('id')] = $(value).val();
    });

    this.$createBundleFormDefaultValues = storage;
  }

  /**
   * Reset 'add bundle' form values
   * using previously stored default values
   *
   * @private
   */
  resetBundleFormDefaultValues() {
    const previouslyStoredValues = this.$createBundleFormDefaultValues;

    $('#advancedpack-bundle-form').find('input').each((index, value) => {
      $(value).val(previouslyStoredValues[$(value).attr('id')]);
    });

    $('#advancedpack-bundle-form').find('select').each((index, value) => {
      $(value).val(previouslyStoredValues[$(value).attr('id')]).change();
    });

    $('#advancedpack-bundle-form').find('input:checkbox').each((index, value) => {
      $(value).prop('checked', false);
    });

    $('#advancedpack-bundle-form .product-image.img-highlight').toggleClass('img-highlight', false);

    $('#bundle-price').prop('disabled', false);
    $('#bundle-reduction-amount').prop('disabled', false);

    $('#js-open-create-bundle-form').removeClass('hide');
  }

  /**
   * Open 'edit bundle' form
   *
   * @param integer bundleId
   *
   * @private
   */
  openEditBundleForm(bundleId) {
    const url = $('#js-bundle-list')
      .data('actionEdit')
      .replace('bundleId=0', `bundleId=${bundleId}`);

    $.ajax({
      type: 'GET',
      url,
      data: {
        ajax: 1,
        submitAjaxMethod: 1
      },
      dataType: 'json',
    })
      .done((response) => {
        this.resetBundleFormDefaultValues();
        $('#js-open-create-bundle-form').addClass('hide');
        $('#advancedpack-bundle-form').data('bundleId', bundleId);
        $('#bundle-id').val(bundleId);
        this.setEditBundleDatas(response);
        $('#advancedpack-bundle-form').collapse('show');
        document.querySelector('#bundles').scrollIntoView({ behavior: 'smooth' });
      })
      .fail((errors) => {
        window.showErrorMessage(errors.responseJSON);
      });
  }

  /**
   * Refresh bundle form
   *
   * @private
   */
  updateEditBundleForm() {
    const url = $('#advancedpack-bundle-form')
      .data('actionRefresh')
      .replace('productId=0', `productId=${this.getProductId()}`);

    $.ajax({
      type: 'GET',
      url,
      data: {
        ajax: 1,
        submitAjaxMethod: 1
      },
      dataType: 'json',
    })
    .done((response) => {
      $('#bundles-container').replaceWith(response.form);
      new BundleFormHandler(true);
    })
    .fail((errors) => {
      window.showErrorMessage(errors.responseJSON);
    });
  }

  /**
   * Get product ID for current Catalog Product page
   *
   * @returns integer
   *
   * @private
   */
  getProductId() {
    if ($(pm_advancedpack.bundle.selectors.id_product).data('productId')) {
      return $(pm_advancedpack.bundle.selectors.id_product).data('productId');
    }
    return $(pm_advancedpack.bundle.selectors.id_product).val();
  }
}

export default BundleFormHandler;
