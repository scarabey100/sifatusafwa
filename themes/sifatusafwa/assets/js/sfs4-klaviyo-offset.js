// SFS-4: detect any third-party fixed-bottom promotional banner (the Klaviyo
// "Get 10% Off" signup form being the case in production, but the detection
// is intentionally selector-agnostic in case Klaviyo or a successor module
// changes its DOM signature) and tag it with [data-sfs4-banner] on <body>.
//
// The companion stylesheet uses --klaviyo-form-h (live banner height) to lift
// the cart's sticky bottom panel above it on mobile, and uses [data-sfs4-banner]
// to suppress the banner inside transient overlays (mini-cart popup, quick-view)
// and on the multi-step checkout, where it would block primary CTAs.
//
// See https://youtrack.belvgdev.com/issue/SFS-4
(function () {
    'use strict';

    // Theme-owned fixed/sticky bottom elements that must NEVER be treated as
    // a third-party banner. Any element matching one of these — or having one
    // of these among its ancestors — is excluded from detection.
    var OURS = [
        '.cart-grid-right',
        '.popup',
        '.notification',
        '#cookiesplus-modal-container',
        '[id^="cookiesplus"]',
        '[class^="cookiesplus"]',
        '#header',
        '#footer',
        'header',
        'footer',
        '#launcher',
        'iframe[id^="launcher"]'
    ].join(',');

    var observed = (typeof WeakSet === 'function') ? new WeakSet() : null;
    var resizeObs = (typeof ResizeObserver === 'function')
        ? new ResizeObserver(scheduleUpdate)
        : null;

    function isThirdPartyBottomBanner(el) {
        if (el === document.body || el === document.documentElement) return false;
        if (el.matches && el.matches(OURS)) return false;
        if (el.closest && el.closest(OURS)) return false;
        var cs = window.getComputedStyle(el);
        // position:fixed only — sticky elements anchored to the bottom of a
        // scroll container can match by accident at certain scroll positions.
        if (cs.position !== 'fixed') return false;

        // If we already tagged this element as a banner, keep it tagged
        // regardless of current display — the suppression CSS may have set
        // display:none on it inside a popup or on /order. Untagging here
        // would unhide it (CSS rule no longer matches), and the next pass
        // would re-tag it, causing visible oscillation. We accept either
        // h=0 (hidden by our CSS or by the form's own dismiss) or h>=30
        // (real banner); the in-between range would leak a phantom gap
        // through --klaviyo-form-h, so untag in that case.
        if (el.hasAttribute('data-sfs4-banner')) {
            var h0 = el.getBoundingClientRect().height;
            return h0 === 0 || h0 >= 30;
        }

        if (cs.display === 'none' || cs.visibility === 'hidden' || parseFloat(cs.opacity) === 0) return false;
        var rect = el.getBoundingClientRect();
        if (rect.height < 30 || rect.height > 400) return false;
        if (rect.width < window.innerWidth * 0.5) return false;
        // Anchored near the bottom of the viewport.
        return rect.bottom > window.innerHeight - 60;
    }

    function findBanners() {
        var banners = [];
        var seen = (typeof Set === 'function') ? new Set() : null;
        var all = document.body.getElementsByTagName('*');
        for (var i = 0; i < all.length; i++) {
            var el = all[i];
            if (seen && seen.has(el)) continue;
            if (!isThirdPartyBottomBanner(el)) continue;
            if (seen) seen.add(el);
            banners.push(el);
        }
        return banners;
    }

    function update() {
        var banners = findBanners();
        var max = 0;
        for (var i = 0; i < banners.length; i++) {
            var el = banners[i];
            if (resizeObs && observed && !observed.has(el)) {
                observed.add(el);
                resizeObs.observe(el);
            }
            var h = el.getBoundingClientRect().height;
            if (h > max) max = h;
        }

        // Re-tag only when the set actually changes — re-tagging on every rAF
        // during a slide-in animation otherwise causes the cart panel position
        // to oscillate frame-to-frame.
        var currentlyTagged = document.querySelectorAll('[data-sfs4-banner]');
        var changed = currentlyTagged.length !== banners.length;
        if (!changed) {
            for (var j = 0; j < banners.length; j++) {
                if (!banners[j].hasAttribute('data-sfs4-banner')) { changed = true; break; }
            }
        }
        if (changed) {
            for (var k = 0; k < currentlyTagged.length; k++) {
                currentlyTagged[k].removeAttribute('data-sfs4-banner');
            }
            for (var m = 0; m < banners.length; m++) {
                banners[m].setAttribute('data-sfs4-banner', '');
            }
        }

        var newH = Math.ceil(max) + 'px';
        if (document.body.style.getPropertyValue('--klaviyo-form-h') !== newH) {
            document.body.style.setProperty('--klaviyo-form-h', newH);
        }
    }

    var scheduled = false;
    function scheduleUpdate() {
        if (scheduled) return;
        scheduled = true;
        var run = function () {
            scheduled = false;
            update();
        };
        if (typeof requestAnimationFrame === 'function') {
            requestAnimationFrame(run);
        } else {
            setTimeout(run, 16);
        }
    }

    function init() {
        update();

        window.addEventListener('resize', scheduleUpdate, { passive: true });
        window.addEventListener('orientationchange', scheduleUpdate, { passive: true });

        // Klaviyo (and similar) often inject their banner with display:none
        // first, then reveal it later via a class toggle — that's an attribute
        // mutation, not childList, so we have to watch attributes too.
        // attributeFilter limits the noise to class/style, which is what
        // controls banner visibility in practice. scheduleUpdate is rAF-
        // debounced, so a burst of attribute mutations collapses to one run.
        var bodyObs = new MutationObserver(scheduleUpdate);
        bodyObs.observe(document.body, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['class', 'style']
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
