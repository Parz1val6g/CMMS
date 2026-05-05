import { useState, useEffect, useCallback, useMemo } from 'react';
import { router } from '@inertiajs/react';
import { Loader2, AlertCircle } from 'lucide-react';
import TaskTreeNode from './TaskTreeNode';
import buildTaskTree from '../../Utils/buildTaskTree';

/* ── Status label helpers ───────────────────────────────────── */
const taskStatusLabel = {
  pending: 'Pending',
  in_progress: 'In Progress',
  completed: 'Completed',
  cancelled: 'Cancelled',
  blocked: 'Blocked',
};

/**
 * SOTasksTree — Hierarchical tasks tree for a Service Order.
 *
 * Fetches tasks + mini-tasks from the API, transforms the flat
 * lists into a nested tree, and renders it with expand/collapse.
 *
 * @param {Object}   props
 * @param {string}   props.serviceOrderId   - The SO UUID
 * @param {string}   [props.taskApiUrl]     - Override tasks API base
 * @param {string}   [props.miniTaskApiUrl] - Override mini-tasks API base
 */
export default function SOTasksTree({
  serviceOrderId,
  workflowType,
  onInitiateReturn,
  taskApiUrl = '/api/tasks',
  miniTaskApiUrl = '/api/mini-tasks',
}) {
  const [tasks, setTasks] = useState([]);
  const [miniTasksMap, setMiniTasksMap] = useState({});
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState(null);

  /* ── Expanded set (node ids) ──────────────────────────────── */
  const [expandedIds, setExpandedIds] = useState(new Set());

  /* ── Fetch tasks ──────────────────────────────────────────── */
  useEffect(() => {
    if (!serviceOrderId) {
      setLoading(false);
      return;
    }

    let cancelled = false;
    setLoading(true);
    setError(null);

    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    fetch(`${taskApiUrl}?service_order_id=${serviceOrderId}&per_page=100`, {
      headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
    })
      .then((r) => { if (!r.ok) throw new Error(`HTTP ${r.status}`); return r.json(); })
      .then((body) => {
        if (cancelled) return;
        const list = body.data ?? body ?? [];
        setTasks(list);

        // Auto-expand root tasks
        setExpandedIds(new Set(list.map((t) => t.id)));
      })
      .catch((err) => {
        if (cancelled) return;
        setError(err.message);
      })
      .finally(() => { if (!cancelled) setLoading(false); });

    return () => { cancelled = true; };
  }, [serviceOrderId, taskApiUrl]);

  /* ── Fetch mini-tasks for each task (parallel) ────────────── */
  useEffect(() => {
    if (tasks.length === 0) return;

    let cancelled = false;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    Promise.all(
      tasks.map((task) =>
        fetch(`${miniTaskApiUrl}?task_id=${task.id}&per_page=100`, {
          headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' },
        })
          .then((r) => (r.ok ? r.json() : { data: [] }))
          .then((body) => ({ taskId: task.id, miniTasks: body.data ?? [] }))
          .catch(() => ({ taskId: task.id, miniTasks: [] }))
      )
    ).then((results) => {
      if (cancelled) return;
      const map = {};
      for (const { taskId, miniTasks } of results) {
        map[taskId] = miniTasks;
      }
      setMiniTasksMap(map);
    });

    return () => { cancelled = true; };
  }, [tasks, miniTaskApiUrl]);

  /* ── Build tree from flat data ─────────────────────────────── */
  const tree = useMemo(() => {
    if (tasks.length === 0) return [];

    // Normalize mini-tasks into the same flat array with parent_id
    const flat = [];

    for (const t of tasks) {
      flat.push({ ...t, _type: 'task' });
    }

    for (const mtList of Object.values(miniTasksMap)) {
      for (const mt of mtList) {
        flat.push({ ...mt, _type: 'mini_task', parent_id: mt.task_id });
      }
    }

    return buildTaskTree(flat, { parentKey: 'parent_id' });
  }, [tasks, miniTasksMap]);

  /* ── Check if return task already exists in tree ──────────── */
  const hasReturnTask = useMemo(() => {
    return tasks.some((t) => t.name === 'Devolução de Equipamento');
  }, [tasks]);

  /* ── Initiate return via API ────────────────────────────────── */
  const handleInitiateReturn = useCallback((soId) => {
    router.post(`/api/service-orders/${soId}/initiate-return`, {}, {
      onSuccess: () => {
        window.location.reload();
      },
      onError: () => {
        // Silently ignore — parent component handles error state
      },
    });
  }, []);

  /* ── Toggle expand/collapse ────────────────────────────────── */
  const handleToggle = useCallback((id) => {
    setExpandedIds((prev) => {
      const next = new Set(prev);
      if (next.has(id)) next.delete(id);
      else next.add(id);
      return next;
    });
  }, []);

  /* ── Loading state ────────────────────────────────────────── */
  if (loading) {
    return (
      <div className="flex items-center justify-center h-40 text-slate-400 gap-2">
        <Loader2 className="h-5 w-5 animate-spin" />
        <span className="text-sm">Loading tasks…</span>
      </div>
    );
  }

  /* ── Error state ──────────────────────────────────────────── */
  if (error) {
    return (
      <div className="flex items-center justify-center h-40 text-red-400 gap-2">
        <AlertCircle className="h-5 w-5" />
        <span className="text-sm">Failed to load tasks: {error}</span>
      </div>
    );
  }

  /* ── Empty state ──────────────────────────────────────────── */
  if (tree.length === 0) {
    return (
      <div className="flex items-center justify-center h-40 text-slate-500">
        <p className="text-sm">No tasks assigned to this service order.</p>
      </div>
    );
  }

  /* ── Summary bar ──────────────────────────────────────────── */
  const totalTasks = tasks.length;
  const completedTasks = tasks.filter((t) => t.status === 'completed').length;
  const subTaskCount = tree.reduce((sum, n) => sum + countDescendants(n), 0);

  return (
    <div>
      {/* Summary */}
      <div className="flex items-center justify-between mb-4 px-4 py-2 rounded-lg bg-slate-800/40 border border-slate-700/50">
        <span className="text-xs text-slate-400">
          <span className="font-semibold text-slate-300">{totalTasks}</span> tasks
          {completedTasks > 0 && (
            <>
              {' · '}
              <span className="font-semibold text-green-400">{completedTasks}</span> completed
            </>
          )}
        </span>
        <span className="text-xs text-slate-500">
          {totalTasks} task{totalTasks !== 1 ? 's' : ''}
          {subTaskCount > 0 && (
            <> · {subTaskCount} sub-task{subTaskCount !== 1 ? 's' : ''}</>
          )}
        </span>
      </div>

      {/* Tree */}
      <div className="rounded-lg border border-slate-700/50 overflow-hidden divide-y divide-slate-700/30">
        {tree.map((node) => (
          <TaskTreeNode
            key={node.item.id}
            node={node}
            depth={0}
            expandedIds={expandedIds}
            onToggle={handleToggle}
            workflowType={workflowType}
            onInitiateReturn={handleInitiateReturn}
            hasReturnTask={hasReturnTask}
          />
        ))}
      </div>
    </div>
  );
}

/* ── Count all descendant nodes ─────────────────────────────── */
function countDescendants(node) {
  if (!node.children?.length) return 0;
  return node.children.reduce((sum, c) => sum + 1 + countDescendants(c), 0);
}
