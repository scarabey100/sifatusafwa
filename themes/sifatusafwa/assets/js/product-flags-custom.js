(function () {
    'use strict';

    var frameId = null;
    var magicObserver = null;
    var prestashopBound = false;

    function resetProductFlags(flags) {
        if (!flags) {
            return;
        }

        flags.classList.remove('eg-image-bound');
        flags.style.removeProperty('--eg-image-left');
        flags.style.removeProperty('--eg-image-top');
        flags.style.removeProperty('--eg-image-width');
        flags.style.removeProperty('--eg-image-height');
    }

    function alignProductFlags() {
        var pageContent = document.querySelector('#product .product__media .page-content');
        var flags = pageContent && pageContent.querySelector(':scope > .product-flags');
        var image = pageContent && pageContent.querySelector('.magic-slide.mt-active a.MagicZoom');

        if (!pageContent || !flags || !image) {
            resetProductFlags(flags);
            return;
        }

        var containerRect = pageContent.getBoundingClientRect();
        var imageRect = image.getBoundingClientRect();

        // A failed/not-yet-loaded Magic Zoom image can report a 2x2px anchor.
        // Keep the original container-based positioning until a real image exists.
        if (imageRect.width < 50 || imageRect.height < 50) {
            resetProductFlags(flags);
            return;
        }

        flags.classList.add('eg-image-bound');
        flags.style.setProperty('--eg-image-left', (imageRect.left - containerRect.left) + 'px');
        flags.style.setProperty('--eg-image-top', (imageRect.top - containerRect.top) + 'px');
        flags.style.setProperty('--eg-image-width', imageRect.width + 'px');
        flags.style.setProperty('--eg-image-height', imageRect.height + 'px');
    }

    function scheduleAlignment() {
        if (frameId !== null) {
            window.cancelAnimationFrame(frameId);
        }

        frameId = window.requestAnimationFrame(function () {
            frameId = null;
            alignProductFlags();
        });
    }

    function observeMagicZoom() {
        var pageContent = document.querySelector('#product .product__media .page-content');
        if (!pageContent || typeof MutationObserver === 'undefined') {
            return;
        }

        if (magicObserver) {
            magicObserver.disconnect();
        }

        magicObserver = new MutationObserver(function (mutations) {
            var shouldAlign = mutations.some(function (mutation) {
                return mutation.type === 'childList'
                    || (mutation.target.classList && mutation.target.classList.contains('magic-slide'));
            });

            if (shouldAlign) {
                scheduleAlignment();
            }
        });

        magicObserver.observe(pageContent, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class']
        });
    }

    function bindPrestaShopUpdate() {
        if (prestashopBound || !window.prestashop || typeof window.prestashop.on !== 'function') {
            return;
        }

        prestashopBound = true;
        window.prestashop.on('updatedProduct', function () {
            window.setTimeout(function () {
                observeMagicZoom();
                scheduleAlignment();
            }, 0);
            window.setTimeout(scheduleAlignment, 250);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        scheduleAlignment();
        observeMagicZoom();
        bindPrestaShopUpdate();

        // Magic Zoom can finish measuring after DOMContentLoaded.
        window.setTimeout(bindPrestaShopUpdate, 250);
        window.setTimeout(scheduleAlignment, 250);
        window.setTimeout(scheduleAlignment, 1000);
    });

    window.addEventListener('load', function () {
        bindPrestaShopUpdate();
        scheduleAlignment();
    });
}());
