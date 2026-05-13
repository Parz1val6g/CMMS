import { useState } from 'react';

/**
 * Uncontrolled tab component with flat props API.
 * Each tab receives `{ id, label, content }`.
 */
export default function Tabs({ tabs, defaultTab, onChange, className = '' }) {
  const [active, setActive] = useState(defaultTab ?? tabs[0]?.id);
  const activeTab = tabs.find((t) => t.id === active) ?? tabs[0];
  if (!tabs.length) return null;

  const handleClick = (id) => {
    setActive(id);
    onChange?.(id);
  };

  return (
    <div className={className}>
      {/* Tab navigation */}
      <div className="shrink-0 border-b border-brand-mid/20">
        <nav className="flex gap-6 overflow-x-auto" role="tablist" style={{ scrollbarWidth: 'none' }}>
          {tabs.map((tab) => (
            <button
              key={tab.id}
              id={`${tab.id}-tab`}
              role="tab"
              aria-selected={active === tab.id}
              aria-controls={`${tab.id}-pane`}
              onClick={() => handleClick(tab.id)}
              className={`shrink-0 border-b-2 bg-transparent px-0 pb-3 pt-2 text-sm font-medium transition-colors
                ${active === tab.id
                  ? 'border-brand-accent text-brand-accent'
                  : 'border-transparent text-brand-mid hover:border-brand-mid/30 hover:text-brand-darkest'
                }`}
            >
              {tab.label}
            </button>
          ))}
        </nav>
      </div>

      {/* Active tab content */}
      {activeTab && (
        <div key={activeTab.id} id={`${activeTab.id}-pane`} role="tabpanel" aria-labelledby={`${activeTab.id}-tab`} className="tab-pane fade show active">
          {activeTab.content}
        </div>
      )}
    </div>
  );
}
