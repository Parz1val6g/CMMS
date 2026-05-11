/**
 * Reusable section header with optional icon.
 * Used in Dashboard cards, Settings sections, etc.
 */
export default function SectionHeader({ title, icon: Icon, color = 'gray' }) {
  const textColor = color === 'red'
    ? 'text-red-600 dark:text-red-400'
    : 'text-gray-900 dark:text-white';

  return (
    <div className="border-b border-gray-200 bg-gray-50 px-6 py-4 dark:border-gray-700 dark:bg-gray-800/80">
      <h3 className={`flex items-center gap-2 text-lg font-medium ${textColor}`}>
        {Icon && <Icon className="h-5 w-5" />}
        {title}
      </h3>
    </div>
  );
}
