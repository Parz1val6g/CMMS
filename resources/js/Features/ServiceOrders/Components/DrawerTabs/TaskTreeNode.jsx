import { memo } from 'react';
import { ChevronRight, ChevronDown } from 'lucide-react';
import { t } from '@/utils/i18n';

/* ── Status badge mapping ───────────────────────────────────── */
const STATUS_STYLE = {
  pending:     'bg-yellow-100 text-yellow-700 border border-yellow-200',
  in_progress: 'bg-blue-100  text-blue-700  border border-blue-200',
  completed:   'bg-green-100 text-green-700  border border-green-200',
  cancelled:   'bg-red-100   text-red-700    border border-red-200',
  blocked:     'bg-orange-100 text-orange-700 border border-orange-200',
};

/* ── Type icon map ─────────────────────────────────────────── */
const NODE_ICON = {
  task:      'bg-brand-accent/15 text-brand-accent',
  mini_task: 'bg-brand-mid/20  text-brand-mid',
};

/* ── Recursive tree node ────────────────────────────────────── */
function TaskTreeNode({ node, depth = 0, expandedIds, onToggle }) {
  const { item, children } = node;
  const hasChildren = children?.length > 0;
  const isExpanded = expandedIds.has(item.id);
  const isSubTask = depth > 0;

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
            className="absolute inset-y-0 border-l-2 border-brand-mid/20"
            style={{ left: `${10 + (depth - 1) * 24}px` }}
          />
        )}

        {/* L-shaped corner connector for sub-tasks */}
        {isSubTask && (
          <div
            className="absolute top-0 w-3 border-l-2 border-b-2 border-brand-mid/20 rounded-bl-lg"
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
                <span className="h-1 w-1 rounded-full bg-brand-mid" />
              ) : (
                <span className="h-1.5 w-1.5 rounded-full bg-brand-mid" />
              )}
            </span>
          )}
        </span>

        {/* Type badge */}
        <span className={`shrink-0 inline-flex items-center px-1.5 py-0.5 text-[10px] font-mono font-semibold rounded ${NODE_ICON[item._type] || 'bg-brand-light text-brand-mid'}`}>
          {item._type === 'task' ? 'T' : 'MT'}
        </span>

        {/* Title */}
        <span className="flex-1 min-w-0 text-sm font-medium text-brand-darkest truncate group-hover:text-brand-darkest transition-colors">
          {item.name || item.description || '—'}
        </span>

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
          {item.status?.replace(/_/g, ' ') || t('pages.service_orders.tasks_tree.status_pending')}
        </span>
      </div>

      {/* ══ Children (recursive) ════════════════════════════════ */}
      {hasChildren && isExpanded && (
        <div className="relative">
          {/* Vertical trunk line connecting parent's children chain */}
          <div
            className="absolute top-0 bottom-0 border-l-2 border-brand-mid/20"
            style={{ left: `${10 + depth * 24}px` }}
          />
          {children.map((child) => (
            <TaskTreeNode
              key={child.item.id}
              node={child}
              depth={depth + 1}
              expandedIds={expandedIds}
              onToggle={onToggle}
            />
          ))}
        </div>
      )}
    </>
  );
}

export default memo(TaskTreeNode);
