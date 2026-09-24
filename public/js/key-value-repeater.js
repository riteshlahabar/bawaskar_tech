/**
 * Add/remove rows for the shared admin "key value repeater" field
 * (resources/views/admin/shared/fields/key-value-repeater.blade.php).
 *
 * Deliberately standalone rather than folded into
 * admin-module-js/products/repeaters.js: that file also drives the product
 * variant and media repeaters and is only loaded on the product form.
 */
(function () {
    'use strict';

    function initKeyValueRepeaters() {
        document.querySelectorAll('[data-key-value-repeater]').forEach(function (repeater) {
            if (repeater.dataset.keyValueReady === '1') return;
            repeater.dataset.keyValueReady = '1';

            var rowsHost = repeater.querySelector('[data-key-value-rows]');
            var template = repeater.querySelector('[data-key-value-template]');
            var addButton = repeater.querySelector('[data-add-key-value-row]');

            if (!rowsHost || !template || !addButton) return;

            // Carries on from the rows already rendered, so a new row never
            // reuses an index and overwrites an existing one.
            var nextIndex = rowsHost.children.length;

            addButton.addEventListener('click', function () {
                var html = template.innerHTML.replaceAll('__INDEX__', String(nextIndex++));
                rowsHost.insertAdjacentHTML('beforeend', html);
            });

            repeater.addEventListener('click', function (event) {
                var removeButton = event.target.closest('[data-remove-key-value-row]');
                if (!removeButton) return;

                var row = removeButton.closest('[data-key-value-row]');
                if (row) row.remove();
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initKeyValueRepeaters);
    } else {
        initKeyValueRepeaters();
    }
})();
