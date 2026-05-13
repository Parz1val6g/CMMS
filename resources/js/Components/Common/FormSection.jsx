/**
 * FormSection — Grid layout wrapper with title + description + children.
 */
export default function FormSection({ title, description, children }) {
  return (
    <div className="mb-5 grid gap-5 border-b border-brand-mid/20 pb-5 md:grid-cols-3">
      <div className="md:col-span-1">
        <h6 className="mb-1 text-sm font-bold text-brand-darkest">{title}</h6>
        <p className="text-xs text-brand-mid">{description}</p>
      </div>
      <div className="md:col-span-2">{children}</div>
    </div>
  );
}
