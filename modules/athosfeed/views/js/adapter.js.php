(function (window, document) {
    'use strict';

    function renderZone(element) {
        var zoneId = element.getAttribute('data-athos-zone');
        if (!zoneId || !window.AthosSnap || typeof window.AthosSnap.renderZone !== 'function') {
            return;
        }
        window.AthosSnap.renderZone(element, zoneId, window.athosFeedConfig || {});
    }

    function initialize() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-athos-zone]'), renderZone);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initialize);
    } else {
        initialize();
    }
}(window, document));
