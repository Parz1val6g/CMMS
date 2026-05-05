import { useEffect, useRef } from 'react';
import { AlertCircle, CheckCircle, Info, AlertTriangle, X } from 'lucide-react';

const typeConfig = {
    success: {
        icon: CheckCircle,
        bgColor: 'bg-green-500/10',
        borderColor: 'border-green-500/30',
        iconColor: 'text-green-400',
        buttonColor: 'bg-green-600 hover:bg-green-700',
    },
    error: {
        icon: AlertCircle,
        bgColor: 'bg-red-500/10',
        borderColor: 'border-red-500/30',
        iconColor: 'text-red-400',
        buttonColor: 'bg-red-600 hover:bg-red-700',
    },
    warning: {
        icon: AlertTriangle,
        bgColor: 'bg-yellow-500/10',
        borderColor: 'border-yellow-500/30',
        iconColor: 'text-yellow-400',
        buttonColor: 'bg-yellow-600 hover:bg-yellow-700',
    },
    info: {
        icon: Info,
        bgColor: 'bg-blue-500/10',
        borderColor: 'border-blue-500/30',
        iconColor: 'text-blue-400',
        buttonColor: 'bg-blue-600 hover:bg-blue-700',
    },
    confirm: {
        icon: AlertCircle,
        bgColor: 'bg-indigo-500/10',
        borderColor: 'border-indigo-500/30',
        iconColor: 'text-indigo-400',
        buttonColor: 'bg-indigo-600 hover:bg-indigo-700',
    },
};

export default function DialogModal({
    open = false,
    onClose = () => { },
    type = 'info',
    title = '',
    description = '',
    buttons = [],
    children = null,
}) {
    const overlayRef = useRef(null);
    const contentRef = useRef(null);  // moved above early return to satisfy rules-of-hooks
    const config = typeConfig[type] || typeConfig.info;
    const Icon = config.icon;

    // Close on Escape key
    useEffect(() => {
        const handler = (e) => {
            if (e.key === 'Escape' && open) onClose();
        };
        if (open) document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }, [open, onClose]);

    // Manage body scroll
    useEffect(() => {
        if (open) document.body.style.overflow = 'hidden';
        else document.body.style.overflow = '';
        return () => {
            document.body.style.overflow = '';
        };
    }, [open]);

    // Focus trap — must be before early return to satisfy rules-of-hooks
    useEffect(() => {
        if (!open) return;
        const content = contentRef.current;
        if (!content) return;

        const sel = 'button:not([disabled]), [href], input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';
        const getFocusable = () => Array.from(content.querySelectorAll(sel));

        getFocusable()[0]?.focus();

        const trapFocus = (e) => {
            if (e.key !== 'Tab') return;
            const focusable = getFocusable();
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey) {
                if (document.activeElement === first) { e.preventDefault(); last?.focus(); }
            } else {
                if (document.activeElement === last) { e.preventDefault(); first?.focus(); }
            }
        };

        document.addEventListener('keydown', trapFocus);
        return () => document.removeEventListener('keydown', trapFocus);
    }, [open]);

    if (!open) return null;

    // Default buttons based on type
    const defaultButtons = {
        success: [{ label: 'OK', onClick: onClose, variant: 'primary' }],
        error: [{ label: 'OK', onClick: onClose, variant: 'primary' }],
        warning: [{ label: 'OK', onClick: onClose, variant: 'primary' }],
        info: [{ label: 'OK', onClick: onClose, variant: 'primary' }],
        confirm: [
            { label: 'Cancel', onClick: onClose, variant: 'secondary' },
            { label: 'Confirm', onClick: onClose, variant: 'primary' },
        ],
    };

    const renderButtons = buttons.length > 0 ? buttons : defaultButtons[type] || defaultButtons.info;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center p-4">
            {/* Overlay */}
            <div
                ref={overlayRef}
                className="absolute inset-0 bg-black/60 backdrop-blur-sm"
                onClick={(e) => {
                    if (e.target === overlayRef.current) onClose();
                }}
            />

            {/* Dialog */}
            <div
                ref={contentRef}
                className={`relative w-full max-w-md rounded-xl shadow-2xl border ${config.bgColor} ${config.borderColor}`}
                style={{
                    backgroundColor: 'rgba(15, 23, 42, 0.95)',
                    borderColor: 'rgba(100, 116, 139, 0.5)',
                }}
            >
                {/* Header with Icon */}
                <div className="flex items-start justify-between px-6 py-5">
                    <div className="flex items-start gap-4">
                        <Icon className={`h-6 w-6 shrink-0 mt-0.5 ${config.iconColor}`} />
                        <div>
                            {title && <h3 className="text-lg font-semibold text-white">{title}</h3>}
                            {description && (
                                <p className="mt-1 text-sm text-slate-300 leading-relaxed">{description}</p>
                            )}
                        </div>
                    </div>
                    <button
                        type="button"
                        className="rounded-lg p-1 text-slate-400 hover:bg-slate-700 hover:text-white transition-colors shrink-0"
                        onClick={onClose}
                        aria-label="Close"
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                {/* Custom Content */}
                {children && <div className="px-6 py-3 border-t border-slate-700/50 text-sm text-slate-300">{children}</div>}

                {/* Buttons */}
                <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-700/50">
                    {renderButtons.map((btn, idx) => (
                        <button
                            key={idx}
                            type="button"
                            disabled={btn.disabled}
                            className={`rounded-lg px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${btn.variant === 'primary'
                                    ? `${config.buttonColor} text-white shadow-sm`
                                    : 'border border-slate-600 bg-slate-700/50 text-slate-300 hover:bg-slate-600 hover:text-white'
                                }`}
                            onClick={() => {
                                if (!btn.disabled) btn.onClick?.();
                            }}
                        >
                            {btn.label}
                        </button>
                    ))}
                </div>
            </div>
        </div>
    );
}
