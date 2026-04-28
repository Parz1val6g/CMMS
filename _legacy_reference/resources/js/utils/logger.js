/**
 * Centralized Logging Utility
 * In production, sends to server; in development, uses console (controlled)
 */

const isDev = import.meta.env.DEV || process.env.NODE_ENV === 'development';

/**
 * Log error to server (or console in dev)
 * @param {string} message - Error message
 * @param {*} error - Error object or data
 * @param {Object} context - Additional context
 */
export function logError(message, error = null, context = {}) {
    const logData = {
        timestamp: new Date().toISOString(),
        message,
        context,
        userAgent: navigator.userAgent,
    };

    if (error) {
        logData.error = error instanceof Error ? {
            message: error.message,
            stack: error.stack,
            name: error.name,
        } : error;
    }

    if (isDev) {
        console.error('[APP ERROR]', message, logData);
    } else {
        // Send to server in production
        sendLogToServer('error', logData);
    }
}

/**
 * Log warning
 * @param {string} message - Warning message
 * @param {Object} context - Additional context
 */
export function logWarning(message, context = {}) {
    const logData = {
        timestamp: new Date().toISOString(),
        message,
        context,
    };

    if (isDev) {
        console.warn('[APP WARNING]', message, logData);
    } else {
        sendLogToServer('warning', logData);
    }
}

/**
 * Log info
 * @param {string} message - Info message
 * @param {Object} context - Additional context
 */
export function logInfo(message, context = {}) {
    const logData = {
        timestamp: new Date().toISOString(),
        message,
        context,
    };

    if (isDev) {
        console.info('[APP INFO]', message, logData);
    } else {
        sendLogToServer('info', logData);
    }
}

/**
 * Send log to server endpoint
 * @param {string} level - Log level: error, warning, info
 * @param {Object} data - Log data
 */
function sendLogToServer(level, data) {
    // Use sendBeacon for reliability (doesn't require response)
    try {
        const payload = JSON.stringify({
            level,
            ...data,
            url: window.location.href,
            userId: getUserIdIfAvailable(),
        });

        if (navigator.sendBeacon) {
            navigator.sendBeacon('/api/logs', payload);
        } else {
            // Fallback for older browsers
            fetch('/api/logs', {
                method: 'POST',
                body: payload,
                headers: { 'Content-Type': 'application/json' },
                keepalive: true,
            }).catch(() => {
                // Silently fail - don't log logging failures
            });
        }
    } catch (err) {
        // Fallback: if logging fails, at least log locally in dev
        if (isDev) {
            console.error('[LOGGER ERROR]', err);
        }
    }
}

/**
 * Attempt to get current user ID from page context
 * @returns {string|null} User ID or null
 */
function getUserIdIfAvailable() {
    // Try to get from meta tag, window object, or localStorage
    const metaTag = document.querySelector('meta[name="user-id"]');
    if (metaTag?.content) return metaTag.content;

    if (typeof window.__userId !== 'undefined') return window.__userId;
    if (typeof window.__user?.id !== 'undefined') return window.__user.id;

    return null;
}

/**
 * Safe try-catch wrapper with automatic logging
 * @param {Function} fn - Function to execute
 * @param {string} context - Context description
 * @returns {*} Function result or null on error
 */
export function tryCatch(fn, context = 'operation') {
    try {
        return fn();
    } catch (error) {
        logError(`Error during ${context}`, error, { context });
        return null;
    }
}
