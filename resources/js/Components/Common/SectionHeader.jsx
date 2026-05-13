/**
 * Reusable section header with optional icon.
 * Used in Dashboard cards, Settings sections, etc.
 */
export default function SectionHeader({ title, icon: Icon, color = 'gray' }) {
  const textColor = color === 'red'
    ? 'text-red-600'
    : 'text-brand-darkest';

  return (
    <div className="border-b border-brand-mid/20 bg-brand-light px-6 py-4">
      <h3 className={`flex items-center gap-2 text-lg font-medium ${textColor}`}>
        {Icon && <Icon className="h-5 w-5" />}
        {title}
      </h3>
    </div>
  );
}
