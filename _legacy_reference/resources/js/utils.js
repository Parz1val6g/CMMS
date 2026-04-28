/**
 * Shared Utility Functions
 */
import { displayErrorsFromObject } from './utils/domSanitizer.js';

/**
 * Display form validation errors in error container
 * @param {Object} errors - Validation errors object {field: [messages]}
 * @param {string} containerId - ID of error container to display in
 */
export function displayErrors(errors, containerId = 'sm-form-error-container') {
    displayErrorsFromObject(errors, containerId);
}
