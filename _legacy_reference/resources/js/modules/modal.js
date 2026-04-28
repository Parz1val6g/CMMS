/**
 * Create Modal Module
 * Handles create modal form submission and accessibility
 */

import { displayErrors } from '../utils.js';
import { logError } from '../utils/logger.js';

export function initCreateModal() {
    const createForm = document.getElementById('create-modal-form');
    if (!createForm) return;

    const createModalElement = document.getElementById('createRecordModal');
    if (createModalElement) {
        // Close modal with Escape key (P13)
        createModalElement.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                const bsModal = window.bootstrap?.Modal.getInstance(createModalElement);
                if (bsModal) {
                    bsModal.hide();
                }
            }
        });
    }

    /**
     * Handle create modal form submission
     */
    createForm.addEventListener('submit', async function (e) {
        e.preventDefault();
        const formData = new FormData(createForm);

        try {
            const response = await fetch(createForm.action, {
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
                displayErrors(errors, 'modal-form-error-container');
            }
        } catch (error) {
            logError('Create modal submission failed', error, { action: createForm.action });
        }
    });
}
