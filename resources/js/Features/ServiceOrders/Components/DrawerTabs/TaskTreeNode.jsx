import { memo } from 'react';
import { ChevronRight, ChevronDown, RotateCcw } from 'lucide-react';

/* ── Status badge mapping ───────────────────────────────────── */
const STATUS_STYLE = {
  pending:     'bg-yellow-500/20 text-yellow-300 border border-yellow-500/40',
  in_progress: 'bg-blue-500/20  text-blue-300  border border-blue-500/40',
  completed:   'bg-green-500/20 text-green-300  border border-green-500/40',
  cancelled:   'bg-red-500/20   text-red-300    border border-red-500/40',
  blocked:     'bg-orange-500/20 text-orange-300 border border-orange-500/40',
};

/* ── Type icon map ─────────────────────────────────────────── */
const NODE_ICON = {
  task:      'bg-brand-accent/15 text-brand-accent',
  mini_task: 'bg-slate-600/40  text-brand-mid',
};

/* ── Recursive tree node ────────────────────────────────────── */
function TaskTreeNode({ node, depth = 0, expandedIds, onToggle, workflowType, onInitiateReturn, hasReturnTask }) {
  const { item, children } = node;
  const hasChildren = children?.length > 0;
  const isExpanded = expandedIds.has(item.id);
  const isSubTask = depth > 0;

  const showReturnBtn = workflowType === 'loan'
    && item._type === 'task'
    && item.name === 'Empréstimo de Equipamento'
    && item.status === 'completed'
    && !hasReturnTask;

  return (
    <>
      {/* ══ Row ═════════════════════════════════════════════════ */}
      <div
        className={`
          group relative flex items-center gap-2 px-4 py-2.5 cursor-pointer transition-colors
          ${depth === 0 ? 'bg-brand-light' : 'bg-brand-white'}
          hover:bg-brand-light
        `}
        style={{ paddingLeft: `${16 + depth * 24}px` }}
        onClick={() => hasChildren && onToggle(item.id)}
      >
        {/* Vertical guide line (visible on child containers) */}
        {isSubTask && (
          <div
            className="absolute inset-y-0 border-l-2 border-slate-700/50"
            style={{ left: `${10 + (depth - 1) * 24}px` }}
          />
        )}

        {/* L-shaped corner connector for sub-tasks */}
        {isSubTask && (
          <div
            className="absolute top-0 w-3 border-l-2 border-b-2 border-slate-700/50 rounded-bl-lg"
            style={{
              left: `${10 + (depth - 1) * 24}px`,
              height: '50%',
            }}
          />
        )}

        {/* Expand/Collapse Chevron */}
        <span className="shrink-0 w-5 h-5 flex items-center justify-center">
          {hasChildren ? (
            isExpanded ? (
              <ChevronDown className="h-4 w-4 text-brand-mid group-hover:text-brand-accent transition-colors" />
            ) : (
              <ChevronRight className="h-4 w-4 text-brand-mid group-hover:text-brand-accent transition-colors" />
            )
          ) : (
            <span className="h-4 w-4 flex items-center justify-center">
              {isSubTask ? (
                /* Sub-task bullet */
                <span className="h-1 w-1 rounded-full bg-slate-500" />
              ) : (
                <span className="h-1.5 w-1.5 rounded-full bg-slate-600" />
              )}
            </span>
          )}
        </span>

        {/* Type badge */}
        <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded ${NODE_ICON[item._type] || 'bg-slate-700 text-brand-mid'}`}>
          {item._type === 'task' ? 'T' : 'MT'}
        </span>

        {/* Title */}
        <span className="flex-1 min-w-0 text-sm font-medium text-brand-darkest truncate group-hover:text-brand-darkest transition-colors">
          {item.name || item.description || '—'}
        </span>

        {/* Initiate Return button (loan workflow only) */}
        {showReturnBtn && onInitiateReturn && (
          <button
            type="button"
            onClick={(e) => { e.stopPropagation(); onInitiateReturn(item.service_order_id); }}
            className="shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-semibold rounded bg-brand-accent hover:bg-brand-accent/90 text-white transition-colors shadow-sm"
          >
            <RotateCcw className="h-3.5 w-3.5" />
            Iniciar Devolução
          </button>
        )}

        {/* Manager / Assignee */}
        {item.manager?.name && (
          <span className="shrink-0 text-xs text-brand-mid truncate max-w-[120px] hidden sm:inline">
            {item.manager.name}
          </span>
        )}
        {item.supervisor?.name && (
          <span className="shrink-0 text-xs text-brand-mid truncate max-w-[120px] hidden sm:inline">
            {item.supervisor.name}
          </span>
        )}

        {/* Status Badge */}
        <span
          className={`shrink-0 inline-flex items-center px-2 py-0.5 text-xs font-semibold rounded-full ${
            STATUS_STYLE[item.status] || STATUS_STYLE.pending
          }`}
        >
          {item.status?.replace(/_/g, ' ') || 'pending'}
        </span>
      </div>

      {/* ══ Children (recursive) ════════════════════════════════ */}
      {hasChildren && isExpanded && (
        <div className="relative">
          {/* Vertical trunk line connecting parent's children chain */}
          <div
            className="absolute top-0 bottom-0 border-l-2 border-slate-700/50"
            style={{ left: `${10 + depth * 24}px` }}
          />
          {children.map((child) => (
            <TaskTreeNode
              key={child.item.id}
              node={child}
              depth={depth + 1}
              expandedIds={expandedIds}
              onToggle={onToggle}
              workflowType={workflowType}
              onInitiateReturn={onInitiateReturn}
              hasReturnTask={hasReturnTask}
            />
          ))}
        </div>
      )}
    </>
  );
}

export default memo(TaskTreeNode);
