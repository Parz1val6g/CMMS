import { createContext, useContext, useState, useCallback } from 'react';
import Toast from './Toast';

/**
 * Toast context for global toast notifications
 */
const ToastContext = createContext();

export function ToastProvider({ children }) {
    const [toasts, setToasts] = useState([]);

    const addToast = useCallback((message, type = 'info', options = {}) => {
        const id = Date.now() + Math.random();
        const duration = options.duration || (type === 'success' ? 4000 : type === 'error' ? 6000 : 5000);

        setToasts((prev) => [...prev, { id, message, type, duration }]);

        // Auto-remove if duration is set
        if (duration) {
            setTimeout(() => {
                dismissToast(id);
            }, duration);
        }

        return id;
    }, []);

    const dismissToast = useCallback((id) => {
        setToasts((prev) => prev.filter((toast) => toast.id !== id));
    }, []);

    const toast = {
        success: (message, options) => addToast(message, 'success', options),
        warning: (message, options) => addToast(message, 'warning', options),
        info: (message, options) => addToast(message, 'info', options),
    };

    return (
        <ToastContext.Provider value={toast}>
            {children}

            {/* Toast container */}
            <div className="fixed bottom-4 right-4 z-50 space-y-2 max-w-md pointer-events-none">
                {toasts.map((t) => (
                    <div key={t.id} className="pointer-events-auto">
                        <Toast
                            id={t.id}
                            type={t.type}
                            message={t.message}
                            onDismiss={dismissToast}
                            autoClose={true}
                            duration={t.duration}
                        />
                    </div>
                ))}
            </div>
        </ToastContext.Provider>
    );
}

/**
 * Hook to use toast notifications anywhere in the app
 * 
 * Usage:
 *   const toast = useToast();
 *   toast.success('Saved successfully');
 *   toast.warning('Warning message');
 */
export function useToast() {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within ToastProvider');
    }
    return context;
}
