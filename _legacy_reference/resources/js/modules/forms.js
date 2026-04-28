/**
 * Edit Forms Module
 * Handles side panel form expansion and AJAX submission for edits
 */

import { displayErrors } from '../utils.js';
import { logError } from '../utils/logger.js';

export function initEditForms() {
    const formPanel = document.getElementById('sm-form-panel');
    const tablePanel = document.getElementById('sm-table-panel');
    const form = document.getElementById('sm-edit-form');

    if (!formPanel || !tablePanel || !form) return;

    let currentSelectedRow = null;

    // Use event delegation for edit buttons (works with dynamic content)
    if (tablePanel) {
        tablePanel.addEventListener('click', function (e) {
            const editBtn = e.target.closest('.js-btn-edit');
            if (editBtn) {
                e.preventDefault();
                e.stopPropagation();
                const payload = JSON.parse(editBtn.dataset.payload);
                const itemId = editBtn.dataset.itemId;
                populateForm(payload, itemId);
                highlightSelectedRow(itemId);
                expandSmEditPanel();
            }
        });
    }

    /**
     * Highlight the selected row
     */
    function highlightSelectedRow(itemId) {
        // Remove previous highlight
        if (currentSelectedRow) {
            currentSelectedRow.classList.remove('table-active');
            currentSelectedRow.style.backgroundColor = '';
        }
        // Highlight new row
        const row = document.querySelector(`tr[data-row-id="${itemId}"]`);
        if (row) {
            row.classList.add('table-active');
            row.style.backgroundColor = 'rgba(79, 70, 229, 0.08)';
            currentSelectedRow = row;
        }
    }

    /**
     * Populate form with record data
     */
    function populateForm(data, itemId) {
        const inputs = form.querySelectorAll('input, select, textarea');
        inputs.forEach(input => {
            if (data[input.name] !== undefined) {
                input.value = data[input.name];
            }
        });
        // Store itemId for form submission
        form.dataset.itemId = itemId;

        // Notify map picker (and any other listeners) that the form was reloaded
        document.dispatchEvent(new CustomEvent('sm-form-loaded', { detail: { data, itemId } }));
    }

    /**
     * Expand side panel to show edit form
     */
    function expandSmEditPanel() {
        tablePanel.classList.add('w-50-gap');
        formPanel.classList.remove('form-panel-collapsed');
        formPanel.classList.add('w-50-gap');

        // Guard: dispatch sm-panel-opened exactly ONCE (transitionend fires
        // once per property when using transition:all, plus the setTimeout fallback)
        var fired = false;
        function fireSmPanelOpened() {
            if (fired) return;
            fired = true;
            document.dispatchEvent(new CustomEvent('sm-panel-opened'));
        }

        formPanel.addEventListener('transitionend', function handler() {
            formPanel.removeEventListener('transitionend', handler);
            fireSmPanelOpened();
        });

        // Fallback: in case transitionend never fires (reduced-motion, etc.)
        setTimeout(fireSmPanelOpened, 400);
    }

    /**
     * Close side panel and reset form
     */
    window.closeSmEditPanel = function () {
        tablePanel.classList.remove('w-50-gap');
        formPanel.classList.add('form-panel-collapsed');
        formPanel.classList.remove('w-50-gap');
        form.reset();
        document.getElementById('sm-form-error-container').innerHTML = '';
        // Remove highlight
        if (currentSelectedRow) {
            currentSelectedRow.classList.remove('table-active');
            currentSelectedRow.style.backgroundColor = '';
            currentSelectedRow = null;
        }
        // Tell map picker to destroy its Leaflet instance
        document.dispatchEvent(new CustomEvent('sm-panel-closed'));
    };

    /**
     * Handle form submission
     */
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const itemId = form.dataset.itemId;
        const actionUrl = form.dataset.routeTemplate.replace(':id', itemId);

        try {
            const response = await fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
                }
            });

            if (response.ok) {
                location.reload();
            } else {
                const errors = await response.json();
                displayErrors(errors, 'sm-form-error-container');
            }
        } catch (error) {
            logError('Form submission failed', error, { actionUrl });
        }
    });

    /**
     * Handle softdelete
     * @returns void
     */
    window.softDeleteItem = function () {
        const itemId = editBtn?.dataset?.itemId;
        if (!itemId || !confirm('{{ __("Are you sure?") }}')) return;

        const form = document.getElementById('sm-edit-form');
        const destroyRoute = form.dataset.destroyRoute || '';
        const url = destroyRoute.replace(':id', itemId);

        fetch(url, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
            }
        }).then(r => r.ok ? (closeSmEditPanel(), submitFilterForm()) : alert('Failed'));
    };
}
