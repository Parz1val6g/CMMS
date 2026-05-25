import { AlertTriangle, Clock, ClipboardList, ListChecks, ScrollText, ChevronRight } from 'lucide-react';

const REASON_LABEL = {
  high_priority:    'Alta Prioridade',
  stale_order:      'Sem progresso',
  stale_task:       'Sem progresso',
  awaiting_approval:'Aguarda Aprovação',
  open_work_log:    'Work Log Aberto',
};

const REASON_COLOR = {
  high_priority:    'bg-red-50 text-red-700 ring-1 ring-inset ring-red-200',
  stale_order:      'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-200',
  stale_task:       'bg-yellow-50 text-yellow-700 ring-1 ring-inset ring-yellow-200',
  awaiting_approval:'bg-indigo-50 text-indigo-700 ring-1 ring-inset ring-indigo-200',
  open_work_log:    'bg-orange-50 text-orange-700 ring-1 ring-inset ring-orange-200',
};

const TYPE_ICON = {
  order:    ClipboardList,
  task:     ListChecks,
  work_log: ScrollText,
};

function AttentionRow({ item, onClick }) {
  const Icon = TYPE_ICON[item.type] ?? ClipboardList;
  return (
    <button
      type="button"
      onClick={() => onClick(item)}
      className="group flex w-full items-center gap-3 rounded-lg px-3 py-2.5 text-left transition-colors hover:bg-brand-light"
    >
      <Icon size={15} className="shrink-0 text-brand-mid" />
      <div className="min-w-0 flex-1">
        <span className="block truncate text-sm font-semibold text-brand-darkest font-mono">
          {item.reference}
        </span>
        {item.location && (
          <span className="block truncate text-xs text-brand-mid">{item.location}</span>
        )}
      </div>
      <div className="flex shrink-0 items-center gap-2">
        <span className={`rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide ${REASON_COLOR[item.reason]}`}>
          {REASON_LABEL[item.reason]}
        </span>
        <span className="text-xs text-brand-mid tabular-nums">{item.age_label}</span>
        <ChevronRight size={13} className="text-brand-mid/40 group-hover:text-brand-mid transition-colors" />
      </div>
    </button>
  );
}

export default function NeedsAttention({ items = [], onItemClick }) {
  return (
    <div className="flex h-full flex-col overflow-hidden rounded-xl border border-brand-mid/20 bg-brand-white shadow-sm">
      <div className="flex items-center gap-2 border-b border-brand-mid/20 px-4 py-3">
        <AlertTriangle size={15} className="text-orange-500" />
        <h2 className="text-sm font-semibold text-brand-darkest">Requer Atenção</h2>
        {items.length > 0 && (
          <span className="ml-auto rounded-full bg-orange-100 px-2 py-0.5 text-xs font-semibold text-orange-700">
            {items.length}
          </span>
        )}
      </div>
      <div className="flex-1 overflow-y-auto p-2">
        {items.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-10 text-brand-mid">
            <Clock size={28} className="mb-2 opacity-30" />
            <p className="text-sm">Nenhum item crítico</p>
          </div>
        ) : (
          <div className="space-y-0.5">
            {items.map((item, i) => (
              <AttentionRow key={`${item.type}-${item.id}-${i}`} item={item} onClick={onItemClick} />
            ))}
          </div>
        )}
      </div>
    </div>
  );
}
