import { useEffect, useRef } from 'react';

const ALL_SHORTCUTS = [
  { keys: ['Ctrl', 'Shift', 'K'], label: 'Selecionar / Trocar função', can: null },
  { keys: ['Ctrl', 'Shift', 'D'], label: 'Dashboard', can: 'viewDashboard' },
  { keys: ['Ctrl', 'Shift', 'S'], label: 'Definições', can: 'viewSettings' },
  { keys: ['Ctrl', 'Shift', 'H'], label: 'Mostrar / Esconder atalhos', can: null },
];

export default function ShortcutsHelp({ open, onClose, onToggle, can = {} }) {
  // Filter shortcuts based on user permissions (null = always visible)
  const shortcuts = ALL_SHORTCUTS.filter((s) => s.can === null || can[s.can]);

  // ── Ctrl+Shift+H toggle (even when closed) ──
  const openRef = useRef(open);
  openRef.current = open;
  const onToggleRef = useRef(onToggle);
  onToggleRef.current = onToggle;

  useEffect(() => {
    const handler = (e) => {
      if (!e.ctrlKey || !e.shiftKey) return;
      if (e.key !== 'H' && e.code !== 'KeyH') return;
      const tag = e.target?.tagName?.toLowerCase();
      if (tag === 'input' || tag === 'textarea' || tag === 'select' || e.target?.isContentEditable) return;
      e.preventDefault();
      onToggleRef.current?.();
    };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, []);

  // ── Escape to close ──
  useEffect(() => {
    if (!open) return;
    const handler = (e) => { if (e.key === 'Escape') onClose(); };
    window.addEventListener('keydown', handler);
    return () => window.removeEventListener('keydown', handler);
  }, [open, onClose]);

  if (!open) return null;

  return (
    <div
      className="fixed inset-0 z-[350] flex items-center justify-center bg-black/40 backdrop-blur-sm px-4"
      onClick={(e) => { if (e.target === e.currentTarget) onClose(); }}
    >
      <div className="w-full max-w-sm rounded-2xl border border-white/[0.08] bg-brand-darkest shadow-[0_25px_60px_rgba(0,0,0,0.5)] overflow-hidden">
        <div className="flex items-center justify-between px-4 py-3 border-b border-white/[0.06]">
          <h2 className="text-sm font-semibold text-brand-white">Atalhos de teclado</h2>
          <kbd className="text-[10px] text-brand-mid/40">Esc para fechar</kbd>
        </div>

        <div className="px-2 py-2">
          {shortcuts.map((shortcut) => (
            <div
              key={shortcut.label}
              className="flex items-center justify-between px-3 py-2 rounded-lg"
            >
              <span className="text-sm text-brand-mid">{shortcut.label}</span>
              <span className="inline-flex items-center gap-1">
                {shortcut.keys.map((key, i) => (
                  <span key={i}>
                    <kbd className="inline-flex items-center justify-center rounded-md border border-white/[0.08] bg-white/[0.04] px-1.5 py-0.5 text-[11px] font-medium text-brand-mid/70 min-w-[20px]">
                      {key}
                    </kbd>
                    {i < shortcut.keys.length - 1 && (
                      <span className="text-[10px] text-brand-mid/30 mx-0.5">+</span>
                    )}
                  </span>
                ))}
              </span>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
