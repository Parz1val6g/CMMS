export default function Scrollable({ children, className = '' }) {
  return (
    <div className={`flex h-full w-full flex-col overflow-y-auto overflow-x-hidden scrollbar-thin ${className}`}>
      {children}
    </div>
  );
}
