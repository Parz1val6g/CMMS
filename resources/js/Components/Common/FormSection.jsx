/**
 * FormSection — Grid layout wrapper with title + description + children.
 */
export default function FormSection({ title, description, children }) {
  return (
    <div className="mb-5 grid gap-5 border-b border-gray-200 pb-5 dark:border-gray-700 md:grid-cols-3">
      <div className="md:col-span-1">
        <h6 className="mb-1 text-sm font-bold text-gray-900 dark:text-white">{title}</h6>
        <p className="text-xs text-gray-500 dark:text-gray-400">{description}</p>
      </div>
      <div className="md:col-span-2">{children}</div>
    </div>
  );
}
