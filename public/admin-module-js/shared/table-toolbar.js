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

        // Products only: the bulk Change Category modal sits outside the card
        // and posts its own form, because #bulkActionForm is a DELETE. The
        // ticked rows are copied into it each time the modal opens.
        var categoryModal = document.getElementById('bulkCategoryModal');
        var categoryForm = document.getElementById('bulkCategoryForm');
        var categoryIds = document.getElementById('bulkCategoryIds');

        if (categoryModal && categoryForm && categoryIds) {
            var checkedIds = function () {
                return rowCheckboxes
                    .filter(function (checkbox) { return checkbox.checked; })
                    .map(function (checkbox) { return checkbox.value; });
            };

            categoryModal.addEventListener('show.bs.modal', function () {
                var ids = checkedIds();
                categoryIds.innerHTML = '';

                ids.forEach(function (id) {
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = id;
                    categoryIds.appendChild(input);
                });

                var counter = categoryModal.querySelector('[data-bulk-category-count]');
                if (counter) {
                    counter.textContent = ids.length;
                }
            });

            categoryForm.addEventListener('submit', function (event) {
                if (checkedIds().length === 0) {
                    event.preventDefault();
                    alert('Select at least one product first.');
                }
            });
        }
    });
})();