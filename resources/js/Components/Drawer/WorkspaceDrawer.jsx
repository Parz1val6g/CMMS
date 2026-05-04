import { useState, useEffect } from 'react';
import { X } from 'lucide-react';

/**
 * WorkspaceDrawer — Generic right-sliding workspace panel.
 *
 * @param {boolean}         isOpen   - Whether the drawer is visible
 * @param {Function}        onClose  - Callback to close the drawer
 * @param {string|ReactNode} title   - Header title
 * @param {string|ReactNode} subtitle - Optional subtitle below header
 * @param {Array<{id:string, label:string, component:ReactNode}>} tabs - Tab definitions
 */
export default function WorkspaceDrawer({ isOpen, onClose, title, subtitle, tabs = [] }) {
  const [activeTab, setActiveTab] = useState(tabs[0]?.id ?? null);

  /* Reset active tab when tabs change or current tab is removed */
  useEffect(() => {
    if (!tabs.some((t) => t.id === activeTab)) {
      setActiveTab(tabs[0]?.id ?? null);
    }
  }, [tabs, activeTab]);

  /* Close on Escape key */
  useEffect(() => {
    if (!isOpen) return;
    const handler = (e) => { if (e.key === 'Escape') onClose?.(); };
    document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [isOpen, onClose]);

  /* Lock body scroll when open */
  useEffect(() => {
    if (isOpen) document.body.style.overflow = 'hidden';
    else document.body.style.overflow = '';
    return () => { document.body.style.overflow = ''; };
  }, [isOpen]);

  const activeComponent = tabs.find((t) => t.id === activeTab)?.component ?? null;

  return (
    <>
      {/* Overlay */}
      <div
        className={`fixed inset-0 z-40 bg-slate-900/80 backdrop-blur-sm transition-opacity duration-300 ease-in-out ${
          isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'
        }`}
        onClick={onClose}
        aria-hidden="true"
      />

      {/* Drawer Panel */}
      <div
        className={`fixed inset-y-0 right-0 z-50 w-full max-w-4xl flex flex-col bg-slate-800 shadow-2xl border-l border-slate-700 overflow-hidden transform transition-transform duration-300 ease-in-out ${
          isOpen ? 'translate-x-0' : 'translate-x-full'
        }`}
      >
        {/* Header */}
        <div className="shrink-0 flex items-center justify-between px-6 py-4 border-b border-slate-700 bg-slate-900/50">
          <div className="flex items-center gap-4 min-w-0">
            {title && (
              <h2 className="text-lg font-bold text-indigo-400 font-mono truncate">
                {title}
              </h2>
            )}
          </div>

          <button
            type="button"
            onClick={onClose}
            className="shrink-0 rounded-lg p-1.5 text-slate-400 hover:bg-slate-700 hover:text-white transition-colors"
            aria-label="Close drawer"
          >
            <X className="h-5 w-5" />
          </button>
        </div>

        {/* Subtitle */}
        {subtitle && (
          <div className="shrink-0 px-6 py-3 border-b border-slate-700 bg-slate-800/60">
            <p className="text-sm text-slate-300">{subtitle}</p>
          </div>
        )}

        {/* Tabs Navigation */}
        {tabs.length > 1 && (
          <div className="shrink-0 flex border-b border-slate-700 bg-slate-800/30 px-6">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                type="button"
                onClick={() => setActiveTab(tab.id)}
                className={`relative px-4 py-3 text-sm font-medium transition-colors ${
                  activeTab === tab.id
                    ? 'text-indigo-400 after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-indigo-400'
                    : 'text-slate-400 hover:text-slate-200'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        )}

        {/* Tab Content */}
        <div className="flex-1 overflow-y-auto px-6 py-6">
          {activeComponent}
        </div>
      </div>
    </>
  );
}
