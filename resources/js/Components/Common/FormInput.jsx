import { useCallback } from 'react';

export default function FormInput({
    field,
    value = '',
    onChange,
    onBlur,
    error: serverError,
    fieldConfig = {},
}) {
    const {
        key = field?.key || fieldConfig?.key,
        label = field?.label || fieldConfig?.label,
        required = field?.required || fieldConfig?.required,
        type = field?.type || fieldConfig?.type || 'text',
    } = field || fieldConfig;

    const hasError = !!serverError;

    const handleBlur = useCallback((e) => {
        onBlur?.(e);
    }, [onBlur]);

    const handleChange = useCallback((e) => {
        onChange?.(e);
    }, [onChange]);

    const baseInputClass = `block w-full rounded-lg border bg-slate-800/60 px-3 py-2 text-sm text-slate-200 placeholder:text-slate-500 focus:ring-1 transition-colors ${hasError ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-slate-700 focus:border-indigo-500 focus:ring-indigo-500'
        }`;

    return (
        <div className="mb-4">
            {label && (
                <label htmlFor={key} className="block text-sm font-medium text-slate-300 mb-1.5">
                    {label}
                    {required && <span className="text-red-500 ml-1">*</span>}
                </label>
            )}

            <input
                id={key}
                name={key}
                type={type}
                defaultValue={value}
                onChange={handleChange}
                onBlur={handleBlur}
                required={required}
                className={baseInputClass}
                placeholder={field?.placeholder || fieldConfig?.placeholder}
            />

            {serverError && (
                <p className="text-xs text-red-500 mt-1.5">{serverError}</p>
            )}
        </div>
    );
}
