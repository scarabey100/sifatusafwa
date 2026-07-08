document.addEventListener('DOMContentLoaded', function () {
    var packIdProductAttribute = null;
    var packContentAttributesCache = null;
    var selectProduct = document.querySelector(window.pm_advancedpack.selectors.selectProduct);
    var combinationSelector = document.querySelector(window.pm_advancedpack.selectors.combinationSelector);
    var productTable = document.querySelector(window.pm_advancedpack.selectors.productTable);
    var selectProductContainer = document.querySelector(window.pm_advancedpack.selectors.selectProductContainer);
    var selectCombinationContainer = document.querySelector(window.pm_advancedpack.selectors.selectCombinationContainer);
    var stockInformationContainer = document.querySelector(window.pm_advancedpack.selectors.stockInformationContainer);
    var packConfigurationContainer = document.querySelector('#ap5-pack-configuration');
    var addPackToCartButton = document.querySelector(window.pm_advancedpack.selectors.addPackToCartButton);
    var addPackToCartButtonOriginalLabel = addPackToCartButton.innerHTML;
    var packConfiguration = document.createElement('div');
    packConfiguration.setAttribute('id', 'ap5-pack-configuration');
    packConfiguration.setAttribute('class', 'row mt-3');

    var getCurrentCartId = function getCurrentCartId() {
        return parseInt($(window.pm_advancedpack.selectors.getCurrentCartId).data('cartId'));
    };

    var getCurrentProductId = function getCurrentProductId() {
        return parseInt(selectProduct.value);
    };

    var productIsAPack = function productIsAPack(idProduct) {
        return window.pm_advancedpack.allPackIds.includes(idProduct);
    };

    var updatePackFormattedAttributes = function updatePackFormattedAttributes(packContentAttributes) {
        document.querySelectorAll(window.pm_advancedpack.selectors.productAttributesLabel).forEach(function (productAttributesItem) {
            for (packUniqueAttribute in packContentAttributes) {
                var packFormattedAttributes = packContentAttributes[packUniqueAttribute];

                if (productAttributesItem.innerHTML.indexOf(packUniqueAttribute) != -1) {
                    productAttributesItem.innerHTML = productAttributesItem.innerHTML.replace(packUniqueAttribute, packFormattedAttributes.cart);
                    return;
                }
            }
        });
    };

    var retrievePackSettings = function retrievePackSettings(idPack) {
        // Loading message
        packConfiguration.innerHTML = '<div class="col-3"></div><div class="col-4"><div class="alert alert-info"><div class="alert-text">' + window.pm_advancedpack.genericLoadingMessage + '</div></div></div>';
        packIdProductAttribute = null;
        addPackToCartButton.setAttribute('disabled', '');
        var httpRequest = new XMLHttpRequest();
        var response = null;

        httpRequest.onreadystatechange = function handleGetPackInfosXhr() {
            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                if (httpRequest.status === 200) {
                    response = JSON.parse(httpRequest.responseText);
                    if (response.result) {
                        packConfiguration.innerHTML = response.packConfiguration;
                        addPackToCartButton.removeAttribute('disabled');
                        return;
                    }
                }
                // Error
                packConfiguration.innerHTML = '<div class="col-3"></div><div class="col-4"><div class="alert alert-danger"><div class="alert-text">' + window.pm_advancedpack.genericErrorMessage + (response && response.error ? ' - ' + response.error : '') + '</div></div></div>';
            }
        };

        httpRequest.open('POST', window.pm_advancedpack.adminOrdersPackInfosUrl);
        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpRequest.send('idPack=' + encodeURIComponent(idPack));
    };

    var retrievePackFormattedAttributes = function retrievePackFormattedAttributes() {
        var httpRequest = new XMLHttpRequest();

        httpRequest.onreadystatechange = function handleGetPackFormattedAttributesXhr() {
            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                if (httpRequest.status === 200) {
                    var response = JSON.parse(httpRequest.responseText);
                    if (response.result) {
                        updatePackFormattedAttributes(response.packContentAttributes);
                        // Save to cache
                        packContentAttributesCache = response.packContentAttributes;
                        return;
                    }
                }
                // Error
                alert(window.pm_advancedpack.genericErrorMessage);
            }
        };

        httpRequest.open('POST', window.pm_advancedpack.adminOrdersGetPackFormattedAttributes);
        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpRequest.send('idCart=' + encodeURIComponent(getCurrentCartId()));
    };

    var disablePackCustomPriceInput = function disablePackCustomPriceInput() {
        document.querySelectorAll(window.pm_advancedpack.selectors.deleteProductButton).forEach(function (productDeleteButtonItem) {
            var productId = null;

            productId = $(productDeleteButtonItem).data('productId');

            if (!productIsAPack(productId)) {
                return;
            }

            // Disable price input
            productDeleteButtonItem.closest('tr').querySelector(window.pm_advancedpack.selectors.customProductPriceInput).setAttribute('disabled', '');
        });
    };

    var displayPackOrderSettings = function displayPackOrderSettings(idPack) {
        // Hide vanilla combinations selector
        if (!packConfigurationContainer) {
            selectProductContainer.parentNode.insertBefore(packConfiguration, selectProductContainer.nextSibling);
        }

        packConfiguration.classList.remove('ap5-admin-hide');
        selectCombinationContainer.classList.add('ap5-admin-hide');
        stockInformationContainer.parentNode.classList.add('ap5-admin-hide');
        addPackToCartButton.innerHTML = window.pm_advancedpack.addPackToCartButtonLabel;

        // Call to retrieve pack settings and insert to packConfiguration
        retrievePackSettings(idPack);
    };

    var hidePackOrderSettings = function hidePackOrderSettings(idPack) {
        // Hide vanilla combinations selector
        packConfiguration.classList.add('ap5-admin-hide');
        selectCombinationContainer.classList.remove('ap5-admin-hide');
        selectCombinationContainer.removeAttribute('style');
        stockInformationContainer.parentNode.classList.remove('ap5-admin-hide');
        addPackToCartButton.innerHTML = addPackToCartButtonOriginalLabel;
    };

    var checkProductValue = function checkProductValue() {
        if (productIsAPack(getCurrentProductId())) {
            // Display pack settings
            displayPackOrderSettings(getCurrentProductId());
        } else {
            // Hide pack block
            hidePackOrderSettings(getCurrentProductId());
        }
    };

    var addPackToCart = function addPackToCart(event) {
        if (!productIsAPack(getCurrentProductId())) {
            return;
        }

        if (packIdProductAttribute !== null) {
            // Follow the classic process and reset the packIdProductAttribute
            packIdProductAttribute = null;
            return;
        }

        event.stopPropagation();

        // Build JSON payload
        var packCombinations = {};
        document.querySelectorAll('select.ap5-product-combination').forEach(function (selectItem) {
            var idProductPack = parseInt(selectItem.getAttribute('data-id-product-pack'));
            packCombinations[idProductPack] = parseInt(selectItem.value);
        });
        var httpRequest = new XMLHttpRequest();

        httpRequest.onreadystatechange = function handleAddPackToCartXhr() {
            if (httpRequest.readyState === XMLHttpRequest.DONE) {
                if (httpRequest.status === 200) {
                    var response = JSON.parse(httpRequest.responseText);
                    if (response.result) {
                        // Create fake combination option, add it to select
                        combinationSelector.querySelectorAll('option').forEach(function (optionItem) {
                            combinationSelector.removeChild(optionItem);
                        });
                        var packFakeIpaOption = document.createElement('option');
                        packFakeIpaOption.setAttribute('value', response.idProductAttribute);
                        packFakeIpaOption.innerHTML = 'Pack';
                        combinationSelector.appendChild(packFakeIpaOption);

                        // Update pack unique attributes, display pack content
                        updatePackFormattedAttributes(response.packContentAttributes);
                        retrievePackFormattedAttributes();

                        // Redispatch original event
                        event.fromAddPackToCart = true;

                        addPackToCartButton.dispatchEvent(event);
                        return;
                    }
                }

                // Error
                alert(window.pm_advancedpack.genericErrorMessage);
            }
        };

        httpRequest.open('POST', window.pm_advancedpack.adminOrdersAddToCartUrl);
        httpRequest.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
        httpRequest.send('json=' + encodeURIComponent(JSON.stringify({
            idPack: getCurrentProductId(),
            idCart: getCurrentCartId(),
            quantity: 1,
            combinations: packCombinations
        })));
    };

    // Observe changes on current selected product
    var productObserver = new MutationObserver(function () {
        checkProductValue();
    });

    // Start observing the target node for configured mutations
    productObserver.observe(selectProduct, {
        childList: true,
        subtree: true
    });

    // Observe changes on cart content
    var cartContentObserver = new MutationObserver(function () {
        // Disable price input for pack
        disablePackCustomPriceInput();

        // Update pack unique attributes, display pack content
        if (packContentAttributesCache !== null) {
            updatePackFormattedAttributes(packContentAttributesCache);
        }
        retrievePackFormattedAttributes();
    });
    cartContentObserver.observe(productTable, {
        childList: true,
        subtree: true
    });

    selectProduct.addEventListener('change', function () {
        checkProductValue();
    });

    addPackToCartButton.addEventListener('click', function (event) {
        if (event.fromAddPackToCart) {
            return;
        }

        addPackToCart(event);
    });
});
