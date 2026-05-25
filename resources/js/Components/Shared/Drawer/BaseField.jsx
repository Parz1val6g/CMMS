export default function BaseField({ label, value, children, variant = 'brand' }) {
    const isGray = variant === 'gray';

    return (
        <div className={`flex flex-col ${isGray ? 'gap-0.5' : 'gap-1'}`}>
            <span className={`text-xs font-medium uppercase ${isGray ? 'tracking-wide text-gray-400' : 'tracking-wide text-brand-mid'}`}>
                {label}
            </span>
            {children != null ? (
                children
            ) : (
                <span className={`text-sm ${isGray ? 'text-gray-800 font-medium' : 'text-brand-darkest'}`}>
                    {value != null ? value : (
                        <span className={isGray ? 'text-gray-400 italic' : 'text-brand-mid'}>
                            {'\u2014'}
                        </span>
                    )}
                </span>
            )}
        </div>
    );
}
