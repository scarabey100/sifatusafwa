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


}());
