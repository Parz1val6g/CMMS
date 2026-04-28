/**
 * Safe DOM Creation & Manipulation Utilities
 * Prevents XSS attacks by avoiding innerHTML with unsanitized content
 */

/**
 * Create an alert element safely without innerHTML injection
 * @param {string} message - Alert message text (will be escaped)
 * @param {string} type - Alert type: 'danger', 'success', 'warning', 'info'
 * @param {boolean} dismissible - Whether alert can be dismissed
 * @returns {HTMLElement} Alert div element
 */
export function createAlertElement(message, type = 'danger', dismissible = true) {
    const alert = document.createElement('div');
    alert.className = `alert alert-${type}${dismissible ? ' alert-dismissible fade show' : ''}`;
    alert.setAttribute('role', 'alert');

    // Create message text node (safe, no XSS)
    const messageSpan = document.createElement('span');
    messageSpan.textContent = message;
    alert.appendChild(messageSpan);

    // Add dismiss button if needed
    if (dismissible) {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'btn-close';
        button.setAttribute('data-bs-dismiss', 'alert');
        button.setAttribute('aria-label', 'Close');
        alert.appendChild(button);
    }

    return alert;
}

/**
 * Clear container and add alert safely
 * @param {string} containerId - ID of container
 * @param {string} message - Alert message text
 * @param {string} type - Alert type: 'danger', 'success', 'warning', 'info'
 */
export function setAlertInContainer(containerId, message, type = 'danger') {
    const container = document.getElementById(containerId);
    if (!container) return;

    // Safe: clear HTML
    container.innerHTML = '';

    // Safe: add element with text content, not innerHTML
    const alert = createAlertElement(message, type);
    container.appendChild(alert);
}

/**
 * Add multiple error messages to container
 * @param {Object} errors - Validation errors {field: [messages]}
 * @param {string} containerId - Container ID
 */
export function displayErrorsFromObject(errors, containerId = 'sm-form-error-container') {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';

    Object.values(errors).forEach(messages => {
        if (Array.isArray(messages)) {
            messages.forEach(message => {
                const alert = createAlertElement(message, 'danger', true);
                container.appendChild(alert);
            });
        }
    });
}

/**
 * Safely set text content with optional formatting
 * @param {HTMLElement} element - Target element
 * @param {string} text - Text content (will be escaped)
 */
export function setSafeText(element, text) {
    element.textContent = text;
}

/**
 * Safely set HTML from a trusted source (pre-sanitized only)
 * WARNING: Only use with content you fully control or have sanitized
 * @param {HTMLElement} element - Target element
 * @param {string} html - HTML content (MUST be pre-sanitized)
 * @param {boolean} isSanitized - Must explicitly confirm sanitization
 */
export function setTrustedHTML(element, html, isSanitized = false) {
    if (!isSanitized) {
        console.warn('[Security] setTrustedHTML called without isSanitized=true. Using textContent instead.');
        element.textContent = html;
        return;
    }
    element.innerHTML = html;
}

/**
 * Create a data attribute with safe JSON
 * @param {HTMLElement} element - Target element
 * @param {string} attrName - Attribute name (without 'data-')
 * @param {*} value - Value to store (will be JSON stringified)
 */
export function setDataAttribute(element, attrName, value) {
    element.setAttribute(`data-${attrName}`, JSON.stringify(value));
}

/**
 * Retrieve and parse data attribute
 * @param {HTMLElement} element - Source element
 * @param {string} attrName - Attribute name (without 'data-')
 * @returns {*} Parsed JSON value
 */
export function getDataAttribute(element, attrName) {
    const value = element.getAttribute(`data-${attrName}`);
    return value ? JSON.parse(value) : null;
}
