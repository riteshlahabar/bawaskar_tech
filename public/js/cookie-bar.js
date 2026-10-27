/*
 * Cookie bar consent, shared by every storefront page that renders
 * .cookie-bar-box (homepage, product detail, search, shop).
 *
 * The Fastkart template's own handler only adds the .hide class, so the bar
 * came back on every page load. The acceptance is remembered here instead:
 * once OK is clicked the bar is never rendered again for that visitor.
 *
 * Storage is best-effort - private browsing or blocked site data simply means
 * the bar shows again, which is the template's original behaviour.
 */
(function () {
    'use strict';

    var STORAGE_KEY = 'cookie_consent';
    var ACCEPTED = 'accepted';

    function hasAccepted() {
        try {
            return window.localStorage.getItem(STORAGE_KEY) === ACCEPTED;
        } catch (error) {
            return false;
        }
    }

    function remember() {
        try {
            window.localStorage.setItem(STORAGE_KEY, ACCEPTED);
        } catch (error) {
            // Storage unavailable - nothing to remember.
        }
    }

    function init() {
        var bar = document.querySelector('.cookie-bar-box');

        if (!bar) {
            return false;
        }

        if (hasAccepted()) {
            bar.parentNode.removeChild(bar);

            return true;
        }

        var okButton = bar.querySelector('.ok-button');

        if (okButton) {
            okButton.addEventListener('click', function () {
                bar.classList.add('hide');
                remember();
            });
        }

        return true;
    }

    // The script sits after the markup, so this normally runs before the first
    // paint; the listener is the fallback for any page that loads it earlier.
    if (!init()) {
        document.addEventListener('DOMContentLoaded', init);
    }
})();
