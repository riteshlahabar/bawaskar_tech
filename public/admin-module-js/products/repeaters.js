(function () {
    'use strict';

    var ROW_SELECTOR = '[data-product-variant-row], [data-product-media-row], [data-product-additional-info-row]';

function initProductRepeaters() {
        document.querySelectorAll('[data-product-variants-repeater], [data-product-media-repeater], [data-product-additional-info-repeater]').forEach(function (repeater) {
            var rowsHost = repeater.querySelector('[data-repeater-rows]');
            var template = repeater.querySelector('[data-repeater-template]');
            var addButton = repeater.querySelector('[data-add-repeater-row]');
            var nextIndex = rowsHost ? rowsHost.children.length : 0;

            function updateVariantTotals(row) {
                if (!row || !row.matches('[data-product-variant-row]')) return;
                var units = parseFloat((row.querySelector('[data-units-per-case]') || {}).value || 0);
                var mrp = parseFloat((row.querySelector('[data-variant-mrp]') || {}).value || 0);
                var dealer = parseFloat((row.querySelector('[data-variant-dealer-price]') || {}).value || 0);
                var mrpTarget = row.querySelector('[data-case-mrp]');
                var dealerTarget = row.querySelector('[data-case-dealer]');
                if (mrpTarget) mrpTarget.textContent = (units * mrp).toFixed(2);
                if (dealerTarget) dealerTarget.textContent = (units * dealer).toFixed(2);
            }

            function syncMediaFields(row) {
                if (!row || !row.matches('[data-product-media-row]')) return;
                var source = row.querySelector('[data-media-source]');
                var uploadField = row.querySelector('[data-media-upload-field]');
                var youtubeField = row.querySelector('[data-media-youtube-field]');
                var isYoutube = source && source.value === 'youtube';
                if (uploadField) uploadField.style.display = isYoutube ? 'none' : '';
                if (youtubeField) youtubeField.style.display = isYoutube ? '' : 'none';
            }

            function initialiseRow(row) {
                updateVariantTotals(row);
                syncMediaFields(row);
            }

            if (addButton && template && rowsHost) {
                addButton.addEventListener('click', function () {
                    var html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                    rowsHost.insertAdjacentHTML('beforeend', html);
                    initialiseRow(rowsHost.lastElementChild);
                });
            }

            repeater.addEventListener('click', function (event) {
                var removeButton = event.target.closest('[data-remove-repeater-row]');
                if (removeButton) {
                    var row = removeButton.closest(ROW_SELECTOR);
                    if (row) row.remove();
                }
            });

            repeater.addEventListener('change', function (event) {
                var row = event.target.closest(ROW_SELECTOR);
                if (event.target.matches('[data-main-display-pack]') && event.target.checked) {
                    repeater.querySelectorAll('[data-main-display-pack]').forEach(function (checkbox) {
                        if (checkbox !== event.target) checkbox.checked = false;
                    });
                }
                initialiseRow(row);
            });

            repeater.addEventListener('input', function (event) {
                updateVariantTotals(event.target.closest('[data-product-variant-row]'));
            });

            rowsHost.querySelectorAll(ROW_SELECTOR).forEach(initialiseRow);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductRepeaters);
    } else {
        initProductRepeaters();
    }
})();
