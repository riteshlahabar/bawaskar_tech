(function () {
    function tableKey(card) {
        return 'admin-table-columns-' + (card.getAttribute('data-table-key') || 'default');
    }

    document.querySelectorAll('.admin-table-card').forEach(function (card) {
        var key = tableKey(card);
        var saved = {};

        try {
            saved = JSON.parse(localStorage.getItem(key) || '{}');
        } catch (error) {
            saved = {};
        }

        function setColumn(index, visible) {
            card.querySelectorAll('[data-column-index="' + index + '"]').forEach(function (cell) {
                cell.classList.toggle('d-none', !visible);
            });
        }

        card.querySelectorAll('.admin-column-toggle').forEach(function (toggle) {
            var index = toggle.getAttribute('data-column-index');
            if (Object.prototype.hasOwnProperty.call(saved, index)) {
                toggle.checked = !!saved[index];
            }
            setColumn(index, toggle.checked);

            toggle.addEventListener('change', function () {
                saved[index] = toggle.checked;
                localStorage.setItem(key, JSON.stringify(saved));
                setColumn(index, toggle.checked);
            });
        });

        var selectAll = card.querySelector('.admin-select-all');
        var rowCheckboxes = Array.prototype.slice.call(card.querySelectorAll('.admin-row-checkbox'));

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        }

        var bulkForm = card.querySelector('#bulkActionForm');
        if (bulkForm) {
            bulkForm.addEventListener('submit', function (event) {
                if (!rowCheckboxes.some(function (checkbox) { return checkbox.checked; })) {
                    event.preventDefault();
                    alert('Select at least one record.');
                }
            });
        }

        // Bulk-move modals (Change Category, Change Section Title) sit outside
        // the card and post their own forms, because #bulkActionForm is a
        // DELETE and its checkboxes cannot reach a POST route. Each modal is
        // marked data-bulk-selection-modal and gets the ticked rows copied in
        // every time it opens, so a new one needs no change here.
        var checkedIds = function () {
            return rowCheckboxes
                .filter(function (checkbox) { return checkbox.checked; })
                .map(function (checkbox) { return checkbox.value; });
        };

        document.querySelectorAll('[data-bulk-selection-modal]').forEach(function (modal) {
            var form = modal.querySelector('form');
            var idsContainer = modal.querySelector('[data-bulk-selection-ids]');

            if (!form || !idsContainer) {
                return;
            }

            modal.addEventListener('show.bs.modal', function () {
                var ids = checkedIds();
                idsContainer.innerHTML = '';

                ids.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = id;
                    idsContainer.appendChild(input);
                });

                var counter = modal.querySelector('[data-bulk-selection-count]');
                if (counter) {
                    counter.textContent = ids.length;
                }
            });

            form.addEventListener('submit', function (event) {
                if (checkedIds().length === 0) {
                    event.preventDefault();
                    alert('Select at least one product first.');
                }
            });
        });
    });
})();