import { useEffect, useRef } from 'react';
import { useFocusTrap } from '@/Hooks/useFocusTrap';
import { useBodyLock } from '@/Hooks/useBodyLock';
import { AlertCircle, CheckCircle, Info, AlertTriangle, X } from 'lucide-react';
import { t } from '@/utils/i18n';

const typeConfig = {
    success: {
        icon: CheckCircle,
        bgColor: 'bg-emerald-50',
        borderColor: 'border-emerald-200',
        iconColor: 'text-emerald-600',
        buttonColor: 'bg-emerald-600 hover:bg-emerald-700',
    },
    error: {
        icon: AlertCircle,
        bgColor: 'bg-red-50',
        borderColor: 'border-red-200',
        iconColor: 'text-red-600',
        buttonColor: 'bg-red-600 hover:bg-red-700',
    },
    warning: {
        icon: AlertTriangle,
        bgColor: 'bg-amber-50',
        borderColor: 'border-amber-200',
        iconColor: 'text-amber-600',
        buttonColor: 'bg-amber-600 hover:bg-amber-700',
    },
    info: {
        icon: Info,
        bgColor: 'bg-sky-50',
        borderColor: 'border-sky-200',
        iconColor: 'text-sky-600',
        buttonColor: 'bg-sky-600 hover:bg-sky-700',
    },
    confirm: {
        icon: AlertCircle,
        bgColor: 'bg-brand-accent/5',
        borderColor: 'border-brand-accent/30',
        iconColor: 'text-brand-accent',
        buttonColor: 'bg-brand-accent hover:bg-brand-accent/90',
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

    useBodyLock(open);
    useFocusTrap(contentRef, open);

    if (!open) return null;

    // Default buttons based on type
    const defaultButtons = {
        success: [{ label: t('pages.datamanager.ok_btn'), onClick: onClose, variant: 'primary' }],
        error: [{ label: t('pages.datamanager.ok_btn'), onClick: onClose, variant: 'primary' }],
        warning: [{ label: t('pages.datamanager.ok_btn'), onClick: onClose, variant: 'primary' }],
        info: [{ label: t('pages.datamanager.ok_btn'), onClick: onClose, variant: 'primary' }],
        confirm: [
            { label: t('pages.datamanager.cancel_btn'), onClick: onClose, variant: 'secondary' },
            { label: t('pages.modal.confirm_btn'), onClick: onClose, variant: 'primary' },
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
                className={`relative w-full max-w-md rounded-xl shadow-xl bg-white border ${config.borderColor}`}
            >
                {/* Header with Icon */}
                <div className="flex items-start justify-between px-6 py-5">
                    <div className="flex items-start gap-4">
                        <Icon className={`h-6 w-6 shrink-0 mt-0.5 ${config.iconColor}`} />
                        <div>
                            {title && <h3 className="text-lg font-semibold text-brand-darkest">{title}</h3>}
                            {description && (
                                <p className="mt-1 text-sm text-brand-mid leading-relaxed">{description}</p>
                            )}
                        </div>
                    </div>
                    <button
                        type="button"
                        className="rounded-xl p-1 text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors shrink-0"
                        onClick={onClose}
                        aria-label={t('pages.datamanager.close_aria')}
                    >
                        <X className="h-5 w-5" />
                    </button>
                </div>

                {/* Custom Content */}
                {children && <div className="px-6 py-3 border-t border-brand-mid/10 text-sm text-brand-mid">{children}</div>}

                {/* Buttons */}
                <div className="flex items-center justify-end gap-3 px-6 py-4 border-t border-brand-mid/10">
                    {renderButtons.map((btn, idx) => (
                        <button
                            key={idx}
                            type="button"
                            disabled={btn.disabled}
                            className={`rounded-xl px-4 py-2 text-sm font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${btn.variant === 'primary'
                                    ? `${config.buttonColor} text-white shadow-sm`
                                    : 'border border-brand-mid/20 bg-brand-light/50 text-brand-mid hover:bg-brand-mid/10 hover:text-brand-darkest'
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
