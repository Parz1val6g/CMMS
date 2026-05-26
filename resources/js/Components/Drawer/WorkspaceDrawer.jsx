import { useState, useEffect, useRef, useCallback } from 'react';
import { X } from 'lucide-react';
import { useFocusTrap } from '@/Hooks/useFocusTrap';
import { useBodyLock } from '@/Hooks/useBodyLock';
import { t } from '@/utils/i18n';

const MIN_WIDTH = 320;
const DEFAULT_WIDTH = 672;

/**
 * WorkspaceDrawer — Generic right-sliding workspace panel.
 *
 * @param {boolean}         isOpen   - Whether the drawer is visible
 * @param {Function}        onClose  - Callback to close the drawer
 * @param {string|ReactNode} title   - Header title
 * @param {string|ReactNode} subtitle - Optional subtitle below header
 * @param {Array<{id:string, label:string, component:ReactNode}>} tabs - Tab definitions
 * @param {ReactNode}        headerActions - Optional actions rendered left of the close button
 */
export default function WorkspaceDrawer({ isOpen, onClose, title, subtitle, tabs = [], headerActions = null, stacked = false }) {
  const [activeTab, setActiveTab] = useState(tabs[0]?.id ?? null);
  const [width, setWidth] = useState(DEFAULT_WIDTH);
  const panelRef = useRef(null);
  const isResizing = useRef(false);
  const startX = useRef(0);
  const startWidth = useRef(0);

  const onResizeStart = useCallback((e) => {
    e.preventDefault();
    e.stopPropagation();
    isResizing.current = true;
    startX.current = e.clientX;
    startWidth.current = panelRef.current?.offsetWidth ?? DEFAULT_WIDTH;

    const onMouseMove = (ev) => {
      if (!isResizing.current) return;
      const delta = startX.current - ev.clientX;
      const next = Math.min(window.innerWidth * 0.95, Math.max(MIN_WIDTH, startWidth.current + delta));
      setWidth(next);
    };
    const onMouseUp = () => {
      isResizing.current = false;
      document.body.style.cursor = '';
      document.body.style.userSelect = '';
      document.removeEventListener('mousemove', onMouseMove);
      document.removeEventListener('mouseup', onMouseUp);
    };

    document.body.style.cursor = 'ew-resize';
    document.body.style.userSelect = 'none';
    document.addEventListener('mousemove', onMouseMove);
    document.addEventListener('mouseup', onMouseUp);
  }, []);

  /* Reset active tab when tabs change or current tab is removed */
  useEffect(() => {
    if (!tabs.some((t) => t.id === activeTab)) {
      setActiveTab(tabs[0]?.id ?? null);
    }
  }, [tabs, activeTab]);

  /* Close on Escape key — only when not stacked (parent drawer owns Esc) */
  useEffect(() => {
    if (!isOpen || stacked) return;
    const handler = (e) => { if (e.key === 'Escape') onClose?.(); };
    document.addEventListener('keydown', handler);
    return () => document.removeEventListener('keydown', handler);
  }, [isOpen, onClose, stacked]);

  /* Body lock — only when not stacked (parent drawer already locked) */
  useBodyLock(isOpen && !stacked);
  useFocusTrap(panelRef, isOpen);

  const activeComponent = tabs.find((t) => t.id === activeTab)?.component ?? null;

  const overlayZ = stacked ? 'z-[60]' : 'z-40';
  const panelZ = stacked ? 'z-[70]' : 'z-50';

  return (
    <>
      {/* Overlay */}
      <div
        className={`fixed inset-0 ${overlayZ} bg-brand-darkest/80 backdrop-blur-sm transition-opacity duration-300 ease-in-out ${
          isOpen ? 'opacity-100' : 'opacity-0 pointer-events-none'
        }`}
        onClick={onClose}
        aria-hidden="true"
      />

      {/* Drawer Panel */}
      <div
        ref={panelRef}
        style={{ width: `${width}px` }}
        className={`fixed inset-y-0 right-0 ${panelZ} flex flex-col bg-brand-white shadow-2xl border-l border-brand-mid/20 overflow-hidden transform transition-transform duration-300 ease-in-out ${
          isOpen ? 'translate-x-0' : 'translate-x-full'
        }`}
      >
        {/* Resize handle */}
        <div
          onMouseDown={onResizeStart}
          className="absolute left-0 inset-y-0 w-1.5 cursor-ew-resize z-10 group"
          title={t('pages.common.resize_drawer')}
        >
          <div className="absolute left-0 inset-y-0 w-0.5 bg-brand-mid/20 group-hover:bg-brand-accent/60 group-active:bg-brand-accent transition-colors" />
        </div>
        {/* Header */}
        <div className="shrink-0 flex items-center justify-between px-6 py-4 border-b border-brand-mid/20 bg-brand-light">
          <div className="flex items-center gap-4 min-w-0">
            {title && (
              <h2 className="text-lg font-bold text-brand-accent font-mono truncate">
                {title}
              </h2>
            )}
          </div>

          <div className="flex items-center gap-2 shrink-0">
            {headerActions}
            <button
              type="button"
              onClick={onClose}
              className="rounded-lg p-1.5 text-brand-mid hover:bg-brand-light hover:text-brand-darkest transition-colors"
              aria-label={t('pages.common.close_drawer')}
            >
              <X className="h-5 w-5" />
            </button>
          </div>
        </div>

        {/* Subtitle */}
        {subtitle && (
          <div className="shrink-0 px-6 py-3 border-b border-brand-mid/20 bg-brand-light">
            <div className="text-sm text-brand-mid">{subtitle}</div>
          </div>
        )}

        {/* Tabs Navigation */}
        {tabs.length > 1 && (
          <div className="shrink-0 flex border-b border-brand-mid/20 bg-brand-light px-6">
            {tabs.map((tab) => (
              <button
                key={tab.id}
                type="button"
                onClick={() => setActiveTab(tab.id)}
                className={`relative px-4 py-3 text-sm font-medium transition-colors ${
                  activeTab === tab.id
                    ? 'text-brand-accent after:absolute after:bottom-0 after:left-0 after:right-0 after:h-0.5 after:bg-brand-accent'
                    : 'text-brand-mid hover:text-brand-darkest'
                }`}
              >
                {tab.label}
              </button>
            ))}
          </div>
        )}

        {/* Tab Content — only render when open so child components don't mount eagerly */}
        <div className="flex-1 overflow-y-auto px-6 py-6">
          {isOpen && activeComponent}
        </div>
      </div>
    </>
  );
}
