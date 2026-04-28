import 'bootstrap';
import { initTheme } from './modules/theme.js';
import { initEditForms } from './modules/forms.js';
import { initCreateModal } from './modules/modal.js';

document.addEventListener('DOMContentLoaded', () => {
    // Initialize all feature modules
    initTheme();
    initEditForms();
    initCreateModal();
});