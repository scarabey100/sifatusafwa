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

    
}());
