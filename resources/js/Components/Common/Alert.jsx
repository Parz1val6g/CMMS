import { useState } from 'react';

const icons = {
  success: (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
      <path d="M10.854 7.854a.5.5 0 0 0-.708-.708L7.5 9.793 6.354 8.646a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l3-3z" />
      <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z" />
    </svg>
  ),
  danger: (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
      <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0l-5.708 9.75a1.13 1.13 0 0 0 .98 1.684h11.456c.912 0 1.469-.921.98-1.684L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
    </svg>
  ),
  warning: (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
      <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0l-5.708 9.75a1.13 1.13 0 0 0 .98 1.684h11.456c.912 0 1.469-.921.98-1.684L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
    </svg>
  ),
  info: (
    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
      <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z" />
    </svg>
  ),
};

const alertStyles = {
  success: 'border-green-500 bg-green-50 text-green-800 dark:bg-green-900/30 dark:text-green-300',
  danger: 'border-red-500 bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-300',
  warning: 'border-yellow-500 bg-yellow-50 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-300',
  info: 'border-blue-500 bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300',
};

export default function Alert({ type = 'info', title, icon = true, dismissible = true, children }) {
  const [closed, setClosed] = useState(false);

  if (closed) return null;

  const style = alertStyles[type] ?? alertStyles.info;

  return (
    <div className={`flex items-start gap-3 rounded-lg border-l-4 p-4 ${style}`} role="alert">
      {icon && icons[type] && (
        <div className="shrink-0">{icons[type]}</div>
      )}

      <div className="min-w-0 flex-1">
        {title && <h5 className="mb-1 font-medium">{title}</h5>}
        <div className="text-sm">{children}</div>
      </div>

      {dismissible && (
        <button
          type="button"
          className="shrink-0 rounded-full p-1 opacity-70 hover:opacity-100 transition-opacity"
          onClick={() => setClosed(true)}
          aria-label="Close alert"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708l2.647-2.646-2.647-2.646a.5.5 0 0 1 0-.708z" />
          </svg>
        </button>
      )}
    </div>
  );
}
