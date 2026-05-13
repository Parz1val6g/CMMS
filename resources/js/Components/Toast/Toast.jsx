import { useEffect, useState } from 'react';
import { AlertCircle, CheckCircle, AlertTriangle, Info, X } from 'lucide-react';

/**
 * Toast notification component
 * Displays a dismissible notification with different severity levels
 * Auto-dismisses after 4-5 seconds
 */
export default function Toast({
    id,
    type = 'info', // 'success', 'error', 'warning', 'info'
    message,
    onDismiss,
    autoClose = true,
    duration = 5000
}) {
    const [isExiting, setIsExiting] = useState(false);

    useEffect(() => {
        if (!autoClose) return;

        const timer = setTimeout(() => {
            setIsExiting(true);
            setTimeout(() => onDismiss?.(id), 300); // Wait for animation
        }, duration);

        return () => clearTimeout(timer);
    }, [autoClose, duration, id, onDismiss]);

    const typeConfig = {
        success: {
            bg: 'bg-green-50',
            border: 'border-green-200',
            text: 'text-green-800',
            icon: 'text-green-600',
            Icon: CheckCircle
        },
        error: {
            bg: 'bg-red-50',
            border: 'border-red-200',
            text: 'text-red-800',
            icon: 'text-red-600',
            Icon: AlertCircle
        },
        warning: {
            bg: 'bg-yellow-50',
            border: 'border-yellow-200',
            text: 'text-yellow-800',
            icon: 'text-yellow-600',
            Icon: AlertTriangle
        },
        info: {
            bg: 'bg-blue-50',
            border: 'border-blue-200',
            text: 'text-blue-800',
            icon: 'text-blue-600',
            Icon: Info
        }
    };

    const config = typeConfig[type] || typeConfig.info;
    const { Icon } = config;

    return (
        <div
            className={`transform transition-all duration-300 ${isExiting ? 'translate-x-full opacity-0' : 'translate-x-0 opacity-100'
                }`}
        >
            <div
                className={`
          flex items-start gap-3 rounded-lg border px-4 py-3
          ${config.bg} ${config.border}
        `}
            >
                <Icon className={`h-5 w-5 mt-0.5 flex-shrink-0 ${config.icon}`} />
                <p className={`text-sm font-medium ${config.text}`}>{message}</p>
                <button
                    onClick={() => {
                        setIsExiting(true);
                        setTimeout(() => onDismiss?.(id), 300);
                    }}
                    className={`ml-auto -mr-1.5 inline-flex flex-shrink-0 rounded-md transition-colors ${config.text} hover:opacity-75`}
                    aria-label="Dismiss"
                >
                    <X className="h-5 w-5" />
                </button>
            </div>
        </div>
    );
}
