(function () {
    'use strict';

    var form = document.querySelector('[data-homepage-content-form]');

    if (!form) {
        return;
    }

    var rowsWrapper = form.querySelector('[data-content-rows]');
    var template = form.querySelector('[data-content-row-template]');

    // Mirrors HomepageSectionContentFields::rowLabel() so a row keeps the same
    // name after a client-side add or remove as it had server-rendered.
    function rowLabel(position) {
        if (rowsWrapper.dataset.contentRowLabels !== 'product') {
            return 'Row ' + (position + 1);
        }

        return position === 0 ? 'Special Offer (deal card)' : 'Product ' + position;
    }

    function renumber() {
        var rows = rowsWrapper.querySelectorAll('[data-content-row]');

        rows.forEach(function (row, position) {
            var label = row.querySelector('[data-content-row-label]');
            if (label) {
                label.textContent = rowLabel(position);
            }
        });
    }

    // select2 gives the product dropdown its search box. jQuery and select2
    // both ship with the admin template, so nothing is loaded from a CDN.
    function enhanceProductSelects(scope) {
        if (typeof window.jQuery !== 'function' || typeof window.jQuery.fn.select2 !== 'function') {
            return;
        }

        window.jQuery(scope).find('[data-product-select]').each(function () {
            var $select = window.jQuery(this);

            if ($select.data('select2')) {
                return;
            }

            $select.select2({
                width: '100%',
                placeholder: 'Search a product...',
                allowClear: true
            });
        });
    }

    function syncVideoFields(row) {
        var source = row.querySelector('[data-video-source]');

        if (!source) {
            return;
        }

        row.querySelectorAll('[data-video-field]').forEach(function (field) {
            var visible = field.dataset.videoField === source.value;
            field.style.display = visible ? '' : 'none';

            // A hidden input must not post, or a row switched to a link would
            // still send the file field and vice versa.
            field.querySelectorAll('input, select, textarea').forEach(function (control) {
                control.disabled = !visible;
            });
        });
    }

    function initRow(row) {
        enhanceProductSelects(row);
        syncVideoFields(row);
    }

    form.addEventListener('change', function (event) {
        if (event.target.matches('[data-video-source]')) {
            var row = event.target.closest('[data-content-row]');
            if (row) {
                syncVideoFields(row);
            }
        }
    });

    form.addEventListener('click', function (event) {
        var removeButton = event.target.closest('[data-remove-content-row]');

        if (!removeButton) {
            return;
        }

        var row = removeButton.closest('[data-content-row]');

        if (!row) {
            return;
        }

        var idField = row.querySelector('input[name$="[id]"]');
        var isSaved = idField && idField.value !== '' && idField.value !== '0';

        if (isSaved) {
            if (!window.confirm('Remove this saved row?')) {
                return;
            }

            // Kept in the form so the save knows to delete it.
            var deleteField = row.querySelector('[data-content-row-delete]');
            if (deleteField) {
                deleteField.value = '1';
            }
            row.style.display = 'none';
            row.querySelectorAll('input:not([data-content-row-delete]):not([name$="[id]"]), select, textarea').forEach(function (control) {
                control.disabled = true;
            });
        } else {
            row.remove();
        }

        renumber();
    });

    if (template) {
        var addButton = form.querySelector('[data-add-content-row]');

        if (addButton) {
            addButton.addEventListener('click', function () {
                var nextIndex = rowsWrapper.querySelectorAll('[data-content-row]').length;
                var markup = template.innerHTML.split('__INDEX__').join(String(nextIndex));
                var holder = document.createElement('div');
                holder.innerHTML = markup;

                var row = holder.querySelector('[data-content-row]');

                if (!row) {
                    return;
                }

                rowsWrapper.appendChild(row);
                initRow(row);
                renumber();
            });
        }
    }

    rowsWrapper.querySelectorAll('[data-content-row]').forEach(initRow);
    renumber();
})();
